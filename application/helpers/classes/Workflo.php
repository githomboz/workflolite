<?php

require_once 'core/WFMessaging.php';
require_once 'core/WFBroadcast.php';

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 1/7/17
 * Time: 10:50 AM
 */
class Workflo
{
  private static $_Messaging = null;
  private static $_Broadcast = null;
  private static $_MetaGrab = null;
  private static $_Webhooks = null;

  public function __construct()
  {
    self::Messaging();
    self::Broadcast();
  }

  public static function Messaging(){
    if(!self::$_Messaging) self::$_Messaging = new WFMessaging();
    return self::$_Messaging;
  }

  public static function Broadcast(){
    if(!self::$_Broadcast) self::$_Broadcast = new WFBroadcast();
    return self::$_Broadcast;
  }

  public static function MetaGrab(){
    if(!self::$_MetaGrab) self::$_MetaGrab = null;
    return self::$_MetaGrab;
  }

  public static function Webhooks(){
    if(!self::$_Webhooks) self::$_Webhooks = null;
    return self::$_Webhooks;
  }

}