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

function add_contact(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $contactData = array();
  foreach(add_contact_args_map() as $i => $field) if(isset($data[$field])) $contactData[$field] = $data[$field];
  $contactData['settings'] = array(
    'emailUpdates' => ($contactData['emailUpdates'] == 'false' ? false : true),
    'smsUpdates' => ($contactData['smsUpdates'] == 'false' ? false : true)
  );

  $contactData['active'] = true;
  $contactData['dateAdded'] = new MongoDate();
  $contactData['pin'] = _generate_id(4);

  $contactData['organizationId'] = _id($contactData['organizationId']);

  foreach(array('phone','mobile') as $field){
    if(isset($contactData[$field])) $contactData[$field] = str_replace(array('(',')','-',' '), '', $contactData[$field]);
  }

  if(isset($data['contactId']) && !empty($data['contactId'])) {
    $contactId = $data['contactId'];
    $response['response']['newContact'] = false;
  } else {
    // Create Contact
    $contactId = Contact::Create($contactData);
    $response['response']['newContact'] = true;
  }
  $response['response']['contactId'] = $contactId;
  if($contactId){

    // Add Contact to Job
    $job = Job::Get($contactData['jobId']);
    if($job){
      $job->addContactById($contactId, $contactData['role'], false, true);
      $response['response']['success'] = $job->isContact($contactId);
      $contact = Contact::Get($contactId);
      $contact->setValue('role', $contactData['role']);
      $roles = $job->loadWorkflow()->getValue('workflow')->getValue('roles');
      $response['response']['people_html'] = get_include(APPPATH.'/views/widgets/_people-contact-include.php', array('contact' => $contact,'roles' => $roles), true);
      $response['response']['sidebar_html'] = get_include(APPPATH.'/views/widgets/_sidebar-contact-include.php', array('contact' => $contact), true);
    } else {
      $response['errors'][] = 'Error has occurred. Invalid job id provided';
    }
  } else {
    $response['errors'][] = 'Error has occurred. Contact could not be created';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function add_contact_args_map(){
  return array('organizationId','jobId','contactId','name','role','email','phone','mobile','emailUpdates','smsUpdates','active');
}

// Field names of fields required
function add_contact_required_fields(){
  return array('organizationId','jobId','name','role','email');
}

function search_contacts(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $org = Organization::Get($data['organizationId']);
  if($org){
    $contacts = $org->searchContactsByName($data['term']);
    foreach($contacts as $i => $contact) {
      if(isset($contact['phone']) && !empty($contact['phone'])) $contacts[$i]['phone'] = phoneFormat($contact['phone']);
      if(isset($contact['mobile']) && !empty($contact['mobile'])) $contacts[$i]['mobile'] = phoneFormat($contact['mobile']);
    }
    $response['response'] = $contacts;
  } else {
    $response['errors'][] = 'Invalid organization id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function search_contacts_args_map(){
  return array('organizationId','term');
}

// Field names of fields required
function search_contacts_required_fields(){
  return array('organizationId','term');
}

function remove_contact(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response']['success'] = false;
  $job = Job::Get($data['jobId']);
  if($job){
    if($job->isContact($data['contactId'])) $job->removeContact($data['contactId']);
    $response['response']['success'] = true;
  } else {
    $response['errors'][] = 'Invalid job id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function remove_contact_args_map(){
  return array('contactId','jobId');
}

// Field names of fields required
function remove_contact_required_fields(){
  return array('contactId','jobId');
}

function update_contact(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $contactData = array();
  foreach(add_contact_args_map() as $i => $field) if(isset($data[$field])) $contactData[$field] = $data[$field];
  $contactData['settings'] = array(
    'emailUpdates' => ($contactData['emailUpdates'] == 'false' ? false : true),
    'smsUpdates' => ($contactData['smsUpdates'] == 'false' ? false : true)
  );

  $contactData['organizationId'] = _id($contactData['organizationId']);
  $response['response']['updateContactData'] = $contactData;

  foreach(array('phone','mobile') as $field){
    if(isset($contactData[$field])) $contactData[$field] = str_replace(array('(',')','-',' '), '', $contactData[$field]);
  }

  if($contactData['contactId']){
    // Update Contact
    $response['response']['success'] = Contact::Update($contactData['contactId'], $contactData);
    if($response['response']['success']) {
      $job = Job::Get($contactData['jobId']);
      $job->updateContactRole($contactData['contactId'], $contactData['role']);
    }
  } else {
    $response['errors'][] = 'Error has occurred. Contact could not be created';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function update_contact_args_map(){
  return array('organizationId','jobId','contactId','name','role','email','phone','mobile','emailUpdates','smsUpdates','active');
}

// Field names of fields required
function update_contact_required_fields(){
  return array('contactId','name','role','email');
}

function save_meta(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response']['success'] = false;
  $collection = di_decrypt_s($data['collection'], salt());
  $jobId = di_decrypt_s($data['record'], salt());
  $job = Job::Get($jobId);
  $metaArray = $job->getRawMeta();
  $response['response']['rawMeta'] = $metaArray;
  $field = $data['field'];
  if($job){
    $meta = new $data['metaObject']($data['value']);
    if(!$meta->errors()){
      $metaArray[$field] = $meta->get();
      $job->meta()->set('meta', $metaArray)->save('meta');
      $response['response']['raw'] = $meta->get();
      $response['response']['display'] = $meta->display();
      $response['response']['success'] = true;
    }
  } else {
    $response['errors'][] = 'Invalid job id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function save_meta_args_map(){
  return array('metaObject','record','collection','field','value');
}

// Field names of fields required
function save_meta_required_fields(){
  return array('metaObject','record','collection','field','value');
}



