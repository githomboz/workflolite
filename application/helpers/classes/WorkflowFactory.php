<?php

require_once 'utilities/StringTemplater.php';

class WorkflowFactory
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

  public static $mdb;
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

  public static function initDB(){
    if(!self::$dbInitialized){
      self::$mdb =& get_instance();
      self::$mdb->load->model(array(
        'main_model','jobs_model','tasks_model','workflows_model','tasktemplates_model'
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
      $this->_current['organization'] = new Organization(self::LoadRecord($this->_current['organizationId'], 'organizations'));
    }
    return $this;
  }

  /**
   * Load job for this element
   * @return $this
   */
  public function loadJob(){
    if(isset($this->_current['jobId'])){
      $this->_current['job'] = new Job(self::LoadRecord($this->_current['jobId'], 'jobs'));
    }
    return $this;
  }

  /**
   * Load workflow for this element
   * @return $this
   */
  public function loadWorkflow(){
    if(isset($this->_current['workflowId'])){
      $this->_current['workflow'] = new Workflow(self::LoadRecord($this->_current['workflowId'], 'workflows'));
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
    return self::$mdb->main_model->_update($data, array('_id' => $id), static::$_collection);
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
    $collection = $collection ? $collection : static::$_collection;
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
   */
  public static function LoadId($id, $collection = null){
    self::initDB();
    $collection = $collection ? $collection : static::$_collection;
    $modelName = $collection . '_model';
    self::$mdb->load->model($modelName);
    return self::$mdb->$modelName->get($id);
  }

  public static function LoadData(array $data, $class = null){
    $class = $class ? $class : get_called_class();
    if(self::ValidData($data)) return new $class($data);
  }

  public static function CollectionName(){
    return static::$_collection;
  }


}

interface WorkflowInterface
{

  /**
   * @param $data Values that will create new entity
   * @param null $templateId The id of the template from which to generate this entity
   * @return mixed New entity
   */
  public static function Create($data, $templateId = null);

  /**
   * @param string|MongoId $id The id of the subject
   * @param array $data Values to be merged with new entity
   * @return MongoId New entity id
   */
  public static function Duplicate($id, array $data = array());

  /**
   * @param string|MongoId $id The id of the target
   * @return mixed Instance of entity class
   */
  public static function LoadId($id);

  /**
   * @param array Data to be passed to load new instance
   * @param string $class The name of the object to return
   * @return mixed Instance of entity class
   */
  public static function LoadData(array $data, $class);

  /**
   * @param array Data to be validated
   * @return bool Whether data is valid or not
   */
  public static function ValidData(array $data);

}
