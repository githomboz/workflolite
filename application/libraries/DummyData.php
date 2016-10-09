<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/8/16
 * Time: 11:35 AM
 */
class DummyData
{

  private static $CI = null;

  public static function get_dummy_user($count = null, $nationality = null, $gender = null, $includeFields = array(), $excludeFields = array()){
    $limit = 5000;
    if($count > $limit) $count = $limit;
    if(!$count) $count = 1;

    if(!$nationality) $nationality = 'us,ca';

    self::CI()->load->library('Curl');
    $apiUrl = 'https://randomuser.me/api/?';
    if($count) $apiUrl .= '&results=' . $count;
    if($nationality) $apiUrl .= '&nat=' . $nationality;
    if($gender) $apiUrl .= '&gender=' . $gender;
    if($includeFields) $apiUrl .= '&inc=' . join(',', $includeFields);
    if($excludeFields) $apiUrl .= '&exc=' . join(',', $excludeFields);
    $userData = self::CI()->curl->simple_get($apiUrl);
    $userData = json_decode($userData, true);
    return $userData;
  }

  private static function CI(){
    if(!self::$CI){
      self::$CI = get_instance();
    }
    return self::$CI;
  }

}