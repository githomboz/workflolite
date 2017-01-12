<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 1/11/17
 * Time: 6:48 PM
 */
class Bytion_SuperClass
{
  private static $instance = null;
  private static $_slug = 'Bytion';
  private $lib = [];

  public function __construct()
  {
    $this->init();
  }

  public function init(){
    static::_getRequestParser();
  }

  public static function GetInstance(){
    if(!self::$instance) self::$instance = new Bytion_SuperClass();
    return self::$instance;
  }

  public function RegisterLibrary($libName, $slug = null, $params = array(), $location = null){
    $logs = WFClientInterface::_getLogsTemplate();

    // Check if lib location is set
    // If set, verify file location
    // Require file
    if($location) {
      if(file_exists($location)){
        require_once $location;
      } else {
        $logs['errors'][] = 'Invalid library location provided ('.$location.')';
      }
    }

    if(strpos($libName, static::$_slug) ===  false) $libName = static::$_slug . '_' . $libName;

    if(!class_exists($libName)){
      // Check if file exists in orgs folder
      $fileName = $libName . '.php';
      $fileLocation = APPPATH.'helpers/orgs/' . $fileName;
      if(file_exists($fileLocation)) require_once $fileLocation;
    }

    $myClassInstance = null;
    // Check if lib name exists
    if(class_exists($libName)) {
      // Create instance of $libName in $lib array using $slug as key
      $reflection = new \ReflectionClass($libName);
      $myClassInstance = $reflection->newInstanceArgs($params);
    } else {
      $logs['errors'][] = 'The library requested could not be found';
    }

    $active = false;
    if($myClassInstance) $active = true;

    if(!$slug) $slug = $libName;

    $found = false;
    foreach($this->lib as $i => $lib){
      if($lib['libName'] == $libName){
        $found = true;
        $this->lib[$i] = [
          'libName' => $libName,
          'slug' => $slug,
          'location' => $location,
          'instance' => $myClassInstance,
          'active' => $active,
          'logs' => static::_mergeLogs($lib['logs'], $logs)
        ];
      }
    }
    if(!$found){
      $this->lib[] = [
        'libName' => $libName,
        'slug' => $slug,
        'location' => $location,
        'instance' => $myClassInstance,
        'active' => $active,
        'logs' => $logs
      ];
    }

    var_dump($this->lib);
    return $this;
  }

  public function lib($libName){
    foreach($this->lib as $lib){
      if($lib['libName'] == $libName && $lib['active']){
        return $lib;
      }
    }
    return false;
  }

  public function _($slug){
    foreach($this->lib as $lib){
      if($lib['slug'] == $slug && $lib['active']){
        return $lib;
      }
    }
    return false;
  }

  public function _getRequestParser(){
    return $this->RegisterLibrary('RequestParser','RequestParser');
  }

  public static function _mergeLogs(array $current_logs, array $new_logs){
    foreach(['errors','logs'] as $context){
      if(isset($current_logs[$context]) && isset($new_logs[$context])){
        $current_logs[$context] = array_merge($current_logs[$context], $new_logs[$context]);
      }
    }
    return $current_logs;
  }

}

function Bytion_SC(){
  return Bytion_SuperClass::GetInstance();
}