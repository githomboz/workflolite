<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/20/16
 * Time: 10:36 PM
 */

require_once 'MetaObject.php';

class MetaString extends MetaObject {

  protected static $_type = 'string';

  public static function formatData($val){
    return (string) $val;
  }

  public static function defaultValidationRoutines(){
    return array_merge(parent::defaultValidationRoutines(), array('is_string','MetaString::not_empty'));
  }

  public static function alphanumeric($val){

  }

  public static function test_alphanumeric(){
    $cases = array(
      array('val' => 'sn4i2395&^#%', 'assert' => false),
      array('val' => 'snn49320', 'assert' => true),
      array('val' => 42833, 'assert' => true),
      array('val' => '(451) 423-3123', 'assert' => false),
      array('val' => '44.00', 'assert' => false),
      array('val' => '$44.00', 'assert' => false),
      array('val' => 'andf sdfa4 4', 'assert' => false),
      array('val' => 'andf sdfa4 -4', 'assert' => false),
    );
    return $cases;
  }

  public static function not_empty($val){
    return trim((string) $val) != '';
  }

  public static function test_not_empty(){
    $cases = array(
      array('val' => null, 'assert' => false),
      array('val' => '  ', 'assert' => false),
      array('val' => '0', 'assert' => true),
      array('val' => 134, 'assert' => true),
      array('val' => 0, 'assert' => true),
    );
    return $cases;
  }


}