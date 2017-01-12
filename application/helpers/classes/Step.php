<?php

require_once 'PreCondition.php';
require_once 'Trigger.php';

class Step
{

  protected $id = null;

  protected $status = 'active';

  private $_current = null;

  /**
   * Conditions that must equate to true in order for this step to show
   * @var array
   */
  protected $preconditions = array();

  public function __construct(array $data){
    $this->_current = $data;
    if(isset($data['id'])) $this->id = $data['id'];
  }

  public function setNotApplicable(){
    $this->status = 'NA';
    return $this;
  }

  public function save(){

  }

  public function getData(){
    $data = array(
      'id' => $this->id,
      'name' => $this->_current['name']
    );
    return $data;
  }

  public function id(){
    return $this->id;
  }

  /**
   * Check if a step is applicable after all preconditions are met
   */
  public function isApplicable(){

  }

  public function registerPrecondition(PreCondition $precondition){
    $this->preconditions[] = $precondition;
    return $this;
  }

  public function unRegisterPrecondition(PreCondition $precondition){
    foreach($this->preconditions as $i => $preCon) if((string) $precondition->id() == (string) $preCon->id()) unset($this->preconditions[$i]);
    $this->preconditions = array_values($this->preconditions);
    return $this;
  }

  public function registerTrigger(Trigger $trigger){
    return $this;
  }

  public function unRegisterTrigger(Trigger $trigger){
    return $this;
  }

  public function getTask(){
    return $this->_task;
  }

  public static function Create($data, Task $task){
    $data = self::ValidateNewData($data);
    return new Step($data, $task);
  }

  public static function ValidateNewData($data){
    return $data;
  }


}