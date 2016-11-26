<?php

require_once 'Task2.php';
require_once 'TaskTemplate2.php';
require_once 'WorkflowFactory.php';

class Template extends WorkflowFactory
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'templates';

  private static $taskTemplatesCache = array();

  public function __construct(array $data)
  {
    parent::__construct();
    $this->_initialize($data);
    $this->sortMetaSettings();
    //$this->_temp_importTaskTemplates();
  }

  public function _temp_importTaskTemplates(){
    $requires_update = false;
    $taskTemplates = $this->getValue('taskTemplates');
    if(isset($taskTemplates[0]) && $taskTemplates[0] instanceof MongoId) $requires_update = true;
    if($requires_update){
      $entities = self::CI()->mdb->whereIn('_id', $this->getValue('taskTemplates'))->get(TaskTemplate::CollectionName());
      foreach($entities as $i => $entity){
        $entities[$i]['id'] = (string) $entity['_id'];
        unset($entities[$i]['_id']);
        unset($entities[$i]['organizationId']);
      }
      $entities = array_values($entities);
      $this->setValue('taskTemplates', $entities);
      $this->save('taskTemplates');
      var_dump($this);
    }
  }


  public function displayDetails(){
    $data = array(
      'taskCount' => $this->taskCount()
    );

    return $data;
  }

  public function projectCount(){
    return self::CI()->mdb->where(array('templateId' => $this->id()))->count(Job::CollectionName());
  }

  public function taskCount(){
    return count($this->getValue('taskTemplates'));
  }

  public function getProjects(){
    $projects = self::CI()->mdb->where('templateId', $this->id())->get(Project::CollectionName());
    foreach($projects as $i => $project) $projects[$i] = new Project($project);
    return $projects;
  }

  public function getTemplates(){
    $entities = array();
    foreach($this->getValue('taskTemplates') as $i => $entity) $entities[] = new TaskTemplate2($entity);
    return $entities;
  }

  public static function Get($id){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    return new $class($record);
  }

  public static function cacheFlush($templateId = null){
    if($templateId){
      $templateId = (string) $templateId;
      if(isset(self::$taskTemplatesCache[$templateId])) unset(self::$taskTemplatesCache[$templateId]);
    } else {
      self::$taskTemplatesCache = array();
    }
  }

  public static function cacheGet($templateId, $flush = false){
    $templateId = (string) $templateId;
    if($flush) self::cacheFlush($templateId);
    if(isset(self::$taskTemplatesCache[$templateId])) return self::$taskTemplatesCache[$templateId];
    $template = Template::Get($templateId);
    if($template) self::cacheSet($templateId, $template);
    return $template;
  }

  public static function cacheSet($templateId, $data){
    $templateId = (string) $templateId;
    self::$taskTemplatesCache[$templateId] = $data;
  }

  public function getUrl(){
    return site_url('templates/' . $this->id());
  }

  public function getProjectsUrl(){
    return $this->getUrl() . '/projects';
  }

  public function createProjectUrl(){
    return self::GetCreateProjectUrl($this->id());
  }

  public static function GetCreateProjectUrl($templateId = null){
    return site_url('projects/create') . ($templateId ? '?template=' . $templateId : '');
  }

  public function sortMetaSettings(){
    // Handle Sorting
    $settings = array();
    foreach($this->getMetaSettings() as $i => $fieldData) {
      if(isset($fieldData['sort']) && !isset($settings[$fieldData['sort']])) $settings[$fieldData['sort']] = $fieldData;
      else $settings[] = $fieldData;
    }
    $this->setValue('metaFields', $settings);
    ksort($this->_current['metaFields'], SORT_NUMERIC);
  }

  public function getMetaSettings($slug = null){
    $fields = $this->getValue('metaFields');
    if($slug){
      foreach($fields as $i => $field) if($slug == $field['slug']) return $field;
      return false;
    }
    return $fields;
  }

  public static function MetaDataTypes(){
    $dataFormats = array(
      'string' => array(
        'validation' => array(
          'maxLengthDefault' => 255,
        ),
        'multiLine' => null
      ),
      'text' => array(
        'multiLine' => true
      ),
      'number' => array(
        'multiLine' => false
      ),
      'date' => array(
        'formatDefault' => 'n/j/Y',
        'multiLine' => null,
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
        'multiLine' => false,
        'options' => array(
          'formats' => array(
            'g:ia',
            'H:i:s',
            'G:i:s',
          )
        )
      ),
      'dateTime' => array(
        'formatDefault' => 'n/j/Y g:i a',
        'multiLine' => null,
        'options' => array(
          'formats' => array()
        )
      ),
      'address' => array(
        'multiLine' => true
      ),
      'url' => array(
        'multiLine' => null
      ),
      'phone' => array(
        'multiLine' => null
      ),
      'array' => array(
        'multiLine' => true
      )
    );

    foreach($dataFormats['date']['options']['formats'] as $dateOption){
      foreach($dataFormats['time']['options']['formats'] as $timeOption){
        $dataFormats['dateTime']['options']['formats'][] = $dateOption . ' ' . $timeOption;
      }
    }
    return $dataFormats;
  }

  public function addMeta($key, $slug = null, $type = null, $hide = false, $defaultValue = null){
    $dataTypes = self::MetaDataTypes();
    $dataFields = array_keys($dataTypes);

    $meta = array(
      'field' => $key,
      'sort' => null,
      'type' => in_array($type, $dataFields) ? $type : 'string',
      'hide' => (bool) $hide,
      'slug' => $slug ? $slug : StringTemplater::CamelCase($key),
      'clientView' => false
    );
    if($defaultValue) $meta['defaultValue'] = $defaultValue;
    $this->_current['metaFields'][] = $meta;
    foreach($this->_current['metaFields'] as $i => $metaField){
      if(!isset($metaField['sort'])) $this->_current['metaFields'][$i]['sort'] = $i;
      if(!isset($metaField['hide'])) $this->_current['metaFields'][$i]['hide'] = false;
      if(!isset($metaField['slug'])) $this->_current['metaFields'][$i]['slug'] = StringTemplater::CamelCase($metaField['field']);
      if(!isset($metaField['defaultValue'])) $this->_current['metaFields'][$i]['defaultValue'] = '';
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
    return $this->save('metaFields');
    //return self::Update($this->id(), array( 'metaFields' => $this->_current['metaFields'] ));
  }

  public static function GetAll(){
    $records = self::CI()->mdb->where('organizationId', UserSession::Get_Organization()->id())->get(self::CollectionName());
    foreach($records as $i => $record) $records[$i] = new Template($record);
    return $records;
  }




}