<?php

require_once 'main_model.php';

class Triggers_model extends Main_model
{

  public static $collection = 'triggers';

  public function __construct() {
    parent::__construct();
  }

}