<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define('MODULES_CLASS_NAME_FIELD', 'module');

/**
 * Class instantiated by CodeIgniter.  Created to only allow for one instance of Modules (Singleton)
 * Class Modules
 */
class Modules implements ArrayAccess {

  private static $_instance = null;

  public function __construct(){
    if(!self::$_instance){
      self::$_instance = new ModulesSystem();
    }
    return self::$_instance;
  }

  public function __call($name, $args){
    if(method_exists(self::$_instance, $name)){
      return call_user_func_array(array(self::$_instance, $name), $args);
    }
    throw new Exception('Call to undefined method ' . __CLASS__ . '::'.$name.'()');
  }

  public static function __callStatic($name, $args){
    if(method_exists(self::$_instance, $name)){
      return call_user_func_array(array(self::$_instance, $name), $args);
    }
    throw new Exception('Call to undefined method ' . __CLASS__ . '::'.$name.'()');
  }

  public function offsetSet($offset, $value) {
    return self::$_instance->offsetGet($offset, $value);
  }

  public function offsetExists($offset) {
    return self::$_instance->offsetExists($offset);
  }

  public function offsetUnset($offset) {
    return self::$_instance->offsetUnset($offset);
  }

  public function offsetGet($offset) {
    return self::$_instance->offsetGet($offset);
  }
}

/**
 * Module System that discovers, installs, activates, and otherwise manages modules throughout system.
 * Class ModulesSystem
 */
class ModulesSystem implements ArrayAccess {

  /**
   * Folder name of the directory where modules are contained
   * @var string
   */
  private static $_folder = 'modules';

  /**
   * Directory where modules are contained
   * @var string
   */
  private static $_PATH = null;

  /**
   * Initialization file for each module
   * @var string
   */
  private static $_module_file = 'module.php';

  /**
   * Name of main modules collection
   * @var string
   */
  private static $collection = 'modules';

  /**
   * Array of modules discovered in the modules folder
   * @var array
   */
  private static $_discovered = array();

  /**
   * Array of instantiated modules
   * @var array
   */
  private static $_modules = array();

  /**
   * The last module acted upon
   * @var string
   */
  private static $_last_touched = null;

  private static $CI;

  private static $mdb;

  public function __construct(){
    // Instantiate CI class
    self::$CI =& get_instance();
    $this->_init();
  }

  private function _init(){
    // Set up $_PATH variable
    static::$_PATH = APPPATH . static::$_folder;

    if(!isset(self::$CI->installedModules)) self::$CI->installedModules = array();

    if(self::$CI->config->item('utilize_mongo_db')) self::$mdb = get_mongo_instance();

    // Discover if any modules are present in $_PATH directory
    $this->_discover();

    // Compare what is discovered to what is installed
    $this->_reconsile();

    // Expose active modules
    $this->exposeActiveModules();
  }

  /**
   * Discover what modules are available in the modules folder and create an array of module details
   */
  private function _discover(){
    // Load file_helper
    self::$CI->load->helper('file');

    // Check if $_PATH folder is readable
    if($results = get_dir_file_info(self::$_PATH)){
      // Grab only directories
      foreach($results as $file_name => $file_data){
        if(strpos($file_name, '.') === false){
          // Check each directory for a file called "module.php"
          $module_location = self::$_PATH.'/'.$file_name;
          $files = get_dir_file_info($module_location);
          if(isset($files[self::$_module_file])){
            $moduleDetails = null;
            $file = $module_location.'/'.self::$_module_file;
            if(file_exists($file)) {
              include $file;
              if(debug_mode()) log_message('debug', 'SF: ' . $moduleDetails[MODULES_CLASS_NAME_FIELD] . ' Module (filename: '.$file.') included.', false, array('modules', __METHOD__));
            }
            if(is_array($moduleDetails) && isset($moduleDetails[MODULES_CLASS_NAME_FIELD])) {

              if(debug_mode()) log_message('debug', 'SF: ' . $moduleDetails[MODULES_CLASS_NAME_FIELD] . ' Module about to instantiate.', false, array('modules', __METHOD__));
              self::$_discovered[] = $moduleDetails[MODULES_CLASS_NAME_FIELD];

              // Instantiate module class
              $class = '\Modules\\'.$moduleDetails[MODULES_CLASS_NAME_FIELD];
              if(debug_mode()) log_message('debug', 'SF: ' . $moduleDetails[MODULES_CLASS_NAME_FIELD] . ' Checking for class name ('.$class.').', false, array('modules', __METHOD__));
              if (class_exists($class)) {
                $mod = new $class($moduleDetails);
                if($mod instanceof ModulesImplementation) {
                  $this->setModule($moduleDetails[MODULES_CLASS_NAME_FIELD], $mod);
                  if(debug_mode()) log_message('debug', 'SF: ' . $moduleDetails[MODULES_CLASS_NAME_FIELD] . ' Module instantiated', false, array('modules', __METHOD__));
                }
              }
            }
          }
        }
      }
    }
    return $this;
  }

  public function discovered(){
    return self::$_discovered;
  }

  /**
   * Compares the modules that have been discovered to the modules that the db knows about and uninstalls or deactivates those that are missing
   */
  private function _reconsile(){
    // Grab all installed modules
    self::$CI->installedModules = (self::$mdb) ? self::$mdb->get(self::$collection) : array();

    //var_dump(self::$CI->installedModules);
    if(debug_mode()) log_message('debug', 'SF: ' . 'Running ModuleSystem->_reconsile().', false, array('modules', __METHOD__));

    // Check if all installed modules have been discovered
    foreach(self::$CI->installedModules as $i => $module){
      if(!$this->moduleExists($module[MODULES_CLASS_NAME_FIELD])){
        // If active, but not discovered, deactivate
        if($module['active']) {
          // Check if module deactivated
          if(debug_mode()) log_message('debug', 'SF: ' . $module[MODULES_CLASS_NAME_FIELD] . ' Module deactivated', false, array('modules', __METHOD__));

          // Deactivate in db
          self::$mdb->where(array(MODULES_CLASS_NAME_FIELD => $module[MODULES_CLASS_NAME_FIELD]))->set(array('active' => false))->update(self::$collection);

          // Deactivate in mem cache
          self::$CI->installedModules[$i]['active'] = false;
        }
      } else {
        // Add installed to $_modules array
        if(debug_mode()) log_message('debug', 'SF: ' . $module[MODULES_CLASS_NAME_FIELD] . ' added to installed modules', false, array('modules', __METHOD__));
        $this->getModule($module[MODULES_CLASS_NAME_FIELD])->setInstalled(true);
      }

    }

  }

  /**
   * Run installation routines for the given module.  Create DB structure, create records, etc.
   * @param $module_name string Name of the module to install
   * @return bool Returns the ModuleImplementation object
   */
  public function install($module_name){
    if($this->moduleExists($module_name)){
      if(!$this->installed($module_name)){
        $this->getModule($module_name)->install();
        if(debug_mode()) log_message('debug', 'SF: ' . $module_name . ' Module installed', false, array('modules', __METHOD__));
      }
      self::$_last_touched = $module_name;
    }
    return $this;
  }

  /**
   * Run uninstallation routines for the given module.  Remove DB structure, truncate collection, etc.
   * @param $module_name string Name of the module to uninstall
   * @return bool Returns true or false depending on success
   */
  public function uninstall($module_name){
    if($this->moduleExists($module_name)){
      $this->getModule($module_name)->uninstall();
      self::$_last_touched = $module_name;
      if(debug_mode()) log_message('debug', 'SF: ' . $module_name . ' Module uninstalled', false, array('modules', __METHOD__));
    }
    return $this;
  }

  /**
   * Returns array of installed modules, or a true or false depending on whether the module passed in is installed or not
   * @param $module_name string Name of module to check if installed (optional)
   * @return array | bool
   */
  public function installed($module_name = null){
    if($module_name){
      foreach(self::$CI->installedModules as $i => $module){
        if($module[MODULES_CLASS_NAME_FIELD] == $module_name) return true;
      }
      return false;
    } else {
      return self::$CI->installedModules;
    }
  }

  /**
   * Activate the specified module
   * @param $module_name string Name of module to be activated
   * @return Modules $this
   */
  public function activate($module_name = null){
    if(!$module_name && self::$_last_touched) $module_name = self::$_last_touched;
    if($module_name){
      if($this->moduleExists($module_name)) {
        $this->getModule($module_name)->activate();
        self::$_last_touched = $module_name;
      }

      if(debug_mode()) log_message('debug', 'SF: ' . $module_name . ' Module activated', false, array('modules', __METHOD__));
      $this->exposeActiveModules();
    }
    return $this;
  }

  /**
   * Deactivate the specified module
   * @param $module_name string Name of module to be deactivated
   * @return Modules $this
   */
  public function deactivate($module_name = null){
    if(!$module_name && self::$_last_touched) $module_name = self::$_last_touched;
    if($module_name) {
      if ($this->moduleExists($module_name)) {
        $this->getModule($module_name)->deactivate();
        self::$_last_touched = $module_name;
      }

      if(debug_mode()) log_message('debug', 'SF: ' . $module_name . ' Module deactivated', false, array('modules', __METHOD__));
      $this->exposeActiveModules();
    }
    return $this;
  }

  /**
   * An array featuring the details of the module that is being installed
   * @param $module_name string Name of module to be detailed
   * @return array Details
   */
  public function details($module_name = null){
    if(!$module_name && self::$_last_touched) $module_name = self::$_last_touched;

    // handle grabbing the details

    self::$_last_touched = $module_name;
  }

  /**
   * Loop through active modules and expose their functionality
   */
  public function exposeActiveModules(){
    foreach($this->getAllModules() as $module){
      if($module->isActive()){
        $module->expose();
      }
    }
  }

  public function getAllModules(){
    return self::$_modules;
  }

  public function getModule($module_name){
    if($this->moduleExists($module_name)) return self::$_modules[$module_name];
    return false;
  }

  public function setModule($module_name, $value){
    if($value instanceof ModulesImplementation) self::$_modules[$module_name] = $value;
  }

  public function unsetModule($module_name){
    if($this->moduleExists($module_name)) unset(self::$_modules[$module_name]);
  }

  public function moduleExists($module_name){
    return isset(self::$_modules[$module_name]);
  }

  public function offsetSet($offset, $value) {
    return $this->setModule($offset, $value);
  }

  public function offsetExists($offset) {
    return $this->moduleExists($offset);
  }

  public function offsetUnset($offset) {
    return $this->unsetModule($offset);
  }

  public function offsetGet($offset) {
    return $this->getModule($offset);
  }
}

abstract class ModulesImplementation {

  public static $mainCollection = 'modules';

  public static $module = null;
  public static $namespace = null;
  public static $collection = null;

  public static $moduleFile = null;
  public static $moduleDir = null;

  protected static $meta = null;

  protected static $mdb;

  protected static $CI;

  /**
   * Run installation routines such as creating database or collection, indexes, and creating records or files if necessary
   * @return mixed
   */
  abstract public function install();

  /**
   * Run uninstallation routines such as deleting database or collection, or removing files
   * @return mixed
   */
  abstract public function uninstall();

  /**
   * Truncate collection or clear out db collection to fresh install setup
   * @return mixed
   */
  abstract public function reset();

  public function __construct($meta = null){
    // Set CI
    self::$CI =& get_instance();

    // Instantiate MongoDB
    self::$mdb = null;//early_mongo_connection();

    // Normalize $name variable
    static::$module = str_replace(static::$namespace.'\\', '', static::$module);

    // Db Collection
    static::$collection = 'mod_' . strtolower(static::$module);

    // Set Meta
    static::$meta = $meta;

    // Default installed to false
    $this->setInstalled(false);
  }

  /**
   * Returns true if update successful. False if failure.
   * @return bool
   * @throws \MongoQB\Exception
   */
  public function activate(){
    // Update memory cache
    foreach(self::$CI->installedModules as $i => $module){
      if($module[MODULES_CLASS_NAME_FIELD] == static::$module){
        self::$CI->installedModules[$i]['active'] = true;
      }
    }

    // Update db
    return self::$mdb->where(array(MODULES_CLASS_NAME_FIELD => static::$module))->set(array('active' => true))->update(self::$mainCollection);
  }

  /**
   * Returns true if update successful. False if failure.
   * @return bool
   * @throws \MongoQB\Exception
   */
  public function deactivate(){
    // Update memory cache
    foreach(self::$CI->installedModules as $i => $module){
      if($module[MODULES_CLASS_NAME_FIELD] == static::$module){
        self::$CI->installedModules[$i]['active'] = false;
      }
    }

    // Update db
    return self::$mdb->where(array(MODULES_CLASS_NAME_FIELD => static::$module, 'active' => true))->set(array('active' => false))->update(self::$mainCollection);
  }

  /**
   * Returns true if module has been activated.  False if not active.
   * @return bool
   */
  public function isActive(){
    foreach(self::$CI->installedModules as $i => $module) {
      if($module[MODULES_CLASS_NAME_FIELD] == static::$module) return $module['active'];
    }
    return false;
    //return self::$mdb->where(array(MODULES_CLASS_NAME_FIELD => static::$module, 'active' => true))->count(self::$mainCollection) > 0;
  }

  /**
   * Returns true if module has been installed.  False if not installed.
   * @return bool
   */
  public function isInstalled(){
    foreach(self::$CI->installedModules as $i => $module) if($module[MODULES_CLASS_NAME_FIELD] == static::$module) return true;
    return false;
    //return self::$mdb->where(array('module' => static::$module, 'installed' => true))->count(self::$mainCollection) > 0;
  }

  public function hasAdminPages(){
    return isset(static::$meta['admin_section']) && !empty(static::$meta['admin_section']);
  }

  public function createCollection(){
    $this->addSetting('collectionCreated', true);
  }

  public function deleteCollection(){
    if($this->getSetting('collectionCreated') === true){
      $config = early_mongo_config();
      return self::$mdb->dropCollection($config['mongo_database'], static::$collection);
    }
  }

  public function truncateCollection(){
    if($this->getSetting('collectionCreated') === true) {
      return self::$mdb->deleteAll(static::$collection);
    }
  }

  /**
   * Calls api functionality to make it accessible to rest of the app/site
   */
  public function expose(){
    if($this->isActive()){
      if(file_exists(static::$moduleDir.'/common.php')) include_once static::$moduleDir.'/common.php';
      if(file_exists(static::$moduleDir.'/api.php')) include_once static::$moduleDir.'/api.php';
      // Call General functions
      $namespace = static::$module.'_Module\\';
      $functions = array('register_styles','register_scripts');
      foreach($functions as $func){
        $function = $namespace.'register_styles';
        if(function_exists($function)){
          call_user_func($function, static::$module);
        }
      }
    }
  }

  public function getDashboardWidget(){
    if(file_exists(static::$moduleDir.'/common.php')) include_once static::$moduleDir.'/common.php';
    $namespace = static::$module.'_Module\\';
    $function = $namespace.'add_dashboard_widget';
    if(function_exists($function)){
      return call_user_func($function, static::$module);
    }
    return false;
  }

  public function getRegisteredSettingsScripts(){
    if(file_exists(static::$moduleDir.'/common.php')) include_once static::$moduleDir.'/common.php';
    $namespace = static::$module.'_Module\\';
    $function = $namespace.'register_scripts';
    if(function_exists($function)){
      return call_user_func($function, static::$module);
    }
    return false;
  }

  public function getRegisteredSettingsStyles(){
    if(file_exists(static::$moduleDir.'/common.php')) include_once static::$moduleDir.'/common.php';
    $namespace = static::$module.'_Module\\';
    $function = $namespace.'register_styles';
    if(function_exists($function)){
      return call_user_func($function, static::$module);
    }
    return false;
  }

  /**
   * Meta install setter
   * @param $value bool
   */
  public function setInstalled($value){
    static::$meta['installed'] = $value;
  }

  /**
   * MUST be called at the beginning of every install implementation.
   */
  protected function _initInstall(){
    // Add module to main module collection
    $data = array(
      'dateAdded' => new MongoDate(),
      'name' => $this->meta('name'),
      'version' => $this->meta('version'),
      'module' => $this->meta('module'),
      'idHash' => md5($this->meta('module').$this->meta('version')),
      'settings' => array(),
      'active' => false
    );

    $id = self::$mdb->insert(self::$mainCollection, $data);
    $data['_id'] = $id;

    // Add to memory cache
    self::$CI->installedModules[] = $data;
  }

  /**
   * MUST be called at the beginning of every uninstall implementation
   */
  protected function _initUninstall(){
    // Delete module from the main module collection
    self::$mdb->where(array(MODULES_CLASS_NAME_FIELD=>static::$module))->limit(1)->delete(self::$mainCollection);

    // Remove from memory cache
    self::$CI =& get_instance();
    foreach(self::$CI->installedModules as $i => $module) {
      if($module[MODULES_CLASS_NAME_FIELD] == static::$module) {
        unset(self::$CI->installedModules[$i]);
        self::$CI->installedModules = array_values(self::$CI->installedModules);
      }
    }

    // Remove modules collection
    $this->deleteCollection();
  }

  /**
   * Return all meta or a specified field
   * @param null $field (Optional)
   * @return mixed Value of specified field or all meta data if no field is specified. Null if specified but not found
   */
  public function meta($field = null){
    if($field){
      return isset(static::$meta[$field]) ? static::$meta[$field] : null;
    }
    return static::$meta;
  }

  /**
   * @alias meta()
   * @param null $field
   * @return mixed
   */
  public function getMeta($field = null){
    return $this->meta($field);
  }

  public function getSetting($field){
    // Return mem cache
    $settings = $this->getSettings();
    return isset($settings[$field]) ? $settings[$field] : null;
  }

  public function getSettings(){
    // Return mem cache
    foreach(self::$CI->installedModules as $i => $module) {
      if ($module[MODULES_CLASS_NAME_FIELD] == static::$module) {
        return $module['settings'];
      }
    }
  }

  public function addSetting($key, $value){
    // Store in mem cache
    foreach(self::$CI->installedModules as $i => $module) {
      if ($module[MODULES_CLASS_NAME_FIELD] == static::$module) {
        if ($module[MODULES_CLASS_NAME_FIELD] == static::$module) {
          $id = $module['_id'];
          $settings = array_merge($module['settings'], array($key => $value));
          self::$CI->installedModules[$i]['settings'] = $settings;
        }
      }
    }

    // Store in db
    if(isset($id) && isset($settings)){
      self::$mdb->where(array('_id'=>$id))->set('settings', $settings)->update(self::$mainCollection);
    }
  }

  /**
   * See dir()
   * @return string
   * @alias dir();
   */
  public function path(){
    return static::$moduleDir;
  }

  /**
   * Path to module folder
   * @return string
   */
  public function dir(){
    return $this->path();
  }

  /**
   * Path to module master class
   * @return string
   */
  public function file(){
    return static::$moduleFile;
  }

}



/* End of file Modules.php */