<?php

class TaskTemplate2
{

  public $_current = array();

  private $_sortOrder = null;

  public function __construct(array $data, $sortOrder)
  {
    $this->setSortOrder($sortOrder);
    $this->_initialize($data);
  }

  public function id(){
    return isset($this->_current['id']) ? $this->_current['id'] : null;
  }

  public function getCurrent(){
    if(!isset($this->_current['_exists'])) $this->_current['_exists'] = true;
    return $this->_current;
  }

  public function getSortOrder(){
    return $this->_sortOrder;
  }

  public function setSortOrder($sortOrder){
    $this->_sortOrder = $sortOrder;
  }

  public function getFields(array $fields = null){
    $fields = !empty($fields) ? $fields : array();
    $data = array();
    foreach($this->_current as $field => $value){
      if(in_array($field, $fields)) $data[$field] = $value;
    }
    return $data;
  }

  public static function GetSettingsDataFields($return_empty_array = false) {
    $fields = array('id','taskGroup','name','estimatedTime','instructions','clientView','milestone','description','sortOrder');
    if($return_empty_array === true){
      $return = array();
      foreach($fields as $field) $return[$field] = null;
      return $return;
    }
    return $fields;
  }

  public function getSettingsData(){
    $fields = self::GetSettingsDataFields();
    $data = $this->getFields($fields);
    foreach($fields as $field) {
      if(!isset($data[$field])) {
        switch ($field):
          case 'description': $data[$field] = "";
            break;
          case 'sortOrder': $data[$field] = $this->getSortOrder();
            break;
          case 'estimatedTime' : $data[$field] = (float) $data[$field];
            break;
          default:
            $data[$field] = null;
            break;
        endswitch;
      }
    }
    ksort($data);
    return $data;
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

  public function applyVersionData($versionData, $targetVersion){
    if(!empty($versionData)) {
      for($v = 1; $v <= $targetVersion; $v ++){
        $_current = $this->_current;
        $vNum = 'v' . $v;
        if(isset($versionData[$vNum])){
          if(isset($versionData[$vNum]['taskTemplateChanges'])){
            if(isset($versionData[$vNum]['taskTemplateChanges'][(string) $this->id()])){
              $taskTemplateVersionData = $versionData[$vNum]['taskTemplateChanges'][(string) $this->id()];
              $this->_current = array_merge($this->_current, $taskTemplateVersionData);
              //$this->_current = $_current;
            }
          }
        }
      }
    }
    return $this;
  }

}