<?php

require_once 'main_model.php';

class Contacts_model extends Main_model
{

  public static $collection = 'contacts';

  public function __construct() {
    parent::__construct();
  }

}