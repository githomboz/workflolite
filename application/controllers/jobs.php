<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jobs extends Front_Controller {

  public function __construct()
  {
    parent::__construct();
    $this->load->model(array('organizations_model', 'tasks_model'));
    //$this->load->helper('workflow');
  }

  public function task($organizationId, $taskId){
    $data = array();
//    $task = new Task($this->tasks_model->get($taskId), true);
//    var_dump($task->getValue('organizationId'), _id($organizationId));
//    if((string) $task->getValue('organizationId') == $organizationId){
//      $data['task'] = $task;
//    }
    $this->view('task-single', $data);
  }

  public function index(){
    $this->view('home');
	}

}