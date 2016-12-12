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
      $this->version = is_numeric($this->input->get('ver')) ? (int)$this->input->get('ver') : null;
      $this->template = Template::cacheGet($id, $this->version);
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
    $this->version = is_numeric($this->input->get('ver')) ? (int)$this->input->get('ver') : null;
    $this->versions = array();

    $this->template = Template::cacheGet($templateId, $this->version);

    $this->versions['db'] = template()->getValue('version');
    $this->versions['save'] = is_numeric($this->version) ? $this->version : $this->versions['db'];
    $this->versions['highest'] = $this->versions['db'] + 1;

    $processUpdateTaskForm = $this->_processUpdateTaskForm();
    $processAddRoleForm = $this->_processAddRoleForm();

    $validVersion = false;
    if($this->version <= $this->template->version() + 1){
      $validVersion = true;
    }
    if(isset($this->template) && $this->template && $validVersion ){
      $this->view($this->navSelected . '-details');
    } else {
      show_404();
    }
  }

  private function _processUpdateTaskForm(){
    $formErrors = array();
    $formData = null;
    $success = false;
    $submitted = false;
    if($post = $this->input->post()){
      if(isset($post['formAction']) && $post['formAction'] == 'updateTaskTemplate'){
        $submitted = true;
        if(isset($post['taskTemplateId']) && !empty($post['taskTemplateId'])){
          $taskTemplate = template()->getTaskTemplate($post['taskTemplateId']);

          // Process update
          $formData = json_decode($post['formData'], true);
          $updatedData = template()->getTaskTemplateDiff($formData);
          $this->messageBox = array(
            'class' => 'general',
            'content' => null
          );
          if(!empty($updatedData)){
            // Data updated
            $updates = array(
              'taskTemplateChanges' => array(
                (string) $taskTemplate->id() => $updatedData
              )
            );
            template()->applyUpdates($updates, $this->versions['save']);
            $this->messageBox['taskTemplateId'] = (string) $taskTemplate->id();
            $this->messageBox['class'] .= ' success';
            $this->messageBox['content'] = 'The following field(s) were updated successfully ['.join(', ', array_keys($updatedData)).']';
            $success = true;
          }
//          else {
//            // No data to update
//            $this->messageBox['class'] .= ' error';
//            $this->messageBox['content'] = 'No valid updates found for this task';
//          }

        } else {
          $formErrors[] = 'No task template found';
        }
      }
    }
    return array(
      'submitted' => $submitted,
      'errors' => $formErrors,
      'formData' => $formData,
      'success' => $success
    );
  }

  private function _processAddRoleForm(){
    $formErrors = array();
    $formData = null;
    $success = false;
    $submitted = false;
    if($post = $this->input->post()){
      if(isset($post['formAction']) && $post['formAction'] == 'addRole'){
        $submitted = true;
        if(isset($post['role']) && !empty($post['role'])){
          $success = template()->addRole($post['role']);
        } else {
          $formErrors[] = 'No role has been input';
        }
      }
    }
    unset($post['formAction']);
    return array(
      'submitted' => $submitted,
      'errors' => $formErrors,
      'formData' => $post,
      'success' => $success
    );
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