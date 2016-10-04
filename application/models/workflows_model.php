<?php

require_once 'main_model.php';

class Workflows_model extends Main_model
{

  public static $collection = 'workflows';

  public function __construct() {
    parent::__construct();
  }

}