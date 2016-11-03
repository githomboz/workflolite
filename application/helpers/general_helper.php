<?php
// Functions specific to this site/app

function CI(){
  return get_instance();
}

function salt(){
  return config_item('PAK');
}

function organization(){
  if(UserSession::loggedIn()) return UserSession::Get_Organization();
  return null;
}

function user(){
  $CI =& CI();
  if(!isset($CI->me) && UserSession::loggedIn()) {
    $CI->me = UserSession::Get_User();
    return CI()->me;
  }
  return null;
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
    'workflows' => array(
      array('slug' => 'overview', 'href' => '/{page}', 'default' => true),
      //array('slug' => 'reports', 'href' => '/{page}/{slug}',),
      array('slug' => 'jobs', 'href' => '/{page}/{slug}', 'hide' => true),
    ),
    'dashboard' => array(
      array('slug' => 'overview', 'href' => '{page}', 'default' => true),
    ),
    'jobs' => array(
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
        case 'workflows' :
          $navItems[$page][$i]['href'] = str_replace('{workflow.id}', $seg1, $navItems[$page][$i]['href']);
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



function _process_add_task($post, Job $job){
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
      $job->insertTaskAfter($taskId, $post['sortOrderAfter']);
      $response['success'] = $tasktemplateId && $taskId;
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


function phoneFormat($string){
  return '(' . substr($string, 0, 3) . ') ' . substr($string, 3, 3) . '-' . substr($string, 6, 4);
}

function job(){
  if(isset(CI()->job)) return CI()->job;
  return null;
}