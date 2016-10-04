<?php

require_once 'main_model.php';

class Tasks_model extends Main_model
{

  public static $collection = 'tasks';

  public function __construct() {
    parent::__construct();
  }

}