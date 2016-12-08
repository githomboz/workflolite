<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Templates extends Users_Controller {

  public function __construct()
  {
    parent::__construct();
    $this->load->model(array('organizations_model', 'tasks_model'));
    $this->load->helper('workflow');
  }

  public function single($id, $slug = 'overview'){
    $innerNav = _get_inner_nav('templates');
    if(in_array($slug, _get_inner_nav_slugs($innerNav))){
      if($slug == 'projects'){
        $this->navSelected = 'projects';
        $file_prefix = 'templates';
      } else {
        $this->navSelected = 'templates';
        $file_prefix = $this->navSelected;
      }
      $this->preCollapseSidePanel = true;
      $this->template = Template::Get($id);
      if(isset($this->template) && $this->template){

          $this->innerNavSelected = $slug;
          $this->view($file_prefix . '-' . $slug);
      } else {
        show_404();
      }
    } else {
      show_404();
    }
  }

  public function details($templateId){
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'Template Details';
    $this->navSelected = 'templates';
    $this->innerNavSelected = 'details';
    $this->template = Template::Get($templateId);
    $this->version = is_numeric($this->input->get('ver')) ? (int)$this->input->get('ver') : $this->template->version();
    $validVersion = false;
    if($this->version <= $this->template->version() + 1){
      $validVersion = true;
    }
    $this->template = $this->template->setVersion($this->version);
    if(isset($this->template) && $this->template && $validVersion ){
      $this->view($this->navSelected . '-details');
    } else {
      show_404();
    }
  }

  public function archive(){
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'Template Management';
    $this->navSelected = 'templates';
    $this->innerNavSelected = 'overview';
    $this->view($this->navSelected . '-overview');

  }

  public function reports(){
    $this->preCollapseSidePanel = true;
    $this->navSelected = 'templates';
    $this->innerNavSelected = 'reports';
    $this->view($this->navSelected . '-reports');
  }

  public function create_project(){
    if($form_submitted = _process_create_project($this->input->post())){
      if($form_submitted['success'] && $form_submitted['success']['projectId'] instanceof MongoId){
        redirect(site_url('projects/create?created='.$form_submitted['success']['projectId'].'&name='.$form_submitted['success']['name']));
      }
    }
    $this->preCollapseSidePanel = true;
    $this->pageTitle = 'Start a Project';
    $this->navSelected = 'projects';
    $this->innerNavSelected = 'overview';
    $this->view($this->navSelected . '-create');
  }


  public function index(){
    $this->archive();
  }

}