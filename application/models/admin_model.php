<?php

require_once 'main_model.php';

class Admin_model extends Main_model
{

  public static $collection = 'admins';
  public $session_var = 'adminLogged';

  public function __construct() {
    parent::__construct();
  }

    public function user_login($email, $password){
    $data = $this->authenticate_user($email, $password);
    return $this->start_user_session($data);
  }

  public function user_logout(){
    return $this->session->unset_userdata($this->session_var);
  }

  public function user_logged_in(){
    return $this->session->userdata($this->session_var);
  }

  public function user_data($field = null){
    $data = $this->session->userdata($this->session_var);
    if($field && isset($data[$field])) return $data[$field];
    return $data;
  }

  public function authenticate_user($email, $password){
    return $this->mdb->select(array('_id','access','fName','lName'))->where(array('email'=>$email, 'password'=>md5($password), 'status'=>'active'))->get(static::$collection);
  }

  public function start_user_session($data){
    return $this->session->set_userdata(array($this->session_var => $data));
  }

  public function get_setting($key, $group = NULL, $only_return_value = true){
    $handler = $this->mdb->where('key', $key);
    if($group) $handler->where('group', $group);
    $data = $handler->get('settings');
    if(isset($data[0])){
      if($only_return_value) {
        if(isset($data[0]['value'])) return $data[0]['value'];
      } else {
        return $data[0];
      }
    }
    return null;
  }

  public function get_all_settings($group = NULL){
    $handler = $this->mdb->select(array(), array('_id'));
    if($group) $handler->where('group', $group);
    $data = $handler->get('settings');
    return $data;
  }

  public function set_setting($key, $value, $group = NULL, $onload = false, $expires_in_seconds = false){
    $currentVal = $this->get_setting($key, $group, false);
    $data = array('key' => $key, 'value' => $value, 'onload' => $onload);
    if($expires_in_seconds && is_numeric($expires_in_seconds)) {
      $data['dateAdded'] = new MongoDate();
      $data['expires'] = (int)$expires_in_seconds;
    }
    if($group) $data['group'] = $group;
    if($currentVal === null){
      // create
      $filtered = di_allowed_only($data, mongo_get_allowed('settings'));
      return $this->mdb->insert('settings', $filtered);
    } else {
      // update
      return $this->mdb->where('_id', $currentVal['_id'])->set($data)->update('settings');
    }
  }

  public function _admin_is($key, $group = NULL, $default_value = NULL, $onload = false, $save_if_not_set = false, $expires = false){
    $id = NULL;
    $val = $this->get_setting($key, $group, false);
    $value = isset($val['value']) ? $val['value'] : NULL;
    if($value === null && $save_if_not_set) {
      $this->set_setting($key, $default_value, $group, $onload, $expires);
    }
    $value = $this->get_setting($key);
    if($value !== null) return $value;
    return $default_value;
  }

}