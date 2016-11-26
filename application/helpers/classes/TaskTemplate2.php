<?php

class TaskTemplate2
{

  public $_current = array();

  public function __construct(array $data)
  {
    $this->_initialize($data);
  }

  public function id(){
    return isset($this->_current['id']) ? $this->_current['id'] : null;
  }

  /**
   * Prepare data and class for processing
   * @param array $data
   */
  protected function _initialize(array $data){
    $this->_current = $data;
  }


  public static function GetTaskTemplatesByTemplate(Template $template){
    return $template->getTemplates();
  }

  /**
   * Update data within this entity
   * @param array $data
   * @return $this
   */
  public function setValues(array $data){
    if(!empty($data)) $this->_current = array_merge($this->_current, $data);
    return $this;
  }

  /**
   * Update field within this entity
   * @param string $key
   * @param mixed $value
   * @return $this
   */
  public function setValue($key, $value){
    $this->_current[$key] = $value;
    return $this;
  }

  /**
   * Get data within this entity
   * @param string $field Name of the field/property to return
   * @return mixed
   */
  public function getValue($field){
    if(isset($this->_current[$field])) return $this->_current[$field];
    if(isset($this->$field)) return $this->$field;
    return false;
  }

}