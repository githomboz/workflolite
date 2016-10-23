<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/20/16
 * Time: 10:36 PM
 */

require_once 'MetaObject.php';

class MetaPhone extends MetaObject {

  protected static $_type = 'phone';

  public static function formatData($val){
    return self::_get_phone($val);
  }

  public static function defaultValidationRoutines(){
    return array_merge(parent::defaultValidationRoutines(), array('MetaPhone::is_phone'));
  }

  public function display($style = 'default'){
    if($this->_cache) return $this->_cache;
    else {
      $this->_cache = '(' . substr($this->_data, 0, 3) . ') ' . substr($this->_data, 3, 3) . '-' . substr($this->_data, 6, 4);
      return $this->_cache;
    }
  }

  public static function _get_phone($val){
    return preg_replace('/\D+/', '', (string) $val);
  }

  public static function is_phone($val){
    $value = self::_get_phone($val);
    return (is_numeric($value) && strlen($value) == 10);
  }

  public static function test_is_phone(){
    $cases = array(
      array('val' => '5617079761', 'assert' => true),
      array('val' => '(561) 707 - 9761', 'assert' => true),
      array('val' => 8352832312, 'assert' => true),
      array('val' => '(451)423-3123', 'assert' => true),
      array('val' => '423.141.3151', 'assert' => true),
      array('val' => '423.141.31514', 'assert' => true),
      array('val' => '$44.00', 'assert' => false),
      array('val' => 'andf sdfa4 4', 'assert' => false),
    );
    return $cases;
  }


}