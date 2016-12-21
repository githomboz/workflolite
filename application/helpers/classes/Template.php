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

  protected $_version = null;

  private static $taskTemplatesCache = array();

  /**
   * Fields in queue to be saved
   * @var array
   */
  protected $_updates = array();

  public static $_instanceCount = 0;

  /**
   * Current value of getTemplates() method cached. Resets to null after each db save.
   * @var null
   */
  private $cachedSortedTemplates = null;

  /**
   * @var array
   */
  protected $_changedValues = array();

  public function __construct(array $data, $version = null)
  {
    parent::__construct();

    $this->_version = is_numeric($version) ? (int) $version : (int) $data['version'];

    $this->_initialize($data);

    self::$_instanceCount++;

    $this->sortMetaSettings();
    //$this->_temp_importTaskTemplates();
  }

  protected function _initialize(array $data){
    $this->_current = $data;
    if(isset($data['_id'])) $this->_id = $data['_id'];
    
    // Bring in valid template data based upon versionData
    if($this->_version != $data['version']){
      if(isset($data['versionData']['v' . $this->_version])){
        $versionData = $data['versionData']['v' . $this->_version];
        unset($versionData['taskTemplateChanges']);
        $this->_current = array_merge($this->_current, $versionData);
      }

    }
    return $this;

  }

  public function getCurrent(){
    return $this->_current;
  }

  public function stateCheck(){
    return array(
      'instanceCount' => self::$_instanceCount,
      'workingVersion' => $this->getValue('version'),
      'thisVersion' => $this->version(),
    );
  }

  public function setVersion($version){
    $this->_version = is_numeric($version) ? (int) $version : (int) $this->getValue('version');

    $this->_initialize($this->getCurrent());
    return $this;
  }

  public function name(){
    return $this->getValue('name') . ($this->version() > 1 ? ' (v' . $this->version() . ')' : '');
  }

  public function displayDetails(){
    $data = array(
      'taskCount' => $this->taskCount()
    );

    return $data;
  }

  /**
   * Return the number of projects with the current template id and version
   * @param $version int option version number
   * @return int
   */
  public function projectCount($version = null){
    $version = is_numeric($version) ? (int) $version : $this->version();
    return (int) self::GetProjectCount($this->id(), $version);
  }

  /**
   * Return number of projects found with a specific version of a template
   * @param $templateId
   * @param $version
   */
  public static function GetProjectCount($templateId, $version = null){
    if(is_numeric($version)){
      return CI()->mdb->where(array('templateId' => _id($templateId), 'templateVersion' => (int) $version))->count(Project::CollectionName());
    }
    return CI()->mdb->where(array('templateId' => _id($templateId)))->count(Project::CollectionName());
  }

  public function taskCount(){
    return count($this->getTemplates());
  }

  public function getUpdates(){
    return $this->_updates;
  }

  public function getProjects(){
    $projects = self::CI()->mdb->where('templateId', $this->id())->get(Project::CollectionName());
    foreach($projects as $i => $project) $projects[$i] = new Project($project);
    return $projects;
  }

  public function getTemplates(){
    //foreach($this->getValue('taskTemplates') as $i => $entity) $this->entities[] = new TaskTemplate2($entity, ($i+1));
    if(isset($this->cachedSortedTemplates)) return $this->cachedSortedTemplates;


    // Get raw taskTemplates
    $allTaskTemplates = $this->getValue('taskTemplates');
    $templateVersionData = $this->getValue('versionData');

    // Go through each and versionData[v.{versionNumber}]
    for($v = 1; $v <= $this->version(); $v++){
      $vNum = 'v' . $v;
      foreach($allTaskTemplates as $i => $taskTemplate){
        //    if _exists field doesn't exists, create it
        if(!isset($taskTemplate['_exists'])) $allTaskTemplates[$i]['_exists'] = true;
        if(isset($templateVersionData[$vNum])){
          $versionData = $templateVersionData[$vNum];
          $taskTemplateChanges = isset($versionData['taskTemplateChanges']) ? $versionData['taskTemplateChanges'] : array();
          foreach($taskTemplateChanges as $taskTemplateId => $taskTemplateData){
            //    up to the current version, merge in data
            if($taskTemplate['id'] == $taskTemplateId) {
              $allTaskTemplates[$i] = array_merge($allTaskTemplates[$i], $taskTemplateData);
            }
          }
        }
      }
    }

    // Unset taskTemplates where _exists === false
    foreach($allTaskTemplates as $i => $taskTemplate) {
      if($taskTemplate['_exists'] === false) unset($allTaskTemplates[$i]);
    }

    // Run sort

//    $versionData = isset($templateVersionData['v' . $this->version()]) ? $templateVersionData['v' . $this->version()] : array();
//    $taskTemplateChanges = isset($versionData['taskTemplateChanges']) ? $versionData['taskTemplateChanges'] : array();
//    foreach($allTaskTemplates as $i => $taskTemplate){
//      foreach($taskTemplateChanges as $taskTemplateId => $td){
//        if(!isset($taskTemplate['_exists'])) $allTaskTemplates[$i]['_exists'] = true;
//        if($taskTemplate['id'] == $taskTemplateId){
//          $allTaskTemplates[$i] = array_merge($allTaskTemplates[$i], $td);
//        }
//      }
//    }

    //var_dump($allTaskTemplates);
    usort($allTaskTemplates, 'Template::taskSortCompare');
    $this->_current['taskTemplates'] = $allTaskTemplates;
    foreach($allTaskTemplates as $i => $v) {
      //var_dump($v);
      $allTaskTemplates[$i] = new TaskTemplate2($v, $v['sortOrder']);
    }
    $this->cachedSortedTemplates = $allTaskTemplates;
    return $allTaskTemplates;
  }

  public function version(){
    return $this->_version;
  }

  public static function Get($id, $version = null){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    $version = (is_numeric($version) ? $version : $record['version']);
    $template = new $class($record, $version);
//    $templateVersionData = $template->getValue('versionData');
//    $versionData = isset($templateVersionData['v' . $template->version()]) ? $templateVersionData['v' . $template->version()] : array();
//    $taskTemplateChanges = isset($versionData['taskTemplateChanges']) ? $versionData['taskTemplateChanges'] : array();
//    $allTaskTemplates = $template->getTemplates();
//    foreach($taskTemplateChanges as $taskTemplateId => $td){
//      foreach($allTaskTemplates as $i => $taskTemplate){
//        if($taskTemplate instanceof TaskTemplate2 && (string) $taskTemplate->id() == $taskTemplateId){
//          $current = $taskTemplate->getCurrent();
//          $allTaskTemplates[$i] = array_merge($current, $td);
//        }
//      }
//    }
//    usort($allTaskTemplates, 'Template::taskSortCompare');
//    $template->setValue('taskTemplates', $allTaskTemplates);
    return $template;
  }

  public static function cacheFlush($templateId = null, $version = null){
    if($templateId){
      $templateId = (string) $templateId;
      if(isset(self::$taskTemplatesCache[$templateId.(is_numeric($version) ? '_v'.$version : '')])) unset(self::$taskTemplatesCache[$templateId]);
    } else {
      self::$taskTemplatesCache = array();
    }
  }

  public static function cacheGet($templateId, $version = null, $flush = false){
    $templateId = (string) $templateId;
    if($flush) self::cacheFlush($templateId, $version);
    if(isset(self::$taskTemplatesCache[$templateId.(is_numeric($version) ? '_v'.$version : '')])) return self::$taskTemplatesCache[$templateId.(is_numeric($version) ? '_v'.$version : '')];
    $template = Template::Get($templateId, $version);
    if($template) self::cacheSet($templateId, $version, $template);
    return $template;
  }

  public static function cacheSet($templateId, $version = null, $data){
    $templateId = (string) $templateId;
    self::$taskTemplatesCache[$templateId.(is_numeric($version) ? '_v'.$version : '')] = $data;
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
    ksort( $settings, SORT_NUMERIC);
    $this->_current['metaFields'] = $settings;
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

  public function removeMeta($slug){
    $success = false;
    $allMeta = $this->getValue('metaFields');
    foreach($allMeta as $i => $meta){
      if(isset($meta['slug']) && $meta['slug'] == $slug){
        unset($allMeta[$i]);
        $allMeta = array_values($allMeta);
        $success = true;
        $this->clearUpdates();
        $this->applyUpdates(array('metaFields' => $allMeta));
      }
    }
    return $success;
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
    $this->clearUpdates();
    $this->applyUpdates(array('metaFields' => array_values($this->_current['metaFields'])));
    //return self::Update($this->id(), array( 'metaFields' => $this->_current['metaFields'] ));
  }

  public static function GetAll(){
    $records = self::CI()->mdb->where('organizationId', UserSession::Get_Organization()->id())->get(self::CollectionName());
    foreach($records as $i => $record) $records[$i] = new Template($record);
    return $records;
  }

  public function saveTaskTemplate(TaskTemplate2 $taskTemplate, $sortOrder = null, $version = null){
    // Get all task templates for this template
    // Find the matching template
    // update fields
    // update sort order
    // save taskTemplates fields
    $this->saveThisToVersion($version);
  }

  /**
   * Update field within this entity
   * @param string $key
   * @param mixed $value
   * @return $this
   */
  public function setValue($key, $value){
    $this->_current[$key] = $value; // for legacy code. switch out for _updates soon
    $this->_updates[$key] = $value; // More efficient saving method
    if(isset($this->_current[$key])) $this->_changedValues[$key] = $this->_current[$key]; // track the previous value of a field
    return $this;
  }

  public function clearUpdates(){
    $this->_updates = array();
    return $this;
  }

  /**
   * Returns array where only the keys and values that are different from currently stored data are present
   * @param array $updates Updates to be made to db
   * @return array
   */
  public function getTaskTemplateDiff(array $updates){
    if(isset($updates['id'])){
      $return = array();
      foreach($this->getTemplates() as $i => $taskTemplate){
        if((string) $taskTemplate->id() == $updates['id']) {
          foreach($updates as $field => $value){
            if($taskTemplate->getValue($field) != $value){
              $return[$field] = $value;
            }
          }
        }
      }
      return $return;
    } else {
      return false;
    }
  }

  public function getTaskTemplate($id){
    if(!empty($id)){
      foreach($this->getTemplates() as $i => $taskTemplate){
        if((string) $taskTemplate->id() == $id) {
          return $taskTemplate;
        }
      }
    }
  }

  public function addRole($role){
    if(!empty($role)){
      $roles = $this->getValue('roles');
      if(!in_array($role, $roles)) $roles[] = $role;
      $this->clearUpdates();
      $this->applyUpdates(array('roles' => $roles));
      return true;
    }
    return false;
  }

  public function removeRole($role){
    if(!empty($role)){
      $roles = $this->getValue('roles');
      $index = array_search($role, $roles);
      if($index > -1){
        unset($roles[$index]);
        $roles = array_values($roles);
        $this->clearUpdates();
        $this->applyUpdates(array('roles' => $roles));
        return true;
      }
      return false;
    }
    return false;
  }

  public function applyUpdates(array $updates, $version = null){
    foreach($updates as $key => $value) {
      $this->_updates[$key] = $value; // More efficient saving method
      if(isset($this->_current[$key])) $this->_changedValues[$key] = $this->_current[$key]; // track the previous value of a field
    }

    $version = is_numeric($version) ? (int) $version : $this->version();

    //var_dump($version, $updates);
    //var_dump($this->_updates);
    return $this->saveThisToVersion($version);
  }

  public function saveThisToVersion($version = null){
    $return = array(
      'state' => null,
      'updates' => null,
      'hasUpdated' => false,
      'errors' => array()
    );

    if(empty($this->_updates)) $return['errors'][] = 'No updates found';
    else {
      $version = is_numeric($version) ? (int) $version : $this->version();


      $return['updates'] = $this->_updates = self::GenerateVersionData($this, $version, $this->_updates);

      $return['state'] = $this->stateCheck();

      //var_dump($return, $this->getTemplates());

      // Save
      $save = self::SaveToDb($this->id(), $this->getUpdates());
      // Merge data back into _current
      $this->_current = array_merge($this->_current, $this->getUpdates());
      $return['hasUpdates'] = true;

    }

    if(empty($return['errors'])) {
      $return['errors'] = false;
      $this->cachedSortedTemplates = null;
    }
    return $return;
  }

  /**
   * Return new _current data
   * @param $version
   * @param $template
   * @param $updates
   * @return array
   */
  public static function GenerateVersionData($template, $version,  $updates){
    //
    $template = $template->setVersion($version);
    $allTaskTemplates = $template->getTemplates();
    $current = $template->getCurrent();
    $currentVersion = $template->getValue('version');
    $currentUpdatedFieldValues = array();

    $performTaskTemplateSort = array();

    $update = array();
    foreach($updates as $field => $value){

      // Check for sortOrder updates
      if($field == 'taskTemplateChanges'){
        foreach($value as $taskTemplateId => $taskTemplateData){
          foreach($taskTemplateData as $k => $v){
            if($k == 'sortOrder') $performTaskTemplateSort[$taskTemplateId] = $v;
          }
        }
      }

      // Get original pre-update value for all changed fields
      if(isset($current[$field])) $currentUpdatedFieldValues[$field] = $current[$field];
    }

    // @todo:

    $versionData = $current['versionData'];
    // var_dump($versionData['v'.$version], $currentUpdatedFieldValues);

    // Merge $currentUpdateFieldValues with current versionData.version_number()
    // if passed version the same as current version, update local fields as well
    //var_dump('Attempting to save to version ' . $version.'; Most current version is '. $currentVersion.'; Passed in version: ' . $version);
//    if($version == $currentVersion){
//      $taskTemplateChanges = null;
//      if(isset($updates['taskTemplateChanges'])) $taskTemplateChanges = $updates['taskTemplateChanges'];
//      if($taskTemplateChanges){
//        unset($updates['taskTemplateChanges']);
//        // replace taskTemplateChanges in the taskTemplates field
//        $newTaskTemplates = array();
//        foreach($template->getTemplates() as $i => $taskTemplate){
//          if(!$taskTemplate->getValue('sortOrder')) $taskTemplate->setValue('sortOrder', ($i + 1));
//          $taskTemplateCurrent = $taskTemplate->getCurrent();
//          if(isset($taskTemplateChanges[(string) $taskTemplate->id()])){
//            $taskTemplateCurrent = array_merge($taskTemplateCurrent, $taskTemplateChanges[(string) $taskTemplate->id()]);
//          }
//          $newTaskTemplates[] = $taskTemplateCurrent;
//        }
//        foreach($newTaskTemplates as $i => $tempTaskTemplate) $newTaskTemplates[$i]['sortOrder'] = $i + 1;
//        // Add fully formed task templates back into update array
//        $updates['taskTemplates'] = $newTaskTemplates;
//      }
//      $update = $updates;
//    } else {

      // Set or update version data
      if(isset($versionData['v'.$version])) {
        // Handle taskTemplateChanges
        $taskTemplateChanges = isset($versionData['v'.$version]['taskTemplateChanges']) ? $versionData['v'.$version]['taskTemplateChanges'] : null;

        if(isset($updates['taskTemplateChanges'])) {
          $taskTemplateChanges = self::MergeTaskTemplateChanges($taskTemplateChanges, $updates['taskTemplateChanges']);
          //var_dump('taskTemplateChanges after merge', $taskTemplateChanges);
          if(!empty($taskTemplateChanges)) $updates['taskTemplateChanges'] = $taskTemplateChanges;
        }

        // Merge everything else
        $versionData['v'.$version] = array_merge($versionData['v'.$version], $updates);

        // Reinput taskTemplateChanges
      } else $versionData['v'.$version] = $updates;

      // Add previous version if version added is higher than the highest available template version
      if($version > $current['version']){

        if(!isset($versionData['v'.($version-1)]) && $version >= 2){
          $versionData['v'.($version-1)] = $currentUpdatedFieldValues;
        }
        // Create v{x} if it doesn't exist. If higher than current, increment version field by one
        $update['version'] = $version;

        // Merge in local data
      }

      // Sort v1,v2,v3 in versionData
      ksort($versionData);
      $update['versionData'] = $versionData;

//    }

    //var_dump('yay', $update, $performTaskTemplateSort);

    if(!empty($performTaskTemplateSort)){
      $taskTemplate = null;
      $taskTemplateSorted = null;
      foreach($performTaskTemplateSort as $taskTemplateId => $sortOrder){
        foreach($allTaskTemplates as $i => $ttaskTemplate) if((string) $ttaskTemplate->id() == $taskTemplateId) $taskTemplate = $ttaskTemplate;
        if($taskTemplate) $taskTemplateSorted = self::SortTaskTemplates($allTaskTemplates, $taskTemplate, $sortOrder);
      }
      // If isset $update['taskTemplates']
      //var_dump('tester', $update);
//      if(isset($update['taskTemplates'])){
//        $update['taskTemplates'] = $taskTemplateSorted;
//      } else {
        // Else each taskTemplate and corresponding sortOrder to versionData
        $tempVersionData = $update['versionData'];
        //var_dump('test');
        foreach($tempVersionData as $versionId => $versionData){
          if($versionId == 'v'.$version){
            if(!isset($versionData['taskTemplateChanges'])) $tempVersionData[$versionId]['taskTemplateChanges'] = array();
            foreach($taskTemplateSorted as $i => $tmpl){
              $tempData = isset($tempVersionData[$versionId]['taskTemplateChanges'][(string) $tmpl['id']]) ? $tempVersionData[$versionId]['taskTemplateChanges'][(string) $tmpl['id']] : array();
                $temp = array_merge($tmpl, $tempData);
                // var_dump($temp);
                $tempVersionData[$versionId]['taskTemplateChanges'][$tmpl['id']] = $temp;
                $update['versionData'][$versionId]['taskTemplateChanges'][$tmpl['id']]['sortOrder'] = $temp['sortOrder'];
            }
          }
        }
//        if($version > $currentVersion){
//          $update['taskTemplates'] = array_values($tempVersionData['v'.$version]['taskTemplateChanges']);
//          // Since now the current version, unset versionData for this version
//          unset($update['versionData']['v'.$version]);
//        } else {
//        }
//      }
    }
    return $update;
  }

  public static function SortTaskTemplates(array $taskTemplates, $taskTemplate, $sortOrder){

    // Remove template from array
    foreach($taskTemplates as $i => $currentTaskTemplate){
      if((string) $taskTemplate->id() == (string) $currentTaskTemplate->id()){
        unset($taskTemplates[$i]);
      }
    }

    // Remove broken indexes
    $taskTemplates = array_values($taskTemplates);
    foreach($taskTemplates as $i => $currentTaskTemplate) {
      $taskTemplates[$i] =  $currentTaskTemplate->getCurrent();;
    }

    // Determine target index
    $index = $sortOrder >= 1 ? $sortOrder - 1 : 0;

    // Reintroduce template to array sorted
    $taskTemplate = $taskTemplate->getCurrent();

    array_splice($taskTemplates, $index, 0, array($taskTemplate));

    $taskTemplates = array_values($taskTemplates);

    foreach($taskTemplates as $i => $currentTaskTemplate) {
      $taskTemplates[$i]['sortOrder'] = ($i + 1);
    }

    return $taskTemplates;
  }

  public static function MergeTaskTemplateChanges(array $currentTaskTemplateChanges = null, array $newTaskTemplateChanges = null){
    $taskTemplateChanges = empty($currentTaskTemplateChanges) ? array() : $currentTaskTemplateChanges;

    foreach($newTaskTemplateChanges as $templateId => $templateData){
      // If version data doesn't exist for this task, create the array
      if(!isset($taskTemplateChanges[$templateId])) $taskTemplateChanges[$templateId] = $templateData;
      else {
        // If the version data already exists, merge with new data
        $taskTemplateChanges[$templateId] = array_merge($taskTemplateChanges[$templateId], $newTaskTemplateChanges[$templateId]);
      }
    }
    return $taskTemplateChanges;
  }

  public static function taskSortCompare($a, $b) {
    if($a instanceof TaskTemplate2 && $b instanceof TaskTemplate2){
      if ($a->getValue('sortOrder') == $b->getValue('sortOrder')) {
        return 0;
      }
      return ($a->getValue('sortOrder') < $b->getValue('sortOrder')) ? -1 : 1;
    } else {
      if ($a['sortOrder'] == $b['sortOrder']) {
        return 0;
      }
      return ($a['sortOrder'] < $b['sortOrder']) ? -1 : 1;
    }
  }

}