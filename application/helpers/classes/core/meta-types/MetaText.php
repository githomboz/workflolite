<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/20/16
 * Time: 10:36 PM
 */

require_once 'MetaObject.php';

class MetaText extends MetaObject
{

  protected static $_type = 'text';

  public static function formatData($val)
  {
    return (string)$val;
  }

  public static function defaultValidationRoutines()
  {
    return array_merge(parent::defaultValidationRoutines(), array('is_string'));
  }

}