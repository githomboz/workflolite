<?php

class WFLogger
{

  private $_type = null;
  private $_context = null;
  private $_entries = [];

  private $_showFile = false;
  private $_showLine = false;
  private $_showTime = false;

  private static $_collection = 'logger';

  public function __construct($__FUNCTION__, $__FILE__, $context = null){
    if($context) $this->setContext($context);
    $this->setFunction($__FUNCTION__);
    $this->setFile($__FILE__);
  }

  public function showFile($setValue = null){
    if(isset($setValue)) $this->_showFile = (bool) $setValue;
    return $this->_showFile;
  }

  public function showLine($setValue = null){
    if(isset($setValue)) $this->_showLine = (bool) $setValue;
    return $this->_showLine;
  }

  public function showTime($setValue = null){
    if(isset($setValue)) $this->_showTime = (bool) $setValue;
    return $this->_showTime;
  }

  public function setType($type){
    if($this->validType($type)) $this->_type = $this->validType($type);
    return $this;
  }

  public function getType(){
    if(!$this->_type) return 'debug';
    return $this->_type;
  }

  public function validType($type){
    switch ($type):
      case 'errors':
      case 'error':
        return 'errors';
        break;
      case 'info':
      case 'information':
        return 'info';
        break;
      case 'debug':
        return $type;
        break;
      default:
        return false;
      endswitch;
  }

  public function setFunction($val){
    $val = trim((string) $val);
    if(!empty($val)) $this->_context['function'] = $val;
    return $this;
  }

  public function getFunction(){
    return $this->_context['function'];
  }

  public function setScope($val){
    $val = trim((string) $val);
    if(!empty($val)) $this->_context['scope'] = $val;
    return $this;
  }

  public function getScope(){
    return isset($this->_context['scope']) ? $this->_context['scope'] : null;
  }

  public function unsetScope(){
    $this->_context['scope'] = null;
    return $this;
  }

  public function setLine($val){
    $val = trim((string) $val);
    if(!empty($val)) $this->_context['line'] = $val;
    return $this;
  }

  public function getLine(){
    return isset($this->_context['line']) ? $this->_context['line'] : null;
  }

  public function unsetLine(){
    $this->_context['line'] = null;
    return $this;
  }

  public function setEntityType($val){
    $val = trim((string) $val);
    if(!empty($val)) $this->_context['entityType'] = $val;
    return $this;
  }

  public function getEntityType(){
    return isset($this->_context['entityType']) ? $this->_context['entityType'] : null;
  }

  public function unsetEntityType(){
    $this->_context['entityType'] = null;
    return $this;
  }

  public function setEntityId($val){
    $val = trim((string) $val);
    if(!empty($val)) $this->_context['entityId'] = $val;
    return $this;
  }

  public function getEntityId(){
    return isset($this->_context['entityId']) ? $this->_context['entityId'] : null;
  }

  public function unsetEntityId(){
    $this->_context['entityId'] = null;
    return $this;
  }

  public function setFile($val){
    $val = trim((string) $val);
    $file = isset($this->_context['file']) ? $this->_context['file'] : null;
    if(!empty($val)) $file = $val;
    if($file){
      $fileSegs = explode(DIRECTORY_SEPARATOR, $file);
      $file = $fileSegs[(count($fileSegs) - 1)];
    }
    $this->_context['file'] = $file;
    return $this;
  }

  public function getFile(){
    return isset($this->_context['file']) ? $this->_context['file'] : null;
  }

  public function setContext(array $context){
    $contextFields = ['scope','file','function','line','entityType','entityId'];
    foreach($contextFields as $field){
      if(isset($context[$field])){
        switch($field){
          case 'file' : $this->setFile($context[$field]);
            break;
          case 'line' : $this->setLine($context[$field]);
            break;
          case 'scope' : $this->setScope($context[$field]);
            break;
          case 'function' : $this->setFunction($context[$field]);
            break;
          case 'entityType' : $this->setEntityType($context[$field]);
            break;
          case 'entityId' : $this->setEntityId($context[$field]);
            break;
        }
      }
    }
    return $this;
  }

  public function getContext(){
    return $this->_context;
  }

  public function getEntries($type = null){
    if($this->validType($type)){
      $return = [];
      foreach($this->_entries as $entry) if($entry['type'] == $type) $return[] = $entry;
      return $return;
    }
    return (array) $this->_entries;
  }

  public function getMessages($type = null){
    $messages = [];
    $type = $this->validType($type);
    foreach((array) $this->_entries as $entry){
      if($type){
        if($entry['type'] == $type) $messages[] = $entry['message'];
      } else {
        $messages[] = $entry['message'];
      }
    }
    return $messages;
  }

  public function getLogsArray(){
    $logs = WFClientInterface::GetLogsTemplate();
    foreach($this->getEntries() as $entry){
      $logs[$entry['type']][] = $entry['message'];
    }
    return $logs;
  }

  public function isSynced(){
    foreach($this->getEntries() as $entry) {
      if(!$entry['_id']) return false;
    }
    return true;
  }

  private function _addEntry($entry){
    if(!isset($entry['hash'])){
      $entry['hash'] = md5(json_encode($entry));
    }

    $this->_entries[] = $entry;
    return $this;
  }

  private function _drawMessagePrefix($time_in_seconds){
    $output = '[';
    if($this->showTime()) $output .= date('c', $time_in_seconds) . ' ';
    $output .= $this->getFunction();
    if($this->getScope()) $output .= '::' . $this->getScope();
    if($this->showFile() && $this->getFile()) $output .= '::' . $this->getFile();
    if($this->showLine() && $this->getLine()) $output .= '::ln#' . $this->getLine();
    $output .= '] ';
    return $output;
  }

  public function addLogMessage($message, $data = null, $type = null, array $context = null){
    $logTime = new MongoDate();

    if($this->validType($type)) $this->setType($type);
    if($context) $this->setContext($context);

    $entry = [
      'hash' => null,
      'dateAdded' => $logTime,
      'type' => $this->getType(),
      'message' => $this->_drawMessagePrefix($logTime->sec) . $message,
      'data' => $data,
      'context' => $this->getContext(),
    ];

    $this->_addEntry($entry);
    $this->unsetScope();
    $this->unsetLine();
    return $this;
  }

  public function addError($message, $data = null, $context = null){
    return $this->addLogMessage($message, $data, 'errors', $context);
  }

  public function addInfo($message, $data = null, $context = null){
    return $this->addLogMessage($message, $data, 'info', $context);
  }

  public function addDebug($message, $data = null, $context = null){
    return $this->addLogMessage($message, $data, 'debug', $context);
  }

  public function setEntityOrg(){
    return $this->setEntityType('organization');
  }

  public function setEntityProject(){
    return $this->setEntityType('project');
  }

  public function sync(){
    // Save currently unsynced log messages to db in bulk action.
    if(!$this->isSynced()){
      foreach($this->getEntries() as $i => $entry){
        if(!isset($entry['_id'])){
          // Save to log collection
          $id = self::Create($entry);
          // Save new log id back to entry
          if($id && !isset($this->_entries[$i])) $this->_entries[$i]['_id'] = $id;
        }
      }
    }
    return $this;
  }

  public function hasLogs(){
    return !empty($this->_entries);
  }

  public function hasErrors($__FUNCTION__ = null){
    if($__FUNCTION__){
      foreach($this->getEntries() as $entry) if($entry['type'] == 'errors' && (isset($entry['context']['function']) && $entry['context']['function'] == $__FUNCTION__)) return true;
      return false;
    }
  }

  public function hasDebug(){
    foreach($this->getEntries() as $entry) if($entry['type'] == 'debug') return true;
    return false;
  }

  public function hasInfo(){
    foreach($this->getEntries() as $entry) if($entry['type'] == 'info') return true;
    return false;
  }

  public static function CollectionName(){
    return static::$_collection;
  }

  public static function Create($data){
    $filtered = di_allowed_only($data, mongo_get_allowed(static::CollectionName()));
    return CI()->mdb->insert(static::CollectionName(), $filtered);
  }

  public static function Read(array $args = null, $returnCount = false){
    $handler = CI()->mdb->select(['dateAdded','type','message','data','context']);
    $defaults = self::QueryDefaults();
    $args = array_merge($defaults, (array) $args);
    // Validate / Typecast Data before checks
    $wheres = [];
    foreach($args as $arg => $argVal){
      switch ($arg){
        case 'startingRecord':
        case 'endingRecord':
          if($argVal && !$argVal instanceof MongoId) $args[$arg] = _id($argVal);
        if($args[$arg]) $wheres[] = ['dateAdded' => [($arg == 'startingRecord' ? '$gte':'$lte') => new MongoDate($args[$arg]->getTimestamp())]];
        break;
        case 'rangeStart':
        case 'rangeEnd':
          $args[$arg] = strtotime($argVal);
        if($args[$arg]) $wheres['dateAdded'] = [($arg == 'rangeStart' ? '$gte':'$lte') => new MongoDate($args[$arg])];
        break;
        case 'sortField':
          if(!in_array($argVal, self::GetDBFields())) $args[$arg] = $defaults[$arg];
          break;
        case 'sortDirection':
          if(!in_array(strtolower($argVal), ['asc','desc'])) $args[$arg] = $defaults[$arg];
          break;
        case 'limit':
          $args[$arg] = is_numeric($argVal) ? (int) $argVal : null;
          break;
        case 'page':
          $args[$arg] = is_numeric($argVal) ? (int) $argVal : $defaults[$arg];
          break;
      }
    }

    if(count($wheres) >= 2){
      $handler->wheres(['$and' => $wheres]);
    } elseif(count($wheres) === 1) {
      $handler->wheres($wheres);
    }

    $handler->order_by([$args['sortField'] => $args['sortDirection']]);
    if($args['limit']) {
      $handler->limit($args['limit']);
      $handler->offset($args['limit'] * ($args['page'] - 1));
    }

    if($returnCount){
      return $handler->count(static::CollectionName());
    } else {
      return $handler->get(static::CollectionName());
    }
  }

  public function importLogs($logs){
    if(isset($logs['info']) && isset($logs['debug']) && isset($logs['errors'])){
      foreach($logs as $type => $messages){
        foreach($messages as $message){
          $this->addLogMessage($message, null, $type);
        }
      }
    }
    return $this;
  }

  public static function QueryDefaults(){
    return [
      'startingRecord'    => null, // Query starting from a specific record
      'endingRecord'      => null, // Query ending at a specific record
      'rangeStart'        => null, // Query from date time
      'rangeEnd'          => null, // Query to date time
      'sortField'         => 'dateAdded',
      'sortDirection'     => 'asc',
      'limit'             => 100,
      'page'              => 1
    ];
  }
  
  public static function GetDBFields(){
    return mongo_get_allowed(static::CollectionName());
  }

  /**
   * Combine logger instances to maintain awareness throughout app
   * @param WFLogger $logger
   * @return WFLogger instance
   */
  public function merge(WFLogger $logger){
    $this->_entries = array_merge($this->getEntries(), $logger->getEntries());
    return $this;
  }


}

