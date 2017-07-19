<?php

if(!function_exists('_api_template')) require 'api_v1__helper.php';

// Accessible via http://www.appname.com/api/v1/general/details/arg1/arg2/arg3[...]
function mark_complete(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $task = null;
  if(strtolower($data['type']) == 'project'){
    $entity = Project::Get($data['entityId']);
    $task = $entity->getTaskById($data['taskId']);
  }

  if($task->id()){
    if(!$task->isStarted()) {
      $task->start();
      $response['response']['startDate'] = date('m/d/y', $task->getValue('startDate')->sec);
    }
    $task->complete();
    $response['response']['entityType'] = 'tasks';
    $response['response']['taskId'] = (string) $task->id();
    $response['response']['endDate'] = date('m/d/y', $task->getValue('completeDate')->sec); //@todo might still be in use
    $response['response']['taskUpdates'] = [
      'endDate' => date('m/d/y', $task->getValue('completeDate')->sec),
      'status' => 'completed'
    ];
  } else {
    $response['errors'][] = 'Invalid task id provided';
  }

  $response['response']['endDate'] = date('m/d/y');

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function mark_complete_args_map(){
  return array('entityId', 'type', 'taskId');
}

// Field names of fields required
function mark_complete_required_fields(){
  return array('entityId', 'type', 'taskId');
}

function mark_incomplete(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $task = null;
  if($data['type'] == 'Project'){
    $entity = Project::Get($data['entityId']);
    $task = $entity->getTaskById($data['taskId']);
  } else {
    $task = Task::Get($data['taskId']);
  }

  if($task){
    $task->incomplete();
    $response['response']['entityType'] = 'tasks';
    $response['response']['taskId'] = (string) $task->id();
    $response['response']['taskUpdates'] = [
      'completeDate' => null,
      'completionReport' => null,
      'status' => Task2::$statusActive
    ];
  } else {
    $response['errors'][] = 'Invalid task id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function mark_incomplete_args_map(){
  return array('entityId', 'type', 'taskId');
}

// Field names of fields required
function mark_incomplete_required_fields(){
  return array('entityId', 'type', 'taskId');
}

function clear_dependency_checks(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $task = null;
  if($data['type'] == 'Project'){
    $entity = Project::Get($data['entityId']);
    $task = $entity->getTaskById($data['taskId']);
  } else {
    $task = Task::Get($data['taskId']);
  }

  if($task){
    $task->clearDependencyChecks();
    $response['response']['entityType'] = 'tasks';
    $response['response']['taskId'] = (string) $task->id();
    $response['response']['taskUpdates'] = [
      'dependenciesOKTimeStamp' => false
    ];
  } else {
    $response['errors'][] = 'Invalid task id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function clear_dependency_checks_args_map(){
  return array('entityId', 'type', 'taskId');
}

// Field names of fields required
function clear_dependency_checks_required_fields(){
  return array('entityId', 'type', 'taskId');
}

function start_task(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $task = null;
  if($data['type'] == 'Project'){
    $entity = Project::Get($data['entityId']);
    $task = $entity->getTaskById($data['taskId']);
  } else {
    $task = Task::Get($data['taskId']);
  }

  if($task) {
    if (!$task->isStarted()) {
      $task->start();
      $response['response']['startDate'] = date('m/d/y', $task->getValue('startDate')->sec);
      $response['response']['entityType'] = 'tasks';
      $response['response']['taskId'] = (string) $task->id();
    }
  } else {
    $response['errors'][] = 'Invalid task id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function start_task_args_map(){
  return array('taskId','entityId','type');
}

// Field names of fields required
function start_task_required_fields(){
  return array('taskId','entityId','type');
}

function save_comment(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $task = null;
  if($data['type'] == 'Project'){
    $entity = Project::Get($data['entityId']);
    $task = $entity->getTaskById($data['taskId']);
  } else {
    $task = Task::Get($data['taskId']);
  }

  if($task) {
    $task->setComments($data['comments']);
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
  return array('taskId','comments','entityId','type');
}

// Field names of fields required
function save_comment_required_fields(){
  return array('taskId','comments','entityId','type');
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

    // Add Contact to Entity
    $entity = $data['type'] == 'Project' ? Project::Get($data['entityId']) : Job::Get($data['entityId']);
    if($entity){
      $entity->addContactById($contactId, $contactData['role'], false, true);
      $response['response']['success'] = $entity->isContact($contactId);
      $contact = Contact::Get($contactId);
      $contact->setValue('role', $contactData['role']);
      $roles = $entity->loadTemplate()->getValue('template')->getValue('roles');
      $response['response']['people_html'] = get_include(APPPATH.'/views/widgets/_people-contact-include.php', array('contact' => $contact,'roles' => $roles), true);
      $response['response']['sidebar_html'] = get_include(APPPATH.'/views/widgets/_sidebar-contact-include.php', array('contact' => $contact), true);
    } else {
      $response['errors'][] = 'Error has occurred. Invalid entity id provided';
    }
  } else {
    $response['errors'][] = 'Error has occurred. Contact could not be created';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function add_contact_args_map(){
  return array('organizationId','entityId','type','contactId','name','role','email','phone','mobile','emailUpdates','smsUpdates','active');
}

// Field names of fields required
function add_contact_required_fields(){
  return array('organizationId','entityId','type','name','role','email');
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
  $entity = $data['type'] == 'Project' ? Project::Get($data['entityId']) : Job::Get($data['entityId']);
  if($entity){
    if($entity->isContact($data['contactId'])) $entity->removeContact($data['contactId']);
    $response['response']['success'] = true;
  } else {
    $response['errors'][] = 'Invalid job id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function remove_contact_args_map(){
  return array('contactId','entityId','type');
}

// Field names of fields required
function remove_contact_required_fields(){
  return array('contactId','entityId','type');
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
      $entity = $data['type'] == 'Project' ? Project::Get($data['entityId']) : Job::Get($data['entityId']);
      $entity->updateContactRole($contactData['contactId'], $contactData['role']);
    }
  } else {
    $response['errors'][] = 'Error has occurred. Contact could not be created';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function update_contact_args_map(){
  return array('organizationId','entityId','type','contactId','name','role','email','phone','mobile','emailUpdates','smsUpdates','active');
}

// Field names of fields required
function update_contact_required_fields(){
  return array('contactId','name','role','email','entityId','type');
}

function save_meta(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response']['success'] = false;
  $collection = di_decrypt_s($data['collection'], salt());
  $entityId = di_decrypt_s($data['record'], salt());
  $entity = $data['type'] == 'Project' ? Project::Get($entityId) : Job::Get($entityId);

  $metaArray = $entity->getRawMeta();
  $response['response']['rawMeta'] = $metaArray;
  $field = $data['field'];
  if($entity){
    $meta = new $data['metaObject']($data['value']);
    if(!$meta->errors()){
      $metaArray[$field] = $meta->get();
      $entity->meta()->set('meta', $metaArray)->save('meta');
      $response['response']['raw'] = $meta->get();
      $response['response']['display'] = $meta->display();
      $response['response']['success'] = true;
    } else {
      if(!is_array($response['errors'])) $response['errors'] = [];
      $response['errors'] = array_merge($response['errors'], (array) $meta->errors());
    }
  } else {
    $response['errors'][] = 'Invalid entity id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function save_meta_args_map(){
  return array('metaObject','record','collection','field','value','type');
}

// Field names of fields required
function save_meta_required_fields(){
  return array('metaObject','record','collection','field','value','type');
}

function post_note(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response']['success'] = false;
  $entity = $data['type'] == 'Project' ? Project::Get($data['entityId']) : Job::Get($data['entityId']);

  if($entity){

    $current_author = UserSession::Get_User();
    $user = User::Get($data['author']['id']);

    $current_author_id = $current_author->id();

    $note = array(
      'datetime' => date('c'),
      'author' => array(
        'id' => $data['author']['id'],
        'type' =>  $data['author']['type'],
        'shortName' => $user->getValue('firstName') . ' ' . substr($user->getValue('lastName'), 0, 1) . '.'
      ),
      'content' => nl2br($data['note']),
      'verb' => '',
      'noun' => '',
      'currentTaskId' => $entity->getNextTask()->id(),
      'tags' => array(),
      'reference' => null
    );


    if(is_array($data['tags'])){
      foreach($data['tags'] as $tagData){
        $note['tags'][] = $tagData['value'];
      }

    }

    $response['response']['test'] = array(
      $data['note'], strlen($data['note']), nl2br($data['note']), strlen(nl2br($data['note']))
    );

    $note['id'] = $entity->addNote($note);

    $response['response']['success'] = (bool) $note['id'];

    $response['response']['payload'] = $note;

    $response['response']['noteHTML'] = get_include(APPPATH.'/views/widgets/_notes-list.php', array('notes'=> array($note), 'current_author_id'=>$current_author_id), true);

    $response['response']['noteHTML'] = str_replace(array(), '', $response['response']['noteHTML']);


  } else {
    $response['errors'][] = 'Invalid entity id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function post_note_args_map(){
  return array('entityId','author','note','tags','reference','type');
}

// Field names of fields required
function post_note_required_fields(){
  return array('entityId','author','note','type');
}

function delete_note(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response']['success'] = false;
  $entity = $data['type'] == 'Project' ? Project::Get($data['entityId']) : Job::Get($data['entityId']);
  if($entity){

    $response['response']['success'] = $entity->deleteNote($data['noteId']);

  } else {
    $response['errors'][] = 'Invalid entity id provided';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function delete_note_args_map(){
  return array('entityId','noteId', 'type');
}

// Field names of fields required
function delete_note_required_fields(){
  return array('entityId','noteId', 'type');
}

function task_template_form(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $template = Template::Get($data['templateId'], $data['version']);
  $taskTemplates = $template->getTemplates();
  $templateCount = count($taskTemplates);
  $templateId = $template->id();
  $response['response'] = get_include(APPPATH.'views/widgets/_task-template-details.php', array('templateCount'=>$templateCount, 'templateId' => (string) $templateId, 'version' => $data['version']), true);
  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function task_template_form_args_map(){
  return array('templateId', 'version');
}

// Field names of fields required
function task_template_form_required_fields(){
  return array('templateId', 'version');
}

function remove_role(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $template = Template::Get($data['templateId'], $data['version']);
  $response['response']['success'] = $template->removeRole($data['role']);
  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function remove_role_args_map(){
  return array('templateId', 'role','version');
}

// Field names of fields required
function remove_role_required_fields(){
  return array('templateId', 'role','version');
}


function remove_meta(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $template = Template::Get($data['templateId'], $data['version']);
  $response['response']['success'] = $template->removeMeta($data['metaKey']);
  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function remove_meta_args_map(){
  return array('templateId', 'metaKey','version');
}

// Field names of fields required
function remove_meta_required_fields(){
  return array('templateId', 'metaKey','version');
}

function remove_task_template(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $template = Template::Get($data['templateId'], $data['version']);
  $response['response'] = array('success' => false);
  if($template->taskTemplateExists($data['taskTemplateId'])){
    if($template->removeTaskTemplate($data['taskTemplateId'])){
      $response['response']['success'] = true;
      $response['response']['taskTemplateCount'] = $template->taskCount();
    } else {
      $response['errors'][] = 'An error has occurred while attempting to make this update';
    }
  } else {
    $response['errors'][] = 'No task template matching this id';
  }
  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function remove_task_template_args_map(){
  return array('templateId', 'taskTemplateId', 'version');
}

// Field names of fields required
function remove_task_template_required_fields(){
  return array('templateId', 'taskTemplateId', 'version');
}

function wf_logger(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response']['entries'] = WFLogger::Read((array) CI()->input->get());
  $response['recordCount'] = WFLogger::Read((array) CI()->input->get(), true);
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function wf_logger_args_map(){
  return array();
}

// Field names of fields required
function wf_logger_required_fields(){
  return array();
}

function update_task_template(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  if(!isset($data['version'])) $data['version'] = 1;
  $saved = Template::UpdateTaskTemplate($data['templateId'], $data['taskTemplateId'], $data['updates'], $data['version']);
  $response['response']['saved'] = $saved;//WFLogger::Read((array) CI()->input->get());
  $response['recordCount'] = 0;//WFLogger::Read((array) CI()->input->get(), true);
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function update_task_template_args_map(){
  return array('templateId','taskTemplateId','updates');
}

// Field names of fields required
function update_task_template_required_fields(){
  return array('templateId','taskTemplateId','updates');
}

function update_task_template_block(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  if(!isset($data['version'])) $data['version'] = 1;
  $saved = Template::UpdateTaskTemplateParsed($data['templateId'], $data['taskTemplateId'], $data['updates'], $data['version']);
  $response['response']['saved'] = $saved;//WFLogger::Read((array) CI()->input->get());
  $response['recordCount'] = 0;//WFLogger::Read((array) CI()->input->get(), true);
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function update_task_template_block_args_map(){
  return array('templateId','taskTemplateId','updates');
}

// Field names of fields required
function update_task_template_block_required_fields(){
  return array('templateId','taskTemplateId','updates');
}

function update_template_settings(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  // Do type cast if necessary
  if(isset($data['typeCasts'])){
    foreach($data['settings'] as $k => $v){
      if(isset($data['typeCasts'][$k])){
        switch($data['typeCasts'][$k]){
          case 'bool':
          case 'boolean':
            if(strtolower($v) === 'true') $v = true;
            if(strtolower($v) === 'false') $v = false;
          $data['settings'][$k] = (bool) $v;
            break;
          default:
            break;
        }
      }
    }
  }
  $saved = Template::SetTemplateSettings($data['templateId'], $data['settings']);
  //var_dump($data);
  $response['response']['saved'] = $saved;
  $response['recordCount'] = 0;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function update_template_settings_args_map(){
  return array('templateId','settings','typeCasts');
}

// Field names of fields required
function update_template_settings_required_fields(){
  return array('templateId','settings');
}

function sample_task_data(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response'] = Task2::SampleTaskSetup();
  $response['recordCount'] = 0;
  return $response;
}

function check_task_dependencies(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $project = Project::Get($data['projectId']);
  $response['response']['taskId'] = $data['taskId'];
  $response['response']['projectId'] = $data['projectId'];
  if($project){
    $report = $project->checkTaskDependencies($data['taskId']);
    //var_dump($dependenciesResults);
    if(isset($data['returnReport']) && true === (bool) $data['returnReport']){
      switch ($data['returnReport']){
        case 'condensed':
          $modified = $report;
          foreach($modified['response']['callbacks'] as $i => $execution){
            unset($modified['response']['callbacks'][$i]['tests']);
            unset($modified['response']['callbacks'][$i]['fnExecMethod']);
            unset($modified['response']['callbacks'][$i]['fnParamsData']);
            unset($modified['response']['callbacks'][$i]['fnResponseType']);
            unset($modified['response']['callbacks'][$i]['fnResponse']);
          }
          unset($modified['response']['taskId']);
          unset($modified['logs']['debug']);
          $response['response']['report'] = $modified;
          break;
        default:
          $response['response']['report'] = $report;
          break;
      }
    }
    if(!$report['errors']){
      // save dependenciesOKTimeStamp and return it to client
      $time = time();
      $project->getTaskById($data['taskId'])->setValue('dependenciesOKTimeStamp', $time)->update();
      $response['response']['taskId'] = $data['taskId'];
      $response['response']['taskUpdates']['dependenciesOKTimeStamp'] = $time;

    } else {
      if(!$response['errors']) $response['errors'] = [];
      $response['errors'] = array_merge((array) $response['errors'], $report['logs']['errors']);
    }
  }

  $response['recordCount'] = 0;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function check_task_dependencies_args_map(){
  return array('projectId','taskId','returnReport');
}

// Field names of fields required
function check_task_dependencies_required_fields(){
  return array('projectId','taskId');
}

function run_lambda_routines(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $saltedData = isset($data['saltedData']) ? $data['saltedData'] : null;
  $salt = isset($data['salt']) ? $data['salt'] : null;
  if($saltedData && $salt) $saltedData = di_decrypt_s($saltedData, $salt);

  $callback = null;
  if(is_array($saltedData) && isset($saltedData['trigger'])){
    $triggerData = $saltedData['trigger'];
  } else {
    $project = Project::Get($data['projectId']);
    $task = $project->getTaskById($data['taskTemplateId']);
    $triggerData = $task->getValue('trigger');
  }

  // Check if triggerData is valid
  $issetTriggerType = isset($triggerData['type']) && $triggerData['type'] == 'lambda';
  $issetTriggerId = isset($triggerData['triggerId']) && !empty($triggerData['triggerId']);
  $validTriggerData = $issetTriggerId && $issetTriggerType;

  //var_dump($issetTriggerId, $issetTriggerType, $validTriggerData);

  // Check if trigger has already been validated
  $callbackSet = isset($saltedData['callback']);
  if($callbackSet) $callback = $saltedData['callback'];
  // If not validated, check if trigger is valid
  $triggerSet = isset($saltedData['validTrigger']);
  // Get trigger callback

  $validTrigger = is_array($saltedData) && (isset($saltedData['validTrigger']) && $saltedData['validTrigger']) === true;
  if($validTriggerData && (!$triggerSet || !$callbackSet)){
    $trigger = Trigger2::Get($triggerData['triggerId']);
    //var_dump($triggerData['triggerId'], $trigger);
    if((string) $trigger->id() == $triggerData['triggerId']) $validTrigger = true;
    $callback = $trigger->getValue('callback');
    $callbackSet = true;
  }

  $newSalt = is_array($saltedData) && isset($saltedData['salt']) ? $saltedData['salt'] : md5(time());

  if($issetTriggerType){

    if($issetTriggerId){

      if($validTrigger){

        $response['response'] = [
          'slug' => $data['slug'],
          'success' => false,
          'salt' => $newSalt,
          'saltedData' => $saltedData,
          'data' => [
            'triggerCallback' => $callback,
            'triggerDefaultOptions' => null,
            'triggerId' => $triggerData['triggerId'],
            'validTrigger' => $validTrigger,
            'slug' => $data['slug'],
            'salt' => $newSalt
          ],
          'taskId' => $data['taskTemplateId']
        ];

        switch ($data['slug']){
          case 'validate_lambda_callback':
            $response['response']['data']['method'] = WF::_ValidateCallback($callback);
            $response['response']['success'] = (bool) $response['response']['data']['method'];
            break;
          case 'execute_lambda_callback':
            $response['response']['data']['method'] = WF::_ValidateCallback($callback);

            try {
              switch ($response['response']['data']['method']){
                case 'is_callable':
                  $result = call_user_func_array($callback, []);
                  $resultType = is_array($result) ? 'array' : (is_bool($result) ? 'bool' : null);
                  switch($resultType){
                    case 'array':
                      $response['response']['metaUpdates'] = isset($result['metaUpdates']) ? $result['metaUpdates'] : null;
                      $response['response']['taskUpdates'] = isset($result['taskUpdates']) ? $result['taskUpdates'] : null;
                      break;
                    case 'bool':
                      break;
                  }
                  $response['response']['callbackResponse'] = $result;
                  break;
              }
              if($result) $response['response']['success'] = true;

            }

            catch(Exception $e){
              $response['errors'][] = $e->getMessage();
            }
            //var_dump($response);
            break;
          case 'analyze_callback_results':
            $response['response']['success'] = true;
            break;
        }
        //$response['response']['data'] = di_encrypt_s($response['response']['data'], $response['response']['salt']);

      } else {
        $response['errors'][] = 'Lambda is invalid';
      }

    } else {
      $response['errors'][] = 'Lambda provided must be valid';
    }

  } else {
    $response['errors'][] = 'Trigger type must be lambda function';
  }


  $response['recordCount'] = 0;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function run_lambda_routines_args_map(){
  return array('projectId','taskTemplateId', 'routine','slug', 'saltedData', 'salt');
}

// Field names of fields required
function run_lambda_routines_required_fields(){
  return array('projectId','taskTemplateId', 'routine','slug');
}

function run_form_routines(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $saltedData = isset($data['saltedData']) ? $data['saltedData'] : null;
  $salt = isset($data['salt']) ? $data['salt'] : null;
  if($saltedData && $salt) $saltedData = di_decrypt_s($saltedData, $salt);

  $callback = null;
  if(is_array($saltedData) && isset($saltedData['trigger'])){
    $triggerData = $saltedData['trigger'];
  } else {
    $project = Project::Get($data['projectId']);
    $task = $project->getTaskById($data['taskTemplateId']);
    $triggerData = $task->getValue('trigger');
  }

  // Check if triggerData is valid
  $issetTriggerType = isset($triggerData['type']) && $triggerData['type'] == 'form';
  $issetTriggerId = isset($triggerData['triggerId']) && !empty($triggerData['triggerId']);
  $validTriggerData = $issetTriggerId && $issetTriggerType;

  //var_dump($issetTriggerId, $issetTriggerType, $validTriggerData);

  // Check if trigger has already been validated
  $callbackSet = isset($saltedData['callback']);
  if($callbackSet) $callback = $saltedData['callback'];
  // If not validated, check if trigger is valid
  $triggerSet = isset($saltedData['validTrigger']);
  // Get trigger callback

  $validTrigger = is_array($saltedData) && (isset($saltedData['validTrigger']) && $saltedData['validTrigger']) === true;
  if($validTriggerData && (!$triggerSet || !$callbackSet)){
    $trigger = Trigger2::Get($triggerData['triggerId']);
    //var_dump($triggerData['triggerId'], $trigger);
    if((string) $trigger->id() == $triggerData['triggerId']) $validTrigger = true;
    $callback = $trigger->getValue('callback');
    $callbackSet = true;
  }

  $newSalt = is_array($saltedData) && isset($saltedData['salt']) ? $saltedData['salt'] : md5(time());

  if($issetTriggerType){

    if($issetTriggerId){

      if($validTrigger){

        $response['response'] = [
          'slug' => $data['slug'],
          'success' => false,
          'salt' => $newSalt,
          'saltedData' => $saltedData,
          'data' => [
            'triggerCallback' => $callback,
            'triggerDefaultOptions' => null,
            'triggerId' => $triggerData['triggerId'],
            'validTrigger' => $validTrigger,
            'slug' => $data['slug'],
            'salt' => $newSalt
          ]
        ];

        $typeMeta = $trigger->getValue('typeMeta');
        // Check if form is embedded or referenced
        $isEmbedded = $typeMeta['embedded'] && !empty($typeMeta['form']);
        // If embedded, return the embedded form, if not, get it from the db
        $formData = $isEmbedded ? $typeMeta['form'] : WFSimpleForm::GetFormData($typeMeta['formId']);

        switch ($data['slug']){
          case 'validate_form':
            $validateData = WFSimpleForm::VerifyFormTypeFormat($formData);
            if($validateData['response']['success']){
              $response['response']['success'] = true;
            } else {
              $response['errors'][] = 'Error validating form type format';
            }
            break;
          case 'render_form':
            $validateData = WFSimpleForm::RenderForm($formData);
            if($validateData['response']['success']){
              $response['response']['success'] = true;
              $response['response']['_form'] = $validateData['response']['data'];
            } else {
              $response['errors'][] = 'Error rendering this form';
            }
            break;
        }
        //$response['response']['data'] = di_encrypt_s($response['response']['data'], $response['response']['salt']);

      } else {
        $response['errors'][] = 'Form is invalid';
      }

    } else {
      $response['errors'][] = 'Form provided must be valid';
    }

  } else {
    $response['errors'][] = 'Trigger type must be form';
  }


  $response['recordCount'] = 0;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function run_form_routines_args_map(){
  return array('projectId','taskTemplateId', 'routine','slug', 'saltedData', 'salt');
}

// Field names of fields required
function run_form_routines_required_fields(){
  return array('projectId','taskTemplateId', 'routine','slug');
}

function generate_completion_report(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $project = Project::Get($data['projectId']);
  $response['response']['taskId'] = $data['taskId'];
  if($project){
    $report = $project->checkCompletionScripts($data['taskId']);

    if(isset($data['returnReport']) && true === (bool) $data['returnReport']){
      switch ($data['returnReport']){
        case 'condensed':
          $modified = $report;
          foreach($modified['response']['callbacks'] as $i => $execution){
            unset($modified['response']['callbacks'][$i]['tests']);
            unset($modified['response']['callbacks'][$i]['fnExecMethod']);
            unset($modified['response']['callbacks'][$i]['fnParamsData']);
            unset($modified['response']['callbacks'][$i]['fnResponseType']);
            unset($modified['response']['callbacks'][$i]['fnResponse']);
          }
          unset($modified['response']['taskId']);
          unset($modified['logs']['debug']);
          $response['response']['report'] = $modified;
          break;
        default:
          $response['response']['report'] = $report;
          break;
      }
    }

    $response['response']['taskUpdates']['completionReport'] = $report;

    if(!$report['errors']){
      // Save completionReport to task
      $project->getTaskById($data['taskId'])->setValue('completionReport', (array) json_decode(json_encode($report), true))->update();
    } else {
      if(!$response['errors']) $response['errors'] = [];
      $response['errors'] = array_merge((array) $response['errors'], $report['logs']['errors']);
    }
  }

  $response['recordCount'] = 0;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function generate_completion_report_args_map(){
  return array('projectId','taskId','returnReport');
}

// Field names of fields required
function generate_completion_report_required_fields(){
  return array('projectId','taskId');
}

function validate_meta_field(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response']['success'] = false;
  $meta = new $data['metaObject']($data['value']);
  if(!$meta->errors()){
    $response['response']['success'] = true;
  } else {
    if(!is_array($response['errors'])) $response['errors'] = [];
    $response['errors'] = array_merge($response['errors'], (array) $meta->errors());
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function validate_meta_field_args_map(){
  return array('metaObject','value');
}

// Field names of fields required
function validate_meta_field_required_fields(){
  return array('metaObject','value');
}

function save_meta_field(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $project = Project::Get($data['projectId']);

  $response['response']['success'] = false;

  if($project){
    $metaArray = $project->getRawMeta();
    $meta = new $data['metaObject']($data['value']);
    if(!$meta->errors()){
      $metaArray[$data['slug']] = $meta->get();
      $field = $data;
      unset($field['metaObject']);
      unset($field['projectId']);

      //var_dump($data, $metaArray);
      // Check if local or template store


      // If local, save meta field data to localMetaFields
      // If template, save to metaFields in template

      // Save value to project
      $project->meta()->set('meta', $metaArray)->save('meta');
      $response['response']['success'] = true;
    } else {
      if(!is_array($response['errors'])) $response['errors'] = [];
      $response['errors'] = array_merge($response['errors'], (array) $meta->errors());
    }

  } else {
    $response['errors'][] = 'Invalid projectId provided';
  }


  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function save_meta_field_args_map(){
  return array('metaObject','projectId','slug','value');
}

// Field names of fields required
function save_meta_field_required_fields(){
  return array('metaObject','projectId','slug','value');
}

