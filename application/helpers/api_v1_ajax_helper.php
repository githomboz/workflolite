<?php

if(!function_exists('_api_template')) require 'api_v1__helper.php';

// Accessible via http://www.appname.com/api/v1/general/details/arg1/arg2/arg3[...]
function mark_complete(){
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
    if(!$task->isStarted()) {
      $task->start();
      $response['response']['startDate'] = date('m/d/y', $task->getValue('startDate')->sec);
    }
    $task->complete();
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
  return array('entityId', 'type', 'taskId');
}

// Field names of fields required
function mark_complete_required_fields(){
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
  if($project){
    $dependenciesResults = $project->checkTaskDependencies($data['taskId']);
    if($dependenciesResults['ok']){
      $response['response']['taskUpdates']['dependenciesOKTimeStamp'] = time();
    }
  }

  $response['recordCount'] = 0;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function check_task_dependencies_args_map(){
  return array('projectId','taskId');
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

  $response['response']['saved'] = true;
  $response['recordCount'] = 0;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function run_lambda_routines_args_map(){
  return array('projectId','taskTemplateId', 'routine');
}

// Field names of fields required
function run_lambda_routines_required_fields(){
  return array('projectId','taskTemplateId', 'routine');
}

function generate_completion_script_results(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response']['metaUpdates'] = []; // Updates to be made to meta data
  $response['response']['taskUpdates'] = []; // Updates to be made to task json
  $response['recordCount'] = 0;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function generate_completion_script_results_args_map(){
  return array('projectId','taskTemplateId');
}

// Field names of fields required
function generate_completion_script_results_required_fields(){
  return array('projectId','taskTemplateId');
}

