<?php

require_once 'main_model.php';

class Auth_model extends Main_model
{

  public static $collection = 'users';
  public $session_var = 'userLogged';

  public function __construct() {
    parent::__construct();
  }

  public function auth_by_vars($vars){
    $data = $this->mdb->where(array('email'=>$vars['email'], 'password'=>md5($vars['password']), 'status'=>'active'))->get(static::$collection);

    if(!empty($data)){
      return $data[0];
    }
    return;
  }

  public function auth_by_email($email){
    $data = $this->mdb->where(array('email'=>$email, 'status'=>'active'))->get(static::$collection);

    if(!empty($data)){
      return $data[0];
    }
    return;
  }

  public function user_login($email, $password, $shadow_mode = false){
    $data = $this->authenticate_user($email, $password);
    if($shadow_mode && !empty($data)) $data['shadow_mode'] = true;
    if(!empty($data)) return $this->start_user_session($data);
    return false;
  }

  public function user_shadow_login($userId){
    $data = $this->authenticate_user_by_id($userId);
    if(!empty($data)) $data['shadow_mode'] = true;
    if(!empty($data)) return $this->start_user_session($data);
    return false;

  }

  public function user_logout(){
    return $this->session->unset_userdata($this->session_var);
  }

  public function user_logged_in(){
    return $this->session->userdata($this->session_var);
  }

  public function update_user_data($mixed, $value = NULL){
    $updated = false;
    if(is_array($mixed) && $this->user_logged_in()){
      $data = array_merge($this->user_data(), $mixed);
      $updated = true;
    } elseif(!empty($mixed) && is_string($mixed) && isset($value)){
      $data = user_data();
      switch($mixed){
        case 'settings':
          $this->load->model('users_model');
          $data[$mixed] = Users_model::settings_format($value);
          break;
        default:
          $data[$mixed] = $value;
        break;
      }
      $updated = true;
    }
    if($updated) return $this->session->set_userdata(array($this->session_var => $data));
  }

  public function authenticate_user($email, $password){
    $result = $this->mdb->where(array('email'=>$email, 'password'=>md5($password), 'status'=>'active'))->get(static::$collection);
    return isset($result[0]) ? $result[0] : false;
  }

  public function authenticate_user_by_id($userId){
    if(!$userId instanceof MongoId && (is_string($userId) && strlen($userId) == 24)) $userId = new MongoId($userId);
    $result = $this->mdb->where(array('_id'=>$userId, 'status'=>'active'))->get(static::$collection);
    return isset($result[0]) ? $result[0] : false;
  }

  public function user_data($field = null){
    $data = $this->session->userdata($this->session_var);
    if($field){
      if(isset($data[$field])) return $data[$field]; else return false;
    }
    return $data;
  }

  public function start_user_session($data){
    return $this->session->set_userdata(array($this->session_var => $data));
  }

}