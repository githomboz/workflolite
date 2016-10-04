<?php

require_once 'WorkflowFactory.php';

class Job extends WorkflowFactory implements WorkflowInterface
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'jobs';

  public function __construct(array $data, $fullLoad = false)
  {
    parent::__construct();
    $this->_initialize($data, $fullLoad);
  }

  public function addTask(Task $task){
    if($this->hasId()){
      $task->setValues(array('jobId' => $this->id()))->save();
      return $this;
    } else {
      throw new Exception('Tasks can not be added without an _id');
    }
  }

  public function getTasks(){
    if($this->hasId()){

    } else {
      throw new Exception('Tasks can not be pulled without an _id');
    }
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