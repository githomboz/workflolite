<?php

class Confirmations extends WFInterface
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'confirmations';

  public function __construct(array $data){
    parent::__construct($data);
  }

  public static function getByProjectId($projectId, array $wheres = null){
    $handler = self::CI()->mdb->where('projectId', _id($projectId))->limit(1);
    if(!empty($wheres)) $handler->wheres($wheres);
    $response = $handler->get(self::CollectionName());
    if(!empty($response)) return new Confirmations($response[0]);

  }

}