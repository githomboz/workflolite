<?php

/**
 * Make sure that the required fields are set
 * @return bool
 */
function di_required_set($data, array $required = array()){
  if(empty($required)) return true;
  foreach($required as $field) if(!isset($data[$field])) return false;
  return true;
}

/**
 * Returns only the fields that are allowed
 * @param $data Input data
 * @param array $allowed | The field names that are allowed to be returned
 * @return array | filtered array
 */
function di_allowed_only($data, array $allowed){
  foreach($data as $k => $v) if(!in_array($k, $allowed)) unset($data[$k]);
  return $data;
}

function custom_excerpt($text,$strLen = 200) {
  $text=strip_tags($text);
  $text=str_replace("\n","",$text);
  $text=preg_replace("/\s\s+/"," ",$text);
  $text = preg_replace('@<script[^>]*?>.*?@si', '', $text);
  $text = str_replace(']]>', ']]>', $text);
  $text = preg_replace('`\[[^\]]*\]`','', $text);
  if (strlen($text) > $strLen) {
    $text = substr($text, 0, $strLen);
    $text = substr($text,0,strrpos($text," "));
    $etc = " ...";
    $text = $text.$etc;
  }
  return $text;
}

/**
 * Returns an encrypted & utf8-encoded
 */
function encrypt($pure_string, $encryption_key = null) {
  if(!$encryption_key) $encryption_key = config_item('encrypt_salt');
  $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
  $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
  $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
  return $encrypted_string;
}

/**
 * Returns decrypted original string
 */
function decrypt($encrypted_string, $encryption_key = null) {
  if(!$encryption_key) $encryption_key = config_item('encrypt_salt');
  $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
  $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
  $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
  return $decrypted_string;
}

function di_encrypt($data){
  return base64_encode(encrypt(base64_encode(json_encode($data)), config_item('encrypt_salt')));
}

function di_decrypt($encrypted){
  return json_decode(base64_decode(decrypt(base64_decode($encrypted), config_item('encrypt_salt'))));
}

function di_encrypt_s($data, $salt){
  return base64_encode(encrypt(base64_encode(json_encode($data)), $salt));
}

function di_decrypt_s($encrypted, $salt){
  return json_decode(base64_decode(decrypt(base64_decode($encrypted), $salt)));
}

function get_args($args = NULL){
  if(empty($args)) $args = new stdClass(); else $args = (object) $args;
  $args->page = isset($args->page) && is_numeric($args->page) ? $args->page : 1;
  $args->limit = isset($args->limit) && is_numeric($args->limit) ? $args->limit : 20;
  $args->offset = $args->limit * ($args->page - 1);
  return $args;
}

function rel_url($url = NULL){
  if(!$url) $url = current_url();
  return substr($url, strposX($url, '/', 3));
}

function strposX($haystack, $needle, $number){
  if($number == '1'){
    return strpos($haystack, $needle);
  }elseif($number > '1'){
    return strpos($haystack, $needle, strposX($haystack, $needle, $number - 1) + strlen($needle));
  }else{
    return error_log('Error: Value for parameter $number is out of range');
  }
}

/**
 * @param $count
 * @param null $roundToNearestNum Round the answer to the nearest X and then return
 * @param null $divide_by Divide the count into X
 * @return string
 */
function _convert_num_to_thousands_string($count, $roundToNearestNum = null, $divide_by = null){
  $count = (int)$count;
  if($count > 0 && $count < 1000){
    return (string) $count;
  } elseif($count >= 1000 && $count < 1000000){
    if(!$divide_by) $divide_by = 1000;
    if(!$roundToNearestNum) $roundToNearestNum = 100;
    return round((round($count / $roundToNearestNum) * $roundToNearestNum) / $divide_by, 1).'k';
  } elseif($count >= 1000000) {
    if(!$divide_by) $divide_by = 1000000;
    if(!$roundToNearestNum) $roundToNearestNum = 100000;
    return round((round($count / $roundToNearestNum) * $roundToNearestNum) / $divide_by, 1).'m';
  }
}

function _generate_id($length = 7){
  $id = '';
  for($i = 0; $i < $length; $i ++) {
    $id .= rand(0, 1) ? rand(0, 9) : (rand(0,1) ? chr(rand(ord('a'), ord('z'))) : chr(rand(ord('A'), ord('Z'))));
  }
  return $id;
}

function _unique_db_value($field, $value, $collection){
  $CI =& get_instance();
  $CI->load->model('main_model');
  return $CI->main_model->is_unique($field, $value, $collection);
}

function _generate_unique_id($collection, $field, $length = 7){
  do {
    $value = _generate_id($length);
  } while(!_unique_db_value($field, $value, $collection));
  return $value;
}