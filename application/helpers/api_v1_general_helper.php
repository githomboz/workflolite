<?php

if(!function_exists('_api_template')) require 'api_v1__helper.php';

// Accessible via http://www.appname.com/api/v1/general/details/arg1/arg2/arg3[...]
function demo(){
  $response = _api_template();
  $args = func_get_args();
  $data = _api_process_args($args, __FUNCTION__);
  if(isset($data['_errors']) && is_array($data['_errors'])) $response['errors'] = $data['_errors'];

  $response['response']['args'] = $data;
  $response['response']['actions'] = array();
  $pigs = array(
    'Pig 1 made his house out of straw',
    'Pig 2 made his house out of sticks',
    'Pig 3 made his house out of stone'
  );
  if(isset($data['onlySmartPig']) && $data['onlySmartPig']) $response['response']['actions'] = $pigs[2];
  else {
    switch($data['littlePigs']){
      case 'first':
        $response['response']['actions'] = $pigs[0];
        break;
      case 'second':
        $response['response']['actions'] = $pigs[1];
        break;
      case 'third':
        $response['response']['actions'] = $pigs[2];
        break;
      default:
        $response['errors'][] = 'One of the following values must be passed: [first,second,third]';
        break;
    }
  }
  $response['recordCount'] = 1;
  return $response;
}

// Required to show name and order of arguments when using /arg1/arg2/arg3 $_GET format
function demo_args_map(){
  return array('littlePigs','onlySmartPig');
}

// Field names of fields required
function demo_required_fields(){
  return array('littlePigs');
}