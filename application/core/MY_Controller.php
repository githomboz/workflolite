<?php

/**
 * The base controller which is used by the Front and the Admin controllers
 */


class Base_Controller extends CI_Controller
{
  public $member_area = false;

  public function __construct(){
    parent::__construct();
    date_default_timezone_set('America/New_York');
    $this->memoryStore = array();
    if(config_item('utilize_mongo_db')) $this->mdb = get_mongo_instance();
    $this->load->library('session');
    $this->load->helper(array('url', 'views', 'auth','admin'));
    $this->load->model('admin_model');
    $this->debug_mode = config_item('site_debug_mode');
    $this->user = user_data();
    $nav = config_item('navigation');
    $this->members_nav = isset($nav['members']) ? $nav['members'] : array();
    $this->admin_nav = isset($nav['admin']) ? $nav['admin'] : array();
  }

  /*
  This works exactly like the regular $this->load->view()
  The difference is it automatically pulls in a header and footer.
  */
  public function view($view, $vars = array(), $string=false)
  {
    if($string)
    {
      $result	 = $this->load->view('header', $vars, true);
      $result	.= $this->load->view($view, $vars, true);
      $result	.= $this->load->view('footer', $vars, true);

      return $result;
    }
    else
    {
      $this->load->view('header', $vars);
      $this->load->view($view, $vars);
      $this->load->view('footer', $vars);
    }
  }

  /*
  This function simple calls $this->load->view()
  */
  public function partial($view, $vars = array(), $string=false)
  {
    if($string)
    {
      return $this->load->view($view, $vars, true);
    }
    else
    {
      $this->load->view($view, $vars);
    }
  }

}

class Front_Controller extends Base_Controller
{

  public function __construct(){
    parent::__construct();
  }

}

class Members_Controller extends Base_Controller
{

  public $member_area = true;

  public function __construct()
  {
    parent::__construct();
  }

}

class Admin_Controller extends Base_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->load->helper('admin');
    if(!admin_logged_in()) redirect('admin/login');
  }

}