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
    self::CI()->load->model('users_model');
    $record = self::CI()->users_model->get_by_credentials($username, $password);
    return $record;
  }

  public static function Authorize(User $user){

  }

  public static function Get($id){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    return new $class($record);
  }

  public function login(){
    return UserSession::start($this->sessionData());
  }

}