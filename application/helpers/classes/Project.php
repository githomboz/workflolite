<?php

require_once 'Meta.php';
require_once 'Task2.php';
require_once 'WorkflowFactory.php';
require_once 'ScriptEngine.php';

class Project extends WorkflowFactory
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'projects';

  private $taskTemplates = null;

  private $taskTemplateFields = array();

  private $currentTemplate = null;

  private $templateFields = array();

  private $tasks = array();

  private $sciptEngine = null;

  //private $sortOrder = array();

  /**
   * Meta class
   * @var null Meta
   */
  private $meta = null;

  private $taskCache = null;

  protected static $_contactsField = 'partiesInvolved';

  public function __construct(array $data)
  {
    parent::__construct();
    $this->_initialize($data);
    $this->meta = new Meta($data['meta'], $this);
  }

  public function _initialize(array $data)
  {
    parent::_initialize($data); // TODO: Change the auto-generated stub
    //$this->sortOrder = $this->getValue('sortOrder');
    $this->getAllTasks();
    $this->cacheTasksBeforeUpdates();
  }

  public function ScriptEngine(){
    if(!isset($this->sciptEngine)) $this->sciptEngine = new ScriptEngine($this->id());
    return $this->sciptEngine;
  }

  public function run(){
    $this->ScriptEngine()->run();
  }

  /**
   * Before any changes occur to tasks, record tasks as they currently are
   */
  public function cacheTasksBeforeUpdates(){
    $taskCache = array();
    foreach($this->tasks as $task) {
      $taskSorted = $task->getCurrent();
      ksort($taskSorted);
      $taskCache[] = $taskSorted;
    }
    $this->taskCache = json_encode($taskCache);
  }

  public function addTask(Task $task){
    if($this->hasId()){
      $task->setValues(array('jobId' => $this->id()))->save();
      $this->tasks[] = $task;
      //$this->sort();
      return $this;
    } else {
      throw new Exception('Tasks can not be added without an _id');
    }
  }

  public function saveScript(ScriptEngine $script){
    return self::Update($this->id(), ['script' => $script->getStepsRaw()]);
  }

  public function addNote($noteData){
    $success = false;
    $notes = $this->getNotes();
    $duplicate = false;
    $ids = array();
    if(!empty($notes)){
      $notesJSON = array();
      foreach($notes as $i => $note) {
        unset($note['datetime']);
        if(!empty($note) && !isset($note['id'])) {
          do {
            // generate id
            $id = _generate_id(6);
          } while(in_array($id, $ids)); // Check if unique

          // set id
          $notes[$i]['id'] = $id;
        }
        ksort($note);
        unset($note['id']);
        $notesJSON[] = json_encode($note);
        $ids[] = $notes[$i]['id'];
      }

      $testData = $noteData;
      do {
        // generate id
        $noteData['id'] = _generate_id(6);
      } while(in_array($noteData['id'], $ids)); // Check if unique

      unset($testData['datetime']);
      ksort($testData);
      $testData = json_encode($testData);

      foreach($notesJSON as $i => $noteJSON) {
        //var_dump('$testData: ' . $testData, '$noteJSON: '. $noteJSON);
        if($noteJSON == $testData) $duplicate = true;
      }

    }

    if(!$duplicate){
      $notes[] = $noteData;
      $success = $this->setNotesArray($notes);
    }

    return $noteData['id'];
  }

  public function deleteNote($noteId){
    $notes = $this->getNotes();
    foreach($notes as $i => $note) {
      if($note['id'] == $noteId) {
        unset($notes[$i]);
      }
    }
    return $this->setNotesArray($notes);
  }

  public function getNotes(){
    // Check if id is set
    $changed = false;
    $notes = $this->getValue('notes');
    $ids = array();
    if(!empty($notes)){
      foreach($notes as $i => $note) {
        if(!empty($note) && !isset($note['id'])){
          do {
            // generate id
            $notes[$i]['id'] = _generate_id(6);
          } while(in_array($notes[$i]['id'], $ids)); // Check if unique
          $changed = true;
        }
      }
    }
    if($changed) $this->setNotesArray($notes);
    return $notes;
  }

  public function setNotesArray($notes){
    sortBy('datetime', $notes, 'desc');
    return $this->setValue('notes', $notes)->save('notes');
  }

  public function setStatus($status){
    return $this->setValue('status', $status)->save('status');
  }

  public function searchNotes($term){
    $results = array();
    $inResults = array();
    foreach($this->getNotes() as $i => $note){
      if(strpos(strtolower($note['content']), strtolower($term)) !== false) {
        $results[] = $note;
        $inResults[] = json_encode($note);
      }
      if(!empty($note['tags'])){
        foreach($note['tags'] as $t => $tag){
          if(strpos(strtolower($tag), strtolower($term)) !== false && !in_array(json_encode($note), $inResults)) $results[] = $note;
        }
      }
    }
    return $results;
  }

  public function getNoteTags(){
    $results = array();
    foreach((array) $this->getNotes() as $i => $note){
      if(!empty($note['tags'])){
        foreach($note['tags'] as $t => $tag){
          if(!isset($results[$tag])) $results[$tag] = 0;
          $results[$tag] ++;
        }
      }
    }
    arsort($results);
    return $results;
  }

  public function displayDetails(){
    $data = array(
    );

    return $data;
  }

//  public function acknowledgeTask(Task $task){
//    $this->tasks[] = $task;
//    if(!in_array((string) $task->id(), $this->sortOrder)){
//      $this->sortOrder[] = $task->id();
//    }
//  }
//
//  public function sort(){
//    $reorderedTasks = array();
//    foreach((array) $this->sortOrder as $order => $id){
//      foreach($this->tasks as $i => $task){
//        if((string) $task->id() == (string) $id) $reorderedTasks[] = $task;
//      }
//    }
//    $this->tasks = $reorderedTasks;
//    return $this;
//  }
//
//  /**
//   * Insert given task after the provided task
//   * @param $taskId ID of the task being added
//   * @param $afterTaskId The id of the task that will precede the given task
//   */
//  public function insertTaskAfter($taskId, $afterTaskId){
//    $position = array_search((string) $afterTaskId, $this->sortOrder);
//    array_splice($this->sortOrder, ($position+1), 0, array( _id($taskId)));
//  }
//
//  public function saveSortOrder(){
//    if(json_encode($this->sortOrder) != json_encode($this->getValue('sortOrder'))){
//      self::Update($this->id(), array(
//        'sortOrder' => $this->sortOrder
//      ));
//    }
//  }
//
  public function setTask($taskId, $data, $storageScheme = 'STORE_TASK_META_ONLY'){
    $response = array(
      'response' => null,
      'errors' => array()
    );
    /**
     * Fields that are allowed into taskTemplate array
     */
    if(empty($this->taskTemplateFields)) {
      foreach($this->taskTemplates as $taskTemplate) foreach($taskTemplate as $field => $value) {
        if(!in_array($field, $this->taskTemplateFields)) $this->taskTemplateFields[] = $field;
      }
    }

    if(empty($this->templateFields)){
      foreach($this->currentTemplate->getCurrent() as $field => $value) if(!in_array($field, $this->templateFields)) $this->templateFields[] = $field;
    }

    /**
     * Fields that if changed, automatically convert project to custom
     */
    $forceCustomFields = array('name','instructions','organizationId','roles');

    /**
     * Fields that are always stored in the project instance record
     */
    $taskMetaFields = array('startDate','completeDate','status','comments','assigneeId');

    if(isset($data['name'])) unset($data['name']); // Name should be specified; taskName, templateName

    // Get current task
    $currentTask = null;
    foreach($this->tasks as $i => $task) if((string) $task->id() === (string) $taskId) $currentTask = $task->getCurrent();
    // sort task
    ksort($currentTask);
    // Merge in new data
    $task = array_merge($currentTask, $data);
    // Store based on storageScheme
    $taskMeta = $this->getValue('taskMeta');
    foreach($task as $field => $value){
      // Set taskMeta fields
      if(in_array($field, $taskMetaFields)) {
        // Make sure this task id exists in taskMeta before attempting to add fields to it.
        if(!isset($taskMeta[$taskId])) $taskMeta[$taskId] = array();
        $taskMeta[$taskId][$field] = $value;
      }
    }

    switch ($storageScheme) {
      case 'CONVERT_TO_CUSTOM_PROJECT': // Remove templateId and save taskTemplates locally in project
        break;
      case 'CREATE_STORE_TEMPLATE': // Create a new template based upon changes
        break;
      case 'UPDATE_ORIGINAL_TEMPLATE': // This probably shouldn't be done as it renders projects instantiated prior to change
        // incompatible with future instances. @todo: solve this
        break;
      case 'STORE_TASK_META_ONLY': // Only store taskMeta info and discard all other changes
      default:
        $response['response'] = self::Update($this->id(), array('taskMeta' => $taskMeta));
        break;
    }

    // Foreach of the tasks,
    // Check if changes to taskTemplate fields exist
    // Check if changes to taskMeta fields exists
    // Create taskTemplate array
    // Create taskMeta array
    // Apply storage scheme
    return $response;
  }

  public function saveTasks(){


    var_dump($this->tasks);
  }

  public function getAllTasks(){
    // Check if custom or template
    if($this->isCustom()){
      // If custom, return local taskTemplates
      $this->taskTemplates = $this->getValue('taskTemplates');
    } else {
      // If template, get template taskTemplates
      $templateId = $this->getValue('templateId');
      $this->currentTemplate = Template::cacheGet($templateId, $this->getValue('templateVersion'));
      if($this->currentTemplate){
        $this->taskTemplates = $this->currentTemplate->getValue('taskTemplates');
      }
    }
    // Merge in appropriate taskMeta
    $taskMeta = $this->getValue('taskMeta');
    $tasks = array();
    foreach($this->taskTemplates as $i => $taskTemplate){
      if(isset($taskMeta[$taskTemplate['id']])) {
        $task = array_merge($taskTemplate, $taskMeta[$taskTemplate['id']]);
        // Convert to new Task() objects
        $tasks[] = new Task2($task, $this);
      } else {
        $tasks[] = new Task2($taskTemplate, $this);
      }
    }
    // Return tasks array
    $this->tasks = $tasks;
    return $tasks;
  }

  /**
   * Whether or not this is a custom project or based upon a template
   * @return bool
   */
  public function isCustom(){
    $templateId = $this->getValue('templateId');
    return empty($templateId);
  }

  public function getStatuses(){
    $statuses = $this->get('availStatuses');
    if(empty($statuses)){
      return array(
        array(
          'status' => 'new',
          'displayName' => 'New',
          'description' => 'Task has not yet been started'
        ),
        array(
          'status' => 'active',
          'displayName' => 'Active',
          'description' => 'Task has been started'
        ),
        array(
          'status' => 'skipped',
          'displayName' => 'Skipped (N/A)',
          'description' => 'Task has been deemed inapplicable based upon configured dependencies'
        ),
        array(
          'status' => 'force_skipped',
          'displayName' => 'Skipped',
          'description' => 'Task has been explicitly skipped by user'
        ),
        array(
          'status' => 'completed',
          'displayName' => 'Complete',
          'description' => 'Task is complete'
        ),
        array(
          'status' => 'deleted',
          'displayName' => 'Deleted',
          'description' => 'Task has been removed'
        ),
      );
    } else return $statuses;
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

  public function getNextTask(){
    foreach($this->getActionableTasks() as $i => $task){
      return $task;
    }
  }

  public function stats(){
    return self::CompletionStats($this->getShowableTasks());
  }

  public static function CompletionStats(array $taskSet){
    $stats = array(
      'total' => 0,
      'completed' => 0,
      'deleted' => 0,
      'skipped' => 0,
      'forceSkipped' => 0,
      'completionPercentage' => 0,
      'totalEstimatedTime' => 0,
      'completedTime' => 0
    );
    foreach($taskSet as $task) {
      $stats['total'] ++;
      if($task->isComplete()) {
        $stats['completed'] ++;
        $stats['completedTime'] += (float) $task->getValue('estimatedTime');
      }
      if($task->isSkipped()) $stats['skipped'] ++;
      if($task->isForceSkipped()) $stats['forceSkipped'] ++;
      if($task->isDeleted()) $stats['deleted'] ++;
      $stats['totalEstimatedTime'] += (float) $task->getValue('estimatedTime');
    }
    $stats['completionPercentage'] = round(($stats['completed']/$stats['total']) * 100);
    return $stats;
  }

  public static function CompletionPercentage(array $taskSet){
    $stats = self::CompletionStats($taskSet);
    return $stats['completionPercentage'];
  }

  public function getContacts(){
    $contacts = $this->getValue(self::$_contactsField);
    $roles = array();
    $contactIds = array();
    $userIds = array();
    foreach($contacts as $contact) {
      $userType = null;
      if(isset($contact['contactId'])) {
        $contactIds[] = $contact['contactId'];
        $userType = 'contactId';
      }
      if(isset($contact['userId'])) {
        $userIds[] = $contact['userId'];
        $userType = 'userId';
      }
      $roles[(string) $contact[$userType]] = $contact['role'];
    }
    $contacts = Contact::GetByIds($contactIds);
    $users = User::GetByIds($userIds);
    foreach($contacts as $i => $contact) if(isset($roles[(string) $contact->id()])) $contacts[$i]->setValue('role', $roles[(string) $contact->id()]);
    foreach($users as $i => $user) if(isset($roles[(string) $user->id()])) $users[$i]->setValue('role', $roles[(string) $user->id()]);

    return array_merge($contacts, $users);
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

  public function getCurrentTask(){
    $tasks = $this->getActionableTasks();
    foreach($tasks as $task) if(!$task->isComplete()) return $task;
  }
  
  public function getMeta(){
    return $this->meta->getAll();
  }

  public function getRawMeta(){
    return $this->getValue('meta');
  }

  public function addContactById($contact_or_user_id, $role, $isClient = false, $isContact = true){
    $contactsField = 'partiesInvolved';
    $contacts = $this->getValue($contactsField);
    $contacts = array_values($contacts);
    $userType = $isContact ? 'contactId' : 'userId';
    $contacts[] = array(
      $userType => _id($contact_or_user_id),
      'role' => $role,
      'isClient' => (bool) $isClient,
    );
    $this->setValue($contactsField, $contacts)->save($contactsField);
    return $this;
  }

  /**
   * Check if the passed id is a contact of $this job
   * @param $contact_or_user_id
   * @param bool $isContact
   * @return bool
   */
  public function isContact($contact_or_user_id, $isContact = true){
    $contactsField = 'partiesInvolved';
    $contacts = $this->getValue($contactsField);
    $userType = $isContact ? 'contactId' : 'userId';
    foreach($contacts as $contact) if((string) $contact[$userType] == (string) $contact_or_user_id) return true;
    return false;
  }

  public function removeContact($contactId){
    $save = false;
    $contactsField = 'partiesInvolved';
    $contacts = $this->getValue($contactsField);
    foreach($contacts as $i => $contact){
      if($contact['contactId'] == _id($contactId)) {
        $save = true;
        unset($contacts[$i]);
      }
    }
    if($save){
      $this->setValue($contactsField, $contacts)->save($contactsField);
    }
    return $this;
  }

  public function updateContactRole($contactId, $role){
    $save = false;
    $contactsField = 'partiesInvolved';
    $contacts = $this->getValue($contactsField);
    foreach($contacts as $i => $contact){
      if($contact['contactId'] == _id($contactId)) {
        $save = true;
        $contacts[$i]['role'] = $role;
      }
    }
    if($save){
      $this->setValue($contactsField, $contacts)->save($contactsField);
    }
    return $this;
  }

  public function getUrl(){
    return site_url('projects/' . $this->id() . '/tasks');
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

  public function isMilestone(){
    // check if is last task (automatically a milestone)
    // check if is milestone

  }

  public static function Create($data){

    // Check if template is set
    // If template is set, set template id


    // Create Job
    $projectData = [
      'dateAdded' => new MongoDate(),
      'name' => $data['name'],
      'dueDate' => null,
      'approxEndDate' => null,
      'partiesInvolved' => isset($data['partiesInvolved']) ? (array) $data['partiesInvolved'] : [],
      'nativeId' => _generate_unique_id(Job::CollectionName(), 'nativeId', 7),
      'organizationId' => isset($data['organizationId']) ? $data['organizationId'] : (UserSession::loggedIn() ? UserSession::Get_Organization()->id() : null),
      'viewableContacts' => [],
      'meta' => isset($data['meta']) ? (array) $data['meta'] : [],
      'taskMeta' => [],
      'notes' => [],
      'templateId' => isset($data['templateId']) ? (array) $data['templateId'] : null,
      'templateVersion' => isset($data['templateVersion']) ? $data['templateVersion'] : null,
      'sortOrder' => []
    ];

    if(isset($data['templateId']) && !empty($data['templateId'])) {
      $projectData['templateId'] = _id($data['templateId']);
    }

    $projectId = parent::Create($projectData);

    return $projectId;
  }

  public function meta(){
    return $this->meta;
  }

  /**
   * Payload data to be passed around in place of project instance
   */
  public function payload(){
    $payload = [
      'projectId' => $this->id(),
      'meta' => $this->meta()->getAll()
    ];
    return $payload;
  }

  /**
   * Load workflow for this element
   * @return $this
   */
  public function loadTemplate(){
    if(isset($this->_current['templateId']) && !isset($this->template)){
      $this->template = new Template(self::LoadRecord($this->_current['templateId'], Template::CollectionName()), $this->getValue('templateVersion'));
    }
    return $this;
  }




}