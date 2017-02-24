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
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    if(!$context) {
      if(isset($payload['topic'])) {
        $context = $payload['topic'];
      } else {
        $logger->setLine(__LINE__)->addError('No validation context provided');
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
          $logger->setLine(__LINE__)->addError('Invalid validation context provided ('.$context.')');
          break;
      }
    }

    if(is_array($tests)){
      foreach($tests as $testCallback => $testFields){
        $results = self::_validateFieldsByCallback($payload, $testFields, $testCallback);
        if(WFClientInterface::Valid_WFResponse($results)){
          $logger->setScope('Validate -> self::_validateFieldsByCallback')->setLine(__LINE__)->addDebug('Valid Response `'.$testCallback.'`', $results);
          $logger->merge($results['logger']);
        } else {
          $logger->setScope('Validate -> self::_validateFieldsByCallback')->setLine(__LINE__)->addError('Invalid Response `'.$testCallback.'`', $results);
        }
      }
    }

    $response['response']['success'] = empty($logs['errors']);
    $logger->setLine(__LINE__)->addDebug('Exiting ...');
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
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    $routes = [
      'orders-added' => 'Bytion_OrderManager::PlaceOrder',
      'orders-cancelled' => 'Bytion_OrderManager::CancelOrder',
    ];

    if(isset($routes[$topic])){
      $logger->setLine(__LINE__)->addDebug('Valid route found for topic (`'.$topic.'`)', $routes[$topic]);
      $response['response']['callback'] = $routes[$topic];
      $response['response']['success'] = true;
    } else {
      $logger->setLine(__LINE__)->addError('Invalid topic ('.$topic.')');
    }

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  public static function TestScript($payload){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    $logger->setLine(__LINE__)->addDebug('Payload', $payload);

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  public static function TestScript2($payload){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    $logger->setLine(__LINE__)->addDebug('Payload', $payload);

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  public static function StartTwitterFollowersProject($payload){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
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
              $logger->setScope('StartTwitterFollowersProject -> SimpleValidate')->setLine(__LINE__)->addDebug('Valid response', $validateResponse);

              if(!$validateResponse['errors']){

                // Update project->meta
                $metaArray = $project->getRawMeta();

                if(isset($validateResponse['response']['data'])){

                  $continueScript = true;

                  $twitterData = $validateResponse['response']['data'];

                  $metaArray['accountMeta'] = [
                    '_isPublic' => $twitterData['data']['public'],
                    '_valid' => $twitterData['data']['validTarget'],
                    'followers' => (int) (isset($twitterData['data']['stats']['followers']) ? $twitterData['data']['stats']['followers'] : 0),
                    'following' => (int) (isset($twitterData['data']['stats']['following']) ? $twitterData['data']['stats']['following'] : 0),
                    'tweets' => (int) (isset($twitterData['data']['stats']['tweets']) ? $twitterData['data']['stats']['tweets '] : 0),
                    'name' => $twitterData['data']['profile']['name'],
                    'image' => $twitterData['data']['profile']['image'],
                    'bio' => $twitterData['data']['profile']['bio'],
                    'location' => isset($twitterData['data']['profile']['location']) ? $twitterData['data']['profile']['location'] : '',
                  ];

                  $metaArray['lastCountDate'] = new MongoDate();
                  $metaArray['lastCount'] = $metaArray['accountMeta']['followers'];

                  if($metaArray['accountMeta']['_valid']){

                    $metaArray['twitterId'] = substr(0, 14, md5($metaArray['twitterHandle']));

                    $numDays = round($metaArray['orderCount'] / 500);
                    $metaArray['orderDueDate'] = new MongoDate(strtotime('+ ' . $numDays . ' days'));

                    $project->meta()->set('meta', $metaArray)->save('meta');
                    $logger->setLine(__LINE__)->addDebug('Updated meta field `accountMeta`');
                    $task = $project->getTaskByName('Validate Twitter Handle');
                    $task->complete();

                    $logger->setLine(__LINE__)->addDebug('Task marked complete');
                  } else {
                    $task = $project->getTaskByName('Validate Twitter Handle');
                    $task->setComments('Invalid handle provided.');
                    $task->error();
                    $logger->setLine(__LINE__)->addDebug('Task marked error');
                    $continueScript = false;
                  }

                  // Add next step(s) to script
//                  $step = [
//                    'id' => md5(2),
//                    'scheduleTime' => null,
//                    'executedTime' => null,
//                    'completedTime' => null,
//                    'dependencies' => [],
//                    'callback' => 'Bytion_RequestParser::GenerateFraudReport',
//                    'payload' => $project->payload(),
//                    'description' => 'Perform fraud analysis on key data fields and generate a report to be sent to admins for approval.',
//                    'response' => null,
//                    'taskId' => null,
//                    'logs' => WFClientInterface::GetLogsTemplate(),
//                    'usage' => ['time' => 0, 'mem' => 0],
//                    'status' => 'paused'
//                  ];
                  //$logger->setLine(__LINE__)->addDebug('Steps array before adding step', $project->ScriptEngine()->getStepsRaw());
                  //$project->ScriptEngine()->addStep($step);
                  //$logger->setLine(__LINE__)->addDebug('Steps array after adding step', $project->ScriptEngine()->getStepsRaw());
                  //$logger->setLine(__LINE__)->addDebug('Step added. Preparing to save script', $step);
                  //$project->ScriptEngine()->save();
                  //$logger->setLine(__LINE__)->addDebug('Script saved');

                  if($continueScript){
                    // Validate twitter handle
                    $validateResponse = WFSocialUtilities::FraudAnalysis($payload['meta']['orderData']);
                    if(WFClientInterface::Valid_WFResponse($validateResponse)){
                      $logger->setScope('GenerateFraudReport -> WFSocialUtilities::FraudAnalysis')->setLine(__LINE__)->addDebug('Valid Response [data, "excalibur", response]', [$payload['meta']['orderData'],"excalibur", $validateResponse]);

                      $logger->merge($validateResponse['logger']);

                      if(!$validateResponse['errors']){

                        // Update project->meta
                        $metaArray = $project->getRawMeta();

                        if(isset($validateResponse['response']['data'])){

                          $reportData = $validateResponse['response']['data'];

                          $metaArray['fraudReport'] = json_decode(json_encode($reportData), true);

                          $project->meta()->set('meta', $metaArray)->save('meta');
                          $logger->setLine(__LINE__)->addDebug('Updated meta field `fraudReport`');

                          // Add next step(s) to script
//                        $project->ScriptEngine()->addStep([
//                          'id' => md5(2),
//                          'scheduleTime' => null,
//                          'executedTime' => null,
//                          'completedTime' => null,
//                          'dependencies' => [],
//                          'callback' => 'Bytion_RequestParser::ApproveFraudReport',
//                          'payload' => $project->payload(),
//                          'description' => 'Send fraud report confirmation email, and await response.',
//                          'response' => null,
//                          'taskId' => null,
//                          'logs' => WFClientInterface::GetLogsTemplate(),
//                          'usage' => ['time' => 0, 'mem' => 0],
//                          'status' => 'ready'
//                        ]);

                          $adminRecipient = [
                            'name' => 'German Calas',
                            'email' => 'german@bytion.co'
                          ];

                          $task = $project->getTaskByName('Generate Fraud Analysis Report');
                          $task->complete();
                          $logger->setLine(__LINE__)->addDebug('Task marked complete');

                          $confirmation = [
                            'dateAdded' => new MongoDate(),
                            'projectId' => $project->id(),
                            'redirect' => '/confirmations/{confirmationId}?processed=true',
                            'question' => 'Please review and either approve or deny the following data',
                            'receiptMessage' => 'Thank you. Your response has been captured.',
                            'recipients' => [
                              $adminRecipient
                            ],
                            'payload' => $project->payload(),
                            'payloadTemplater' => null,
                            'callbackYes' => '_bytionApprove_fraudReport',
                            'callbackNo' => '_bytionDeny_fraudReport',
                            'callbackResponse' => null,
                            'confirmed' => false,
                            'processed' => false
                          ];

                          $confirmationId = Confirmations::Create($confirmation);

                          $logger->setLine(__LINE__)->addDebug('Preparing to send confirmation email to admin');
                          CI()->load->helper('communications');
                          $templateData = $project->payload();
                          $templateData['confirmationId'] = $confirmationId;
                          $response = email_template($adminRecipient['email'], $templateData, 'confirmations');
                          $logger->setLine(__LINE__)->addDebug('Fraud report sent');
                          $project->ScriptEngine()->statusPause();

                          $task = $project->getTaskByName('Validate Order Info');
                          $task->start();


                          $response['response']['success'] = true;
                          $response['response']['root'] = 'Ok';

                        } else {
                          $logger->setLine(__LINE__)->addError('Fraud analyzer response format invalid');
                        }
                      } else {
                        $logger->setLine(__LINE__)->addError('Error(s) encountered in fraud analyzer');
                      }
                    } else {
                      $logger->setScope('GenerateFraudReport -> WFSocialUtilities::FraudAnalysis')->setLine(__LINE__)->addError('Invalid Response [data, "excalibur", response]', [$payload['meta']['orderData'],"excalibur", $validateResponse]);
                    }

                  } else {
                    $logger->setLine(__LINE__)->addError('Script halted due to invalid twitter data');
                  }


                  $response['response']['success'] = true;
                  $response['response']['data'] = 'Ok';



                } else {
                  $logger->setLine(__LINE__)->addError('Social validator response format invalid');
                }
              } else {
                $logger->setLine(__LINE__)->addError('Error(s) encountered in social validator');
              }
            } else {
              $logger->setLine(__LINE__)->setScope('StartTwitterFollowersProject -> SimpleValidate')->addError('Invalid response', $validateResponse);
            }
          } else {
            $logger->setLine(__LINE__)->addError('Meta parameter `twitterHandle` is required');
          }
        } else {
          $logger->setLine(__LINE__)->addError('Payload parameter `meta` is required');
        }
      } else {
        $logger->setLine(__LINE__)->addError('Invalid projectId provided; project not found');
      }
    } else {
      $logger->setLine(__LINE__)->addError('Payload parameter `projectId` is required');
    }

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  public static function GenerateFraudReport($payload){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
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
              $logger->setScope('GenerateFraudReport -> WFSocialUtilities::FraudAnalysis')->setLine(__LINE__)->addDebug('Valid Response [data, "excalibur", response]', [$payload['meta']['orderData'],"excalibur", $validateResponse]);

              $logger->merge($validateResponse['logger']);

              if(!$validateResponse['errors']){

                // Update project->meta
                $metaArray = $project->getRawMeta();

                if(isset($validateResponse['response']['data'])){

                  $reportData = $validateResponse['response']['data'];

                  $metaArray['fraudReport'] = $reportData;

                  $project->meta()->set('meta', $metaArray)->save('meta');
                  $logger->setLine(__LINE__)->addDebug('Updated meta field `fraudReport`');

                  $step = [
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
                  ];

                  $logger->setLine(__LINE__)->addDebug('Steps array before adding step', $project->ScriptEngine()->getStepsRaw());
                  //$project->ScriptEngine()->addStep($step);
                  //$logger->setLine(__LINE__)->addDebug('Steps array after adding step', $project->ScriptEngine()->getStepsRaw());

                  $task = $project->getTaskByName('Generate Fraud Analysis Report');
                  $task->complete();

                  $logger->setLine(__LINE__)->addDebug('Task marked complete');

                  $response['response']['success'] = true;
                  $response['response']['root'] = 'Ok';

                } else {
                  $logger->setLine(__LINE__)->addError('Fraud analyzer response format invalid');
                }
              } else {
                $logger->setLine(__LINE__)->addError('Error(s) encountered in fraud analyzer');
              }
            } else {
              $logger->setScope('GenerateFraudReport -> WFSocialUtilities::FraudAnalysis')->setLine(__LINE__)->addError('Invalid Response [data, "excalibur", response]', [$payload['meta']['orderData'],"excalibur", $validateResponse]);
            }
          } else {
            $logger->setLine(__LINE__)->addError('Meta parameter `orderData` is required');
          }
        } else {
          $logger->setLine(__LINE__)->addError('Payload parameter `meta` is required');
        }
      } else {
        $logger->setLine(__LINE__)->addError('Invalid projectId provided; project not found');
      }
    } else {
      $logger->setLine(__LINE__)->addError('Payload parameter `projectId` is required');
    }

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

}