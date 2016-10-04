<?php

require_once 'WorkflowFactory.php';

class TaskTemplate extends WorkflowFactory implements WorkflowInterface
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'taskTemplates';

  public function __construct(array $data, $fullLoad = false)
  {
    parent::__construct();
    $this->_initialize($data, $fullLoad);
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