<?php

class Trigger
{

  protected $id = null;

  /**
   * Task $this step belongs to
   * @var string
   */
  protected $_task = null;

  /**
   * Step $this trigger belongs to
   * @var string
   */
  protected $_step = null;

  protected $status = 'active';

  protected $_raw = null;
  protected $_current = null;
  protected $_updates = null;

  public function __construct(array $data, Step $step){
    $this->_step = $step;
    $this->_task = $step->getTask();
    $this->_current = $data;
    $this->initialize();
  }

  public function getData(){
    $data = array(
      'id' => $this->id,
      'name' => $this->_current['name']
    );
    return $data;
  }

  protected function initialize(){
    
  }

  public function id(){
    return $this->id;
  }

  public static function Create($data, Step $step){
    $data = self::ValidateNewData($data);
    return new Trigger($data, $step);
  }

  public static function ValidateNewData($data){
    return $data;
  }


}