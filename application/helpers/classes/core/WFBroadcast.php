<?php

require_once 'WFBroadcastAWS.php';
require_once 'WFBroadcastInternal.php';
require_once 'WFBroadcastIncoming.php';

class WFBroadcast
{

  private static $_AWS = null;
  private static $_Internal = null;
  private static $_Incoming = null;

  public function __construct(){
    self::AWS();
    self::Internal();
    self::Incoming();
  }

  public static function AWS(){
    if(!self::$_AWS) self::$_AWS = new WFBroadcastAWS();
    return self::$_AWS;
  }

  public static function Internal(){
    if(!self::$_Internal) self::$_Internal = new WFBroadcastInternal();
    return self::$_Internal;
  }

  public static function Incoming(){
    if(!self::$_Incoming) self::$_Incoming = new WFBroadcastIncoming();
    return self::$_Incoming;
  }



}