<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'rest.php';

class Cron extends REST {

  public static $disabled_message = 'Cron Disabled';

  public function __construct(){
    parent::__construct();
    $this->load->helper('url');
    $this->load->model('actions_model');
  }

  public function _copy_from_template(){
    if(_admin_is('cron_enable_'.__METHOD__, 'system', true, false, true) && !_admin_is('cron_disable_all', 'system', false, false, true)){
      $errors = false;
      $response = array();

      $this->actions_model->create(array('type'=>'system','typeId'=>'cron','value'=>__METHOD__,'meta'=>null,'userId'=>'sysUser'));
      $this->_push($this->_template($response, $errors, 200, true));
    } else {
      $this->_disabled_response(__METHOD__);
    }
  }

}