<?php

require_once 'WorkflowFactory.php';

class Workflow extends WorkflowFactory
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'workflows';

  public function __construct(array $data, $fullLoad = false)
  {
    parent::__construct();
    $this->_initialize($data, $fullLoad);
  }

  public function addJob(Job $job){
    if($this->hasId()){
      $job->setValues(array('workflowId' => $this->id()))->save();
      return $this;
    } else {
      throw new Exception('Job can not be added without an _id');
    }
  }
  

}