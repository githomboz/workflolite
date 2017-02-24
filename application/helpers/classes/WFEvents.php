<?php

class WFEvents
{

  private static $_collection = 'events';

  private static $_localSubscriberLimit = 2;

  public function __construct(){
  }

  /**
   * @param $topic
   * @param $callback
   * @param $organizationId
   * @param string $callbackContext
   * @param array|null $requiredFields
   * @param bool $topicExactMatch
   * @return WFClientInterface::Valid_Response
   */
  public static function Subscribe($topic, $callback, $organizationId, $callbackContext = 'slingshot', array $requiredFields = null, $topicExactMatch = true){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    $organization = Organization::Get($organizationId);
    if($organization){
      // Validate $callbackContext
      if(in_array($callbackContext, ['slingshot','awsLambda'])){
        $topic = trim((string) $topic);
        // Validate topic
        if(!empty($topic)){
          $subscriberExists = self::SubscriberExists($topic, $callback, $organizationId);
          $logger->setLine(__LINE__)->addDebug('Subscriber exists', $subscriberExists);
          if(!$subscriberExists){
            $add = [
              'topic' => $topic,
              'callback' => $callback,
              'callbackContext' => $callbackContext,
              'organizationId' => $organizationId,
            ];

            if($callbackContext == 'slingshot' && !is_callable($callback)) $logger->setLine(__LINE__)->addError('Callback is not callable');
            if(!$logger->hasErrors(__FUNCTION__)){

              $id = self::CreateSubscriber($add);
              if($id){
                $response['response']['success'] = true;
                $response['response']['data'] = $id;
              } else {
                $logger->setLine(__LINE__)->addError('An error has occurred while attempting to create subscriber', CI()->mdb->lastQuery());
              }
            }

          } else {
            $logger->setLine(__LINE__)->addError('Subscriber already exists');
          }


        } else {
          $logger->setLine(__LINE__)->addError('Topic is empty');
        }
      } else {
        $logger->setLine(__LINE__)->addError('Invalid callback context provided');
      }
    } else {
      $logger->setLine(__LINE__)->addError('Invalid organization id provided');
    }


    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  /**
   * @param $topic
   * @param $payload
   * @param $organizationId
   * @param array|null $reference
   * @return WFClientInterface::Valid_Response
   */
  public static function Publish($topic, $payload, $organizationId, array $reference = null){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    $organization = Organization::Get($organizationId);
    if($organization) {
      // Parse Topic
      $topic = trim((string) $topic);
      if(!empty($topic)){

        if($reference) $payload['reference'] = $reference;
        // Validate Payload
        if(self::ValidatePayload($payload)){

          $add = [
            'topic' => $topic,
            'payload' => $payload,
            'organizationId' => $organizationId,
            'reference' => $payload['reference'],
          ];

          $id = self::CreateEvent($add);
          $add['eventId'] = $id;
          if(isset($add['_id'])) unset($add['_id']);

          // Send to dynamodb
          $add['payload'] = json_encode($add['payload']);
          $add['organizationId'] = (string) $add['organizationId'];
          $result = addToDynamoDBTable('wfEvents', $add);

          if(isset($result['success']) && isset($result['x-amzn-requestid'])){
            $save = [
              'success' => $result['success'],
              'x-amzn-requestid' => $result['x-amzn-requestid']
            ];
            self::UpdateEvent($id, ['dynamoResponse' => $save]);
          }

        } else {
          $logger->setLine(__LINE__)->addError('Invalid payload provided', $payload);
        }
      } else {
        $logger->setLine(__LINE__)->addError('Topic is empty');
      }
    } else {
      $logger->setLine(__LINE__)->addError('Invalid organization id provided');
    }


    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  /**
   * @param $topic
   * @param $organizationId
   * @return WFClientInterface::Valid_Response
   */
  public static function GetSubscribers($topic, $organizationId){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    $organization = Organization::Get($organizationId);
    if($organization) {
      // Parse Topic
      $topic = trim((string) $topic);
      if(!empty($topic)){
        $wheres = ['topic' => $topic, 'organizationId' => _id($organizationId)];
        $args = ['wheres' => $wheres];
        $response['response']['success'] = true;
        $response['response']['data'] = self::ReadSubscribers($args);

      } else {
        $logger->setLine(__LINE__)->addError('Topic is empty');
      }
    } else {
      $logger->setLine(__LINE__)->addError('Invalid organization id provided');
    }


    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  /**
   * @param $payload
   * @return bool
   */
  public static function ValidatePayload($payload){
    if(isset($payload['reference'])) return self::ValidateReference($payload['reference']);
    return false;
  }

  /**
   * @param $reference
   * @return bool
   */
  public static function ValidateReference($reference){
    foreach(['entityType','entityId','context'] as $field) if(!isset($reference[$field])) return false;
    return true;
  }

  public static function CreateSubscriber(array $data){
    $defaults = [
      'dateAdded' => new MongoDate(),
      'topicExactMatch' => true,
      'requiredFields' => null,
      'active' => true
    ];

    return self::Create(self::SubscribersCollection(), array_merge($defaults, $data));
  }

  public static function CreateEvent(array $data){
    $defaults = [
      'dateAdded' => new MongoDate(),
      'logs' => WFClientInterface::GetLogsTemplate()
    ];

    return self::Create(self::EventsCollection(), array_merge($defaults, $data));
  }

  public static function UpdateEvent($id, $data){
    return self::Update(self::EventsCollection(), $id, $data);
  }

  public static function UpdateSubscriber($id, $data){
    return self::Update(self::SubscribersCollection(), $id, $data);
  }

  public static function ReadEvents(array $args = null, $returnCount = false){
    return self::Read(self::EventsCollection(), $args, $returnCount);
  }

  public static function ReadSubscribers(array $args = null, $returnCount = false){
    return self::Read(self::SubscribersCollection(), $args, $returnCount);
  }

  public static function GetEventById($id){
    return self::GetByID(self::EventsCollection(), $id);
  }

  public static function GetSubscriberById($id){
    return self::GetByID(self::SubscribersCollection(), $id);
  }

  public static function GetByID($collection, $id){
    $results = CI()->mdb->where('_id', _id($id))->limit(1)->get($collection);
    return isset($results[0]) ? $results[0] : null;
  }

  public static function Create($collection, $data){
    if(isset($data['_id'])) unset($data['_id']);
    $filtered = di_allowed_only($data, mongo_get_allowed($collection));
    return CI()->mdb->insert($collection, $filtered);
  }

  public static function Update($collection, $id, $data){
    if(!is_array($id)) $id = array(_id($id));
    foreach($id as $i => $theId) $id[$i] = _id($theId);
    $data = di_allowed_only($data, mongo_get_allowed($collection));
    return CI()->mdb->whereIn('_id', $id)->set($data)->updateAll($collection);
  }

  public static function Read($collection, array $args = null, $returnCount = false){
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
          if(!in_array($argVal, self::GetDBFields($collection))) $args[$arg] = $defaults[$arg];
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
        case 'wheres':
          $wheres = is_array($argVal) ? array_merge($wheres, $argVal) : $wheres;
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
      return $handler->count($collection);
    } else {
      return $handler->get($collection);
    }
  }

  public static function QueryDefaults(){
    return [
      'startingRecord'    => null, // Query starting from a specific record
      'endingRecord'      => null, // Query ending at a specific record
      'rangeStart'        => null, // Query from date time
      'rangeEnd'          => null, // Query to date time
      'sortField'         => 'dateAdded',
      'sortDirection'     => 'asc',
      'limit'             => 200,
      'page'              => 1
    ];
  }

  public static function GetDBFields($collection){
    return mongo_get_allowed($collection);
  }


  public static function SubscribersCollection(){
    return 'subscribers';
  }

  public static function EventsCollection(){
    return 'events';
  }


  /**
   * @param string $topic
   * @param bool $exactMatch
   * @return string || bool Topic or false if invalid
   */
  public static function ParseTopic($topic, $exactMatch = false){
    // Normalize
    $topic = trim((string) $topic);
    if(empty($topic)) return false;
    if($exactMatch) return $topic;
    $segs = explode('.', $topic);
    $topicArray = [];
    foreach($segs as $i => $seg){
      $seg = trim($seg);
      if(empty($seg)) unset($segs[$i]);
    }
    $add = '';
    foreach($segs as $seg){
      if(!empty($add)) $add .= '.';
      $add .= $segs;
      if(!in_array($add, $topicArray)) $topicArray[] = $add;
    }
    return count($topicArray) == 1 ? $topicArray[0] : $topicArray;
  }

  /**
   * @param $topic
   * @param null $callback
   * @param $organizationId
   * @return bool
   */
  public static function SubscriberExists($topic, $callback = null, $organizationId){
    $response = CI()->mdb->where(['topic' => $topic, 'callback' => $callback, 'organizationId' => _id($organizationId)])->count(self::SubscribersCollection());
    return $response;
  }


}

