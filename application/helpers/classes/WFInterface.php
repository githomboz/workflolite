<?php

class WFInterface
{

  protected $_validated = false;
  protected $_valid = false;

  protected $id = null;

  protected $_raw = null;
  protected $_current = null;
  protected $_updates = null;

  private static $CI;
  public static $dbInitialized = false;

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = null;

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

    logger('Data to Validate', $data, null, array(__METHOD__,__FILE__,__LINE__));

    $tests = array(
      'THIS_SET' => true,
      'THAT_SET' => true,
    );

    if(is_array($exclude_tests)){
      foreach($tests as $test => $value){
        if(in_array($test, $exclude_tests)) unset($tests[$test]);
      }
    }

    $return['isValid'] = !in_array(false, $tests);
    logger('Trigger Validation Test Results', $tests, null, array(__METHOD__,__FILE__,__LINE__));
    foreach($tests as $test => $value) if(!$value) $return['errors'][] = 'Test [' . $test . '] failed';
    $return['data'] = $data;
    return $return;
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

  public static function Get($id){
    $response = self::CI()->mdb->where('_id', _id($id))->limit(1)->get(self::CollectionName());
    if(!empty($response)) return $response[0];
  }

  public static function GetAllByIds($ids){
    $response = self::CI()->mdb->whereIn('_id', _id($ids))->get(self::CollectionName());
    return $response;
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
  
  public static function LoggerStateString($__METHOD__,$__FILE__,$__LINE__){

  }

}