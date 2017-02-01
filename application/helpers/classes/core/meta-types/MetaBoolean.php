<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/22/16
 * Time: 12:11 PM
 */

require_once 'MetaObject.php';

class MetaBoolean extends MetaObject {

  protected static $_type = 'boolean';

  public function display(){
    if($this->_cache) return $this->_cache;
    else {
      $html = $this->_data === true ? 'TRUE' : 'FALSE';
      $this->_cache = $html;
      return $this->_cache;
    }
  }

  public static function formatData($val){
    return $val;
  }

  public static function defaultValidationRoutines(){
    return array_merge(parent::defaultValidationRoutines(), array('MetaBoolean::valid_isBool'));
  }

  public static function valid_isBool($val){
    return is_bool($val);
  }

  public static function test_valid_address(){
    $cases = array(
      array('val' => array(
        'street' => '5704 Candlewood Street',
        'city' => 'West Palm Beach',
        'state' => 'FL',
        'zip' => '33407'
      ), 'assert' => true),
    );
    return $cases;
  }

}