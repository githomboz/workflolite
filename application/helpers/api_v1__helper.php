<?php

function _api_template(){
  return array(
    'response' => null,
    'errors' => false
  );
}

function map_api_data($data, $map){
  $input = array();
  if(!empty($map)){
    foreach($map as $index => $field){
      if(isset($data[$index])) $input[$field] = $data[$index];
    }
  } else $input = $data;
  foreach($data as $k => $v) if(!is_numeric($k)) $input[$k] = $v;
  return $input;
}

function _api_process_args($args, $__FUNCTION__){
  /** Prepare Arguments based on passed, and parsed values **/
  $map = function_exists($__FUNCTION__.'_args_map') ? call_user_func($__FUNCTION__.'_args_map') : array();

  $direct_access = false;

  // Decide if this function is a get,url,or direct call
  if(is_array($args)
    && isset($args[0])
    && (is_array($args[0]) && (
        in_array('get', $args[0])
        || in_array('post', $args[0])
        ||  in_array('url', $args[0])))){
    // If get or url, return data using default methods
    $data = map_api_data((isset($args[1]) ? $args[1] : array()), $map);
  } else {
    // Else, if is direct, parse using secondary methods
    $data = map_api_data($args, $map);
    $direct_access = true;
  }
  /** End of Argument Prep */
  // Handle required fields when endpoint function is accessed directly
  if($direct_access){
    $required = function_exists($__FUNCTION__.'_required_fields') ? call_user_func($__FUNCTION__.'_required_fields') : array();
    foreach($required as $field){
      if(!isset($data[$field]) || (isset($data[$field]) && empty($data[$field]))) {
        $data['_errors'][] = strtoupper($field) . ' invalid or empty';
      }
    }
  }
  return $data;
}

function _api_parse_filter_string($string, $style = 1){
  $return = array();
  switch($style){
    case 2:
      $delimiters = array(
        'pairs' => '::::',
        'key_values' => '::',
        'array_values' => '.'
      );
      break;
    default:
      $delimiters = array(
        'pairs' => '::',
        'key_values' => ':',
        'array_values' => '.'
      );
      break;
  }
  $args = explode($delimiters['pairs'], $string);
  if(is_array($args)){
    foreach($args as $i => $arg){
      if(trim($arg) == '') unset($args[$i]); else $args[$i] = $arg = trim($arg);
      $kv = explode($delimiters['key_values'], $arg);
      if(count($kv) == 2){
        if(strpos($kv[1], $delimiters['array_values']) !== false){
          $return[$kv[0]] = explode('.',$kv[1]);
        } else {
          $return[$kv[0]] = $kv[1];
        }
      }
    }
  }
  return $return;
}

function _api_filter_records_for_fields($data, $fields){
  foreach($data as $i => $record){
    foreach($record as $field => $value){
      if(!in_array($field, $fields)) unset($data[$i][$field]);
    }
  }
  return $data;
}

function _api_parse_value_type($string){
  if(strpos($string, '|') !== false){
    list($type, $value) = explode('|', $string);
    switch($type){
      case 'int': return (int) $value;
        break;
      case 'bool': return (bool) $value;
        break;
      case '_id': return _id($value);
        break;
      default: return $value;
        break;
    }
  } else {
    return $string;
  }
}

function _process_date_fields_recursive($data, $level = 1, $depth = 10){
  if($level <= $depth){
    $level ++;
    if(is_array($data)){
      foreach($data as $k => $v){
        if($v instanceof MongoDate || (is_object($v) && (isset($v->sec) && isset($v->usec))) || (is_array($v) && isset($v['sec']) && isset($v['usec']))){
          if(is_array($v)) $v = (object) $v;
          $data[$k] = date('c', $v->sec);
        } elseif(is_array($v)){
          $data[$k] = _process_date_fields_recursive($v, $level, $depth);
        }
      }
    }
    return $data;
  }
}

function _process_id_fields_recursive($data, $level = 1, $depth = 10){
  if($level <= $depth){
    $level ++;
    if(is_array($data)){
      foreach($data as $k => $v){
        if($v instanceof MongoId){
          $data[$k] = (string) $v;
        } elseif(is_array($v)){
          $data[$k] = _process_id_fields_recursive($v, $level, $depth);
        }
      }
    }
    return $data;
  }
}