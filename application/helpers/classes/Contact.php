<?php

require_once 'WorkflowFactory.php';

class Contact extends WorkflowFactory
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'contacts';

  public function __construct(array $data)
  {
    parent::__construct();
    $this->_initialize($data);
  }

  public function getRecipientData(){
    return array();
  }

  public function getEmail(){

  }
  
  public static function AdminToContact(Admin $admin){

  }

  public static function GetByEmail($email){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->addDebug('Entering ...');

    $emails = is_array($email) ? $email : array($email);
    $logger->addDebug('Emails is set to ' . json_encode($emails));

    $contacts = self::CI()->mdb->whereIn('email', $emails)->get(self::CollectionName());
    $class = __CLASS__;
    foreach($contacts as $i => $contact) $contacts[$i] = new $class($contact);
    //var_dump(CI()->mdb->lastQuery());
    return $contacts;
  }

  public static function Get($id){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    if(!empty($record)) return new $class($record);
    return false;
  }

  public static function GetByIds(array $contactIds){
    $contacts = self::CI()->mdb->whereIn('_id', $contactIds)->get(self::CollectionName());
    $class = __CLASS__;
    foreach($contacts as $i => $contact) $contacts[$i] = new $class($contact);
    return $contacts;
  }

  public static function _dummy_generate_contacts(){
    //self::CI()->load->library('DummyData');
    $dummydata_users = DummyData::get_dummy_user(5);
    $dummydata_users = self::_dummy_transform_dummydata_users($dummydata_users['results']);

    foreach($dummydata_users as $user){
      $id = Contact::Create($user);
    }
  }

  private static function _dummy_transform_dummydata_users($dummydata_users){
    foreach($dummydata_users as $i => $user){
      $data = array(
        'dateAdded' => new MongoDate(strtotime($user['registered'])),
        'name' => ucwords($user['name']['first'] . ' ' . $user['name']['last']),
        'phone' => str_replace(array('(',')','-',' '), '', $user['phone']),
        'mobile' => str_replace(array('(',')','-',' '), '', $user['cell']),
        'email' => $user['email'],
        'pin' => _generate_id(4),
        'settings' => array(
          'recieveSMS' => (bool) rand(0,1),
        ),
        'organizationId' => new MongoId('57dcafafc7741905f252fbb3'),
        'active' => true,
        'title' => '',
        'company' => ''
      );
      $dummydata_users[$i] = $data;
    }
    return $dummydata_users;
  }

  public static function Create($data){
    if(!isset($data['dateAdded'])) $data['dateAdded'] = new MongoDate();
    if(!isset($data['pin'])) $data['pin'] = _generate_id(4);
    $data['organizationId'] = isset($data['organizationId']) ? $data['organizationId'] : (UserSession::loggedIn() ? UserSession::Get_Organization()->id() : null);
    if(!isset($data['settings'])){
      $data['settings'] = [
        'emailUpdates' => true,
        'smsUpdates' => false
      ];
    }
    $data['active'] = isset($data['active']) ? (bool) $data['active'] : true;

    return parent::Create($data);
  }



}