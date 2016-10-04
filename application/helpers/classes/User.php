<?php

require_once 'WorkflowFactory.php';

class User extends WorkflowFactory implements WorkflowInterface
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

  }

  public static function Authenticate($username, $password, User $user = null){

  }

  public static function Authorize(User $user){

  }


  public static function ValidData(array $data){
    return !empty($data) && isset($data['name']);
  }

  public static function Create($data, $templateId = null){

  }

  public static function Duplicate($id, array $data = array()){
    return null; // return new id
  }


}