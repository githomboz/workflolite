<?php

require_once 'classes/Logger.php';

function logger($message, $data = null, $type = 'debug', array $context = null){
  return Logger::Post($message, $data, $type, $context);
}

require_once 'classes/Task.php';
require_once 'classes/TaskTemplate.php';
require_once 'classes/Job.php';
require_once 'classes/Project.php';
require_once 'classes/Template.php';
require_once 'classes/Contact.php';
require_once 'classes/Organization.php';
require_once 'classes/Workflow.php';
require_once 'classes/QueueItemSendEmail.php';