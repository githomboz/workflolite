<?php

require_once 'WorkflowFactory.php';
require_once 'Step.php';

class Task extends WorkflowFactory implements WorkflowInterface
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'tasks';

  /**
   * Conditions that must equate to true in order for this task to show
   * @var array
   */
  protected $preconditions = array();

  /**
   * @var array Array of Step objects
   */
  protected $steps = array();

  public function __construct(array $data, $fullLoad = false){
    parent::__construct();
    // Needs to run before _initialize
    if(isset($data['_id'])) $data['taskId'] = $data['_id'];
    $this->_initialize($data, $fullLoad);
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

  /**
   * Prepare data and class for processing
   * @param array $data
   * @param bool $fullLoad Whether or not to load all linked records
   */
  protected function _initialize(array $data, $fullLoad = false){
    parent::_initialize($data, $fullLoad);

    // Get taskTemplate
    $taskTemplate = self::LoadRecord($data['taskTemplateId'], 'taskTemplates');

    // Merge template into current
    $this->_mergeTemplateToTask($taskTemplate);

    // Load All
    if($fullLoad){
      $this->loadAssignees();
      $this->loadSteps();
      $this->loadTaskTemplate();
    }
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
   * Load job for this element
   * @return $this
   */
  public function loadTaskTemplate(){
    if(isset($this->_current['taskTemplateId'])){
      $this->_current['taskTemplate'] = new TaskTemplate(self::LoadRecord($this->_current['taskTemplateId'], 'taskTemplates'));
    }
    return $this;
  }

  public function reOrderSteps(array $orderArray){

  }

  /**
   * Save steps based upon $this->steps array
   * @return $this
   */
  public function saveSteps(){
    return $this;
  }

  public function saveAsTemplate(){

  }

  public function nextStepIndex(){
    end($this->steps);
    return key($this->steps);
  }

  private function _mergeTemplateToTask($template){

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

  public static function ValidData(array $data){
    return !empty($data) && isset($data['name']);
  }

  public static function Create($data, $templateId = null){

  }

  public static function Duplicate($id, array $data = array()){
    return null; // return new id
  }


}