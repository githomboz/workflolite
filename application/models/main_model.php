<?php

class Main_model extends CI_Model
{

  public static $collection = NULL;

  public $sysCache = array();

  public function __construct() {
    parent::__construct();
    $this->load->helper('data_input');
  }

  public function create($data){
    $filtered = di_allowed_only($data, mongo_get_allowed(static::$collection));
    return $this->mdb->insert(static::$collection, $filtered);
  }

  public function get($id){
    if($id instanceof MongoId || (is_string($id) && strlen($id) == 24)){
      if(!$id instanceof MongoId) $id = new MongoId($id);
      $response = $this->mdb->where('_id', $id)->limit(1)->get(static::$collection);
      if(!empty($response)) return $response[0];
    }
  }

  public function update($id, $data){
    if(!is_array($id)) $id = array(_id($id));
    foreach($id as $i => $theId) $id[$i] = _id($theId);
    return $this->mdb->whereIn('_id', $id)->set(di_allowed_only($data, mongo_get_allowed(static::$collection)))->updateAll(static::$collection);
  }

  public function delete($id){
    return $this->mdb->where('_id', _id($id))->limit(1)->delete(static::$collection);
  }

  public function delete_all(){
    return $this->mdb->deleteAll(static::$collection);
  }

  public function get_by_ids($ids){
    if(!empty($ids)) {
      $return = array();
      $response = $this->_get(array('whereIn'=>array('_id',_id($ids)),'limit'=>false));
      foreach($response as $record) $return[(string) $record['_id']] = $record;
      return $return;
    }
  }

  public function _update($value, $where, $collection = NULL){
    if(!$collection) $collection = static::$collection;
    return $this->mdb->where($where)->set($value)->update($collection);
  }

  public function _get(array $args = array()){
    $o = $this->mdb;
    if(isset($args['select']) && is_array($args['select'])) $o = $o->select($args['select']);
    if(isset($args['whereIn']) && is_array($args['whereIn'])) $o = $o->whereIn($args['whereIn'][0],$args['whereIn'][1]);
    if(isset($args['whereNotIn']) && is_array($args['whereNotIn'])) $o = $o->whereNotIn($args['whereNotIn'][0],$args['whereNotIn'][1]);
    if(isset($args['whereNe']) && is_array($args['whereNe'])) $o = $o->whereNe($args['whereNe'][0],$args['whereNe'][1]);
    if(isset($args['orWhere']) && is_array($args['orWhere'])) $o = $o->orWhere($args['orWhere']);
    if(isset($args['where']) && is_array($args['where'])) $o = $o->where($args['where']);
    if(isset($args['orderBy']) && is_array($args['orderBy'])) $o = $o->orderBy($args['orderBy']);
    if(isset($args['limit']) && is_numeric($args['limit'])) $o = $o->limit($args['limit']); else if(!isset($args['limit'])) $o = $o->limit(20);
    if(isset($args['offset']) && is_numeric($args['offset'])) $o = $o->offset($args['offset']);
    return $o->get(static::$collection);
  }

  public function _count(array $args = array()){
    $o = $this->mdb;
    if(isset($args['select']) && is_array($args['select'])) $o = $o->select($args['select']);
    if(isset($args['whereIn']) && is_array($args['whereIn'])) $o = $o->whereIn($args['whereIn'][0],$args['whereIn'][1]);
    if(isset($args['orWhere']) && is_array($args['orWhere'])) $o = $o->orWhere($args['orWhere']);
    if(isset($args['where']) && is_array($args['where'])) $o = $o->where($args['where']);
    return $o->count(static::$collection);
  }

  public function _remove_field($field){
    //@todo: untested.
    return $this->mdb->unsetField($field)->where(array($field => array('$exists'=>true)))->update(static::$collection);
  }

  public function _set_flag($id, $flag, $value){
    $id = _id($id);
    if($id) return $this->mdb->where('_id',new MongoId($id))->set($flag, $value)->update(static::$collection);
    return false;
  }

  public function _get_unflagged($flag = NULL, $current_value = NULL, $limit =  NULL){
    $query = array(
      $flag => array(
        '$exists' => false
      )
    );
    if($current_value){
      $query = array('$or' => array($query));
      $query['$or'][] = array(
        $flag => array(
          '$exists' => true,
          '$lt' => $current_value
        )
      );
    }
    $o = $this->mdb->handler()->selectCollection(static::$collection)->find($query);
    if(is_numeric($limit)) $o = $o->limit($limit);
    return iterator_to_array($o);
  }

  public function _clear_flag($flag){
    return $this->_remove_field($flag);
  }

  public function get_all($args = array(), $return_count = false){
    if(is_array($args)) $args = (object)$args;
    $handle = $this->mdb;

    if(isset($args->select) && is_array($args->select)){
      $handle->select($args->select);
    }

    if(isset($args->wheres) && !empty($args->wheres)) $handle->where($args->wheres);

    if($return_count) return $handle->count(static::$collection);
    $args->direction = isset($args->direction) && strtolower($args->direction) == 'asc' ?  1 : -1;
    if(isset($args->orderby) && !empty($args->orderby)) {
      $handle->order_by(array($args->orderby=>$args->direction));
    } else {
      $handle->order_by(array('dateAdded'=>$args->direction));
    }
    if(!isset($args->limit)) $args->limit = config_item('pagination_rpp');
    if(!isset($args->offset)) $args->offset = 0;
    if(isset($args->limit) && is_numeric($args->limit)) $handle->limit($args->limit);
    if(isset($args->offset) && is_numeric($args->offset)) $handle->offset($args->offset);
    return (array) $handle->get(static::$collection);
  }

  public static function calculate_pagination($page = 1, $rpp = 20, $offset = NULL){
    $args = new stdClass();
    $args->page = $page;
    $args->limit = (int) $rpp;

    if($offset){
      // Handle using $rpp and $offset
      $args->offset = (int) $offset;
      $args->page = $args->offset / $args->limit;
      $args->page += 1;
    } else {
      // Handle using $page
      $args->page = is_numeric($page) ? (int) $page : 1;
      $args->offset = $args->limit * ($args->page - 1);
    }
    return (array) $args;
  }

  public function get_distinct_counts($field, array $fields_to_return = NULL, $where = NULL){
    $query = array();
    if($where && is_array($where)) {
      $query[]['$match'] = $where;
    }
    $query[]['$group'] = array(
      "_id" => '$'.$field,
      "count" => array('$sum' => 1),
    );

    if(!isset($query[0]['$match'])) $query = $query[0];

    $response = $this->mdb->get_handler()->selectCollection(static::$collection)->aggregate($query);
    $return = array(
      '_sum' => 0
    );
    if(isset($response['ok']) && $response['ok']){
      foreach($response['result'] as $row){
        $return[$row['_id']] = $row['count'];
        $return['_sum'] += $row['count'];
      }
    }
    if($fields_to_return) foreach($fields_to_return as $field) if(!isset($return[$field])) $return[$field] = 0;
    return $return;
  }

  public function is_unique($field, $value, $collection){
    return $this->mdb->where($field, $value)->count($collection) == 0;
  }

}