<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/22/16
 * Time: 12:11 PM
 */

require_once 'MetaObject.php';

class MetaTwitterHandle extends MetaObject {

  protected static $_type = 'twitterHandle';

  public function display(){
    if($this->_cache) return $this->_cache;
    else {
      $html = '@' . str_replace('@','', $this->get());
      $html .= ' <a href="http://www.twitter.com/'.$this->get().'" target="_blank" class="fa fa-twitter" style="color: #4992d0"></a>';
      $this->_cache = $html;
      return $this->_cache;
    }
  }

  public static function formatData($val){
    return $val;
  }

  public static function defaultValidationRoutines(){
    return array_merge(parent::defaultValidationRoutines(), array('MetaTwitterHandle::valid_twitterHandle'));
  }

  public static function valid_twitterHandle($val){
    return true;
  }

  public static function test_valid_twitterHandle(){
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