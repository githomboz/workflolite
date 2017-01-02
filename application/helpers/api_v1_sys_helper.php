<?php

if(!function_exists('_api_template')) require 'api_v1__helper.php';

function process_queue(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response'] = TriggerQueueItem::ProcessUnprocessed();
  $response['recordCount'] = count($response['response']);
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function process_queue_args_map(){
  return array();
}

// Field names of fields required
function process_queue_required_fields(){
  return array();
}

function get_process_queue(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response'] = TriggerQueueItem::GetUnprocessed();
  $response['recordCount'] = TriggerQueueItem::GetUnprocessed(null, true);
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function get_process_queue_args_map(){
  return array();
}

// Field names of fields required
function get_process_queue_required_fields(){
  return array();
}

