<?php

if(!function_exists('_api_template')) require 'api_v1__helper.php';

// Accessible via http://www.appname.com/api/v1/documentation/func/arg1/arg2/arg3[...]
function func(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $CI =& get_instance();

  $response['response']['method'] = 'GET';
  $response['response']['function'] = str_replace(array('{',urlencode('{'),'}',urlencode('}')),'', $data['method']);
  $response['response']['resourceURI'] = site_url('api/v1/'.str_replace('::','/', $response['response']['function']));
  $response['recordCount'] = 1;

  if(strpos($data['method'], '::') !== false){
    list($group, $function) = explode("::", $data['method']);
  } else {
    $response['errors'][] = 'Invalid or prohibited method name passed as an argument';
  }

  if(empty($response['errors'])){
    $doc = array();
    $file = APPPATH.'helpers/docs/'.$group.'_docs.php';
    if(file_exists($file)) {
      require_once $file;
      if(isset($doc[$function])) {
        $response['response']['method'] = $doc[$function]['method'];
        $response['response']['description'] = $doc[$function]['description'];
        foreach($doc[$function]['methods'] as $methodType => $apidocs){
          if(isset($data['argumentDocs']) && !empty($data['argumentDocs'])) {
            $response['response'][$methodType]['arguments'] = $apidocs['arguments'];
            $example = isset($doc[$function]['example']) && $doc[$function]['example'];
            if($example && config_item('api_example_uri')) $response['response']['example_uri'] = $response['response']['resourceURI'];
            if($example && config_item('api_example_query') || $CI->input->get('example_query')) $response['response']['example_query'] = '';
            foreach($response['response'][$methodType]['arguments'] as $i => $argument) {
              if(empty($argument['options'])) unset($response['response'][$methodType]['arguments'][$i]['options']);
              if($example && (config_item('api_example_uri') || $CI->input->get('example_uri')) && isset($argument['example'])) $response['response']['example_uri'] .= '/'.$argument['example'];
              ;              if($example && (config_item('api_example_query') || $CI->input->get('example_query')) && isset($argument['example'])) $response['response']['example_query'] .= $i .'='.$argument['example'].'&';
              unset($response['response'][$methodType]['arguments'][$i]['example']);
            }
            if((config_item('api_example_query') || $CI->input->get('example_query')) && isset($response['response']['example_query'])) $response['response']['example_query'] = trim($response['response']['example_query'], '&');
          } else {
            $response['response'][$methodType]['argumentFields'] = array_keys($doc[$function]['arguments']);
          }
        }
        if(isset($data['extras']) && !empty($data['extras'])) {
          $common_docs = array();
          require_once 'docs/documentation.inc';
          $response['response'] = array_merge($response['response'], $common_docs);
        }
      } else {
        $response['response']['description'] = 'ERROR: Callback not found in documentation group file';
      }
    } else {
      $response['response']['description'] = 'ERROR: Callback group documentation file not found';
    }
  }
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function func_args_map(){
  return array(
    'method', // name of method you want to display
    'argumentDocs', // display documentation for each argument (0 or 1)
    'extras', // display common documentation extras like the type of $_GET params you can send to each call
  );
}

// Field names of fields required
function func_required_fields(){
  return array('method');
}