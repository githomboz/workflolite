<?php
// Functions specific to this site/app

function _process_add_task($post){
  if(isset($post['action']) && $post['action'] == 'add-task'){
    $response = array(
      'errors' => array(),
      'success' => false,
      'response' => null
    );
    $post['dateAdded'] = new MongoDate();
    $post['name'] = trim($post['name']);
    $post['instructions'] = '';
    $post['nativeTriggers'] = array();
    $post['publiclyAccessible'] = false;
    $post['visibility'] = false;
    $post['optional'] = false;
    $post['activeUsers'] = array();
    $post['assigneeId'] = array();
    $post['triggers'] = array();
    $post['sortOrder'] = 100;
    $post['status'] = 'new';
    $post['jobId'] = _id($post['jobId']);
    $post['organizationId'] = _id($post['organizationId']);
    $post['workflowId'] = _id($post['workflowId']);
    $post['estimatedTime'] = (int) $post['estimatedTime'];

    // Validate
    if($post['name'] == '') $response['errors'][] = 'Name is not set';
    if($post['taskGroup'] == '') $response['errors'][] = 'Task group is not set';
    if(empty($post['estimatedTime'])) $response['errors'][] = 'Estimated completion time is not set';

    // Create Records
    if(empty($response['errors'])){
      $tasktemplateId = TaskTemplate::Create($post);
      $post['taskTemplateId'] = $tasktemplateId;
      $taskId = Task::Create($post);
      $response['taskTemplateId'] = $tasktemplateId;
      $response['taskId'] = $taskId;
      $response['success'] = $tasktemplateId && $taskId;
    }
    return $response;
  }
}