<?php
$common_docs = array(
  '$_GET_params' => array(
    'docs' => array(
      'options' => array(0,1),
      'default' => 0,
      'availEnvironments' => 'all',
      'desc' => 'If not automatically expressed, display the documentation link for the current resource endpoint',
    ),
    'show_args' => array(
      'options' => array(0,1),
      'default' => 0,
      'availEnvironments' => 'all',
      'desc' => 'If not automatically expressed, display the arguments that have been passed into the resource endpoint',
    ),
    'show_query' => array(
      'options' => array(0,1),
      'default' => 0,
      'availEnvironments' => 'all',
      'desc' => 'If not automatically expressed, display the query that have been produced based on args passed into the resource endpoint',
    ),
    'example_uri' => array(
      'options' => array(0,1),
      'default' => 0,
      'availEnvironments' => 'all',
      'desc' => 'If not automatically expressed, display an example of the URI resource endpoint expected',
    ),
    'example_query' => array(
      'options' => array(0,1),
      'default' => 0,
      'availEnvironments' => 'all',
      'desc' => 'If not automatically expressed, display the query string that the api resource endpoint expects',
    ),
    'benchmark' => array(
      'options' => array(0,1),
      'default' => 0,
      'availEnvironments' => 'all',
      'desc' => 'If not automatically expressed, display the time that it took to process and return output to resource endpoint',
    ),
    'select' => array(
      'type' => 'string',
      'default' => null,
      'availEnvironments' => 'all',
      'desc' => 'Limit the fields called from db to lessen network I/O and traffic.  Comma separated list of field names. Note: Only works on record sets.',
    ),
    'fields' => array(
      'type' => 'string',
      'default' => null,
      'availEnvironments' => 'all',
      'desc' => 'Limit the fields returned to api endpoint to lessen network I/O and traffic.  Comma separated list of field names. Note: Only works on record sets.',
    ),
    'debug' => array(
      'options' => array(0,1,2),
      'type' => 'string',
      'default' => 0,
      'availEnvironments' => 'all',
      'desc' => 'Display debug information',
    ),
  )
);