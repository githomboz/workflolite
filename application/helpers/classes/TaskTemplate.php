<?php

require_once 'WorkflowFactory.php';

class TaskTemplate extends WorkflowFactory
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
  

}