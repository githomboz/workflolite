<?php

class TriggerQueueItem
{

  protected $_validated = false;
  protected $_valid = false;

  protected $id = null;

  protected $_raw = null;
  protected $_current = null;
  protected $_updates = null;

  protected static $_trigger = null;

  private static $CI;
  public static $dbInitialized = false;

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'triggerQueue';

  public function __construct(array $data){
    $this->_raw = $data;
    $this->initialize();
  }

  public static function CI(){
    if(!self::$CI){
      self::$CI = get_instance();
    }
    return self::$CI;
  }

  public function getValue($key){
    if(isset($this->_current[$key])) return $this->_current[$key];
    return null;
  }

  public function setValue($key, $value = null){
    if(is_string($key)){
      $this->_updates[$key] = $value;
    } else {
      if(is_array($key)){
        foreach($key as $k => $v) $this->_updates[$k] = $v;
      }
    }
    return $this;
  }

  public function save(){
    if(!empty($this->_updates)){
      $success = self::SaveToDb($this->id(), $this->_updates);
      if($success){
        $this->_current = array_merge($this->_current, $this->_updates);
        $this->_updates = array();
        return true;
      }
      return false;
    }
    return false;
  }

  public function getCurrent(){
    return $this->_current;
  }

  public function getRaw(){
    return json_decode($this->_raw, true);
  }

  protected function initialize(){
    $validation = self::ValidateData($this->_raw);
    logger('TriggerQueueItem validation response', $validation, null, array('method'=>__METHOD__,'line'=>__LINE__));
    if($validation){
      if($validation['isValid']){
        $this->_raw = json_encode($this->_raw);
        $this->_current = $validation['data'];
        $this->id = $this->_current['_id'];
        $this->_valid = true;
        logger('TriggerQueueItem validation success', $validation, null, array('method'=>__METHOD__,'line'=>__LINE__));
      } else {
        logger('TriggerQueueItem validation failed', $validation, 'error', array('method'=>__METHOD__,'line'=>__LINE__));
      }
      $this->_validated = true;
    }
  }

  public function id(){
    return _id($this->id);
  }

  public static function ValidateData($data, array $exclude_tests = null){
    $return = array(
      'isValid' => false,
      'data' => null,
      'errors' => null
    );

    logger('Data to Validate', $data, null, array('method'=>__METHOD__,'line'=>__LINE__));

    $tests = array(
      // organizationId must be set as it's required to track broadcast usage
      'orgId_set' => isset($data['organizationId']) && $data['organizationId'] instanceof MongoId,
      // Trigger must be set as it dictates Class
      'trigger_set' => isset($data['trigger']) && !empty($data['trigger']),
      'broadcast_set' => isset($data['broadcast']),
      'broadcast_valid' => null,
      'dateAdded_valid' => isset($data['dateAdded']) && $data['dateAdded'] instanceof MongoDate,
      'scheduleDateTime_valid' => null,
      'completeDateTime_valid' => null,
      'dependencies_valid' => null,
      'payload_valid' => null,
    );

    // Broadcast must be set and contain vendor, service, and channel
    $valid = false;
    if($tests['broadcast_set']){
      $valid = is_array($data['broadcast']);
      if($valid){
        foreach(array('vendor','service','channel','webhook') as $field){
            if(!isset($data['broadcast'][$field])) $valid = false;
        }
      }
    }
    $tests['broadcast_valid'] = $valid;

    // Validate schedule date
    $tests['scheduleDateTime_valid'] = !isset($data['scheduleDateTime']) || isset($data['scheduleDateTime']) && $data['scheduleDateTime'] instanceof MongoDate;

    // Validate complete date
    $tests['completeDateTime_valid'] = !isset($data['completeDateTime']) || isset($data['completeDateTime']) && $data['completeDateTime'] instanceof MongoDate;

    // Validate dependencies
    if(!isset($data['dependencies'])) {
      $valid = true;
    } else {
      if(is_array($data['dependencies'])){
        foreach($data['dependencies'] as $dependency) if(!$dependency instanceof MongoId) $valid = false;
      } else {
        $valid = false;
      }
    }
    $tests['dependencies_valid'] = $valid;

    $payload_test = self::ValidatePayload($data['payload'], true);

    logger('Payload Test Results', $payload_test, null, array('method'=>__METHOD__,'line'=>__LINE__));

    $tests['payload_valid'] = is_array($payload_test) ? !in_array(false, $payload_test) : $payload_test;

    if(is_array($exclude_tests)){
      foreach($tests as $test => $value){
        if(in_array($test, $exclude_tests)) unset($tests[$test]);
      }
    }

    //var_dump($tests, $payload_test);

    $return['isValid'] = !in_array(false, $tests);
    logger('Trigger Validation Test Results', $tests, null, array('method'=>__METHOD__,'line'=>__LINE__));
    foreach($tests as $test => $value) if(!$value) $return['errors'][] = 'Test [' . $test . '] failed';
    $return['data'] = $data;
    return $return;
  }

  public static function ValidatePayload($payload, $return_results = false){
    if(!empty($payload)){
      return static::_validatePayload($payload, $return_results);
    } else {
      return false;
    }
  }

  protected static function _validatePayload($payload, $return_results = false){
    return true;
  }

  public function readyForProcessing($return = false){
    $conditions = array(
      'Unprocessed' => !$this->getValue('processed'),
      //'Dependencies Resolved' => $this->checkDependenciesComplete(),
      'Scheduled' => $this->checkIsScheduled(),
      'Data Valid' => $this->isValid()
    );

    if($return) return $conditions;

    foreach($conditions as $condition => $success) if($success === false) return false;
    return true;
  }

  public function checkDependenciesComplete(){
    $dependencies = $this->getValue('dependencies');
    if(!$dependencies) $dependencies = array();
    return true;
  }

  public function checkIsScheduled(){
    if($scheduled = $this->getValue('scheduleDateTime')){
      if($scheduled instanceof MongoDate){
        if($scheduled->sec <= time()) return true;
        return false;
      } else {
        return true;
      }
    } else {
      return true;
    }
  }

  public function checkPayloadValid(){
    return $this->_valid;
  }

  public static function registerDependencies(array $triggerQueueItems = null){

  }

  public function isValid(){
    return $this->_valid;
  }

  public function getPayload(){
    return $this->getValue('payload');
  }

  public function isProcessed(){
    return $this->getValue('processed');
  }

  public function isSuccess(){
    return $this->getStatus() == 'success';
  }

  public function isFailure(){
    return $this->getStatus() == 'failure';
  }

  public function isNew(){
    return $this->getStatus() == 'queued';
  }

  public function isWorking(){
    return $this->getStatus() == 'working';
  }

  public function getStatus(){
    return $this->getValue('status');
  }

  public function setStatus($status) {
    $this->setValue('status', $status)->save();
  }

  public function getCaller(){
    return array(
      'userId' => $this->getValue('userId'),
      'projectId' => $this->getValue('projectId'),
      'taskId' => $this->getValue('taskId'),
      'organizationId' => $this->getValue('organizationId'),
    );
  }

  public function notifyCaller(){

  }

  public function notifyWebhook(){

  }

  public static function initDB(){
    if(!self::$dbInitialized){
      self::CI()->load->model(array(
        'main_model','users_model','jobs_model','tasks_model','workflows_model','tasktemplates_model'
      ));
      self::$dbInitialized = true;
    }
  }

  public static function SaveToDb(MongoId $id, array $data){
    self::initDB();
    return self::$CI->main_model->_update($data, array('_id' => $id), static::CollectionName());
  }

  public static function log($message, $type = 'debug', $var_dump = false){
    log_message($type, static::$_trigger . ': ' . $message);
    if($var_dump) var_dump($message);
  }

  public static function CollectionName(){
    return static::$_collection;
  }

  public static function Create($data){
    $filtered = di_allowed_only($data, mongo_get_allowed(static::CollectionName()));
    return self::CI()->mdb->insert(static::CollectionName(), $filtered);
  }

  public static function Update($id, $data){
    if(!is_array($id)) $id = array(_id($id));
    foreach($id as $i => $theId) $id[$i] = _id($theId);
    return self::CI()->mdb->whereIn('_id', $id)->set(di_allowed_only($data, mongo_get_allowed(static::CollectionName())))->updateAll(static::CollectionName());
  }

  public static function RecordBroadCastAndServiceUsage($usageData){

  }

  public static function GenerateWebhook($_current){
    $webhookRoot = 'http://workflolite.com/api/v1/webhooks/post_trigger_response';
    return $webhookRoot;
  }

  public static function GetBroadCastData($trigger, $_current){
    switch ($trigger):
      case 'messaging-email':
        $return = array(
          'vendor' => 'aws',
          'service' => 'dynamodb',
          'channel' => 'send_email',
          'webhook' => self::GenerateWebhook($_current)
        );
      break;
    endswitch;
    return $return;
  }

  public static function AddTrigger($data){
    $add = array(
      'dateAdded' => new MongoDate(),
      'scheduleDateTime' => null,
      'completeDateTime' => null,
      'userId' => null,
      'projectId' => null,
      'taskId' => null,
      'trigger' => static::$_trigger,
      'broadcast' => null,
      'dependencies' => null,
      'payload' => null,
      'status' => 'queued',
      'processed' => false,
      'acknowledged' => false,
      'organizationId' => null,
      'returnPayload' => null,
      'webhook' => null,
      'topic' => null,
    );

    $add = array_merge($add, $data);

    if(!isset($add['broadcast'])){
      $add['broadcast'] = self::GetBroadCastData(static::$_trigger, $add);
    }

    $validation = self::ValidateData($add);

    //var_dump($validation);

    if($validation['isValid']){
      $id = static::Create($validation['data']);
      self::ProcessUnprocessed();
      return $id;
    }
    return $validation['errors'];
  }

  public static function Get($id){
    $response = self::CI()->mdb->where('_id', _id($id))->limit(1)->get(self::CollectionName());
    if(!empty($response)) return $response[0];
  }

  public static function GetAllByIds($ids){
    $response = self::CI()->mdb->whereIn('_id', _id($ids))->get(self::CollectionName());
    return $response;
  }

  public static function GetAsObject($id){
    $record = self::Get($id);
    if(isset($record['trigger']) && !empty($record['trigger'])){
      logger('Valid Trigger', array($id, $record),null,array('method'=>__METHOD__,'line'=>__LINE__));
      switch ($record['trigger']){
        case 'messaging-email':
          require_once 'QueueItemSendEmail.php';
          logger('Queue Message', $record,null,array('method'=>__METHOD__,'line'=>__LINE__));
          $object = new QueueItemSendEmail($record);
          break;
        default:
          logger('Default', $record,null,array('method'=>__METHOD__,'line'=>__LINE__));
          $object = new TriggerQueueItem($record);
          break;
      }
      logger('Object', $object,null,array('method'=>__METHOD__,'line'=>__LINE__));
      return $object;
    } else {
      logger('Invalid Trigger', array($id, $record),'error',array('method'=>__METHOD__,'line'=>__LINE__));
    }
  }

  public static function GetAll($args = array(), $return_count = false){
    if(is_array($args)) $args = (object)$args;
    $handle = self::CI()->mdb;

    if(isset($args->select) && is_array($args->select)){
      $handle->select($args->select);
    }

    if(isset($args->wheres) && !empty($args->wheres)) $handle->where($args->wheres);

    if($return_count) return $handle->count(self::CollectionName());
    $args->direction = isset($args->direction) && strtolower($args->direction) == 'asc' ?  1 : -1;
    if(isset($args->orderby) && !empty($args->orderby)) {
      $handle->order_by(array($args->orderby=>$args->direction));
    } else {
      $handle->order_by(array('dateAdded'=>$args->direction));
    }
    if(!isset($args->limit)) $args->limit = config_item('pagination_rpp');
    if(!isset($args->offset)) $args->offset = 0;
    if(isset($args->limit) && is_numeric($args->limit)) $handle->limit($args->limit);
    if(isset($args->offset) && is_numeric($args->offset)) $handle->offset($args->offset);
    $response = (array) $handle->get(self::CollectionName());
    return $response;
  }

  public static function GetUnprocessed($limit = 1, $get_count = false){
    $args = array('wheres' => array('processed' => false));
    return self::GetAll($args, $get_count);
  }

  public static function GetRecordObjects(array $records){
    $single = !isset($records[0]);
    if($single) $records = array($records);
    foreach($records as $i => $record) {
      $validate = self::ValidateData($record);
      if($validate['isValid']){
        switch($record['trigger']){
          case 'messaging-email':
            $records[$i] = new QueueItemSendEmail($record);
            break;
        }
      }
    }
    if($single) return $records[0];
    return $records;
  }

  public static function ProcessUnprocessed($limit = 20){
    $records = self::GetUnprocessed($limit);
    $records = self::GetRecordObjects($records);

    foreach($records as $i => $record){
      if($record instanceof TriggerQueueItem) {
        $ready = $record->readyForProcessing(true);
        if($ready) {
          $record->broadcast();
        }
      }
    }
    return $records;
  }

}