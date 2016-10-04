<?php

require_once 'main_model.php';

class Organizations_model extends Main_model
{

  public static $collection = 'organizations';

  public function __construct() {
    parent::__construct();
  }

}