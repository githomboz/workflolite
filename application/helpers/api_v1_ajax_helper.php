<?php

if(!function_exists('_api_template')) require 'api_v1__helper.php';

// Accessible via http://www.appname.com/api/v1/general/details/arg1/arg2/arg3[...]
function mark_complete(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];


  $task = Task::Get($data['taskId']);
  if($task){
    $task->complete();
    if(!$task->isStarted()) {
      $task->start();
      $response['response']['startDate'] = date('m/d/y', $task->getValue('startDate')->sec);
    }
    $response['response']['entityType'] = 'tasks';
    $response['response']['taskId'] = (string) $task->id();
    $response['response']['endDate'] = date('m/d/y', $task->getValue('completeDate')->sec);
  } else {
    $response['errors'][] = 'Invalid task id provided';
  }

  $response['response']['endDate'] = date('m/d/y');

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function mark_complete_args_map(){
  return array('taskId');
}

// Field names of fields required
function mark_complete_required_fields(){
  return array('taskId');
}

function start_task(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $task = Task::Get($data['taskId']);
  if($task){
    $task->start();
    $response['response']['entityType'] = 'tasks';
    $response['response']['taskId'] = (string) $task->id();
    $response['response']['startDate'] = date('m/d/y', $task->getValue('startDate')->sec);
  } else {
    $response['errors'][] = 'Invalid task id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function start_task_args_map(){
  return array('taskId');
}

// Field names of fields required
function start_task_required_fields(){
  return array('taskId');
}

function save_comment(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $task = Task::Get($data['taskId']);
  if($task){
    $task->setValue('comments', trim($data['comments']))->save('comments');
    $response['response']['entityType'] = 'tasks';
    $response['response']['taskId'] = (string) $task->id();
    $response['response']['comments'] = $task->getValue('comments');
  } else {
    $response['errors'][] = 'Invalid task id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function save_comment_args_map(){
  return array('taskId','comments');
}

// Field names of fields required
function save_comment_required_fields(){
  return array('taskId','comments');
}

