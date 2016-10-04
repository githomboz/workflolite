<?php

class UserSession
{

  private static $instance = null;

  private $_data = array();

  protected function __construct(){
  }

  public static function getInstance(){
    if(null === static::$instance){
      static::$instance = new UserSession();
    }
    return static::$instance;
  }

  public function login(User $user){

  }

  public function logout(User $user = null){

  }

  public function set($key, $value, $group = null){
    if(is_array($key)){
      if(is_string($group)){
        $this->_data = array_merge($this->_data, array($group => $key));
      } else {
        $this->_data = array_merge($this->_data, $key);
      }
    } else {
      if(is_string($key)){
        if(is_string($group)){
          if(!isset($this->_data[$group])) $this->_data[$group] = array();
          $this->_data[$group][$key] = $value;
        } else {
          $this->_data[$key] = $value;
        }
      }
    }
    return $this;
  }

  public function save(){

  }

  private function __clone(){}

  private function __wakeup(){}


}