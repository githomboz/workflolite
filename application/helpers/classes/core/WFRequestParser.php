<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 1/27/17
 * Time: 1:54 PM
 */
class WFRequestParser
{
  public function __construct()
  {
    $this->init();
  }

  public function init(){
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
    } else {
      $logger->setLine(__LINE__)->addDebug('Context has been set to `'.$context.'`')->sync();
    }

    $tests = [];
    if(empty($logs['errors'])){
      switch ($context){
        case 'orders-added':

          // Test names and the fields they affect
          $tests = [
            'WFRequestParser::_validateIsset' => ['topic','orgId','orderType','orderCount','twitterHandle','customer','customer.name','customer.email','customer.ipAddress','customer.country'],
          ];

          break;
        default:
          $logger->setLine(__LINE__)->addError('Invalid validation context provided ('.$context.')');
          break;
      }
    }

    if(is_array($tests)){
      foreach($tests as $testCallback => $testFields){
        $results = static::_validateFieldsByCallback($payload, $testFields, $testCallback);
        if(WFClientInterface::Valid_WFResponse($results)){
          
          $logger->merge($results['logger']);
        } else {
          $logger->setLine(__LINE__)->addError('Invalid response from _validateFieldsByCallback');
        }
      }
    }

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['response']['success'] = empty($logs['errors']);
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  /**
   * Validate each field in the $fields array that is set in the $data array, and call the $callback on the value
   * @param array $data Array of key value pairs
   * @param array $fields Array of dot notation strings associating with fields in the $data array that $callback is run on.
   * @param string $callback Public static method to call on $data[$fields[x]]
   * @return array WFClientInterface::GetPayloadTemplate() [ success:bool @response ]
   */
  public static function _validateFieldsByCallback(array $data, array $fields, $callback){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    $response['response']['success'] = true;

    if(is_callable($callback)){
      $logger->setLine(__LINE__)->addDebug('Callback is callable (`'.$callback.'`)');
      if(!empty($fields)){
        $logger->setScope($callback)->setLine(__LINE__)->addDebug('Fields to test: ['.join(', ', $fields).']');
        $parsedFields = static::_parseDotNotation($fields);
        $fieldsSet = [];
        foreach($parsedFields['response']['routes'] as $route){
          $segs = explode('.', $route);
          switch(count($segs)){
            case 1:
              $fieldsSet[$route] = isset($data[$segs[0]]) ? $data[$segs[0]] : null;
              if(in_array($route, $fields)){
                $fieldsSet[$route] = call_user_func_array($callback, array($fieldsSet[$route]));
              }
              break;
            case 2:
              $fieldsSet[$route] = isset($data[$segs[0]][$segs[1]]) ? $data[$segs[0]][$segs[1]] : null;
              if(in_array($route, $fields)){
                $fieldsSet[$route] = call_user_func_array($callback, array($fieldsSet[$route]));
              }
              break;
            case 3:
              $fieldsSet[$route] = isset($data[$segs[0]][$segs[1]][$segs[2]]) ? $data[$segs[0]][$segs[1]][$segs[2]] : null;
              if(in_array($route, $fields)){
                $fieldsSet[$route] = call_user_func_array($callback, array($fieldsSet[$route]));
              }
              break;
            case 4:
              $fieldsSet[$route] = isset($data[$segs[0]][$segs[1]][$segs[2]][$segs[3]]) ? $data[$segs[0]][$segs[1]][$segs[2]][$segs[3]] : null;
              if(in_array($route, $fields)){
                $fieldsSet[$route] = call_user_func_array($callback, array($fieldsSet[$route]));
              }
              break;
            case 5:
              $fieldsSet[$route] = isset($data[$segs[0]][$segs[1]][$segs[2]][$segs[3]][$segs[4]]) ? $data[$segs[0]][$segs[1]][$segs[2]][$segs[3]][$segs[4]] : null;
              if(in_array($route, $fields)){
                $fieldsSet[$route] = call_user_func_array($callback, array($fieldsSet[$route]));
              }
              break;
          }
        }

        $logger->setLine(__LINE__)->addDebug('Field Set :' . json_encode($fieldsSet));

        if(in_array(false, $fieldsSet)){
          $invalid = [];
          foreach($fieldsSet as $route => $valid) if($valid === false) $invalid[] = $route;
          $logger->setScope(explode('::', $callback)[1])->setLine(__LINE__)->addError('Field(s) invalid ('.join(',', $invalid).')');
        } else {
          $logger->setLine(__LINE__)->addDebug('Field(s) valid');
        }

        $response['response']['success'] = true;
        $response['response']['fieldSet'] = $fieldsSet;

      } else {
        $logger->setLine(__LINE__)->addError('No fields to validate against');
      }
    } else {
      $logger->setLine(__LINE__)->addError('Callback is invalid');
    }

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  /**
   * @param array $fields Array of fields names in dot notation
   * @return array WFClientInterface::GetPayloadTemplate()
   */
  public static function _parseDotNotation(array $fields){
    $response = WFClientInterface::GetPayloadTemplate();
    $parsedFieldNames = [];
    $maxDepth = 1;
    $orderedParse = [];
    foreach($fields as $field){
      $loc = &$parsedFieldNames; // for parsed
      $parts = explode('.', $field);
      $parseString = '';
      foreach($parts as $i => $step) {
        if($i == 0) $parseString = '';
        $parseStringSegment = $step;
        $parseString .= $parseString == '' ? $parseStringSegment : '.'.$parseStringSegment;
        if(!in_array($parseString, $orderedParse)) $orderedParse[] = $parseString;
        if($field == $parseString)
        if(($i + 1) > $maxDepth) $maxDepth = $i + 1; // for max depth
        $loc =& $loc[$step]; // for parsed
      }
    }
    $response['response']['parsed'] = $parsedFieldNames;
    $response['response']['routes'] = $orderedParse;
    $response['response']['maxDepth'] = $maxDepth;
    $response['errors'] = false;
    return $response;
  }

  public static function _validateIsset($var){
    return isset($var);
  }

  public static function _validateNotEmpty($var){
    return !empty($var);
  }

  public static function _validateNumeric($var){
    return is_numeric($var);
  }

  public static function _validateIpAddress($var){
    $tests = [
      !filter_var($var, FILTER_VALIDATE_IP) === false,
      !filter_var($var, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false,
      !filter_var($var, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false,
    ];
    return in_array(true, $tests);
  }

  public static function _validateCustomer($var){
    return is_array($var) && isset($var['name']) && isset($var['ipAddress']) && isset($var['country']);
  }

  public static function _validateEmail($var){
    CI()->load->helper('email');
    return valid_email($var);
  }

  public static function _validateOrganization($var){
    return (bool) Organization::Get($var);
  }

  /**
   * Maps topic string to a SuperClass::Method
   * @param string $topic
   * @param array $additionalRoutes
   * @return array WFClientInterface::GetPayloadTemplate() [ Static method string @response ]
   */
  public static function ParseTopic($topic, array $additionalRoutes = []){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    // Topics paired with the callbacks they execute
    $routes = [
      'simple-email' => 'WFMessaging::SimpleEmail',
    ];
    if(!empty($additionalRoutes)) $routes = array_merge($routes, $additionalRoutes);

    if(isset($routes[$topic])){
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

  /**
   * Receives all incoming organization webhooks requests and routes them to callbacks
   * @param $payload
   * @return array WFClientInterface::GetPayloadTemplate() [ success:bool @response ]
   */
  public static function Router($payload){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $logs = WFClientInterface::GetLogsTemplate();
    $response = WFClientInterface::GetPayloadTemplate();

    // Validate the request
    $validate = Bytion_RequestParser::Validate($payload);
    if(WFClientInterface::Valid_WFResponse($validate)){
      $logger->setScope('Router -> Validate')->setLine(__LINE__)->addDebug('Valid response', $validate);
      $logger->merge($validate['logger']);
      if(!$validate['errors']){

        // @todo: Identify the organization and call it's request parser

        // Parse topic
        $parseTopic = Bytion_RequestParser::ParseTopic($payload['topic']);
        if(WFClientInterface::Valid_WFResponse($parseTopic)){
          $logger->setScope('Router -> ParseTopic')->setLine(__LINE__)->addDebug('Valid response', $parseTopic);
          $logger->merge($parseTopic['logger']);
          if(!$parseTopic['errors']){

            // Route the request to the appropriate classes and methods
            $request = static::ProcessRequest($payload, $parseTopic['response']['callback']);
            if(WFClientInterface::Valid_WFResponse($request)){
              $logger->setScope('Router -> ProcessRequest')->setLine(__LINE__)->addDebug('Valid response', $request);
              $logger->merge($request['logger']);
              if(!$request['errors']){

                $response['response'] = $request['response'];
                $response['response']['success'] = $request['response']['success'];
              }

            } else {
              $logger->setScope('Router -> ProcessRequest')->setLine(__LINE__)->addError('Invalid response', $request);
            }
          }
        } else {
          $logger->setScope('Router -> ParseTopic')->setLine(__LINE__)->addError('Invalid response', $parseTopic);
        }
      }
    } else {
      $logger->setScope('Router -> Validate')->setLine(__LINE__)->addError('Invalid response', $validate);
    }

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logs;
    $logger->importLogs($logs)->sync();
    $response['logger'] = $logger;
    //@todo: remove this as each individual method will log itself, this would likely produce duplicate log messages
    $response['errors'] = !empty($logs['errors']);
    return $response;
  }

  /**
   * Validates callback, and executes callback passing in payload and returns response
   * @param $payload
   * @param $requestCallback string
   * @return array WFClientInterface::GetPayloadTemplate() [ Callback response @response ]
   */
  public static function ProcessRequest($payload, $requestCallback){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...', $requestCallback);
    $response = WFClientInterface::GetPayloadTemplate();

    if($requestCallback && is_callable($requestCallback)){
      $logger->setLine(__LINE__)->addDebug('Preparing to execute callback (`'.$requestCallback.'`)');
      $result = call_user_func_array($requestCallback, array($payload));
      $logger->setLine(__LINE__)->setScope('ProcessRequest -> '.$requestCallback)->addDebug('Callback (`'.$requestCallback.'`) executed', ['payload' => $payload]);
      $response['response']['processed'] = $result;
      $response['response']['success'] = true;
    } else {
      $logger->setLine(__LINE__)->addError('Invalid callback (`'.$requestCallback.'`)');
    }

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }
}