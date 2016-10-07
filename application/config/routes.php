<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "main";
$route['404_override'] = '';

$route['login'] = 'main/login';
$route['logout'] = 'main/logout';
$route['admin/login'] = 'main/admin_login';
$route['admin/logout'] = 'main/admin_logout';


$route['tasks'] = 'main/tasks';
$route['people'] = 'main/people';
$route['time'] = 'main/time';
$route['notes'] = 'main/notes';
$route['client-view'] = 'main/client_view';


$route['o/(:any)/w/(:any)'] = 'jobs/all/$1/$2'; // A list of jobs belonging to the given workflow
$route['o/(:any)/workflows'] = 'jobs/workflows/$1'; // List of workflows from a given org.
$route['o/(:any)/workflows/(:any)'] = 'jobs/workflow/$1/$2'; // The given workflow's structure
$route['o/(:any)/job/(:any)'] = 'jobs/tasklist/$1/$2'; // A list of tasks for a given job
$route['o/(:any)/task/(:any)'] = 'jobs/task/$1/$2'; // A specific task
$route['o/(:any)/webhooks'] = 'webhooks/process/$1'; // Process a webhook
$route['progress/(:any)'] = 'job/progress/$1'; // A page for showing status to outside parties


$route['api/v1/(:any)'] = 'api/v1/call_method/$1';

/* End of file routes.php */
/* Location: ./application/config/routes.php */