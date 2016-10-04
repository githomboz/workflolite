<?php

function member_area($override = 0){
  $CI =& get_instance();
  if($override) {
    return true;
  }
  return $CI->member_area;
}

function logged_in(){
  $CI =& get_instance();
  $CI->load->model('auth_model');
  return $CI->auth_model->user_logged_in();
}

function login($email, $password){
  $CI =& get_instance();
  $CI->load->model('auth_model');
  return $CI->auth_model->user_login($email, $password);
}

function logout(){
  $CI =& get_instance();
  $CI->load->model('auth_model');
  return $CI->auth_model->user_logout();
}

function user_data($field = null){
  $CI =& get_instance();
  $CI->load->model('auth_model');
  return $CI->auth_model->user_data($field);
}

function user_profiles(){
  $CI =& get_instance();
  $CI->load->model('auth_model');
  return $CI->auth_model->user_profiles();
}

function update_user_data($mixed, $value = NULL){
  $CI =& get_instance();
  $CI->load->model('auth_model');
  return $CI->auth_model->update_user_data($mixed, $value);
}

function is_admin($access = null){

}

function my_setting($key){
  $settings = user_data('settings');
  foreach($settings as $setting){
    if($setting['k'] == $key) return $setting['v'];
  }
  return null;
}

function admin_setting($key, $group = null, $return_all = false){
  $CI =& get_instance();
  $CI->load->model(array('admin_model'));
  return $CI->admin_model->get_setting($key, $group, !$return_all);
}

function set_my_settings($key, $value){
  $CI =& get_instance();
  $CI->load->model(array('users_model'));
  return $CI->users_model->set_settings(array($key => $value));
}

function set_admin_settings($key, $value, $group = null, $onload = false){
  $CI =& get_instance();
  $CI->load->model(array('admin_model'));
  return $CI->admin_model->set_setting($key, $value, $group, $onload);
}

function _is($key, $default_value = NULL, $save_if_not_set = false){
  $value = my_setting($key);
  if($value == null && $save_if_not_set) {
    set_my_settings($key, $default_value);
  }
  $value = my_setting($key);
  if($value != null) return $value;
  return $default_value;
}

function _admin_is($key, $group = NULL, $default_value = NULL, $onload = false, $save_if_not_set = false, $exires_in_seconds = false){
  $CI =& get_instance();
  $CI->load->model(array('admin_model'));
  return $CI->admin_model->_admin_is($key, $group, $default_value, $onload, $save_if_not_set, $exires_in_seconds);
}

function get_setting_value($field){
  $val = config_item($field);
  if($val) return $val;
  $val = _admin_is($field, null, $val);
  return $val;
}

function curl_exec_follow($ch, &$maxredirect = null) {

  // we emulate a browser here since some websites detect
  // us as a bot and don't let us do our job
  $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5)".
    " Gecko/20041107 Firefox/1.0";
  curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );

  $mr = $maxredirect === null ? 5 : intval($maxredirect);

  if (filter_var(ini_get('open_basedir'), FILTER_VALIDATE_BOOLEAN) === false
    && filter_var(ini_get('safe_mode'), FILTER_VALIDATE_BOOLEAN) === false
  ) {

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
    curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  } else {

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    if ($mr > 0)
    {
      $original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
      $newurl = $original_url;

      $rch = curl_copy_handle($ch);

      curl_setopt($rch, CURLOPT_HEADER, true);
      curl_setopt($rch, CURLOPT_NOBODY, true);
      curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
      do
      {
        curl_setopt($rch, CURLOPT_URL, $newurl);
        $header = curl_exec($rch);
        if (curl_errno($rch)) {
          $code = 0;
        } else {
          $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
          if ($code == 301 || $code == 302) {
            preg_match('/Location:(.*?)\n/i', $header, $matches);
            $newurl = trim(array_pop($matches));

            // if no scheme is present then the new url is a
            // relative path and thus needs some extra care
            if(!preg_match("/^https?:/i", $newurl)){
              $newurl = $original_url . $newurl;
            }
          } else {
            $code = 0;
          }
        }
      } while ($code && --$mr);

      curl_close($rch);

      if (!$mr)
      {
        if ($maxredirect === null)
          trigger_error('Too many redirects.', E_USER_WARNING);
        else
          $maxredirect = 0;

        return false;
      }
      curl_setopt($ch, CURLOPT_URL, $newurl);
    }
  }
  return curl_exec($ch);
}

function admin_logged_in(){
  $CI =& get_instance();
  $CI->load->model('admin_model');
  return $CI->admin_model->user_logged_in();
}

function admin_login($email, $password){
  $CI =& get_instance();
  $CI->load->model('admin_model');
  return $CI->admin_model->user_login($email, $password);
}

function admin_logout(){
  $CI =& get_instance();
  $CI->load->model('admin_model');
  return $CI->admin_model->user_logout();
}

function admin_data($field = null){
  $CI =& get_instance();
  $CI->load->model('admin_model');
  return $CI->admin_model->user_data($field);
}
