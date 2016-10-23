<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/20/16
 * Time: 10:36 PM
 */

require_once 'MetaObject.php';

class MetaArray extends MetaObject {

  protected static $_type = 'array';

  public function display(){
    if($this->_cache) return $this->_cache;
    else {
      $html = '';
      foreach($this->get() as $key => $value){
        $html .= '<span class="meta-array">';
        $html .= '<span class="array-key">' . $key . '</span>';
        $html .= '<i class="fa fa-arrow-right"></i>';
        $html .= '<span class="array-value">' . $value . '</span>';
        $html .= '</span>';
      }
      $this->_cache = $html;
      return $this->_cache;
    }

  }

  public static function formatData($val){
    return (array) $val;
  }

  public static function defaultValidationRoutines(){
    return array_merge(parent::defaultValidationRoutines(), array());
  }

}