<?php

require_once 'Step.php';

class Task2
{

  public $_current = array();
  /**
   * Conditions that must equate to true in order for this task to show
   * @var array
   */
  protected $preconditions = array();

  public static $statusComplete = 'completed';
  public static $statusDeleted = 'deleted';
  public static $statusSkipped = 'skipped';
  public static $statusForceSkipped = 'force_skipped';
  public static $statusActive = 'active';
  public static $statusNotStarted = 'new';

  /**
   * @var array Array of Step objects
   */
  protected $steps = array();

  protected $project = null;

  public function __construct(array $data, Project $project){
    // Needs to run before _initialize
    if(isset($data['id'])) $data['taskId'] = $data['id'];
    $this->_initialize($data);
    $this->project = $project;
    //var_dump($this->_current);
  }

  public function id(){
    return isset($this->_current['id']) ? $this->_current['id'] : null;
  }

  public function assign($userIds){
    if(is_array($userIds)){
      foreach($userIds as $id){
        if($id instanceof MongoId) $this->_current['assigneeId'][] = $id;
        else throw new Exception('User Ids must be MongoId');
      }
    } else {
      if($userIds instanceof MongoId) {
        $this->_current['assigneeId'][] = $userIds;
      } else {
        throw new Exception('User Ids must be MongoId');
      }
    }
  }

  public function runPostRoutines(){

  }

  public function queueTriggers(){

  }

  /**
   * Prepare data and class for processing
   * @param array $data
   */
  protected function _initialize(array $data){
    $this->_current = $data;
  }

  /**
   * Load assignees for a certain task
   * @return $this
   */
  public function loadAssignees(){
    if(isset($this->_current['assigneeId'])){
      $return = array();
      foreach($this->_current['assigneeId'] as $assigneeId){
        $return[] = self::LoadRecord($assigneeId, 'users');
      }
      $this->_current['assignees'] = $return;
    }
    return $this;
  }

  /**
   * Load steps
   * @return $this
   */
  public function loadSteps(){
    if(isset($this->_current['steps']) && is_array($this->_current['steps'])){
      foreach($this->_current['steps'] as $i => $step) $this->steps = new Step($step, $this, $i);
    }
    return $this;
  }

  /**
   * Save steps based upon $this->steps array
   * @return $this
   */
  public function saveSteps(){
    return $this;
  }

  public function getStartDate($format = 'l, F j, Y h:i:s'){
    if($date = $this->getValue('startDate')){
      return date($format, $date->sec);
    }
    return null;
  }

  public function getCompleteDate($format = 'l, F j, Y h:i:s'){
    if($date = $this->getValue('completeDate')){
      return date($format, $date->sec);
    }
    return null;
  }

  public function start(){
    $update = array(
      'startDate' => new MongoDate(),
      'status' => Task2::$statusActive
    );
    $this->_current = array_merge($this->_current, $update);
    return $this->update();
  }

  public function complete(){
    $update = array(
      'completeDate' => new MongoDate(),
      'status' => Task2::$statusComplete
    );
    $this->_current = array_merge($this->_current, $update);
    $this->runPostRoutines();
    $this->queueTriggers();
    return $this->update();
  }

  public function clearStart(){
    $update = array(
      'startDate' => null,
    );
    $this->_current = array_merge($this->_current, $update);
    return $this->update();
  }

  public function clearComplete(){
    $update = array(
      'completeDate' => null,
    );
    $this->_current = array_merge($this->_current, $update);
    return $this->update();
  }

  public function setComments($comments){
    $this->_current['comments'] = trim($comments);
    $this->update();
  }

  public function update(){
    return $this->project->setTask($this->id(), $this->_current);
  }

  public function getCurrent(){
    return $this->_current;
  }

  /**
   * Whether or not this task is active based on dependencies
   */
  public function isActionable(){
    $response = true;
    if(!empty($this->preconditions)) $response = false;
    if(in_array($this->getValue('status'), array(self::$statusComplete, self::$statusSkipped, self::$statusForceSkipped, self::$statusDeleted))) $response = false;
    return $response;
  }

  /**
   * Whether or not this task is skipped based on dependencies or user action
   */
  public function isSkipped(){
    $response = false;
    if(in_array($this->getValue('status'), array(self::$statusSkipped, self::$statusForceSkipped))) $response = true;
    return $response;
  }

  /**
   * Whether or not this task is skipped based on user action
   */
  public function isForceSkipped(){
    $response = false;
    if(in_array($this->getValue('status'), array(self::$statusForceSkipped))) $response = true;
    return $response;
  }

  /**
   * Whether or not this display this task
   */
  public function isShowable(){
    return !$this->isDeleted();
  }

  /**
   * Whether or not this task has been deleted
   */
  public function isDeleted(){
    $response = false;
    if(in_array($this->getValue('status'), array(self::$statusDeleted))) $response = true;
    return $response;
  }

  /**
   * Whether or not this display this task
   */
  public function isClientViewable(){
    return $this->getValue('clientView');
  }

  public function isComplete(){
    return $this->getValue('status') == self::$statusComplete;
  }

  public function isStarted(){
    return $this->getValue('startDate');
  }

  public function nextStepIndex(){
    end($this->steps);
    return key($this->steps);
  }

  public function statusText(){
    return self::GetStatusText($this->getValue('status'));
  }

  private function _mergeTemplateToTask(TaskTemplate2 $template){
    $template = $template->getCurrent();

    // Merge triggers
    $this->_mergeInTemplateTriggers($template);

    // Unset template fields
    unset($template['_id']);
    unset($template['organizationId']);
    unset($template['nativeTriggers']);

    // Merge template into current
    $this->_current = array_merge($template, $this->_current);

  }

  private function _mergeInTemplateTriggers($template){
    // Merge Triggers
    $tempTriggers = array_merge($template['nativeTriggers'], $this->_current['triggers']);

    // Sequence Map; A map displaying each trigger's sequence and index
    $sequenceMap = array();

    // Overwrite template triggers if necessary; This is done by using the same sequence for task and template
    foreach($tempTriggers as $i => $trigger){
      if(!isset($sequenceMap[$trigger['sequence']])) $sequenceMap[$trigger['sequence']] = $i;
      else {
        $tempTriggers[$sequenceMap[$trigger['sequence']]] = $trigger;
        unset($tempTriggers[$i]);
      }
    }

    // Merge back into triggers
    $tempTriggers = array_values($tempTriggers);
    $this->_current['triggers'] = $tempTriggers;

  }

  public static function GetStatusText($status){
    switch ($status){
      case self::$statusForceSkipped :
          return 'Skipped by User';
        break;
      case self::$statusNotStarted :
          return 'Not Started';
        break;
      default:
        return ucwords(join(' ', explode(' ', $status)));
        break;
    }
  }

  /**
   * Update data within this entity
   * @param array $data
   * @return $this
   */
  public function setValues(array $data){
    if(!empty($data)) $this->_current = array_merge($this->_current, $data);
    return $this;
  }

  /**
   * Update field within this entity
   * @param string $key
   * @param mixed $value
   * @return $this
   */
  public function setValue($key, $value){
    $this->_current[$key] = $value;
    return $this;
  }

  /**
   * Get data within this entity
   * @param string $field Name of the field/property to return
   * @return mixed
   */
  public function getValue($field){
    if(isset($this->_current[$field])) return $this->_current[$field];
    if(isset($this->$field)) return $this->$field;
    return false;
  }


}