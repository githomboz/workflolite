<?php

require_once APPPATH.'libraries/phpQuery/phpQuery.php';

function html_find_contents($string, $start_string, $end_string = NULL){
  $start_pos = (strpos($string, $start_string) + strlen($start_string));
  $end_pos = $end_string ? (strpos($string, $end_string, $start_pos)) : (strlen($string));
  $length = $end_pos - $start_pos;
  return substr($string, $start_pos, $length);
}

function _handle($handle){
  $handle = str_replace(array('@'),'', $handle);
  return ltrim($handle, '@');
}
