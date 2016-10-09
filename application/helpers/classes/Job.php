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

  protected static $_contactsField = 'partiesInvolved';

  public function __construct(array $data, $fullLoad = false)
  {
    parent::__construct();
    $this->_initialize($data, $fullLoad);
  }

  public function addTask(Task $task){
    if($this->hasId()){
      $task->setValues(array('jobId' => $this->id()))->save();
      $this->tasks[] = $task;
      return $this;
    } else {
      throw new Exception('Tasks can not be added without an _id');
    }
  }

  public function getAllTasks(){
    if($this->hasId()){
      if(!empty($this->tasks)) return $this->tasks;
      else {
       $tasks = self::CI()->mdb->where('jobId', $this->id())->get(Task::CollectionName());
       foreach($tasks as $i => $task) $this->tasks[$i] = new Task($task);
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

  public function getMeta(){
    return $this->getValue('meta');
  }

  public function addContact(Contact $contact){

  }

  public function removeContact(Contact $contact){

  }

  public static function Get($id){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    return new $class($record);
  }


}