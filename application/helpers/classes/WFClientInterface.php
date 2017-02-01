<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 1/11/17
 * Time: 6:28 PM
 */
class WFClientInterface
{

  public static function BenchmarkMarker($marker){
    return CI()->benchmark->mark($marker);
  }

  public static function BenchmarkElapsedTime($startMarker, $endMarker){
    return CI()->benchmark->elapsed_time($startMarker, $endMarker);
  }
  
  public static function GetLogsTemplate(){
    return ['info'=>[],'debug'=>[],'errors'=>[]];
  }

  public static function GetPayloadTemplate(){
    return [
      'response' => [ 'success' => false ],
      'logger' => null,
      'logs' => self::GetLogsTemplate(),
      'errors' => true
    ];
  }

  public static function Valid_WFResponse($response){
    if(!isset($response['response'])) return false;
    if(!isset($response['response']['success'])) return false;
    if(!isset($response['logs'])) return false;
    if(!isset($response['logger'])) return false;
    if(!isset($response['errors'])) return false;
    return true;
  }

  public static function MergeLogs(array $current_logs, array $new_logs){
    foreach(['info','errors','logs'] as $context){
      if(isset($current_logs[$context]) && isset($new_logs[$context])){
        $current_logs[$context] = array_merge($current_logs[$context], $new_logs[$context]);
      } elseif(!isset($current_logs[$context]) && isset($new_logs[$context])) {
        $current_logs[$context] = $new_logs[$context];
      }
    }
    return $current_logs;
  }

  public static function Valid_JSON($string){
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
  }

  public static function Valid_Date($date, $format = 'Y-m-d H:i:s')
  {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
  }

}

