<?php

class UserSession
{

  private static $CI;

  private static  $session_var = 'userLogged';

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

  public static function login(User $user){
    return $user->login();
  }

  public static function logout(){
  }

  public static function loggedIn(){
    return self::CI()->session->userdata(self::$session_var);
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

  public function end(){

  }

  private static function CI(){
    if(!self::$CI){
      self::$CI = get_instance();
    }
    return self::$CI;
  }

  public static function Get_User(){
    $userData = self::CI()->session->userdata(self::$session_var);
    return User::Get($userData['userId']);
  }

  public static function Get_Organization(){
    $userData = self::CI()->session->userdata(self::$session_var);
    return Organization::Get($userData['organizationId']);
  }

  public static function start($data){
    return self::CI()->session->set_userdata(array(self::$session_var => $data));
  }

  public static function value($key){
    $userData = self::CI()->session->userdata(self::$session_var);
    return $userData[$key];
  }

  public static function EncodePassword($password){
    CI()->load->helper('data_input');
    return di_encrypt_s($password, salt());
  }

  private function __clone(){}

  private function __wakeup(){}


}