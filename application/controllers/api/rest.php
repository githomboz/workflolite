<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class REST extends Base_Controller {

  protected $public_access = false;

  protected $authorized = false;

  public function __construct(){
    parent::__construct();
    $this->output->set_content_type('application/json');
  }

  public function _template(
    $response = NULL,
    $errors = false,
    $status = 200,
    $status_override = false,
    $record_count = null,
    $elapsed = null,
    $docs = false,
    $query = null,
    $args = array()
  ){
    if($this->authorized){
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
    }
    $passed_status = $status;
    $status = ($errors && $status == 200) ? 400 : $status;
    if($status_override) $status = $passed_status;
    $data = array(
      'status' => $status,
      'recordCount' => !is_null($record_count) ? (int) $record_count : (is_array($response) ? count($response) : NULL),
      'response' => $response,
      'errors' => $errors,
    );
    if($docs) $data['documentation'] = $docs;
    if((config_item('api_benchmarking') || $this->input->get('benchmark')) && $elapsed) $data['elapsedTime'] = $elapsed;
    if(config_item('api_show_args') || $this->input->get('show_args')) $data['args'] = $args;
    if((config_item('api_show_query') || $this->input->get('show_query')) && $query) {
      $data['query'] = $query;
      if(ENVIRONMENT == 'production' && isset($data['query']['collection'])) $data['query']['collection'] = 'PROD_REDACTED';
    }
    return $data;
  }

  public function _push($response, $return = false){
    $this->output->set_status_header($response['status']);
    if(is_null($response['recordCount'])) unset($response['recordCount']);
    if($return) return $response; else {
      $output = '';
      // Specify a callback field name
      $callbackString = $this->input->get('callbackString');
      $checkCallback = !empty($callbackString) ? $callbackString : 'callback';

      // Check for existance of the aforementioned callback field name
      $callback = $this->input->get($checkCallback);
      if(!empty($callback)) $output .= $callback.'(';
      $output .= json_encode($response);
      if(!empty($callback)) $output .= ');';
      $this->output->set_output($output);
    }
  }
}