<?php

require_once 'utilities/StringTemplater.php';

class WorkflowFactory extends WorkflowInterface
{

  /**
   * @var MongoId ID for $this
   */
  protected $_id = null;

  /**
   * Array of data as it is currently
   * @var null
   */
  protected $_current = null;

  private static $CI;
  public static $dbInitialized = false;

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = '';

  /**
   * Persistant cache to db requests to improve performance
   * @var array
   */
  private static $_cache = array();

  /**
   * Class that processes strings and applies data if necessary
   * @var StringTemplater
   */
  public $templater;

  public function __construct()
  {
    $this->templater = new StringTemplater();
    self::initDB();
  }

  /**
   * Prepare data and class for processing
   * @param array $data
   * @param bool $fullLoad Whether or not to load all linked records
   */
  protected function _initialize(array $data, $fullLoad = false){
    $this->_current = $data;
    if(isset($data['_id'])) $this->_id = $data['_id'];
    if($fullLoad){
      $this->loadOrganization();
      $this->loadJob();
      $this->loadWorkflow();
      if($this->isTask()){
        $this->loadTask();
      }
    }
  }

  protected static function CI(){
    if(!self::$CI){
      self::$CI = get_instance();
    }
    return self::$CI;
  }

  public static function initDB(){
    if(!self::$dbInitialized){
      self::CI()->load->model(array(
        'main_model','users_model','jobs_model','tasks_model','workflows_model','tasktemplates_model'
      ));
      self::$dbInitialized = true;
    }
  }

  public function hasId(){
    return isset($this->_id);
  }

  public function id(){
    return $this->_id;
  }

  /**
   * Load organization for this element
   * @return $this
   */
  public function loadOrganization(){
    if(isset($this->_current['organizationId'])){
      $this->organization = new Organization(self::LoadRecord($this->_current['organizationId'], 'organizations'));
    }
    return $this;
  }

  /**
   * Load job for this element
   * @return $this
   */
  public function loadJob(){
    if(isset($this->_current['jobId'])){
      $this->job = new Job(self::LoadRecord($this->_current['jobId'], 'jobs'));
    }
    return $this;
  }

  /**
   * Load workflow for this element
   * @return $this
   */
  public function loadWorkflow(){
    if(isset($this->_current['workflowId'])){
      $this->workflow = new Workflow(self::LoadRecord($this->_current['workflowId'], 'workflows'));
    }
    return $this;
  }

  /**
   * Load task for this element
   * @return $this
   */
  public function loadTask(){
    if(isset($this->_current['taskId'])){
      $this->_current['task'] = $this;
    }
    return $this;
  }

  public function getCurrent(){
    return $this->_current;
  }

  /**
   * Update data within this entity
   * @param array $data
   * @return $this
   */
  public function setValues(array $data){
    if(!empty($data)) $this->_current = array_merge($this->_current, $data);
    return $this;
  }

  /**
   * Update field within this entity
   * @param string $key
   * @param mixed $value
   * @return $this
   */
  public function setValue($key, $value){
    $this->_current[$key] = $value;
    return $this;
  }

  /**
   * Get data within this entity
   * @param string $field Name of the field/property to return
   * @return mixed
   */
  public function getValue($field){
    if(isset($this->_current[$field])) return $this->_current[$field];
    if(isset($this->$field)) return $this->$field;
    return false;
  }

  /**
   * Save the _current data to db
   * @throws Exception
   * @return $this
   */
  public function save(){
    if($this->hasId()){
      self::SaveToDb($this->id(), $this->_current);
      return $this;
    } else {
      throw new Exception('Entity (' . __CLASS__ . ') can not be pulled without an _id');
    }
  }

  public function isTask(){
    return static::$_collection === 'tasks';
  }

  public static function SaveToDb(MongoId $id, array $data){
    self::initDB();
    return self::$CI->main_model->_update($data, array('_id' => $id), static::CollectionName());
  }

  /**
   * Check if data has been cached
   * @param string $group The grouping for this particular cache request
   * @param string $key The key that identifies the data desired
   * @return bool
   */
  public static function IsCached($group, $key){
    return isset(self::$_cache[$group]) && isset(self::$_cache[$group][$key]);
  }

  /**
   * Save data to local cache
   * @param string $group The grouping for this particular cache request
   * @param string $key The key that identifies the data desired
   * @param mixed $data The data to store
   * @return bool
   */
  public static function AddToCache($group, $key, $data){
    if(!isset(self::$_cache[$group])) self::$_cache[$group] = array();
    self::$_cache[$group][$key] = $data;
    return true;
  }

  /**
   * Retrieve data from local cache
   * @param string $group The grouping for this particular cache request
   * @param string $key The key that identifies the data desired
   * @return mixed | bool
   */
  public static function GetFromCache($group, $key){
    if(isset(self::$_cache[$group]) && isset(self::$_cache[$group][$key])) return self::$_cache[$group][$key];
    return false;
  }

  public static function LoadRecord($id, $collection = null){
    $collection = $collection ? $collection : static::CollectionName();
    if(self::IsCached($collection, (string) $id)){
      return self::GetFromCache($collection, (string) $id);
    } else {
      $record = self::LoadId($id, $collection);
      self::AddToCache($collection, (string) $id, $record);
      return $record;
    }
  }

  /**
   * @param $id
   * @param $collection
   * @return array
   */
  public static function LoadId($id, $collection = null){
    self::initDB();
    $collection = $collection ? $collection : static::$_collection;
    $modelName = $collection . '_model';
    self::CI()->load->model($modelName);
    return self::CI()->$modelName->get($id);
  }

  public static function LoadData(array $data, $class = null){
    $class = $class ? $class : get_called_class();
    if(self::ValidData($data)) return new $class($data);
  }

  public static function CollectionName(){
    return static::$_collection;
  }


  public static function ValidData(array $data){
    return !empty($data) && isset($data['name']);
  }

  public static function Create($data){
    $filtered = di_allowed_only($data, mongo_get_allowed(static::CollectionName()));
    return self::CI()->mdb->insert(static::CollectionName(), $filtered);
  }

  public static function Duplicate($id, array $data = array()){
    return null; // return new id
  }
}

abstract class WorkflowInterface
{

  /**
   * @param string|MongoId $id The id of the subject
   * @param array $data Values to be merged with new entity
   * @return MongoId New entity id
   */
  abstract public static function Duplicate($id, array $data = array());

  /**
   * @param string|MongoId $id The id of the target
   * @return mixed Instance of entity class
   */
  abstract public static function LoadId($id);

  /**
   * @param array Data to be passed to load new instance
   * @param string $class The name of the object to return
   * @return mixed Instance of entity class
   */
  abstract public static function LoadData(array $data, $class);

  /**
   * @param array Data to be validated
   * @return bool Whether data is valid or not
   */
  abstract public static function ValidData(array $data);

}
