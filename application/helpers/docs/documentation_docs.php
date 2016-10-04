<?php
$docGroup = 'DOCUMENTATION: ';

$doc['func'] = array(
  'description' => $docGroup . 'Get documentation about an API function',
  'method' => array('GET','URL','POST'),
  'methods' => array(
    'GET|URL|POST' => array(
      'arguments' => array(
        'method' => array(
          'type' => array('string'),
          'options' => array(),
          'default' => null,
          'required' => true,
          'desc' => 'Name of function for lookup in the format: \'functionGroup::functionName\'',
          'sample' => 'documentation::func',
          'example' => '{functionGroup::functionName}'
        ),
        'argumentDocs' => array(
          'type' => array('boolean'),
          'options' => array(1,0),
          'default' => 0,
          'required' => false,
          'desc' => 'Display all argument data',
          'example' => 1
        ),
        'extras' => array(
          'type' => array('boolean'),
          'options' => array(1,0),
          'default' => 0,
          'required' => false,
          'desc' => 'Display all common API features such as the possible $_GET params to use while debugging',
          'example' => 1
        ),
      ),
    ),
  ),
  'example' => true,
);