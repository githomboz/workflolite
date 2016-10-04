<?php

$config = null;
require APPPATH.'config/app_settings.php';

$enable_db_logging = isset($config['enable_db_logging']) ? $config['enable_db_logging'] : false;
$utilize_mongo_db = isset($config['utilize_mongo_db']) ? $config['utilize_mongo_db'] : false;

function early_mongo_connection($config_name = 'default'){
  global $utilize_mongo_db;
  if($utilize_mongo_db){
    require APPPATH.'/third_party/Mongo_qb.php';
    $config = null;

    require APPPATH.'config/'.ENVIRONMENT.'/mongodb.php';

    $connection_info = $config[$config_name];
    $dsn = 'mongodb://'
      .$connection_info['mongo_username']
      .':'
      .$connection_info['mongo_password']
      .'@'
      .$connection_info['mongo_hostbase']
      .'/'
      .$connection_info['mongo_database']
      .(isset($connection_info['mongo_replica_set']) && !empty($connection_info['mongo_replica_set']) ? '?replicaSet=set-'.$connection_info['mongo_replica_set'] : '');

    return new \MongoQB\Builder(array('dsn' => $dsn));
  }
}

function early_mongo_config($config_name = 'default'){
  $config = null;

  require APPPATH.'config/'.ENVIRONMENT.'/mongodb.php';

  return $config[$config_name];
}


if ( ! function_exists('log_message') && $enable_db_logging && $utilize_mongo_db)
{
  function log_message($level = 'error', $message, $php_error = FALSE, $tags = null)
  {
    static $_log;

    $mdb = early_mongo_connection();
    $mdb->insert('logs', array(
      'dateAdded'=>new MongoDate(),
      'type' => $level,
      'tags' => $tags,
      'content' => $message,
      'phpErr' => $php_error,
    ));

    if(is_array($message) || is_object($message)) $message = json_encode($message); else $message = (string) $message;

    if (config_item('log_threshold') == 0)
    {
      return;
    }

    $_log =& load_class('Log');
    $_log->write_log($level, $message, $php_error);
  }
}
