<?php

require_once 'WorkflowFactory.php';

class Job extends WorkflowFactory
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'jobs';

  private $tasks = array();

  private $sortOrder = array();

  protected static $_contactsField = 'partiesInvolved';

  public function __construct(array $data)
  {
    parent::__construct();
    $this->_initialize($data);
  }

  public function _initialize(array $data)
  {
    parent::_initialize($data); // TODO: Change the autogenerated stub
    $this->sortOrder = $this->getValue('sortOrder');
  }

  public function addTask(Task $task){
    if($this->hasId()){
      $task->setValues(array('jobId' => $this->id()))->save();
      $this->tasks[] = $task;
      $this->sort();
      return $this;
    } else {
      throw new Exception('Tasks can not be added without an _id');
    }
  }

  public function acknowledgeTask(Task $task){
    $this->tasks[] = $task;
    if(!in_array((string) $task->id(), $this->sortOrder)){
      $this->sortOrder[] = $task->id();
    }
  }

  public function sort(){
    $reorderedTasks = array();
    foreach((array) $this->sortOrder as $order => $id){
      foreach($this->tasks as $i => $task){
        if((string) $task->id() == (string) $id) $reorderedTasks[] = $task;
      }
    }
    $this->tasks = $reorderedTasks;
    return $this;
  }

  /**
   * Insert given task after the provided task
   * @param $taskId ID of the task being added
   * @param $afterTaskId The id of the task that will precede the given task
   */
  public function insertTaskAfter($taskId, $afterTaskId){
    $position = array_search((string) $afterTaskId, $this->sortOrder);
    array_splice($this->sortOrder, ($position+1), 0, array( _id($taskId)));
  }

  public function saveSortOrder(){
    if(json_encode($this->sortOrder) != json_encode($this->getValue('sortOrder'))){
      self::Update($this->id(), array(
        'sortOrder' => $this->sortOrder
      ));
    }
  }

  public function getAllTasks($grouped = false){
    if($this->hasId()){
      if(!empty($this->tasks)) return $this->tasks;
      else {
        $tasks = self::CI()->mdb->where('jobId', $this->id())->get(Task::CollectionName());
        foreach($tasks as $i => $task) $this->acknowledgeTask(new Task($task));
        $this->saveSortOrder();
        $this->sort();
        return $this->tasks;
      }
    } else {
      throw new Exception('Tasks can not be pulled without an _id');
    }
  }

  /**
   * Return all tasks that can be executed for the current job
   * @param bool $grouped Whether or not to return in an associated array grouped by "taskGroup"
   * @return array List of Task objects
   */
  public function getActionableTasks($grouped = false){
    $actionable = array();
    foreach($this->getAllTasks() as $i => $task){
      if($task->isActionable()) {
        if($grouped){
          if(!isset($actionable[$task->getValue('taskGroup')])) $actionable[$task->getValue('taskGroup')] = array();
          $actionable[$task->getValue('taskGroup')][] = $task;
        } else {
          $actionable[] = $task;
        }
      }
    }
    return $actionable;
  }

  /**
   * Return all tasks that can be displayed executed for the current job
   * @param bool $grouped Whether or not to return in an associated array grouped by "taskGroup"
   * @return array List of Task objects
   */
  public function getShowableTasks($grouped = false){
    $actionable = array();
    foreach($this->getAllTasks() as $i => $task){
      if($task->isShowable()) {
        if($grouped){
          if(!isset($actionable[$task->getValue('taskGroup')])) $actionable[$task->getValue('taskGroup')] = array();
          $actionable[$task->getValue('taskGroup')][] = $task;
        } else {
          $actionable[] = $task;
        }
      }
    }
    return $actionable;
  }

  /**
   * Return all tasks that can be displayed executed for the current job
   * @param bool $grouped Whether or not to return in an associated array grouped by "taskGroup"
   * @return array List of Task objects
   */
  public function getClientViewableTasks($grouped = false){
    $actionable = array();
    foreach($this->getAllTasks() as $i => $task){
      if($task->isClientViewable()) {
        if($grouped){
          if(!isset($actionable[$task->getValue('taskGroup')])) $actionable[$task->getValue('taskGroup')] = array();
          $actionable[$task->getValue('taskGroup')][] = $task;
        } else {
          $actionable[] = $task;
        }
      }
    }
    return $actionable;
  }

  public static function CompletionPercentage(array $taskSet){
    $completedCount = 0;
    $taskCount = 0;
    foreach($taskSet as $task) {
      $taskCount ++;
      if($task->isComplete()) $completedCount++;
    }
    return round(($completedCount/$taskCount) * 100);
  }

  public function getContacts(){
    $contacts = $this->getValue(self::$_contactsField);
    $roles = array();
    $contactIds = array();
    foreach($contacts as $contact) {
      if(isset($contact['contactId'])) $contactIds[] = $contact['contactId'];
      $roles[(string) $contact['contactId']] = $contact['role'];
    }
    $contacts = Contact::GetByIds($contactIds);
    foreach($contacts as $i => $contact) if(isset($roles[(string) $contact->id()])) $contacts[$i]->setValue('role', $roles[(string) $contact->id()]);

    return $contacts;
  }

  /**
   * Get task object from tasks list instead of calling from db
   * @param $id
   * @return bool|mixed
   */
  public function getTaskById($id){
    foreach($this->tasks as $task) if((string) $task->id() == (string) $id) return $task;
    return false;
  }

  public function getMeta(){
    return $this->getValue('meta');
  }

  public function addContact(Contact $contact){

  }

  public function removeContact(Contact $contact){

  }

  public function getUrl(){
    return site_url('jobs/' . $this->id() . '/tasks');
  }

  public static function Get($id){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    return new $class($record);
  }

  public function saveAsTemplate($name, $description = "", $group = "General"){
    $this->loadWorkflow();
    $newWorkflow = array(
      'name' => $name,
      'description' => $description,
      'organizationId' => UserSession::Get_Organization()->id(),
      'group' => $group ? $group : $this->workflow->getValue('group'),
      'roles' => $this->workflow->getValue('roles'),
      'status' => 'active',
      'taskTemplates' => array(),
      'accessibility' => array(),
      'metaFields' => $this->workflow->getValue('metaFields'),
    );

    // Add Task Templates
    $tasks = $this->getAllTasks();
    foreach($tasks as $task) $newWorkflow['taskTemplates'][] = $task->getValue('taskTemplateId');
    $id = Workflow::Create($newWorkflow);
    if($id){
      $newWorkflow['_id'] = $id;
      return new Workflow($newWorkflow);
    }
    return false;
  }


}