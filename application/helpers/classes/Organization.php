<?php

require_once 'WorkflowFactory.php';

class Organization extends WorkflowFactory
{

  public $workflows = array();

  private static $contactCache = array();

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'organizations';

  public function __construct(array $data)
  {
    parent::__construct();
    $this->_initialize($data);
  }

  public function addWorkflow(Workflow $workflow){
    if($this->hasId()){
      $workflow->setValues(array('workflowId' => $this->id()))->save();
      return $this;
    } else {
      throw new Exception('Workflows can not be added without an _id');
    }
  }

  public function getWorkflows(){
    if($this->hasId()){
      if(!empty($this->workflows)) return $this->workflows;
      else {
        $workflows = self::CI()->mdb->where('organizationId', $this->id())->get(Workflow::CollectionName());
        foreach($workflows as $workflow) $this->workflows[] = new Workflow($workflow);
        return $this->workflows;
      }
    } else {
      throw new Exception('Workflows can not be pulled without an _id');
    }
  }

  public function addTemplate(Template $template){
    if($this->hasId()){
      $template->setValues(array('templateId' => $this->id()))->save();
      return $this;
    } else {
      throw new Exception('Templates can not be added without an _id');
    }
  }

  public function getTemplates(){
    if($this->hasId()){
      if(!empty($this->templates)) return $this->templates;
      else {
        $this->templates = [];
        $templates = self::CI()->mdb->where('organizationId', $this->id())->get(Template::CollectionName());
        foreach($templates as $template) $this->templates[] = new Template($template);
        return $this->templates;
      }
    } else {
      throw new Exception('Templates can not be pulled without an _id');
    }
  }

  public function getProjects(){
    if($this->hasId()){
      if(!empty($this->templates)) return $this->templates;
      else {
        $templates = self::CI()->mdb->where('organizationId', $this->id())->get(Project::CollectionName());
        foreach($templates as $template) $this->templates[] = new Project($template);
        return $this->templates;
      }
    } else {
      throw new Exception('Projects can not be pulled without an _id');
    }
  }

  public function getUsers(){
    if($this->hasId()){
      if(!empty($this->users)) return $this->users;
      else {
        $users = self::CI()->mdb->where('organizationId', $this->id())->get(User::CollectionName());
        foreach($users as $user) $this->users[] = new User($user);
        return $this->users;
      }
    } else {
      throw new Exception('Projects can not be pulled without an _id');
    }
  }

  public function searchContactsByName($string, $limit = 10, $field = 'name'){
//    $query = array(
//      'organizationId' => $this->getValue('organizationId'),
//      'name' => array(
//        '$regex' => '/^'.$string.'/i'
//      )
//    );
//    var_dump($query);
//    $o = $this->CI()->mdb->handler()->selectCollection(Contact::CollectionName())->find($query);
//    if(is_numeric($limit)) $o = $o->limit($limit);
//    return iterator_to_array($o);

    $matches = array();

    // Get all records
    if(isset(self::$contactCache[(string) $this->id()])){
      $contacts = self::$contactCache[(string) $this->id()];
    } else {
      $contacts = $this->getContacts(9999);
      self::$contactCache[(string) $this->id()] = $contacts;
    }
    // Check for string occurrence
    foreach($contacts as $contact){
      if(isset($contact[$field])){
        if(strpos(strtolower($contact[$field]), strtolower($string)) !== false){
          $matches[] = array(
            'contactId' => (string) $contact['_id'],
            'name' => $contact['name'],
            'email' => $contact['email'],
            'phone' => isset($contact['phone']) ? $contact['phone'] : null,
            'mobile' => isset($contact['mobile']) ? $contact['mobile'] : null,
            'collection' => 'contacts',
            'settings' => $contact['settings'],
          );
        }
      }
    }
    // Return id, name, and collection
    return $matches;
  }

  public function getContacts($limit = 500, $page = 1){
    $offset = ($page - 1) * $limit;
    return $this->CI()->mdb->where('organizationId', $this->id())->offset($offset)->limit($limit)->get(Contact::CollectionName());
  }

  public static function Get($id){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    if(!empty($record)) return new $class($record);
    return false;
  }

  public function getSettings($field = null){
    $settings = $this->getValue('settings');
    if(!isset($field)) return $settings;
    return isset($settings[ (string) $field]) ? $settings[ (string) $field] : null;
  }

  public function setSettings($key, $value){
    $settings = $this->getValue('settings');
    $settings[$key] = $value;
    $this->setValue('settings', $settings)->save('settings');
  }
}