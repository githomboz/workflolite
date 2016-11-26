<?php

require_once 'main_model.php';

class Projects_model extends Main_model
{

  public static $collection = 'projects';

  public function __construct() {
    parent::__construct();
  }

}