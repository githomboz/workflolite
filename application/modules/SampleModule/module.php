<?php
// Module Details
$moduleDetails['name'] = 'Sample Module 1.0';
$moduleDetails['module'] = 'SampleModule';
$moduleDetails['description'] = 'A sample module for the Spotflare Standard dev environment';
$moduleDetails['version'] = (float) '1.0';

// Author Details
$moduleDetails['author'] = 'Jahdy Lancelot';
$moduleDetails['url'] = 'http://www.jahdy1.com';
$moduleDetails['social']['twitter'] = '@jahdy1';

$mfile = $moduleDetails['module'].'.php';
if(file_exists($file)) include_once $mfile;