<?php

class Webhooks extends WFInterface
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'webhooks';

  public function __construct(array $data){
    parent::__construct($data);
  }

  public static function GetByTopic($topic, $organizationId){
    $records = self::GetAll( [ 'wheres'=> [ 'topic' => $topic, 'organizationId' => _id($organizationId) ] ] );
    return isset($records[0]) ? $records[0] : null;
  }

  public static function RegisterWebhook($topic, $organizationId, $callback){
    return self::Create([
      'dateAdded' => new MongoDate(),
      'organizationId' => $organizationId,
      'topic' => $topic,
      'callback' => $callback
    ]);
  }

  public static function RegisterIncomingRequest($data){
    foreach(['payload','callbackResponse','logs'] as $field)
      if(isset($data[$field])) $data[$field] = json_decode(json_encode($data[$field]), true);
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $collection = 'incoming';
    $logger->setLine(__LINE__)->addDebug('Saving incoming request data', $data);
    $filtered = di_allowed_only($data, mongo_get_allowed($collection));
    $return = self::CI()->mdb->insert($collection, $filtered);
    $logger->setLine(__LINE__)->addDebug('DB insert result', $return);
    $logger->setLine(__LINE__)->addDebug('Insertion into collection (`'.$collection.'`) result', json_encode(CI()->mdb->lastQuery()));
    $logger->sync();
    return $return;
  }



}