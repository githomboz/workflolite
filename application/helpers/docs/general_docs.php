<?php
$docGroup = 'GENERAL API REQUESTS: ';

$doc['demo'] = array(
  'description' => $docGroup . 'Demonstrates how the API system works using the story of the 3 little pigs',
  'method' => array('GET','URL','POST'),
  'methods' => array(
    'GET|URL|POST' => array(
      'arguments' => array(
        'littlePig' => array(
          'type' => array('string'),
          'options' => array('first','second','third'),
          'default' => null,
          'required' => true,
          'desc' => 'Select the pig to return',
          'sample' => 'second',
        ),
        'onlySmartPig' => array(
          'type' => array('boolean'),
          'options' => array(1,0),
          'default' => 0,
          'required' => false,
          'desc' => 'Only display the smartest of the three pigs',
        ),
      ),
    ),
  ),
);