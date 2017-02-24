<?php
// Functions specific to this site/app

class NullValue {
  function __toString()
  { return '';}
}

function CI(){
  return get_instance();
}

function salt(){
  return config_item('PAK');
}

function isNullValue($value){
  return $value instanceof NullValue;
}

function orgSetting($key, $value = null, $default = null){
  if(isset($value) || $value instanceof NullValue){
    // Set
    if($value instanceof NullValue) $value = null;
    organization()->setSettings($key, $value);
    return isset($value) ? $value : $default;
  } else {
    // Get
    $value = organization()->getSettings($key);
    if(!isset($value)) return $default;
    return $value;
  }
}

function userSetting($key, $value = null, $default = null){
  if(isset($value) || $value instanceof NullValue){
    // Set
    if($value instanceof NullValue) $value = null;
    user()->setSettings($key, $value);
    return isset($value) ? $value : $default;
  } else {
    // Get
    $value = user()->getSettings($key);
    if(!isset($value)) return $default;
    return $value;
  }
}

function organization(){
  if(UserSession::loggedIn()) return UserSession::Get_Organization();
  return null;
}

function show_sidebar($set_true = null){
  $CI = CI();
  if(isset($set_true)) $CI->show_sidebar = (bool) $set_true;
  return !isset($CI->show_sidebar) || (isset($CI->show_sidebar) && $CI->show_sidebar);
}

function user(){
  $CI =& CI();
  if(!isset($CI->me) && UserSession::loggedIn()) {
    $CI->me = UserSession::Get_User();
    return CI()->me;
  }
  return null;
}

function page_file_name($__FILE__){
  $segs = explode('/', $__FILE__);
  return str_replace('.php', '', $segs[(count($segs)-1)]);
}

function sortBy($field, &$array, $direction = 'asc')
{
  usort($array, create_function('$a, $b', '
		$a = $a["' . $field . '"];
		$b = $b["' . $field . '"];

		if ($a == $b)
		{
			return 0;
		}

		return ($a ' . ($direction == 'desc' ? '>' : '<') .' $b) ? -1 : 1;
	'));

  return true;
}

function _get_inner_nav($selectedPage, $seg1 = null, $seg2 = null){
  $navItems = array(
    'jobsInner' => array(
      array('slug' => 'tasks', 'href' => '/jobs/{job.id}/{slug}', 'default' => true),
      array('slug' => 'notes', 'href' => '/jobs/{job.id}/{slug}',),
      array('slug' => 'people', 'href' => '/jobs/{job.id}/{slug}',),
      array('slug' => 'time', 'href' => '/jobs/{job.id}/{slug}',),
      array('slug' => 'client-view', 'href' => '/jobs/{job.id}/{slug}',),
    ),
    'projectsInner' => array(
      array('slug' => 'tasks', 'href' => '/projects/{project.id}/{slug}', 'default' => true),
      array('slug' => 'notes', 'href' => '/projects/{project.id}/{slug}',),
      array('slug' => 'people', 'href' => '/projects/{project.id}/{slug}',),
      array('slug' => 'time', 'href' => '/projects/{project.id}/{slug}',),
      array('slug' => 'client-view', 'href' => '/projects/{project.id}/{slug}',),
    ),
    'workflows' => array(
      array('slug' => 'overview', 'href' => '/{page}', 'default' => true),
      array('slug' => 'jobs', 'href' => '/{page}/{slug}', 'hide' => true),
    ),
    'jobs' => array(
      array('slug' => 'overview', 'href' => '{page}', 'default' => true),
    ),
    'templates' => array(
      array('slug' => 'overview', 'href' => '/{page}', 'default' => true),
      array('slug' => 'projects', 'href' => '/{page}/{slug}', 'hide' => true),
    ),
    'projects' => array(
      array('slug' => 'overview', 'href' => '{page}', 'default' => true),
    ),
    'dashboard' => array(
      array('slug' => 'overview', 'href' => '{page}', 'default' => true),
    ),
    'contacts' => array(
      array('slug' => 'overview', 'href' => '{page}', 'default' => true),
    ),
    'users' => array(
      array('slug' => 'overview', 'href' => '{page}', 'default' => true),
    ),
    'search' => array(
      array('slug' => 'overview', 'href' => '{page}', 'default' => true),
    ),
  );
  foreach($navItems as $page => $items){
    foreach($items as $i => $item){
      $navItems[$page][$i]['href'] = str_replace('{slug}', $item['slug'], $item['href']);
      $navItems[$page][$i]['href'] = str_replace('{page}', $page, $navItems[$page][$i]['href']);
      if(!isset($item['name'])) $navItems[$page][$i]['name'] = ucwords(str_replace(array('-','_'), ' ', $item['slug']));
      switch ($page){
        case 'jobsInner' :
          $navItems[$page][$i]['href'] = str_replace('{job.id}', $seg1, $navItems[$page][$i]['href']);
          break;
        case 'projectsInner' :
          $navItems[$page][$i]['href'] = str_replace('{project.id}', $seg1, $navItems[$page][$i]['href']);
          break;
        case 'workflows' :
          $navItems[$page][$i]['href'] = str_replace('{workflow.id}', $seg1, $navItems[$page][$i]['href']);
          break;
        case 'templates' :
          $navItems[$page][$i]['href'] = str_replace('{template.id}', $seg1, $navItems[$page][$i]['href']);
          break;
      }

    }
  }
  return isset($navItems[$selectedPage]) ? $navItems[$selectedPage] : array();
}

function _get_inner_nav_slugs(array $innerNav){
  $items = array();
  foreach($innerNav as $item){
    $items[] = $item['slug'];
  }
  return $items;
}

function _get_inner_nav_default(array $innerNav){
  $items = array();
  foreach($innerNav as $item){
    if(isset($item['default']) && $item['default'] === true) return $item;
  }
  return;
}



function _process_add_task($post, WorkflowFactory $entity){
  $response = array(
    'errors' => array(),
    'success' => false,
    'response' => null
  );
  if($entity instanceof Job || $entity instanceof Project){
    if(isset($post['action']) && $post['action'] == 'add-task'){
      $post['dateAdded'] = new MongoDate();
      $post['name'] = trim($post['name']);
      $post['instructions'] = '';
      $post['nativeTriggers'] = array();
      $post['publiclyAccessible'] = false;
      $post['clientView'] = true;
      $post['optional'] = false;
      $post['activeUsers'] = array();
      $post['assigneeId'] = array();
      $post['triggers'] = array();
      $post['sortOrder'] = 100;
      $post['status'] = 'active';
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
        $entity->insertTaskAfter($taskId, $post['sortOrderAfter']);
        $response['success'] = $tasktemplateId && $taskId;
      }
    } else {
      $response['errors'][] = 'Attempting to add task to invalid entity';
  }
    return $response;
  }
}

function _process_create_job($post){
  if(isset($post['action']) && $post['action'] == 'create-job'){
    $response = array(
      'errors' => array(),
      'success' => false,
      'response' => null
    );

    // Validate
    if($post['name'] == '') $response['errors'][] = 'Name is not set';
    if($post['workflowId'] == '') $response['errors'][] = 'Workflow is not set';

    // Create Records
    if(empty($response['errors'])){
      $jobId = Job::Create($post);
      $response['success']['jobId'] = $jobId;
      $response['success']['name'] = $post['name'];
    }
    return $response;
  }
}

function _process_create_project($post){
  if(isset($post['action']) && $post['action'] == 'create-project'){
    $response = array(
      'errors' => array(),
      'success' => false,
      'response' => null
    );

    // Validate
    if($post['name'] == '') $response['errors'][] = 'Name is not set';
    if($post['templateId'] == '') $response['errors'][] = 'Workflow is not set';

    // Create Records
    if(empty($response['errors'])){
      $post['templateId'] = _id($post['templateId']);
      $post['templateVersion'] = 1;
      $projectId = Project::Create($post);
      $response['success']['projectId'] = $projectId;
      $response['success']['name'] = $post['name'];
    }
    return $response;
  }
}


function phoneFormat($string){
  return '(' . substr($string, 0, 3) . ') ' . substr($string, 3, 3) . '-' . substr($string, 6, 4);
}

function job(){
  if(isset(CI()->job)) return CI()->job;
  return null;
}

function project(){
  if(isset(CI()->project)) return CI()->project;
  return null;
}

function workflow(){
  if(isset(CI()->workflow)) return CI()->workflow;
  return null;
}

function entity(){
  if(project()) return project();
  if(job()) return job();
  return null;
}

function entityType(){
  if(entity() instanceof Project) return 'project';
  if(entity() instanceof Job) return 'job';
  return null;
}

function template($version = null){
  if(isset(CI()->template)) {
    if($version) CI()->template->setVersion($version);
    return CI()->template;
  }
  return workflow();
}

function _wfSimpleSMS($payload){
  return array(
    'response' => $payload,
    'logs' => array(),
    'errors' => false
  );
}

function _wfSimpleEmail($payload){
  $response = Workflo()->Messaging()->SimpleEmail($payload);
  return array(
    'response' => $response,
    'logs' => array(),
    'errors' => false
  );
}

function _bytionApprove_fraudReport($projectId){
  $logger = new WFLogger(__METHOD__, __FILE__);
  $logger->setLine(__LINE__)->addDebug('Entering ...', $projectId);
  $response = null;

  $response = _bytionValidateOrderInfo($projectId);

  $logger->setLine(__LINE__)->addDebug('Exiting ...');
  return $response;
}

function _bytionDeny_fraudReport($projectId){
  $logger = new WFLogger(__METHOD__, __FILE__);
  $logger->setLine(__LINE__)->addDebug('Entering ...', $projectId);
  $response = null;

  if(isset($projectId)){
    $project = Project::Get($projectId);
    if($project){

      // @todo, cancel confirmation
      $confirmation = Confirmations::getByProjectId($project->id());
      if($confirmation) Confirmations::SaveToDb($confirmation->id(), ['confirmed' => false, 'processed' => true]);

      // @todo Get contact
      // Add recipient info
      $contacts = $project->getContacts();
      $emailData = $project->payload();
      if(isset($contacts[0])){
        $logger->setLine(__LINE__)->addDebug('Contact found. Preparing to send "order_rejection" email');
        $emailData['contact'] = $contacts[0]->getRecipientData();
        email_template($emailData['contact']['email'], $emailData, 'order_rejection');
      }
      // Mark "Place Order" complete
      $task2 = $project->getTaskByName('Validate Order Info');
      $task2->error();

      $response = [
        'test' => 'Fraud Report',
        'value' => 'deny',
        'success' => true
      ];
      $project->meta()->set(['fraudApproval'=> false])->save();
      $logger->setLine(__LINE__)->addDebug('Fraud Denial True');
    } else {
      $logger->setLine(__LINE__)->addError('Project is invalid');
    }
  } else {
    $logger->setLine(__LINE__)->addError('Project ID not set');
  }

  $logger->setLine(__LINE__)->addDebug('Exiting ...');
  return array(
    'response' => $response,
    'logs' => $logger->getMessages(),
    'errors' => !empty($logs['errors'])
  );
}

function _bytionValidateOrderInfo($projectId){
  $logger = new WFLogger(__METHOD__, __FILE__);
  $logger->setLine(__LINE__)->addDebug('Entering ...', $projectId);
  $response = null;

  if(isset($projectId)){
    $project = Project::Get($projectId);
    if($project){

      $task1 = $project->getTaskByName('Validate Order Info');
      $logger->setLine(__LINE__)->addDebug('Task instance of Task2', $task1 instanceof Task2);
      $isComplete = $task1->isComplete();
      $isError = $task1->isErrored();
      $logger->setLine(__LINE__)->addDebug('Task isComplete', $isComplete);
      $logger->setLine(__LINE__)->addDebug('Task isError', $isError);
      if(!$isComplete && !$isError){

        $metaArray = $project->getRawMeta();
        // Set fraudReportApproval to true
        $metaArray['fraudReportApproved'] = true;
        $project->meta()->set('meta', $metaArray)->save('meta');
        // Mark "Validate Order Info" complete
        // Send email to customer w/ cancellation link
        CI()->load->helper('communications');

        // @todo, cancel confirmation
        $confirmation = Confirmations::getByProjectId($project->id());
        if($confirmation) Confirmations::SaveToDb($confirmation->id(), ['confirmed' => true, 'processed' => true]);

        // @todo Get contact
        // Add recipient info
        $contacts = $project->getContacts();
        $emailData = $project->payload();
        if(isset($contacts[0])){
          $logger->setLine(__LINE__)->addDebug('Contact found. Preparing to send "order_received" email');
          $emailData['contact'] = $contacts[0]->getRecipientData();
          email_template($emailData['contact']['email'], $emailData, 'order_received');
        }
        // Mark "Place Order" complete
        $task1->complete(false);
        $task2 = $project->getTaskByName('Place Order');
        $task2->complete();

        $response = [ // Not used anywhere yet
          'test' => 'Fraud Report',
          'value' => 'approve',
          'success' => true
        ];
        $project->meta()->set(['fraudApproval'=> true])->save();
        $logger->setLine(__LINE__)->addDebug('Fraud Approval True');
      } else {
        $logger->setLine(__LINE__)->addDebug('Task status is not complete or error', $task1->getValue('status'));
      }
    } else {
      $logger->setLine(__LINE__)->addError('Project is invalid');
    }
  } else {
    $logger->setLine(__LINE__)->addError('Project ID not set');
  }

  $logger->setLine(__LINE__)->addDebug('Exiting ...');
  return array(
    'response' => $response,
    'logs' => $logger->getMessages(),
    'errors' => !empty($logs['errors'])
  );
}