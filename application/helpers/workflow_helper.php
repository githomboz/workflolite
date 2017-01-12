<?php

require_once 'classes/Logger.php';

function logger($message, $data = null, $type = 'debug', array $context = null){
  return Logger::Post($message, $data, $type, $context);
}

function loggerBulk($message, $data = null, $type = 'debug', array $context = null){
}

require_once 'classes/WFInterface.php';
require_once 'classes/WFClientInterface.php';
require_once 'classes/Workflo.php';
require_once 'classes/Webhooks.php';

$CI =& get_instance();
$CI->Workflo = new Workflo();

function Workflo(){
  return CI()->Workflo;
}

require_once 'classes/Confirmations.php';
require_once 'classes/utilities/WFAction.php';
require_once 'classes/utilities/WFDependencies.php';
require_once 'classes/Task.php';
require_once 'classes/TaskTemplate.php';
require_once 'classes/Job.php';
require_once 'classes/Project.php';
require_once 'classes/Template.php';
require_once 'classes/Contact.php';
require_once 'classes/Organization.php';
require_once 'classes/Workflow.php';
require_once 'classes/QueueItemSendEmail.php';

require_once 'orgs/Bytion_SuperClass.php';