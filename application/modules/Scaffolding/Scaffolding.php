<?php

namespace Modules;

class Scaffolding extends \ModulesImplementation
{

  public static $module = __CLASS__;
  public static $namespace = __NAMESPACE__;
  public static $collection = null;

  public static $moduleFile = __FILE__;
  public static $moduleDir = __DIR__;

  protected static $meta = null;

  private static $_validators = array();
  private static $_processors = array();
  private static $_setvals = array();


  public function __construct($meta = null){
    parent::__construct($meta);
  }

  public function install(){
    $this->_initInstall();
    //echo 'just did stuff to install this module';
  }

  public function uninstall(){
    $this->_initUninstall();
    //echo 'just did stuff to uninstall this module';
  }

  public function reset(){
    //echo 'just reset data and files back to fresh install';
  }

  private static function _process_static_variables(){
    $methods = get_class_methods(__CLASS__);
    foreach($methods as $method){
      foreach(array('validate','process','setval') as $keyword){
        if(strpos($method, $keyword.'_') === 0){
          switch($keyword){
            case 'validate':
              self::$_validators[] = $method;
              break;
            case 'process':
              self::$_processors[] = $method;
              break;
            case 'setval':
              self::$_setvals[] = $method;
              break;
          }
        }
      }
    }
  }

  public static function getValidators(){
    self::_process_static_variables();
    return self::$_validators;
  }

  public static function getProcessors(){
    self::_process_static_variables();
    return self::$_processors;
  }

  public static function getSetvals(){
    self::_process_static_variables();
    return self::$_setvals;
  }

  public static function validate_is_integer($value){

  }

  public static function validate_is_unique($collection, $field, $value){

  }

  public static function validate_date_is_past($date){

  }

  public static function validate_date_is_future($date){

  }

  public static function validate_other($function, $value){

  }

  public static function process_convert_to_date($date){

  }

  public static function process_other($function, $value){

  }

  public static function setval_generate_id($length = 7){

  }

  public static function setval_current_time(){

  }

  public static function setval_other($function, $params){

  }



}