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

}