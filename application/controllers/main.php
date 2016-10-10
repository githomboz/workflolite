<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends Front_Controller {

  public function login(){
    //var_dump(User::Authenticate('jahdy@spotflare.com','jahdy'));
    $redirect = '/';
    $redirect_override = $this->input->get('redirect');
    if(!logged_in()){
      // If form submitted
      $this->load->helper('api');
      $result = process_login();
      $this->page_header = array(
        'icon' => 'fa-sign-in',
        'header' => 'Login / Sign Up',
      );
      $tests = array('unprocessed' => is_null($result), 'success' => $result === true, 'failure' => $result === false);
      $data = array('redirect_url' => current_url());
      switch(true){
        case $tests['unprocessed']:
          break;
        case $tests['success']:
          if($redirect_override){
            redirect($redirect_override);
          } else {
            redirect($redirect);
          }
          break;
        case $tests['failure']:
          break;
      }
      $this->view('login', $data);
    } else {
      if($redirect_override){
        redirect($redirect_override);
      } else {
        redirect($redirect);
      }
    }
  }

  public function logout(){
    if(logged_in()) logout();
    redirect('/');
  }

  public function admin_login(){
    if(!admin_logged_in()){
      // If form submitted
      if($action = $this->input->post('form-action')){
        $this->load->helper('data_input');
        $input = $this->input->post();
        switch($action){
          case 'login-form-submitted':
            if(!empty($input['email']) && !empty($input['password'])){
              $this->load->model('admin_model');
              $response = $this->admin_model->authenticate_user($input['email'], $input['password']);
              if(!empty($response)){
                // Log user in
                //var_dump($response);
                $this->admin_model->start_user_session($response[0]);
                // Redirect to admin section
                redirect('admin');
              } else {
                // Handle Error (invalid email / password combination)
                echo 'invalid email/password combination';
              }
            } else {
              // Handle error
              echo 'email and password fields must both be set';
            }
            break;
          case 'signup-form-submitted':
            break;
        }
      }
      $this->view('admin/login');
    } else redirect('admin');
  }

  public function admin_logout(){
    if(admin_logged_in()) admin_logout();
    redirect('admin');
  }


  public function index(){
    redirect('dashboard');
  }

}