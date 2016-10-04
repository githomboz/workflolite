<?php

require_once 'main_model.php';

class Jobs_model extends Main_model
{

  public static $collection = 'jobs';

  public function __construct() {
    parent::__construct();
  }

}