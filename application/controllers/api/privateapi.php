<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'rest.php';

class PrivateAPI extends REST {

  protected $public_access = false;

  public function __construct(){
    parent::__construct();
  }

  public function _copy_from_template(){
    $errors = false;
    $response = array();
    $post = $this->input->post();
    foreach(array('field1','field2') as $x)
      if(!isset($post[$x]) || (isset($post[$x]) && empty($post[$x]))) $errors[] = 'Error# 11001: '.strtoupper($x) . ' invalid or empty';

    if(empty($errors)){

    }
    $this->_push($this->_template($response, $errors, 200, true));
  }

}