<?php

if(!function_exists('_api_template')) require 'api_v1__helper.php';

function _log_message($type, $message, $data = null, array $context = null){
  return logger($message, $data, $type, $context);
}

function post_trigger_response(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $payload = null;
  $triggerId = CI()->input->get('triggerId');

  $debug = false;

  if(is_string($data['payload'])){
    $data['payload'] = $payload = json_decode($data['payload'], true);
  }

  if(!$payload && isset($data['payload'])) $payload = $data['payload'];
  $response['response'] = $data;

  if($debug) _log_message('debug', '0. Log Inputs [$triggerId, $data, $data["payload"], $payload] ' , [$triggerId, $data, $data['payload'], $payload]);

  if($triggerId){
    $trigger = null;
    $triggerId = trim($triggerId);
    //_log_message('debug', '1. If Trigger Id: ' , $triggerId);
    $record = TriggerQueueItem::Get($triggerId);
    if($debug) _log_message('debug', '1.3. Record: ' , $record);
    if(isset($record['trigger']) && !empty($record['trigger'])) {
      switch ($record['trigger']) {
        case 'messaging-email': $trigger = new QueueItemSendEmail($record); break;
        default: $trigger = new TriggerQueueItem($record); break;
      }
    }
    if($debug) _log_message('debug', '1.5. [$trigger,$record,$record["trigger"]]: ' , [$trigger,$record,$record['trigger']]);
    if($trigger instanceof TriggerQueueItem){
      if($debug) _log_message('debug', '2. If Trigger instance of TriggerQueueItem: ' , $trigger);
      if(!$trigger->isProcessed()){
        if($debug) _log_message('debug', '3. If Trigger Processed: ' , $trigger->isProcessed());
        if($payload){
          if($debug) _log_message('debug', '4. If Payload: ' , $payload);
          if(isset($payload['success']) && $payload['success']){
            if($debug) _log_message('debug', '5. Payload Success: ' , $payload['success']);
            $updates = array(
              'completeDateTime' => new MongoDate(),
              'processed' => true,
              'returnPayload' => $payload,
              'status' => 'completed'
            );
            if($result = $trigger->setValue($updates)->save()){
              if($debug) _log_message('debug', '6. Save Result: ' , $result);
              $response['response']['message'] = 'payload captured';
            } else {
              if($debug) _log_message('error', '6. Save Result: ' , $result);
              $response['errors'][] = 'An error has occurred while attempting to save trigger state';
            }
          } else {
            if($debug) _log_message('error', '5. Payload Success: ' , $payload);
            $response['errors'][] = 'An error occurred, and the payload is invalid';
          }
        } else {
          if($debug) _log_message('error', '4. If Payload ' , $payload);
          $response['errors'][] = 'An error occurred because the payload is invalid';
        }
      } else {
        if($debug) _log_message('error', '3. If Trigger Processed: ' , $trigger);
        $response['errors'][] = 'The trigger provided has already been processed';
      }
    } else {
      if($debug) _log_message('error', '2. If Trigger: ' , $trigger);
      $response['errors'][] = 'The trigger is invalid';
    }
  } else {
    if($debug) _log_message('error', '1. If Trigger Id: ' , $triggerId);
    $response['errors'][] = 'The trigger id provided is invalid';
  }

  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function post_trigger_response_args_map(){
  return array('payload');
}

// Field names of fields required
function post_trigger_response_required_fields(){
  return array('payload');
}

function trigger_status_check(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response'] = array();
  $triggerIds = CI()->input->get('triggerIds');
  if(is_string($triggerIds)) $triggerIds = array($triggerIds);

  if(is_array($triggerIds)){
    $triggers = TriggerQueueItem::GetAllByIds($triggerIds);
    foreach($triggers as $trigger){
      $response['response'][] = array(
        'triggerId' => (string) $trigger['_id'],
        'status' => $trigger['status'],
        'processed' => $trigger['processed'],
      );
    }
  } else {
    $response['errors'][] = 'Get variable "triggerIds" has not been set';
  }
  $response['recordCount'] = count($response['response']);
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function trigger_status_check_args_map(){
  return array();
}

// Field names of fields required
function trigger_status_check_required_fields(){
  return array();
}

function log_trigger_report(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response'] = array('success' => false);
  $triggerId = CI()->input->get('triggerId');

  $trigger = null;
  if(!empty($triggerId)){
    $trigger = TriggerQueueItem::GetAsObject($triggerId);
  }

  if($trigger){
    $response['response']['success'] = $trigger->setValue('logs', $data['logs'])->save();
  } else {
    $response['errors'][] = 'Invalid triggerId provided';
  }
  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function log_trigger_report_args_map(){
  return array('logs');
}

// Field names of fields required
function log_trigger_report_required_fields(){
  return array('logs');
}

function incoming(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $organizationId = CI()->input->get('orgId');
  $topic = CI()->input->get('topic');

  $logger = new WFLogger('/api/v1/webhooks/incoming', __FILE__);

  $logger->setLine(__LINE__)->addDebug('Incoming Webhook Request ------------------------------------------>');

  $return = null;

  if($organizationId){
    $logger->setEntityOrg()->setEntityId($organizationId)->setLine(__LINE__)->addDebug('Valid organization id', $organizationId);
    if($topic){
      $logger->setLine(__LINE__)->addDebug('Valid topic id', $topic);
      $logger->setLine(__LINE__)->addDebug('Preparing to process web hook request...')->sync();
      $return = Workflo()->Broadcast()->Incoming()->ProcessWebhookRequest($organizationId, $topic, $data['payload']);
      $logger->setLine(__LINE__)->addDebug('Processed webhook request');
      $response['response'] = $return['response'];
      if(is_array($return['errors'])) {
        if(is_array($response['errors'])) {
          $response['errors'] = array_merge($response['errors'], $return['errors']);
        } else $response['errors'] = $return['errors'];
      }
    } else {
      $logger->setLine(__LINE__)->addError('Topic provided is invalid');
    }
  } else {
    $logger->setLine(__LINE__)->addError('Organization id provided is invalid');
  }

  if($logger->hasErrors()) $response['errors'] = $logger->getMessages('errors');
  $logger->setLine(__LINE__)->addDebug('Leaving Request');
  $logger->sync();
  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function incoming_args_map(){
  return array('payload');
}

// Field names of fields required
function incoming_required_fields(){
  return array('payload');
}

function run_script(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response'] = array('success' => false);
  $projectId = CI()->input->get('projectId');

  $project = null;
  if(!empty($projectId)){
    $project = Project::Get($projectId);
  }

  if($project){
    $response['response']['success'] = $project->run();
  } else {
    $response['errors'][] = 'Invalid project id provided';
  }
  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function run_script_args_map(){
  return array('logs');
}

// Field names of fields required
function run_script_required_fields(){
  return array('logs');
}







