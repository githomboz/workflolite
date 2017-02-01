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
    $this->RegisterLibrary('OrderManager','OrderManager');
  }

  public static function GetInstance(){
    if(!self::$instance) self::$instance = new Bytion_SuperClass();
    return self::$instance;
  }

  /**
   * Get Organization Templates
   */
  public static function GetTemplates(){

  }

  public function RegisterLibrary($libName, $slug = null, $params = array(), $location = null){
    $logs = WFClientInterface::GetLogsTemplate();
    $ll = '['.__METHOD__.'::scope] ';

    // Check if lib location is set
    // If set, verify file location
    // Require file
    if($location) {
      if(file_exists($location)){
        require_once $location;
      } else {
        $logs['errors'][] = str_replace('::scope','', $ll) . 'Invalid library location provided ('.$location.')';
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
      $logs['errors'][] = str_replace('::scope','', $ll) . 'The library requested could not be found';
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
          'logs' => WFClientInterface::MergeLogs($lib['logs'], $logs)
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

    return $this;
  }

  public function getLib($libName, $getInstance = true){
    foreach($this->lib as $lib){
      if($lib['libName'] == $libName && $lib['active']){
        if($getInstance) return $lib['instance'];
        return $lib;
      }
    }
    return false;
  }

  public function getLibBySlug($slug, $getInstance = true){
    foreach($this->lib as $lib){
      if($lib['slug'] == $slug && $lib['active']){
        if($getInstance) return $lib['instance'];
        return $lib;
      }
    }
    return false;
  }

  public function _getRequestParser(){
    return $this->RegisterLibrary('RequestParser','RequestParser');
  }

}

function Bytion_SC(){
  return Bytion_SuperClass::GetInstance();
}

function Bytion_RP(){
  $BS = Bytion_SuperClass::GetInstance();
  return $BS->getLibBySlug('RequestParser');
}

function Bytion_OM(){
  $BS = Bytion_SuperClass::GetInstance();
  return $BS->getLibBySlug('OrderManager');
}

function Bytion_Router($payload){
  return Bytion_RP()->Router($payload);
}