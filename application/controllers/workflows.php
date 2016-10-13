<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Workflows extends Users_Controller {

  public function __construct()
  {
    parent::__construct();
    $this->load->model(array('organizations_model', 'tasks_model'));
    $this->load->helper('workflow');
  }

  public function single($id, $slug = 'overview'){
    $innerNav = _get_inner_nav('workflows');
    if(in_array($slug, _get_inner_nav_slugs($innerNav))){
      if($slug == 'jobs'){
        $this->navSelected = 'jobs';
        $file_prefix = 'workflows';
      } else {
        $this->navSelected = 'workflows';
        $file_prefix = $this->navSelected;
      }
      $this->preCollapseSidePanel = true;
      $this->workflow = Workflow::Get($id);
      if(isset($this->workflow) && $this->workflow){

          $this->innerNavSelected = $slug;
          $this->view($file_prefix . '-' . $slug);
      } else {
        show_404();
      }
    } else {
      show_404();
    }
  }

  public function details($workflowId){
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'Workflow Template Details';
    $this->navSelected = 'workflows';
    $this->innerNavSelected = 'details';
    $this->workflow = Workflow::Get($workflowId);
    if(isset($this->workflow) && $this->workflow){
      $this->view($this->navSelected . '-details');
    } else {
      show_404();
    }
  }

  public function archive(){
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'Workflow Management';
    $this->navSelected = 'workflows';
    $this->innerNavSelected = 'overview';
    $this->view($this->navSelected . '-overview');

  }

  public function reports(){
    $this->preCollapseSidePanel = true;
    $this->navSelected = 'workflows';
    $this->innerNavSelected = 'reports';
    $this->view($this->navSelected . '-reports');
  }

  public function index(){
    $this->archive();
  }

}