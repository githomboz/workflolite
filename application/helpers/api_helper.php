<?php

function process_login(){
  $CI =& get_instance();
  $CI->load->helper('data_input');
  $input = $CI->input->post();
  if(isset($input['email']) || isset($input['password'])){
    $CI->load->model('auth_model');
    //$response = $CI->auth_model->authenticate_user($input['email'], $input['password']);
    $user = User::Authenticate($input['email'], md5($input['password']));
    //var_dump($response);
    if(!empty($user)){
      // Log user in
      //var_dump($response);
      //$CI->auth_model->start_user_session($response);
      $user->login();
      return true; // if login was successful
    }
    return false; // if login was attempted and failed
  }
  return null; // if login was not attempted
}

function process_admin_login(){
  $CI =& get_instance();
  $CI->load->helper('data_input');
  $input = $CI->input->post();
  if(!empty($input['email']) && !empty($input['password'])){
    $CI->load->model('admin_model');
    $response = $CI->admin_model->authenticate_user($input['email'], $input['password']);
    if(!empty($response)){
      // Log user in
      //var_dump($response);
      $CI->admin_model->start_user_session($response);
      return true; // if login was successful
    }
    return false; // if login was attempted and failed
  }
  return null; // if login was not attempted
}

