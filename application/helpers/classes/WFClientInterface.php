<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 1/11/17
 * Time: 6:28 PM
 */
class WFClientInterface
{

  public static function _getLogsTemplate(){
    return ['debug'=>[],'errors'=>[]];
  }

  public static function _getPayloadTemplate(){
    return [
      'response' => null,
      'logs' => self::_getLogsTemplate(),
      'errors' => true
    ];
  }

  public static function _mergeLogs(array $current_logs, array $new_logs){
    foreach(['errors','logs'] as $context){
      if(isset($current_logs[$context]) && isset($new_logs[$context])){
        $current_logs[$context] = array_merge($current_logs[$context], $new_logs[$context]);
      }
    }
    return $current_logs;
  }


}