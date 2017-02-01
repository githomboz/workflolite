<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/20/16
 * Time: 10:36 PM
 */

require_once 'MetaObject.php';

class MetaNumber extends MetaObject {

  protected static $_type = 'number';


  public function display($var = null){
    if($this->_cache) return $this->_cache;
    else {
      $this->_cache = number_format($this->_data);
      return $this->_cache;
    }
  }

  public static function formatData($val){
    if(is_float($val)) return (float) $val;
    return (int) $val;
  }

  public static function defaultValidationRoutines(){
    return array_merge(parent::defaultValidationRoutines(), array('is_numeric'));
  }
  
}