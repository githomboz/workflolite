<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jahdy
 * Date: 6/26/14
 * Time: 12:04 PM
 * To change this template use File | Settings | File Templates.
 */

function mongo_schema_map($collection){
  $CI =& get_instance();
  if(!isset($CI->memoryStore['mongo_map'])) $CI->memoryStore['mongo_map'] = array();
  if(isset($CI->memoryStore['mongo_map'][$collection])) return $CI->memoryStore['mongo_map'][$collection];
  $maps = config_item('mongo_schema');
  $map = isset($maps[$collection]) ? $maps[$collection] : false;
  if($map){
    // Add universal fields
    $map[] = 'id';
    $map[] = '_id';
    // Normalize data
    $fields = array('required','options','format');
    foreach($map as $k => $v){
      if(is_numeric($k)){
        unset($map[$k]);
        $map[$v] = array('req'=>0);
      }
    }

    foreach($map as $k => $v){
      if(isset($map[$k]['req'])){
        $map[$k]['required'] = (bool)$map[$k]['req'];
        unset($map[$k]['req']);
      } else {
        $map[$k]['required'] = false;
      }

      if(isset($map[$k]['opts'])){
        $map[$k]['options'] = $map[$k]['opts'];
        unset($map[$k]['opts']);
      } else {
        $map[$k]['options'] = null;
      }
      if(!isset($map[$k]['format'])){
        $map[$k]['format'] = null;
      }
    }
  }

  if(!isset($CI->memoryStore['mongo_map'][$collection])) $CI->memoryStore['mongo_map'][$collection] = $map;

  return $map;
}

function mongo_get_required($collection){
  $required = array();
  $fieldData = mongo_schema_map($collection);
  foreach($fieldData as $field => $data) if($data['required']) $required[] = $field;
  return $required;
}

function mongo_get_allowed($collection){
  $fieldData = mongo_schema_map($collection);
  if(is_array($fieldData)){
    return array_keys($fieldData);
  }
  return array();
}

function is_there($var){
  return isset($var) && !empty($var);
}

function _id($id){
  if($id instanceof MongoId) return $id;
  if(is_array($id)){
    $return = array();
    foreach($id as $test) {
      if($test instanceof MongoId) $return[] = $test; else {
        if(is_string($test) && strlen($test) == 24){
          $return[] = new MongoId($test);
        }
      }
    }
    return $return;
  }
  if(is_string($id) && strlen($id) == 24){
    return new MongoId($id);
  }
  return false;
}

function filter_ids(array $resultset){
  $ids = array();
  foreach ($resultset as $result){
    if(isset($result['_id'])) $ids[] = $result['_id'];
  }
  return $ids;
}

function _equals($target, $string){
  if(is_array($target) && in_array($string, $target)) return true;
  if(is_string($target) && $string == $target) return true;
  return false;
}

function flatten_ids($ids){
  if($ids instanceof MongoId) return (string) $ids;
  if(is_array($ids)){
    foreach($ids as $i => $id){
      if($id instanceof MongoId) $ids[$i] = (string)$id;
    }
    return $ids;
  }
}

function mongo_dsn($config = 'default'){
  $CI =& get_instance();
  $CI->load->config('mongodb');
  $connection_info = config_item($config);
  return 'mongodb://'
    .$connection_info['mongo_username']
    .':'
    .$connection_info['mongo_password']
    .'@'
    .$connection_info['mongo_hostbase']
    .'/'
    .$connection_info['mongo_database']
    .(isset($connection_info['mongo_replica_set']) && !empty($connection_info['mongo_replica_set']) ? '?replicaSet=set-'.$connection_info['mongo_replica_set'] : '');
}

function &get_mongo_instance($config_name = 'default'){
  if(config_item('utilize_mongo_db')){
    if(!class_exists('\MongoQB\Builder')) require APPPATH.'/third_party/Mongo_qb.php';
    $CI =& get_instance();
    if(!isset($CI->mdb)) $CI->mdb = new \MongoQB\Builder(array('dsn' => mongo_dsn($config_name)));
    return $CI->mdb;
  }
}

