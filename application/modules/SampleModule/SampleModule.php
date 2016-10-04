<?php

namespace Modules;

class SampleModule extends \ModulesImplementation
{

  public static $module = __CLASS__;
  public static $namespace = __NAMESPACE__;
  public static $collection = null;

  public static $moduleFile = __FILE__;
  public static $moduleDir = __DIR__;

  protected static $meta = null;

  public function __construct($meta = null){
    parent::__construct($meta);
  }

  public function install(){
    $this->_initInstall();
    //echo 'just did stuff to install this module';
  }

  public function uninstall(){
    $this->_initUninstall();
    //echo 'just did stuff to uninstall this module';
  }

  public function reset(){
    //echo 'just reset data and files back to fresh install';
  }

}