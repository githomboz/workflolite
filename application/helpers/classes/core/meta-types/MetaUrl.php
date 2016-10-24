<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/20/16
 * Time: 10:36 PM
 */

require_once 'MetaObject.php';

class MetaUrl extends MetaObject {

  protected static $_type = 'url';

  public function display($link = true){
    if($this->_cache) return $this->_cache;
    else {
      if($link) $this->_cache = '<a href="' . $this->get() . '" target="_blank"><i class="fa fa-external-link"></i> ';
      $this->_cache .= $this->get();
      if($link) $this->_cache .= '</a>';
      return $this->_cache;
    }
  }

  public static function formatData($val){
    return $val;
  }

  public static function defaultValidationRoutines(){
    return array_merge(parent::defaultValidationRoutines(), array('MetaUrl::is_url', 'MetaUrl::url_exists'));
  }

  public static function is_url($val){
    $pattern = "|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i";
    $valid = preg_match($pattern, $val);
    return (bool) $valid;
  }

  public static function test_is_url(){
    $cases = array(
      array('val' => '', 'assert' => true),
    );
    return $cases;
  }

  public static function url_exists($val){
    $url_data = parse_url($val); // scheme, host, port, path, query
    return (bool) @fsockopen($url_data['host'], isset($url_data['port']) ? $url_data['port'] : 80);
  }

  public static function test_url_exists(){
    $cases = array(
      array('val' => '', 'assert' => true),
    );
    return $cases;
  }


}