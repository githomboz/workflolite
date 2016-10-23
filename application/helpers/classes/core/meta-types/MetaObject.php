<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/21/16
 * Time: 11:00 AM
 */

abstract class MetaObject
{
  protected static $_type = null;
  protected $_input = null;
  protected $_data = null;
  protected $_errors = false;
  /**
   * Display content to avoid redraw
   * @var null
   */
  protected $_cache = null;

  public function __construct($val)
  {
    $this->_input = $val;
    $this->set($val);
  }

  public function get(){
    return $this->_data;
  }

  public function set($val, array $additionalFunctions = array()){
    if(static::isValidInput($val, $additionalFunctions)){
      $this->_data = static::formatData($val);
      return $this;
    }
    return false;
  }

  public function ok(){
    return $this->get() !== null && !$this->errors();
  }

  public function errors(){
    if(empty($this->_errors) || $this->_errors === false) return false; else return $this->_errors;
  }

  public function addError($error) {
    $this->_errors[] = $error;
    return $this;
  }

  public function isValidInput($val, array $additionalFunctions = array()){
    $validationFunctions = array_merge(static::defaultValidationRoutines(), $additionalFunctions);
    if(!empty($validationFunctions)){
      foreach($validationFunctions as $function){
        if(is_callable($function)){
          $params = array($val);
          $valid = call_user_func_array($function, $params);
          //var_dump($val, $valid, $function);
          if($valid !== true){
            $this->addError('Validation Failed: ' . $function);
            return false;
          }
        } else {
          $this->addError('Invalid Validation Callback: ' . $function);
        }
      }
      return true;
    }
    return true;
  }

  public static function runTests(){

  }

  /**
   * Return data in display format
   * @param mixed $var
   * @return mixed
   */
  public function display($var = null){
    if($this->_cache) return $this->_cache;
    else {
      $this->_cache = $this->get();
      return $this->_cache;
    }
  }

  public function is($class){
    return is_object($class) && $this instanceof $class;
  }

  public static function defaultValidationRoutines(){
    return array();
  }

  public static function getFormHtmlPath($type){
    $html_location = APPPATH . '/views/widgets/_meta-' . $type . '-form.php';
    if(file_exists($html_location)) return $html_location;
    return null;
  }

  public abstract static function formatData($val);

}