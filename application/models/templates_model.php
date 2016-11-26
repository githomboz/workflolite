<?php

require_once 'main_model.php';

class Templates_model extends Main_model
{

  public static $collection = 'templates';

  public function __construct() {
    parent::__construct();
  }

}