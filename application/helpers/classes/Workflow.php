<?php

require_once 'WorkflowFactory.php';

class Workflow extends WorkflowFactory
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'workflows';

  public function __construct(array $data)
  {
    parent::__construct();
    $this->_initialize($data);
  }

  public function addJob(Job $job){
    if($this->hasId()){
      $job->setValues(array('workflowId' => $this->id()))->save();
      return $this;
    } else {
      throw new Exception('Job can not be added without an _id');
    }
  }

  public function jobCount(){
    return self::CI()->mdb->where(array('workflowId' => $this->id()))->count(Job::CollectionName());
  }

  public function taskCount(){
    return count($this->getValue('taskTemplates'));
  }

  public function getJobs(){
    $jobs = self::CI()->mdb->where('workflowId', $this->id())->get(Job::CollectionName());
    foreach($jobs as $i => $job) $jobs[$i] = new Job($job);
    return $jobs;
  }

  public function getTemplates(){
    $entities = self::CI()->mdb->whereIn('_id', $this->getValue('taskTemplates'))->get(TaskTemplate::CollectionName());
    foreach($entities as $i => $entity) $entities[$i] = new TaskTemplate($entity);
    return $entities;
  }

  public static function Get($id){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    return new $class($record);
  }

  public function getUrl(){
    return site_url('workflows/' . $this->id());
  }

  public function getJobsUrl(){
    return $this->getUrl() . '/jobs';
  }

  public static function MetaDataTypes(){
    $dataFormats = array(
      'string' => array(
        'maxLengthDefault' => 255
      ),
      'integer' => array(

      ),
      'date' => array(
        'formatDefault' => 'n/j/Y',
        'options' => array(
          'formats' => array(
            'n/j/y',
            'n-j-y',
            'n/j/Y',
            'n-j-Y',
            'm/d/Y',
            'm-d-Y',
            'F j, Y',
            'l, F j, Y',
          )
        )
      ),
      'time' => array(
        'formatDefault' => 'g:i a',
        'options' => array(
          'formats' => array(
            'g:ia',
            'g:i a',
            'g:i A',
            'H:i:s',
            'G:i:s',
          )
        )
      ),
      'dateTime' => array(
        'formatDefault' => 'n/j/Y g:i a',
        'options' => array(
          'formats' => array()
        )
      ),
      'address' => array(),
      'url' => array(),
      'array' => array(

      )
    );

    foreach($dataFormats['date']['options']['formats'] as $dateOption){
      foreach($dataFormats['time']['options']['formats'] as $timeOption){
        $dataFormats['dateTime']['options']['formats'][] = $dateOption . ' ' . $timeOption;
      }
    }
    return $dataFormats;
  }

  public function addMeta($key, $slug = null, $type = null, $hide = false, $defaultValue = ''){
    $dataTypes = self::MetaDataTypes();
    $dataFields = array_keys($dataTypes);

    $meta = array(
      'field' => $key,
      'type' => in_array($type, $dataFields) ? $type : 'string',
      'hide' => (bool) $hide,
      'slug' => $slug ? $slug : StringTemplater::CamelCase($key),
      'value' => $defaultValue
    );
    $this->_current['metaFields'][] = $meta;
    foreach($this->_current['metaFields'] as $i => $metaField){
      if(!isset($metaField['hide'])) $this->_current['metaFields'][$i]['hide'] = false;
      if(!isset($metaField['slug'])) $this->_current['metaFields'][$i]['slug'] = StringTemplater::CamelCase($metaField['field']);
      if(!isset($metaField['value'])) $this->_current['metaFields'][$i]['value'] = '';
      switch($metaField['type']){
        case 'string':
        case 'date':
        default:
          if(isset($dataTypes[$metaField['type']])){
            foreach($dataTypes[$metaField['type']] as $key => $value){
              if($key != 'options'){
                if(!isset($metaField[$key])) $this->_current['metaFields'][$i][$key] = $value;
                if(!isset($metaField['_'])) $this->_current['metaFields'][$i]['_'] = $value;
              }
            }
          }
          break;
      }

      // Set slug to index
      //if(!isset($this->_current['metaFields'][$metaField['slug']])) $this->_current['metaFields'][$metaField['slug']] = null;
      //$this->_current['metaFields'][$metaField['slug']] = $this->_current['metaFields'][$i];
    }
    //foreach($this->_current['metaFields'] as $i => $metaField) if(is_numeric($i)) unset($this->_current['metaFields'][$i]);
    $this->_current['metaFields'] = array_values($this->_current['metaFields']);
    return self::Update($this->id(), array( 'metaFields' => $this->_current['metaFields'] ));
  }


}