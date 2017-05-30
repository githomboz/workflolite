<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 4/25/17
 * Time: 9:04 PM
 */
class WF
{

  public static function MetaDataIsSet($metaObject = null){
    if(!$metaObject) return false;
    if($metaObject && !($metaObject instanceOf MetaObject)) return false;
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
  public static function _GetParsedParamsMap(array $paramsMap, $project){
    $params = [];

    foreach($paramsMap as $i => $param){
      if(isset($param['type'])){
        switch($param['type']){
          case 'metaObject':
            $metaData = self::GetMetaDataBySlug($project, $param['value']);
            $param['value'] = isset($metaData['value']) ? $metaData['value'] : null;
            break;
          case 'metaObjectValue':
            $metaData = self::GetMetaDataBySlug($project, $param['value']);
            $param['value'] = isset($metaData['value']) ? $metaData['value']->get() : null;
            break;
          case 'paramMap':
            break;
          case 'callback':
            break;
          case 'value':
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

  /**
   * Addition function. Adds the parameters that are passed in.
   * @return mixed
   */
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
   * @param Project $project
   * @param string $taskId
   * @return mixed
   */
  public static function GenerateCallbackReport($callbackArray, Project $project, $taskId){
    $logs = ['errors'=>[],'debug'=>[]];
    $report = [
      'taskId' => $taskId,
      'callbacks' => [],
    ];
    foreach($callbackArray as $i => $callbacks){
      if(!empty($logs['errors'])) continue;
      //var_dump($callbacks, '-- / callback --');
      $report['callbacks'][$i]['fn'] = isset($callbacks['callback']) ? $callbacks['callback'] : null;
      $report['callbacks'][$i]['fnExecMethod'] = null;
      $report['callbacks'][$i]['fnParams'] = null;
      $report['callbacks'][$i]['fnResponse'] = null;
      $report['callbacks'][$i]['fnResponseType'] = null;
      $report['callbacks'][$i]['assertion'] = null;
      $report['callbacks'][$i]['tests'] = [
        'callbackValidated' => null,
        'assertionValidated' => null,
        'paramsValidated' => null,
        'callbackExecuted' => null,
        'assertionTested' => null,
      ];
      $report['callbacks'][$i]['success'] = false;
      // Callback Validated; Result stored;
      $_callbackExecMethod = WF::_ValidateCallback($callbacks['callback']);
      $report['callbacks'][$i]['fnExecMethod'] = $_callbackExecMethod ? $_callbackExecMethod : false;
      $report['callbacks'][$i]['tests']['callbackValidated'] = (bool) $report['callbacks'][$i]['fnExecMethod'];

      // Assertion is validated; Result stored
      $_assertionSet = isset($callbacks['assertion']);
      $_assertionOperationSet = $_assertionSet && isset($callbacks['assertion']['_op']) && in_array($callbacks['assertion']['_op'], ['==','!=','>','>=','<','<=']);
      $_assertionValueSet = $_assertionSet && isset($callbacks['assertion']['_val']);
      $_assertionDataTypeSet = $_assertionSet && isset($callbacks['assertion']['_dt']);
      if($_assertionDataTypeSet) {
        switch ($callbacks['assertion']['_dt']) {
          case 'boolean':
            $val = $callbacks['assertion']['_val'];
            if(!is_bool($val)){
              if(is_string($val)){
                $val = trim(strtolower($val));
                $callbacks['assertion']['_val'] = !in_array($val, ['false', '0', '']);
              }
              if(is_numeric($val)) $callbacks['assertion']['_val'] = (bool) $callbacks['assertion']['_val'];
              if(!is_bool($callbacks['assertion']['_val'])) $callbacks['assertion']['_val'] = (bool) $callbacks['assertion']['_val'];
            }
            break;
        }
      }

      $error = 'One or more dependency tests have failed';

      // Check for errors and terminate loop if found
      if(in_array(false, $report['callbacks'][$i]['tests'], true)) {
        if(!in_array($error, $logs['errors'])) $logs['errors'][] = $error;
        continue;
      }

      //var_dump($callbacks['assertion'], '-- / assertion --');

      // Assertion is tested against callback response; Result stored
      $report['callbacks'][$i]['tests']['assertionValidated'] = !$_assertionSet || ($_assertionOperationSet && $_assertionValueSet);
      if($report['callbacks'][$i]['tests']['assertionValidated'] && $_assertionSet){
        // Assertion is parsed; Result stored
        $report['callbacks'][$i]['assertion']['_op'] = $callbacks['assertion']['_op'];
        $report['callbacks'][$i]['assertion']['_val'] = $callbacks['assertion']['_val'];
      }

      // Check for errors and terminate loop if found
      if(in_array(false, $report['callbacks'][$i]['tests'], true)) {
        if(!in_array($error, $logs['errors'])) $logs['errors'][] = $error;
        continue;
      }

      // $params array used or $paramsMap parsed to create $params; Result stored
      // Handle case where paramsMap is set
      if(isset($callbacks['paramsMap'])){
        $report['callbacks'][$i]['fnParams'] = WF::_GetParsedParamsMap($callbacks['paramsMap'], $project);
        $report['callbacks'][$i]['tests']['paramsValidated'] = true;
      }

      // Handle case where if report[params] hasn't been set but callback[params] is set, set it
      if(!isset($report['callbacks'][$i]['fnParams']) && $callbacks['params']) {
        $report['callbacks'][$i]['fnParams'] = $callbacks['params'];
        $report['callbacks'][$i]['tests']['paramsValidated'] = true;
      }

      // Check for errors and terminate loop if found
      if((isset($callbacks['paramsMap']) || isset($callbacks['params'])) && !isset($report['callbacks'][$i]['fnParams'])){
        $logs['errors'][] = 'Params invalid';
        $report['callbacks'][$i]['tests']['paramsValidated'] = false;
        continue;
      }

      // Attempt to execute in a try/catch block
      if($report['callbacks'][$i]['fnExecMethod'] && $report['callbacks'][$i]['fn']){
        try {
          // Callback executed; Result stored
          switch ($report['callbacks'][$i]['fnExecMethod']){
            case 'is_callable':
              $result = call_user_func_array($report['callbacks'][$i]['fn'], $report['callbacks'][$i]['fnParams']);
              $report['callbacks'][$i]['fnResponse'] = $result;
              $report['callbacks'][$i]['fnResponseType'] = is_array($result) ? 'array' : (is_bool($result) ? 'bool' : null);
              switch($report['callbacks'][$i]['fnResponseType']){
                case 'array':
                  $report['callbacks'][$i]['fnResponse'] = isset($result['response']) ? $result['response'] : null;
                  $report['metaUpdates'] = isset($result['metaUpdates']) ? $result['metaUpdates'] : null;
                  $report['taskUpdates'] = isset($result['taskUpdates']) ? $result['taskUpdates'] : null;
                  break;
                case 'bool':
                  $report['callbacks'][$i]['fnResponse'] = $result;
                  break;
              }
              $report['callbacks'][$i]['tests']['callbackExecuted'] = true;
              break;
          }
          // Test response against assertion

        }

        catch (Exception $e) {
          $logs['errors'][] = $e->getMessage();
        }

        // Set test for executed callback after the try/catch
        $report['callbacks'][$i]['tests']['callbackExecuted'] = (bool) $report['callbacks'][$i]['tests']['callbackExecuted'];
      }

      // Check for errors and terminate loop if found
      if(in_array(false, $report['callbacks'][$i]['tests'], true)) {
        if(!in_array($error, $logs['errors'])) $logs['errors'][] = $error;
        continue;
      }

      // Test assertion if applicable

      if($report['callbacks'][$i]['assertion']){
        switch ($report['callbacks'][$i]['assertion']['_op']){
          case '==':
            $report['callbacks'][$i]['success'] = $report['callbacks'][$i]['fnResponse'] == $report['callbacks'][$i]['assertion']['_val'];
            break;
          case '!=':
            $report['callbacks'][$i]['success'] = $report['callbacks'][$i]['fnResponse'] != $report['callbacks'][$i]['assertion']['_val'];
            break;
          case '>':
            $report['callbacks'][$i]['success'] = $report['callbacks'][$i]['fnResponse'] > $report['callbacks'][$i]['assertion']['_val'];
            break;
          case '>=':
            $report['callbacks'][$i]['success'] = $report['callbacks'][$i]['fnResponse'] >= $report['callbacks'][$i]['assertion']['_val'];
            break;
          case '<':
            $report['callbacks'][$i]['success'] = $report['callbacks'][$i]['fnResponse'] < $report['callbacks'][$i]['assertion']['_val'];
            break;
          case '<=':
            $report['callbacks'][$i]['success'] = $report['callbacks'][$i]['fnResponse'] <= $report['callbacks'][$i]['assertion']['_val'];
            break;
        }
        $report['callbacks'][$i]['tests']['assertionTested'] = true;
      }

      if(!$report['callbacks'][$i]['success']) {
        $error02 = 'Callback execution was unsuccessful';
        if(!in_array($error02, $logs['errors'])) $logs['errors'][] = $error02;
        continue;
      }

    }
    $response['response'] = $report;
    $response['errors'] = !empty($logs['errors']);
    $response['logs'] = $logs;
    return $response;
  }

}

class JNBPA {
  public static function ValidateFileNumber($fileNumber){
    return strlen((string) $fileNumber) >= 5 && (strpos($fileNumber, 'jnbpa') === 0);
  }
}

class WorkfloCore {
  public static function SimpleSMS(){
    log_message('error','Simple SMS triggered');
    return true;
  }
}