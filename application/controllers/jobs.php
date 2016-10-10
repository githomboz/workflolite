<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jobs extends Users_Controller {

  public function __construct()
  {
    parent::__construct();
    $this->load->model(array('organizations_model', 'tasks_model'));
    $this->load->helper('workflow');
  }

  public function single($jobId, $slug = 'tasks'){
    $this->preCollapseSidePanel = false;
    $this->navSelected = 'jobs';
    $this->job = Job::Get($jobId);
    if($this->job){
      if($this->job->loadOrganization()->getValue('organization')->getValue('_id') == UserSession::value('organizationId')){
        $this->job->loadWorkflow();
        $successfullPosts = array();
        $AddTask = _process_add_task($this->input->post(), $this->job);
        if($AddTask['success']){
          $successfullPosts[] = $AddTask;
        }
        if(!empty($successfullPosts)){
          redirect(current_url().'?success');
        }
        $this->page = $slug;
        $this->view($slug);
      } else {
        show_error('You are not authorized to view this page');
      }
    } else {
      show_404();
    }
  }

  public function dashboard(){
    $this->preCollapseSidePanel = true;
    $this->navSelected = 'dashboard';
    $this->view('dashboard');
	}

  public function archive(){
    $this->preCollapseSidePanel = true;
    $this->navSelected = 'jobs';
    $this->view('archive');
  }

  public function index(){
    $this->archive();
	}

}