<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Projects extends Users_Controller {

  public function __construct()
  {
    parent::__construct();
    $this->load->model(array('organizations_model', 'tasks_model'));
    $this->load->helper('workflow');
  }

  public function single($projectId, $slug = 'tasks'){
    $this->navSelected = 'projectsInner';
    $innerNav = _get_inner_nav($this->navSelected);
    if(in_array($slug, _get_inner_nav_slugs($innerNav))){
      $this->preCollapseSidePanel = false;
      $this->project = Project::Get($projectId);
      if(isset($this->project) && $this->project){
        if($this->project->loadOrganization()->getValue('organization')->getValue('_id') == UserSession::value('organizationId')){
          $this->project->loadTemplate();
          if($slug == 'tasks') load_before_content(get_include(APPPATH.'/views/widgets/_binded-trigger-box.php'));
          $this->template = $this->project->getValue('template');
          $successfullPosts = array();
          $AddTask = _process_add_task($this->input->post(), $this->project);
          if($AddTask['success']){
            $successfullPosts[] = $AddTask;
          }
          if(!empty($successfullPosts)){
            redirect(current_url().'?success');
          }
          $this->innerNavSelected = $slug;
          $this->view('projects-' . $slug);
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

  public function templates(){
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'Templates';
    $this->navSelected = 'templates';
    $this->view('templates-page');
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
    $this->pageTitle = 'Current Projects';
    $this->navSelected = 'projects';
    $this->view('projects-page');
  }

  public function index(){
    $this->archive();
	}

}