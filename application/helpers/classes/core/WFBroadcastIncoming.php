<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 1/7/17
 * Time: 11:02 AM
 */
class WFBroadcastIncoming
{

  /**
   * Receive and process data from an incoming webhook
   * @param $orgId
   * @param $topic
   * @param array|null $payload
   * @return array $return
   */
  public static function ProcessWebhookRequest($orgId, $topic, $payload = null){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $response = [
      'status' => 200,
      'recordCount' => 0,
      'response' => null,
      'errors' => [],
    ];
    $debug = false;
    // Check if organization is valid
    $organization = Organization::Get($orgId);
    if($organization){
      $logger->setEntityId($orgId)->setEntityOrg();
      $logger->setLine(__LINE__)->addDebug('Organization set', $orgId);

      if(!isset($payload['topic'])) $payload['topic'] = $topic;
      if(!isset($payload['orgId'])) $payload['orgId'] = (string) $orgId;

      // Begin creating the $add array for the request
      $add = [
        'dateAdded'           => new MongoDate(),
        'organizationId'      => $orgId,
        'topic'               => $topic,
        'payload'             => $payload,
        'processed'           => false,
        'callbackResponse'    => null,
        'logs'                => []
      ];

      // Check if topic is valid
      if(!empty($topic)){
        $webhook = Webhooks::GetByTopic($topic, $orgId);
        $logger->setLine(__LINE__)->addDebug('Topic set', $topic);
        // Get registered webhook if valid
        if($webhook) {
          // Check if callback is valid
          $logger->setLine(__LINE__)->addDebug('Web hook identified', $webhook);
          if(isset($webhook['registeredCallback']) && is_callable($webhook['registeredCallback'])){
            $logger->setLine(__LINE__)->addDebug('Valid registered callback (`'.$webhook['registeredCallback'].'`)', [$webhook['registeredCallback'], $payload]);
            // Process request data
            if($callbackResponse = call_user_func_array($webhook['registeredCallback'], array($payload))){
              $logger->setLine(__LINE__)->addDebug('Project & Contact info created');
              $logger->setLine(__LINE__)->setScope('ProcessWebhookRequest -> $webhook["registeredCallback"]')->addDebug('Registered callback returned `' .$webhook['registeredCallback']. '`', $callbackResponse);
              // validate response, logs, errors
              if(WFClientInterface::Valid_WFResponse($callbackResponse)){
                $logger->setLine(__LINE__)->setScope('ProcessWebhookRequest -> $webhook["registeredCallback"]')->addDebug('Valid response [registeredCallback, response]', [$webhook['registeredCallback'], $callbackResponse]);
                $logger->merge($callbackResponse['logger']);
                $add['callbackResponse'] = $callbackResponse['response'];
                $add['processed'] = true;
              } else {
                $logger->setLine(__LINE__)->setScope('ProcessWebhookRequest -> $webhook["registeredCallback"]')->addError('Invalid response [registeredCallback, response]', [$webhook['registeredCallback'], $callbackResponse]);
              }
            } else {
              $logger->setLine(__LINE__)->addError('_3; Callback response is was unexpected. Invalid Data');
            }
          } else {
            $logger->setLine(__LINE__)->addError('_2; Callback is invalid '.(isset($webhook['registeredCallback'])?'('.$webhook['registeredCallback'].')':''));
          }
        } // Don't do anything if there is no webhook

        // Add incoming requests to collection
        $id = Webhooks::RegisterIncomingRequest($add);

        if($id){
          $logger->setLine(__LINE__)->addDebug('Request registered successfully', $id);
          // Return request id
          $response['response'] = array(
            'requestId' => (string) $id
          );
          return $response;
        } else {
          $logger->setLine(__LINE__)->addError('Error occurred while registering the incoming request');
        }

        if(empty($response['errors'])) $response['errors'] = false;

      } else {
        $logger->setLine(__LINE__)->addError('Topic is invalid');
      }
    } else {
      $logger->setLine(__LINE__)->addError('Organization is invalid');
    }

    if($logger->hasErrors()) $response['errors'] = $logger->getMessages('errors');
    $logger->sync();

    return $response;
  }

  /**
   * Receive and process data from an incoming trigger response
   * @param $triggerId
   * @param array|null $payload
   * @return array response array
   */
  public static function ProcessTriggerResponse($triggerId, array $payload = null){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $response = [
      'status' => 200,
      'recordCount' => 0,
      'response' => null,
      'errors' => [],
    ];
    $debug = false;
    if($triggerId){
      $trigger = null;
      $triggerId = trim($triggerId);
      $record = TriggerQueueItem::Get($triggerId);
      if($debug) logger('1. Record', $record);
      if(isset($record['trigger']) && !empty($record['trigger'])) {
        switch ($record['trigger']) {
          case 'messaging-email': $trigger = new QueueItemSendEmail($record); break;
          default: $trigger = new TriggerQueueItem($record); break;
        }
      }
      if($debug) logger('1.5. [$trigger,$record,$record["trigger"]]: ' , [$trigger,$record,$record['trigger']]);
      if($trigger instanceof TriggerQueueItem){
        if($debug) logger('2. If Trigger instance of TriggerQueueItem: ' , $trigger);
        if(!$trigger->isProcessed()){
          if($debug) logger('3. If Trigger Processed: ' , $trigger->isProcessed());
          if($payload){
            if($debug) logger('4. If Payload: ' , $payload);
            if(isset($payload['success']) && $payload['success']){
              if($debug) logger('5. Payload Success: ' , $payload['success']);
              $updates = array(
                'completeDateTime' => new MongoDate(),
                'processed' => true,
                'returnPayload' => $payload,
                'status' => 'completed'
              );
              if($result = $trigger->setValue($updates)->save()){
                if($debug) logger('6. Save Result: ' , $result);
                $response['recordCount'] = 1;
                $response['response']['message'] = 'payload captured';
              } else {
                if($debug) logger('6. Save Result: ' , $result);
                $response['errors'][] = 'An error has occurred while attempting to save trigger state';
              }
            } else {
              if($debug) logger('5. Payload Success: ' , $payload);
              $response['errors'][] = 'An error occurred, and the payload is invalid';
            }
          } else {
            if($debug) logger('4. If Payload ' , $payload);
            $response['errors'][] = 'An error occurred because the payload is invalid';
          }
        } else {
          if($debug) logger('3. If Trigger Processed: ' , $trigger);
          $response['errors'][] = 'The trigger provided has already been processed';
        }
      } else {
        if($debug) logger('2. If Trigger: ' , $trigger);
        $response['errors'][] = 'The trigger is invalid';
      }
    } else {
      if($debug) logger('1. If Trigger Id: ' , $triggerId);
      $response['errors'][] = 'The trigger id provided is invalid';
    }

    if($logger->hasErrors()) $response['errors'] = $logger->getMessages('errors');
    $logger->sync();

    return $response;
  }


}