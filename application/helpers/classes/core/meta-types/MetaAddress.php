<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/22/16
 * Time: 12:11 PM
 */

require_once 'MetaObject.php';

class MetaAddress extends MetaObject {

  protected static $_type = 'address';

  public static function formatData($val){
    return $val;
  }

  public static function defaultValidationRoutines(){
    return array_merge(parent::defaultValidationRoutines(), array('MetaAddress::valid_address'));
  }

  public static function getRequiredFields(){
    return array('street','city','state','zip'); // @todo: Do not reorder this list. It contributes to display format.
  }

  public static function valid_address($val){
    foreach(self::getRequiredFields() as $field) if(!isset($val[$field])) return false;
    return true;
  }

  public function display(array $lineBreaksAfterFields = null){
    if($this->_cache) return $this->_cache;
    else {
      if(!$lineBreaksAfterFields) $lineBreaksAfterFields[] = 'street';
      $html = '';
      foreach(self::getRequiredFields() as $field){
        if(isset($this->_data[$field])) {
          $html .= $this->_data[$field];
          if(in_array($field, array('city','street'))) $html .= ', ';
          $html .= (!empty($lineBreaksAfterFields) ? (in_array($field, $lineBreaksAfterFields) ? '<br />' : '') : '');
          $html .= ' ';
        }
      }
      $html .= '<a href="#map_it"><i class="fa fa-map-marker"></i></a>';
      $this->_cache = $html;
      return $this->_cache;
    }
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