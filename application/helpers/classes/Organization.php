<?php

require_once 'WorkflowFactory.php';

class Organization extends WorkflowFactory implements WorkflowInterface
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'organizations';

  public function __construct(array $data, $fullLoad = false)
  {
    parent::__construct();
    $this->_initialize($data, $fullLoad);
  }

  public function addWorkflow(Workflow $workflow){
    if($this->hasId()){
      $workflow->setValues(array('workflowId' => $this->id()))->save();
      return $this;
    } else {
      throw new Exception('Workflows can not be added without an _id');
    }
  }

  public function getWorkflows(){
    if($this->hasId()){

    } else {
      throw new Exception('Workflows can not be pulled without an _id');
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