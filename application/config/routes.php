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


$route['jobs/(:any)/(:any)'] = 'jobs/single/$1/$2';
$route['jobs/(:any)'] = 'jobs/single/$1/tasks';
$route['jobs/create'] = 'workflows/create_job';
$route['workflows/(:any)/(:any)'] = 'workflows/single/$1/$2';
$route['workflows/(:any)'] = 'workflows/details/$1';
$route['projects/(:any)/(:any)'] = 'projects/single/$1/$2';
$route['projects/(:any)'] = 'projects/single/$1/tasks';
$route['projects/create'] = 'templates/create_project';
$route['templates/(:any)/(:any)'] = 'templates/single/$1/$2';
$route['templates/(:any)'] = 'templates/details/$1';
$route['dashboard'] = 'jobs/dashboard';
$route['contacts'] = 'jobs/contacts';
$route['users'] = 'jobs/users';
$route['search'] = 'jobs/search';
$route['progress/(:any)'] = 'main/progress/$1';
$route['confirmations/(:any)'] = 'main/confirmations/$1';


$route['api/v1/(:any)'] = 'api/v1/call_method/$1';

/* End of file routes.php */
/* Location: ./application/config/routes.php */