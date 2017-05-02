<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 4/25/17
 * Time: 9:04 PM
 */
class WF
{

  public static function MetaDataIsSet(MetaObject $metaObject){
    if($metaObject->ok()) return true;
    return false;
  }

  public static function _ProcessCallbackGroup($callbackGroup){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    // Validate fields callback, params, paramsMap, assertion
    if(isset($callbackGroup['callback']) && !empty($callbackGroup['callback'])){
      $callback = $callbackGroup['callback'];

    } else {
      $logger->setLine(__LINE__)->addError('Invalid callback', $callbackGroup);
    }

    // Handle paramsMap

    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  /**
   * Transform $paramsMap array into a $params array of just values
   * @param array $paramsMap
   * @return array Array of parsed map values
   */
  public static function _GetParsedParamsMap(array $paramsMap, $metaArray){
    $params = [];

    foreach($paramsMap as $i => $param){
      if(isset($param['type'])){
        switch($param['type']){
          case 'metaObject':
            break;
          case 'metaObjectValue':
            break;
          case 'paramMap':
            break;
          case 'callback':
            break;
          case 'value':
            break;
          default:
            break;
        }
      }
      $params[$i] = $param['value'];
    }

    return $params;
  }

  public static function GetMetaDataBySlug($project, $slug){
    if($project){
      foreach(['job.','project.'] as $remove) $slug = substr($slug, 0, strlen($remove)) == $remove ? str_replace($remove,'', $slug) : $slug;
      $metaData = $project->meta()->getAll();
      return isset($metaData[$slug]) ? $metaData[$slug] : null;
    }
  }

  /**
   * Try multiple ways to call the callback, and return the successful method. Return null otherwise;
   * @param string $callback String function name to execute
   * @param $flag // Currently unused
   * @return string Method for checking if is executable or null if none found
   */
  public static function _ValidateCallback($callback, $flag = null){
    if(is_string($callback)){
      // function exists
      if(function_exists($callback)) return 'function_exists';
      // Is callable
      if(is_callable($callback)) return 'is_callable';
    }
    // php function exists
    return false;
  }

  public static function _ValidateAssertion(array $assertion){

  }

  public static function Add(){
    $params = func_get_args();
    $logs = ['errors'=>[],'debug'=>[]];
    $total = 0;
    if(count($params) > 1){
      foreach($params as $i => $x){
        if(is_numeric($x)) {
          $total += $x;
        } else {
          $logs['errors'][] = 'Parameter ' . ($i + 1) . ' must be a valid numeric value.';
        }
      }
    } else {
      $logs['errors'][] = 'Invalid number of items to add.';
    }
    $response['response'] = $total;
    $response['errors'] = !empty($logs['errors']);
    $response['logs'] = $logs;
    return $response;
  }

  /**
   * Generate a report detailing the callback groups in the $callbackArray
   * $callbackArray = [
   *  "callback" => "WF::MetaDataIsSet",
   *  "paramsMap": [
   *    "type": "metaObject",
   *    "value": "job.propertyAddress"
   *  ],
   *  "assertion" => [
   *    "_dt" => "boolean", // Type casts the _val to the given type
   *    "_op" => "==",
   *    "_val" => "1"
   *  ]
   * ]
   * @param $callbackArray
   * @param $project
   * @return mixed
   */
  public static function GenerateCallbackReport($callbackArray, $project){
    $logs = ['errors'=>[],'debug'=>[]];
    $report = [
      'callback' => null,
      'params' => [],
      'callbackResult' => null,
      'assertion' => null,
      'tests' => []
    ];
    foreach($callbackArray as $i => $callbacks){
      $report['tests'][$i] = [
        'validateTests' => null,
        'validateAssertion' => null,
        'callbackExecMethod' => null,
        'paramsMapValid' => null,
        'testCallbackResponse' => null,
        'paramsParsed' => null,
      ];
      // Callback Validated; Result stored;
      $_callbackExecMethod = WF::_ValidateCallback($callbacks['callback']);
      $report['tests'][$i]['callbackExecMethod'] = $_callbackExecMethod ? $_callbackExecMethod : false;

      // Assertion is validated; Result stored
      $_assertionSet = isset($callbacks['assertion']);
      $_assertionOperationSet = $_assertionSet && isset($callbacks['assertion']['_op']);
      $_assertionValueSet = $_assertionSet && isset($callbacks['assertion']['_val']);
      $_assertionDataTypeSet = $_assertionSet && isset($callbacks['assertion']['_dt']);
      if($_assertionDataTypeSet) {
        switch ($callbacks['assertion']['_dt']) {
          case 'boolean':
            $val = $callbacks['assertion']['_val'];
            $val = is_string($val) ? trim(strtolower($val)) : (bool)$val;
            $val = (in_array($val, ['false', '0', 0, '']) || empty($val) || !$val);
            $callbacks['assertion']['_val'] = $val;
            break;
        }
      }

      $error = 'One or more tests have failed';

      if(in_array(false, $report['tests'][$i])) {
        if(!in_array($error, $logs['errors'])) $logs['errors'][] = $error;
        continue;
      }

      $report['tests'][$i]['assertionFormatValid'] = $_assertionOperationSet && $_assertionValueSet;

      if(in_array(false, $report['tests'][$i])) {
        if(!in_array($error, $logs['errors'])) $logs['errors'][] = $error;
        continue;
      }

      if(isset($callbacks['paramsMap'])){
        $report['params'][$i] = WF::_GetParsedParamsMap($callbacks['paramsMap']);
      }


      // $params array used or $paramsMap parsed to create $params; Result stored
      // Callback executed; Result stored
      // Assertion is parsed; Result stored
      // Assertion is tested against callback response; Result stored
    }
    $response['response'] = $report;
    $response['errors'] = !empty($logs['errors']);
    $response['logs'] = $logs;
    return $response;
  }

}