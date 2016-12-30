<?php

if(!function_exists('_api_template')) require 'api_v1__helper.php';

function send_email_response(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response'] = array('success' => false);
  if(1){
    log_message('debug', json_encode($data));
  } else {
    $response['errors'][] = 'Error found';
  }
  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function send_email_response_args_map(){
  return array();
}

// Field names of fields required
function send_email_response_required_fields(){
  return array();
}

function catch_all_response(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response'] = array('success' => false);
  if(1){
    log_message('debug', json_encode($data));
  } else {
    $response['errors'][] = 'Error found';
  }
  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function catch_all_response_args_map(){
  return array();
}

// Field names of fields required
function catch_all_response_required_fields(){
  return array();
}





