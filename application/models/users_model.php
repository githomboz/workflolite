<?php

require_once 'main_model.php';

class Users_model extends Main_model
{

  public static $collection = 'users';

  public function __construct() {
    parent::__construct();
  }

  public function create($data){
    // @todo Verify that the email is unique
    $filtered = di_allowed_only($data, mongo_get_allowed(static::$collection));
    return $this->mdb->insert(static::$collection, $filtered);
  }

  public function exists($email){
    return $this->mdb->where('email', $email)->get(static::$collection);
  }

  public function email_belongs($email, $id){
    return $this->mdb->where(array('email'=> $email,'_id'=>_id($id)))->count(static::$collection);
  }

  public function get_all(){
    return $this->_get();
  }

  public function get_all_count(){
    return $this->_count();
  }

  public function get_settings($find = NULL, array $source = NULL){
    $this->load->helper('auth_helper');
    if(!$source) $settings = user_data('settings'); else $settings = $source;
    $return = false;
    foreach($settings as $i => $setting){
      if($find && $setting['k'] == $find) {
        return $setting['v'];
      }
      if(isset($setting['k'])) $return[$setting['k']] = $setting['v'];
      $i ++;
    }
    if(!empty($return) && !empty($settings)) return $return;
    if(!empty($setting) && empty($return)) return $settings;
  }

  public function set_settings($settings){
    $current_settings = $this->get_settings();
    //var_dump($current_settings, $settings);
    $save = self::settings_format(array_merge((array)$current_settings, (array)$settings));
    // Update Session
    $this->auth_model->update_user_data('settings', $save);
    // Update DB
    $this->update($this->user['userId'], array('settings'=>$save));
    return $save;
  }

  public static function settings_format(array $array){
    $save = array();
    $i = 0;
    foreach($array as $k  => $v){
      if(isset($array[$i]['k'])){
        $save[$i] = $array[$i];
      } else {
        $save[$i] = array(
          'k' => $k,
          'v' => $v
        );
      }
      $i ++;
    }
    return $save;
  }

}