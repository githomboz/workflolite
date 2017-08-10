<?php

require_once 'core/meta-types/MetaString.php';
require_once 'core/meta-types/MetaAddress.php';
require_once 'core/meta-types/MetaDateTime.php';
require_once 'core/meta-types/MetaPhone.php';
require_once 'core/meta-types/MetaUrl.php';
require_once 'core/meta-types/MetaArray.php';
require_once 'core/meta-types/MetaNumber.php';
require_once 'core/meta-types/MetaText.php';
require_once 'core/meta-types/MetaBoolean.php';
require_once 'core/meta-types/MetaTwitterHandle.php';

class Meta
{

  protected $id = null;

  protected $_job = null;

  protected $_workflow = null;

  private $_current = null;

  private $_data = array();

  private $_settings = array();

  protected $_toSave = array();

  private static $_errors = array();

  private $_clientViewEnabled = false;

  private $_storedMerge = null;


  public function __construct(array $metaData, WorkflowFactory $entity){
    if($entity instanceof Job || $entity instanceof Project){
      if($entity instanceof Job){
        $this->_job = $entity;
      } else {
        $this->_project = $entity;
      }
      $this->initialize($metaData);
    }
  }

  public function initialize($metaData){
    //var_dump($metaData);
    $this->_current = $metaData;
    $this->generateMetaDataArray();
    //var_dump($this->_current, $this->_data, $this->_settings);
    return $this;
  }

  public function generateMetaDataArray(){
    foreach((array) $this->getMetaFieldSettings() as $i => $metaFieldSettings) {
      $this->registerMetaField($metaFieldSettings['slug']);
    }
  }

  public function current(){
    return $this->_current;
  }

  public function id(){
    return $this->id;
  }

  public function CI(){
    return CI();
  }

  public function job(){
    return $this->_job;
  }

  public function project(){
    return $this->_project;
  }

  public function isJob(){
    return isset($this->_job);
  }

  public function set($mixed, $value = null){
    if(is_string($mixed) && isset($value)) $this->_toSave[$mixed] = $value;
    if(is_array($mixed)) foreach($mixed as $k => $v) $this->_toSave[$k] = $v;
    return $this;
  }

  public function save(){
    if(!empty($this->_toSave)){
      if($this->isJob()){
        foreach($this->_toSave as $key => $value) $this->job()->setValue($key, $value)->save($key);
      } else {
        foreach($this->_toSave as $key => $value) $this->project()->setValue($key, $value)->save($key);
      }

      $this->_toSave = array();
    }
    return $this;
  }



  public function workflow(){
    if($this->_workflow) return $this->_workflow;
    $this->_workflow = $this->job()->loadWorkflow()->getValue('workflow');
    return $this->_workflow;
  }

  public function template(){
    if(isset($this->_template)) return $this->_template;
    $this->_template = $this->project()->loadTemplate()->getValue('template');
    return $this->_template;
  }

  public function getMetaFieldSettings($field = null){
    $templateSource = $this->isJob() ? $this->workflow() : $this->template() ;
    if($templateSource){
      $templateMetaFields = $templateSource->getValue('metaFields');

      // Adding this to merge in local meta fields
      $localMetaField = $this->project()->getValue('localMetaFields');
      //var_dump($localMetaField, $this->_settings);
      if(empty($localMetaField)) $localMetaField = [];
      // Add in meta values

      
      $temp = array_merge($templateMetaFields, $localMetaField);
      if(json_encode($temp) != json_encode($this->_settings)){
        $this->_settings = $temp;
      }

      //var_dump($this->_settings, $this->project()->getValue('meta'));

      // Return values
      if($field) {
        foreach($this->_settings as $i => $fieldData){
          if($field == $fieldData['slug']) return $fieldData;
        }
      } else {
        return $this->_settings;
      }
    }
  }

  public function registerMetaField($field){
    if($field){
      $this->_data[$field] = array_merge((array) $this->getMetaFieldSettings($field), array(
        'value' => isset($this->_current[$field]) ? $this->_current[$field] : null,
        'html' => array(
          'formHTML' => '',
          'rowStrLen' => 0
        ),
      ));

      switch (strtolower($this->_data[$field]['type'])){
        case 'string':
          $this->_data[$field]['value'] = new MetaString($this->_data[$field]['value']);
          break;
        case 'address':
          $this->_data[$field]['value'] = new MetaAddress($this->_data[$field]['value']);
          break;
        case 'phone':
          $this->_data[$field]['value'] = new MetaPhone($this->_data[$field]['value']);
          break;
        case 'url':
          $this->_data[$field]['value'] = new MetaUrl($this->_data[$field]['value']);
          break;
        case 'array':
          $this->_data[$field]['value'] = new MetaArray($this->_data[$field]['value']);
          break;
        case 'number':
          $this->_data[$field]['value'] = new MetaNumber($this->_data[$field]['value']);
          break;
        case 'bool':
        case 'boolean':
          $this->_data[$field]['value'] = new MetaBoolean($this->_data[$field]['value']);
          break;
        case 'text':
          $this->_data[$field]['value'] = new MetaText($this->_data[$field]['value']);
          break;
        case 'twitterhandle':
          $this->_data[$field]['value'] = new MetaTwitterHandle($this->_data[$field]['value']);
          break;
        case 'datetime':
        case 'date':
        case 'time':
          $this->_data[$field]['value'] = new MetaDateTime($this->_data[$field]['value']);
          break;
      }

      //var_dump($field, $this->_data[$field]);

      if($this->_data[$field]['value'] instanceof MetaString)
      $this->_data[$field]['html']['rowStrLen'] = strlen($this->_data[$field]['field']) + strlen($this->_data[$field]['value']->get());

      if(isset($this->_data[$field]['clientView']) && $this->_data[$field]['clientView'] === true) $this->_clientViewEnabled = true;

    } else {
      throw new Exception("Invalid field passed");
    }
    return $this;
  }

  public function unRegisterMetaField($fieldName){
    return $this;
  }

  public static function SetMeta($type, $value){
    $metaClass = "Meta" . ucwords($type);
    if(class_exists($metaClass)){
      return new $metaClass($value);
    } else {
      self::$_errors[] = 'Class [ ' . $metaClass . ' ] does not exist';
    }
    return false;
  }

  public function clientMeta(){
    return $this->_clientViewEnabled;
  }

  public function getAll(){
    return $this->_data;
  }

  public function getAllFieldsHTML(){
    $html = '';
    foreach($this->getAll() as $slug => $data){
      $html .= $this->getFieldFormHTML($data);
    }
    return $html;
  }

  public function getAllFieldsSingleFormHTML(){

  }

  public function getFieldFormHTML(array $field){
    if($field['value'] instanceof MetaObject){
      return get_include($field['value']->getFormHtmlPath($field['type']), array('meta' => $field), true);
    }
    return false;
  }

}