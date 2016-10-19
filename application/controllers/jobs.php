<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jobs extends Users_Controller {

  public function __construct()
  {
    parent::__construct();
    $this->load->model(array('organizations_model', 'tasks_model'));
    $this->load->helper('workflow');
  }

  public function single($jobId, $slug = 'tasks'){
    $this->navSelected = 'jobsInner';
    $innerNav = _get_inner_nav($this->navSelected);
    if(in_array($slug, _get_inner_nav_slugs($innerNav))){
      $this->preCollapseSidePanel = false;
      $this->job = Job::Get($jobId);
      if(isset($this->job) && $this->job){
        if($this->job->loadOrganization()->getValue('organization')->getValue('_id') == UserSession::value('organizationId')){
          $this->job->loadWorkflow();
          $this->workflow = $this->job->getValue('workflow');
          $successfullPosts = array();
          $AddTask = _process_add_task($this->input->post(), $this->job);
          if($AddTask['success']){
            $successfullPosts[] = $AddTask;
          }
          if(!empty($successfullPosts)){
            redirect(current_url().'?success');
          }
          $this->innerNavSelected = $slug;
          $this->view('jobs-' . $slug);
        } else {
          show_error('You are not authorized to view this page');
        }
      } else {
        show_404();
      }
    } else {
      show_404();
    }
  }

  public function dashboard(){
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'My Dashboard';
    $this->navSelected = 'dashboard';
    $this->view('dashboard-page');
	}

  public function workflows(){
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'Workflows';
    $this->navSelected = 'workflows';
    $this->view('workflows-page');
	}

  public function contacts(){
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'Organization Contacts';
    $this->navSelected = 'contacts';
    $this->view('contacts-page');
	}

  public function users(){
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'User Management';
    $this->navSelected = 'users';
    $this->view('users-page.php');
	}

  public function search(){
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'Advanced Search';
    $this->navSelected = 'search';
    $this->view('search-page');
	}

  public function archive(){
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'Current Jobs';
    $this->navSelected = 'jobs';
    $this->view('jobs-page');
  }

  public function index(){
    $this->archive();
	}

}