<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/20/16
 * Time: 10:36 PM
 */

require_once 'MetaObject.php';

class MetaDateTime extends MetaObject {

  protected static $_type = 'dateTime';

  protected static $_dateTimeFormat = 'c';

  public static function formatData($val){
    return self::_get_datetime($val);
  }

  public static function defaultValidationRoutines(){
    return array_merge(parent::defaultValidationRoutines(), array('MetaDateTime::is_datetime'));
  }

  public static function _get_datetime($val){
    $seconds = null;
    if($val instanceof MongoDate) $seconds = $val->sec;
    if(!$seconds && is_string($val)) $seconds = strtotime($val);
    if(!$seconds) return false;
    return date(self::$_dateTimeFormat, $seconds);
  }

  public static function is_datetime($val){
    $seconds = self::_get_datetime($val);
    return (bool) $seconds;
  }

  public function display($dateFormat = 'm-d-Y'){
    if($this->get()){
      if($this->_cache) return $this->_cache;
      else {
        $this->_cache = date($dateFormat, strtotime($this->get())) . ' <a href=#add_to_calendar"><i class="fa fa-calendar-plus-o"></i></a>';
        return $this->_cache;
      }
    }
  }

  public function dbValue(){
    return new MongoDate(strtotime($this->_data));
  }

  public static function test_is_datetime(){
    $cases = array(
      array('val' => '12-09-1984', 'assert' => true),
      array('val' => 'dec 9, 1984', 'assert' => true),
      array('val' => new MongoDate(strtotime('12/9/84')), 'assert' => true),
      array('val' => ' ', 'assert' => false),
      array('val' => false, 'assert' => false),
    );
    return $cases;
  }


}