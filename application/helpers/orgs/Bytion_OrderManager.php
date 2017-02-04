<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 1/14/17
 * Time: 11:11 AM
 */
class Bytion_OrderManager


{

  /**
   * Validates callback, and executes callback passing in payload and returns response
   * @param $payload
   * @return array WFClientInterface::GetPayloadTemplate() [ Callback response @response ]
   */
  public static function PlaceOrder($payload){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    // Find or Create client
    $customerResponse = self::GetContactByEmail($payload['customer'], _id($payload['orgId']));
    $logger->setLine(__LINE__)->addDebug('Response from self::GetContactByEmail', $customerResponse);
    if(WFClientInterface::Valid_WFResponse($customerResponse)){
      $logger->setScope('PlaceOrder -> GetContactByEmail')->setLine(__LINE__)->addDebug('Valid response', $customerResponse);
      $logger->merge($customerResponse['logger']);
      if(!$customerResponse['errors']){
        // Create Project
        switch($payload['orderType']){
          case 'twitter-order':
            $logger->setScope($payload['orderType'])->setLine(__LINE__)->addDebug('Executing `' . $payload['orderType'] . '`');
            $templateId = '587a7c006ccca20165e0ecd9';
            // Create Project
            $add = [
              'name' => '@' . str_replace('@', '', $payload['twitterHandle']),
              'organizationId' => _id($payload['orgId']),
              'partiesInvolved' => [[
                'contactId' => $customerResponse['response']['customer']->id(),
                'role' => 'Interested Party',
                'isClient' => true
              ]],
              'meta' => [
                'twitterHandle' => $payload['twitterHandle'],
                'orderCount' => (int) $payload['orderCount'],
                'orderDate' => new MongoDate(strtotime($payload['orderDate'])),
                'twitterId' => null,
                'orderData' => [
                  'ip' => $payload['customer']['ipAddress'],
                  'country' => $payload['customer']['country'],
                  'email' => $payload['customer']['email'],
                ],
                'fraudReport' => null,
                'fraudReportApproved' => false,
              ],
              'nativeId' => _generate_unique_id(Job::CollectionName(), 'nativeId', 7),
              'script' => [
                'steps' => [
                  [
                    'id' => md5(1),
                    'scheduleTime' => null,
                    'executedTime' => null,
                    'completedTime' => null,
                    'dependencies' => [],
                    'callback' => 'Bytion_RequestParser::StartTwitterFollowersProject',
                    'payload' => null,
                    'description' => 'Check the status of the given twitter account and return meta info.',
                    'response' => null,
                    'taskId' => null,
                    'attempts' => [],
                    'logs' => WFClientInterface::GetLogsTemplate(),
                    'usage' => ['time' => 0, 'mem' => 0],
                    'status' => 'ready'
                  ]
                ],
                'status' => 'ready'
              ],
              'notes' => [],
              'templateId' => _id($templateId),
              'templateVersion' => 1
            ];
            $logger->setLine(__LINE__)->addDebug('Preparing to create project...', $add);
            $projectId = Project::Create($add);
            $logger->setLine(__LINE__)->addDebug('Project created', $projectId);
            //var_dump($projectId, $add);
            // Start Project
            $project = Project::Get($projectId);
            $logger->setLine(__LINE__)->addDebug('Project valid', $project->payload());
            $logger->setLine(__LINE__)->addDebug('Calling $project->run()');
            $project->run();
            break;
        }
      }

    } else {
      $logger->setScope('GetContactByEmail')->addError('Invalid response');
    }


    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  public static function CancelOrder(){

  }

  public static function GetContactByEmail(array $customer, $organizationId = null){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...', [$organizationId, $customer])->sync();
    $response = WFClientInterface::GetPayloadTemplate();
    require_once APPPATH.'/helpers/classes/Contact.php';
    $searchResults = Contact::GetByEmail($customer['email']);

    $logger->setLine(__LINE__)->addDebug('Search results from Contact::GetByEmail', $searchResults);

    if(empty($searchResults)){
      $logger->setLine(__LINE__)->addDebug('No customer found. Preparing to create customer');
      // create
      if(!isset($customer['organizationId'])){
        if(_id($organizationId) instanceof MongoId){
          $customer['organizationId'] = _id($organizationId);
        } else {
          $logger->setLine(__LINE__)->addError('Invalid organization id. Required to create contact.', $organizationId);
        }

        if(!$logger->hasErrors(__METHOD__)){
          $logger->setLine(__LINE__)->addDebug('Creating customer ...');
          $id = Contact::Create($customer);
          $logger->setLine(__LINE__)->addDebug('Customer created', $id);
          $customer = Contact::Get($id);
          $response['response']['success'] = true;
        } else {
          $logger->setLine(__LINE__)->addError('Errors found so contact could not be created', $logger->getMessages('errors'));
        }
      }
    } else {
      $logger->setLine(__LINE__)->addDebug('Customer found.', $customer);
      $customer = $searchResults[0];
    }

    $response['response']['customer'] = $customer;
    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

}