<?php

require_once 'WorkflowFactory.php';

class Organization extends WorkflowFactory
{

  public $workflows = array();

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'organizations';

  public function __construct(array $data)
  {
    parent::__construct();
    $this->_initialize($data);
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
      if(!empty($this->workflows)) return $this->workflows;
      else {
        $workflows = self::CI()->mdb->where('organizationId', $this->id())->get(Workflow::CollectionName());
        foreach($workflows as $workflow) $this->workflows[] = new Workflow($workflow);
        return $this->workflows;
      }
    } else {
      throw new Exception('Workflows can not be pulled without an _id');
    }
  }

  public static function Get($id){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    return new $class($record);
  }
}