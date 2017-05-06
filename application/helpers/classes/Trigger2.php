<?php

require_once 'WorkflowFactory.php';

class Trigger2 extends WorkflowFactory
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'triggers';

  public function __construct($data)
  {
    if(is_array($data)){
      parent::__construct();
      $this->_initialize($data);
    }
  }

  public function _initialize(array $data, $initializeMeta = false)
  {
    parent::_initialize($data); // TODO: Change the auto-generated stub
  }

  public static function Get($id){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    $object = new $class($record);
    if($object instanceof $class) return $object;
  }




}