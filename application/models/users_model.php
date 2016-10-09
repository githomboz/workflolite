<?php

require_once 'main_model.php';

class Users_model extends Main_model
{

  public static $collection = 'users';

  public function __construct() {
    parent::__construct();
    $this->load->helpers('users');
  }

  /**
   * @param $username
   * @param $password
   * @param $status
   * @return bool
   */
  public function get_by_credentials($username, $password, $status = null){
    if(!$status) $status = 'active';
    $record = $this->mdb->where(array('username'=>$username, 'password'=>$password, 'status'=>$status))->get(self::$collection);
    if(empty($record)) return false;
    if(isset($record[0])) return new User($record[0]); else return false;
  }

  public function create($data){
    // @todo Verify that the email is unique
    $filtered = di_allowed_only($data, mongo_get_allowed(static::$collection));
    return $this->mdb->insert(static::$collection, $filtered);
  }

  public function exists($email){
    return $this->mdb->where('email', $email)->get(static::$collection);
  }

  public function get_all(){
    return $this->_get();
  }

  public function get_all_count(){
    return $this->_count();
  }

}