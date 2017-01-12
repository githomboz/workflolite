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
    $collection = 'incoming';
    $filtered = di_allowed_only($data, mongo_get_allowed($collection));
    return self::CI()->mdb->insert($collection, $filtered);
  }



}