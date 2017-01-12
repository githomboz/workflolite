<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 1/6/17
 * Time: 4:58 PM
 */

/**
 * Object passed into a step to create 'action' field
 * Class WFAction
 */

class WFAction
{
  private static $_actionTypes = ['function','form'];
  public $type = null;
  public $payload = null;
  public $instance = null;
  private static $_instanceId = 0;
  private static $_logs = [];


  public function __construct($type)
  {
    $this->instance = 'instance_' . ++self::$_instanceId;
    if(in_array($type, self::GetActionTypes())) $this->type = $type;
    else $this->log('Invalid action type provided', $type, 'error');

    $this->payload = array(
      'preRoutine' => null,
      'preRoutineArguments' => array(),
      'mainRoutine' => array(
        'scope' => null,
        'channel' => null,
        'record' => array(
          'method' => 'POST',
          'type' => 'function',// function or endpoint,
          'value' => 'sendEmail',
          'payload' => []
        )
      )
    );
  }

  public function get(){
    return array(
      'type' => $this->type,
      'actionPayload' => $this->payload
    );
  }

  public function setPreRoutine($preRoutine, array $args = null)
  {
    $this->payload['preRoutine'] = $preRoutine;
    $this->payload['preRoutineArguments'] = (array) $args;
    return $this;
  }

  public function setScope($scope = 'AWS_Lambda'){
    $this->payload['mainRoutine']['scope'] = $scope;
    return $this;
  }

  public function getScope(){
    return $this->payload['mainRoutine']['scope'];
  }

  public function setChannel($channel = 'wfSlingShot'){
    $this->payload['mainRoutine']['channel'] = $channel;
    return $this;
  }

  public function getChannel(){
    return $this->payload['mainRoutine']['channel'];
  }

  public function setRecord(array $record){
    $this->payload['mainRoutine']['record'] = $record;
    return $this;
  }

  public function getRecord(){
    return $this->payload['mainRoutine']['record'];
  }

  public function isSlingShot(){
    return $this->getScope() == 'AWS_Lambda' && $this->getChannel() == 'wfSlingShot';
  }

  /**
   * If is slingshot, validate slingshot payload
   */
  public function validateSlingShot(){
    $record = $this->getRecord();
    $tests = [
      'METHOD IS POST' => $record['method'] == 'POST',
      'TYPE IS VALID' => in_array($record['type'], ['function','endpoint']),
      'VALUE IS VALID' => false,
      'PAYLOAD IS ARRAY' => is_array($record['payload'])
    ];
    switch($record['type']){
      case 'function': $tests['VALUE IS VALID'] = trim($record['value']) != '';
        break;
      case 'endpoint': $tests['VALUE IS VALID'] = !filter_var($record['value'], FILTER_VALIDATE_URL) === false;
        break;
    }
    return array(
      'isValid' => !in_array(false, $tests),
      'tests' => $tests
    );
  }

  public static function GetActionTypes()
  {
    return self::$_actionTypes;
  }

  public function log($message, $data = null, $type = 'debug'){
    self::$_logs[] = array(
      'instance' => $this->instance,
      'type' => $type,
      'message' => $message,
      'data' => $data
    );
    return $this;
  }

  public function getLogs($type = null){
    if($type && in_array($type, self::GetActionTypes())){
      if(isset(self::$_logs[$type])) return self::$_logs[$type];
      return null;
    }
    return self::$_logs;
  }

  public function getErrors(){
    return $this->getLogs('error');
  }

  public function isValid(){
    // validate
    $isValid = true;

    if(!isset($this->payload['mainRoutine'])) $isValid = false;
    else $this->log('payload[mainRoutine] is not set',null, 'error');

    if($isValid && (!$this->getScope() || !$this->getChannel() || !$this->getRecord())) $isValid = false;
    else $this->log('Scope, channel or record is not set',null, 'error');

    if($isValid && $this->isSlingShot()) {
      $validation = $this->validateSlingShot();
      if(!$validation['isValid']) $isValid = false;
      else {
        //logger('Invalid SlingShot data', $validation, 'error', [__FUNCTION__,__FILE__,__LINE__]);
        $this->log('Invalid slingshot data', $validation, 'error');
      }
    }

    return !$this->getErrors();
  }

}