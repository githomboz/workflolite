<?php

require_once 'WorkflowFactory.php';

class User extends WorkflowFactory
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'users';

  public function __construct(array $data, $fullLoad = false)
  {
    parent::__construct();
    $this->_initialize($data, $fullLoad);
  }

  /**
   * Return the data that is put into session variable
   */
  public function sessionData(){
    return array(
      'userId' => $this->id(),
      'displayName' => $this->_current['firstName'],
      'username' => $this->_current['username'],
      'email' => $this->_current['email'],
      'organizationId' => $this->_current['organizationId'],
      'settings' => $this->_current['settings'],
    );
  }

  public static function Authenticate($username, $password){
    CI()->load->model('users_model');
    $record = self::GetByCredentials($username, $password);
    return $record;
  }

  public static function Authorize(User $user){

  }

  public static function GetByIds(array $contactIds){
    $contacts = self::CI()->mdb->whereIn('_id', $contactIds)->get(self::CollectionName());
    $class = __CLASS__;
    foreach($contacts as $i => $contact) $contacts[$i] = new $class($contact);
    return $contacts;
  }

  public static function Get($id){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    return new $class($record);
  }

  public static function GetByCredentials($username, $password, $status = null){
    if(!$status) $status = 'active';
    $record = CI()->mdb->where(array('username'=>$username, 'password'=>$password, 'status'=>$status))->get(self::CollectionName());
    if(empty($record)) return false;
    if(isset($record[0])) return new User($record[0]); else return false;
  }

  public static function Create($data){
    $filtered = di_allowed_only($data, mongo_get_allowed(static::CollectionName()));
    $filtered['email'] = strtolower($filtered['email']);
    return self::CI()->mdb->insert(static::CollectionName(), $filtered);
  }

  public function login(){
    return UserSession::start($this->sessionData());
  }

}