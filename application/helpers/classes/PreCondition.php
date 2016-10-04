<?php

class PreCondition
{

  protected $id = null;

  protected $status = 'active';

  public function __construct(){
  }

  public function id(){
    return $this->id;
  }
  

}