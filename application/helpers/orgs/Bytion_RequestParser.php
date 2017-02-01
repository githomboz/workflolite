<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 1/11/17
 * Time: 6:48 PM
 */
class Bytion_RequestParser extends WFRequestParser
{
  public function __construct()
  {
    parent::__construct();
    $this->init();
  }

  /**
   * @param $payload
   * @param null $context
   * @return array WFClientInterface::GetPayloadTemplate()
   */
  public static function Validate($payload, $context = null){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    if(!$context) {
      if(isset($payload['topic'])) {
        $context = $payload['topic'];
      } else {
        $logger->addError('No validation context provided');
      }
    }

    $tests = [];
    if(empty($logs['errors'])){
      switch ($context){
        case 'orders-added':

          $tests = [
            'Bytion_RequestParser::_validateIsset' => ['topic','orgId','orderType','orderCount','twitterHandle','customer','customer.name','customer.email','customer.ipAddress','customer.country'],
            'Bytion_RequestParser::_validateNotEmpty' => ['topic','orderType','orderCount','twitterHandle'],
            'Bytion_RequestParser::_validateNumeric' => ['orderCount'],
            'Bytion_RequestParser::_validateCustomer' => ['customer'],
            'Bytion_RequestParser::_validateIpAddress' => ['customer.ipAddress'],
            'Bytion_RequestParser::_validateEmail' => ['customer.email'],
            'Bytion_RequestParser::_validateOrganization' => ['orgId'],
          ];

          break;
        default:
          $logger->addError('Invalid validation context provided ('.$context.')');
          break;
      }
    }

    if(is_array($tests)){
      foreach($tests as $testCallback => $testFields){
        $results = self::_validateFieldsByCallback($payload, $testFields, $testCallback);
        if(WFClientInterface::Valid_WFResponse($results)){
          $logger->merge($results['logger']);
        } else {
          $logger->addError('Invalid response from validation test', $testCallback);
        }
      }
    }

    $response['response']['success'] = empty($logs['errors']);
    $logger->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  public static function _validateCustomer($var){
    return is_array($var) && isset($var['name']) && isset($var['ipAddress']) && isset($var['country']);
  }

  /**
   * Maps topic string to a SuperClass::Method
   * @param $topic
   * @return array WFClientInterface::GetPayloadTemplate() [ Static method string @response ]
   */
  public static function ParseTopic($topic){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    $routes = [
      'orders-added' => 'Bytion_OrderManager::PlaceOrder',
      'orders-cancelled' => 'Bytion_OrderManager::CancelOrder',
    ];

    if(isset($routes[$topic])){
      $logger->addDebug('Valid route found for topic (`'.$topic.'`)', $routes[$topic]);
      $response['response']['callback'] = $routes[$topic];
      $response['response']['success'] = true;
    } else {
      $logger->addError('Invalid topic ('.$topic.')');
    }

    $logger->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  public static function StartTwitterFollowersProject($payload){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    // Validate payload isset [projectId, meta]
    if(isset($payload['projectId'])){
      $project = Project::Get($payload['projectId']);
      if($project instanceof Project){
        if(isset($payload['meta'])){
          if(isset($payload['meta']['twitterHandle'])){

            // Validate twitter handle
            $validateResponse = WFSocialUtilitiesTwitter::SimpleValidate($payload['meta']);
            if(WFClientInterface::Valid_WFResponse($validateResponse)){
              $logger->merge($validateResponse['logger']);

              if(!$validateResponse['errors']){

                // Update project->meta
                $metaArray = $project->getRawMeta();

                if(isset($validateResponse['response']['data'])){

                  $twitterData = $validateResponse['response']['data'];

                  $metaArray['accountMeta'] = [
                    '_isPublic' => $twitterData['public'],
                    '_valid' => $twitterData['validTarget'],
                    'followers' => $twitterData['data']['stats']['followers'],
                    'following' => $twitterData['data']['stats']['following'],
                    'tweets' => $twitterData['data']['stats']['tweets'],
                    'name' => $twitterData['data']['profile']['name'],
                    'image' => $twitterData['data']['profile']['image'],
                    'bio' => $twitterData['data']['profile']['bio'],
                    'location' => isset($twitterData['data']['profile']['location']) ? $twitterData['data']['profile']['location'] : '',
                  ];

                  $project->meta()->set('meta', $metaArray)->save('meta');
                  $logger->addDebug('Updated meta field `accountMeta`');

                  // Add next step(s) to script
                  $project->ScriptEngine()->addStep([
                    'id' => md5(2),
                    'scheduleTime' => null,
                    'executedTime' => null,
                    'completedTime' => null,
                    'dependencies' => [],
                    'callback' => 'Bytion_RequestParser::GenerateFraudReport',
                    'payload' => $project->payload(),
                    'description' => 'Perform fraud analysis on key data fields and generate a report to be sent to admins for approval.',
                    'response' => null,
                    'taskId' => null,
                    'logs' => WFClientInterface::GetLogsTemplate(),
                    'usage' => ['time' => 0, 'mem' => 0],
                    'status' => 'ready'
                  ]);

                  $response['response']['success'] = true;
                  $response['response']['data'] = 'Ok';

                } else {
                  $logger->addError('Social validator response format invalid');
                }
              } else {
                $logger->addError('Error(s) encountered in social validator');
              }
            } else {
              $logger->addError('Invalid response from social validator');
            }
          } else {
            $logger->addError('Meta parameter `twitterHandle` is required');
          }
        } else {
          $logger->addError('Payload parameter `meta` is required');
        }
      } else {
        $logger->addError('Invalid projectId provided; project not found');
      }
    } else {
      $logger->addError('Payload parameter `projectId` is required');
    }

    $logger->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  public static function GenerateFraudReport($payload){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    // Validate payload isset [projectId, meta]
    if(isset($payload['projectId'])){
      $project = Project::Get($payload['projectId']);
      if($project instanceof Project){
        if(isset($payload['meta'])){
          if(isset($payload['meta']['orderData'])){

            // Validate twitter handle
            $validateResponse = WFSocialUtilities::FraudAnalysis($payload['meta']['orderData']);
            if(WFClientInterface::Valid_WFResponse($validateResponse)){
            $logger->merge($validateResponse['logger']);

              if(!$validateResponse['errors']){

                // Update project->meta
                $metaArray = $project->getRawMeta();

                if(isset($validateResponse['response']['data'])){

                  $reportData = $validateResponse['response']['data'];

                  $metaArray['fraudReport'] = $reportData;

                  $project->meta()->set('meta', $metaArray)->save('meta');
                  $logger->addDebug('Updated meta field `fraudReport`');

                  // Add next step(s) to script
                  $project->ScriptEngine()->addStep([
                    'id' => md5(2),
                    'scheduleTime' => null,
                    'executedTime' => null,
                    'completedTime' => null,
                    'dependencies' => [],
                    'callback' => 'Bytion_RequestParser::ApproveFraudReport',
                    'payload' => $project->payload(),
                    'description' => 'Send fraud report confirmation email, and await response.',
                    'response' => null,
                    'taskId' => null,
                    'logs' => WFClientInterface::GetLogsTemplate(),
                    'usage' => ['time' => 0, 'mem' => 0],
                    'status' => 'ready'
                  ]);

                  $response['response']['success'] = true;
                  $response['response']['root'] = 'Ok';

                } else {
                  $logger->addError('Fraud analyzer response format invalid');
                }
              } else {
                $logger->addError('Error(s) encountered in fraud analyzer');
              }
            } else {
              $logger->addError('Invalid response from fraud analyzer');
            }
          } else {
            $logger->addError('Meta parameter `orderData` is required');
          }
        } else {
          $logger->addError('Payload parameter `meta` is required');
        }
      } else {
        $logger->addError('Invalid projectId provided; project not found');
      }
    } else {
      $logger->addError('Payload parameter `projectId` is required');
    }

    $logger->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }


}