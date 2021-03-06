<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -------------------------------------------------------------------
 * EXPLANATION OF VARIABLES
 * -------------------------------------------------------------------
 *
 * ['mongo_hostbase'] The hostname (and port number) of your mongod or mongos instances. Comma delimited list if connecting to a replica set.
 * ['mongo_database'] The name of the database you want to connect to
 * ['mongo_username'] The username used to connect to the database (if auth mode is enabled)
 * ['mongo_password'] The password used to connect to the database (if auth mode is enabled)
 * ['mongo_persist']  Persist the connection. Highly recommend you don't set to FALSE
 * ['mongo_persist_key'] The persistant connection key
 * ['mongo_replica_set'] If connecting to a replica set, the name of the set. FALSE if not.
 * ['mongo_query_safety'] Safety level of write queries. "safe" = committed in memory, "fsync" = committed to harddisk
 * ['mongo_suppress_connect_error'] If the driver can't connect by default it will throw an error which dislays the username and password used to connect. Set to TRUE to hide these details.
 * ['mongo_host_db_flag']   If running in auth mode and the user does not have global read/write then set this to true
 */

$config_name = 'default';
$config[$config_name]['mongo_hostbase'] = 'localhost';
$config[$config_name]['mongo_username'] = 'workflowappuser';
$config[$config_name]['mongo_password'] = 'nan4sdi1sdn';
$config[$config_name]['mongo_database'] = 'WorkflowLite';
$config[$config_name]['mongo_persist']  = true;
$config[$config_name]['mongo_persist_key']	 = 'ci_persist';
$config[$config_name]['mongo_replica_set']  = null;
$config[$config_name]['mongo_query_safety'] = 'safe';
$config[$config_name]['mongo_suppress_connect_error'] = true;
$config[$config_name]['mongo_host_db_flag']   = true;

$config_name = 'default';
$config[$config_name]['mongo_hostbase'] = 'candidate.13.mongolayer.com:10879,candidate.12.mongolayer.com:10791';
$config[$config_name]['mongo_username'] = 'workflowappuser';
$config[$config_name]['mongo_password'] = 'nan4sdi1sdn';
$config[$config_name]['mongo_database'] = 'WorkflowLite';
$config[$config_name]['mongo_persist']  = true;
$config[$config_name]['mongo_persist_key']	 = 'ci_persist';
$config[$config_name]['mongo_replica_set']  = '54eed6c48ff6cc6d620000ed';
$config[$config_name]['mongo_query_safety'] = 'safe';
$config[$config_name]['mongo_suppress_connect_error'] = true;
$config[$config_name]['mongo_host_db_flag']   = true;

