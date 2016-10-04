<?php

require_once 'main_model.php';

class Tasktemplates_model extends Main_model
{

  public static $collection = 'taskTemplates';

  public function __construct() {
    parent::__construct();
  }

}