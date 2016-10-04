<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'rest.php';

class V1 extends REST {

  protected $public_access = true;

  public function __construct(){
    parent::__construct();
    $this->load->model('admin_model');
  }

  public function call_method(){
    $errors = false;
    $response = array();
    $status = 200;
    $recordCount = null;
    $callback = null;
    $query = null;

    $benchmarking = config_item('api_benchmarking');
    if($benchmarking) $this->benchmark->mark('api_call_start');
    if(empty($errors)){
      $args = func_get_args();
      $callbackGroup = isset($args[0]) && !empty($args[0]) ? $args[0] : null;
      if(!empty($args) && $callbackGroup && isset($args[1])){
        // Check if helper file exists
        if(file_exists(APPPATH.'helpers/api_v1_'.$callbackGroup.'_helper.php')){
          // Load helper
          $this->load->helper(array('api_v1_','api_v1_'.$callbackGroup));
          // Check if function exists
          $callback = $args[1];
          if(function_exists($callback)){
            $required_fields = function_exists($callback.'_required_fields') ? (array) call_user_func($callback.'_required_fields') : array();

            // Unset first two indexes of args array then reindex the array
            unset($args[0]); unset($args[1]);
            $args = array_values($args);

            // Method type
            $api_actions = array('get');
            if(!empty($args)) $api_actions[] = 'url';

            // Check if in debug mode
            $this->debug_mode = $this->input->get('debug');

            // Normalize the input data into key => value pairs instead of 0 index array for $_GETs
            $post = $this->input->post();
            if($post) {
              // If post, $define $args as $this->input->post();
              $args = $post;
              unset($api_actions[array_search('get', $api_actions)]);
              $api_actions[] = 'post';
            } else {
              // If get, check for function map and map vars
              if(function_exists($callback.'_args_map')){
                $map = (array) call_user_func($callback.'_args_map');
                $args = map_api_data($args, $map);
              }
            }

            // Make sure required are set
            foreach($required_fields as $x)
              if(!isset($args[$x]) || (isset($args[$x]) && empty($args[$x]))) $errors[] = strtoupper($x) . ' invalid or empty';

            if(empty($errors)){
              // Call function function
              $result = call_user_func_array($callback, array($api_actions, $args));
              $args = array();
              $response = $result['response'];

              if($response != null){
                // Transform date fields uniform
                $response = _process_date_fields_recursive($response);

                // Transform id fields uniform
                $response = _process_id_fields_recursive($response);
              }

              // Reconsile results array with response
              if(!empty($result['errors'])) {
                if(is_array($errors)) $errors = array_merge($errors, $result['errors']);
                else $errors = $result['errors'];
              }
              if(isset($result['status'])) $status = $result['status'];
              if(isset($result['recordCount'])) $recordCount = $result['recordCount'];
              if(isset($result['query'])) $query = $result['query'];
              if(isset($result['args'])) $args = $result['args'];
            }
          } else $errors[] = 'API callback does not exist';
        } else $errors[] = 'API Group file does not exist';
      } else {
        // Handle error and display endpoints if they exists in docs file
        $errors[] = 'Arguments array empty or group/callback not set';
        $file = APPPATH.'/helpers/docs/'.$callbackGroup.'_docs.php';
        if(file_exists($file)){
          $doc = null;
          include_once $file;
          $return = array();
          if($doc){
            foreach($doc as $endpoint => $details){
              $return[$endpoint] = array(
                'endpoint' => site_url('api/v1/'.$callbackGroup.'/'.$endpoint),
                'docs' => site_url('api/v1/documentation/func/'.$callbackGroup.'::'.$endpoint.'/1/1'),
                'description' => $details['description']
              );
            }
            $response = $return;
          }
        }
      }
    }

    // errors, send back documentation link
    $docs = false;
    if((!empty($errors) || $this->input->get('docs')) && $callback){
      $file = APPPATH.'/helpers/docs/'.$callbackGroup.'_docs.php';
      if(file_exists($file)) {
        $doc = null;
        include_once $file;
      }
      if(isset($doc) && isset($doc[$callback])) {
        $docs = site_url('api/v1/documentation/func/'.$callbackGroup.'::'.$callback.'/1/1');
      } else {
        $docs = 'Docs are unavailable for this endpoint';
      }
    }

    // handle benchmarking
    $elapsed = null;
    if($benchmarking){
      $this->benchmark->mark('api_call_end');
      $elapsed = $this->benchmark->elapsed_time('api_call_start', 'api_call_end');
    }
    // Output responses
    $this->_push($this->_template($response, $errors, $status, true, $recordCount, $elapsed, $docs, $query, $args));
  }

}