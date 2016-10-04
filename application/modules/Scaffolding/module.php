<?php
// Module Details
$moduleDetails['name'] = 'Scaffolding Platform';
$moduleDetails['module'] = 'Scaffolding';
$moduleDetails['description'] = 'Create fast and easy scaffolding for entry into collections or specific fields as nested documents';
$moduleDetails['version'] = (float) '1.0';

// Author Details
$moduleDetails['author'] = 'Jahdy Lancelot';
$moduleDetails['url'] = 'http://www.jahdy1.com';
$moduleDetails['social']['twitter'] = '@jahdy1';

// Admin Section
$moduleDetails['admin_section'] = array(
  'default_section' => 1,
  'common_scripts' => array(),
  'common_styles' => array(),
  'sub_nav' => array(
    array(
      'text' => 'Manage',
      'sectionId' => 1,
    ),
    array(
      'text' => 'Create',
      'sectionId' => 2,
    ),
  ),
  'sections' => array(
    array(
      'sectionId' => 1,
      'title' => 'Manage Scaffolding',
      'filepath' => 'manage_scaffolding',
      'filedata' => '',
      'styles' => array(),
      'scripts' => array(),
    ),
    array(
      'sectionId' => 2,
      'title' => 'Create New Scaffolding',
      'filepath' => 'create_new_scaffolding.php',
      'filedata' => '',
      'styles' => array(),
      'scripts' => array(),
    ),
  ),
);

$moduleDetails_AddSettings = array(
  'scaffolding_data_types' => array(
    'datetime','bool','string','float','integer','other',
  ),
  'scaffolding_form_type' => array(
    'html5_date','input','textbox','checkbox','radio','select',
  ),
  'scaffolding_validators' => array(),
);

$moduleDetails_Schema = array(
  'scaffoldingTemplates' => array(
    'nativeId',
    'name',
    'description',
    'collection',
    'inputField',
    'fields',
  ),
);

$moduleDetails_SampleRecords = array(
  array(
    'nativeId' => 'XXXXXX001',
    'name' => 'Sample Cars Collection',
    'description' => 'A scaffolding setup for a new sample cars collection',
    'collection' => 'sample_cars',
    'inputField' => null,
    'fields' => array(
      array(
        'field' => 'dateAdded',
        'type' => 'datetime',
        'formType' => 'html5_date',
        'roles' => array('admin'),
        'typeCast' => 'MongoDate',
        'required' => true,
        'validation' => array(), // Functions to call (in order).  These functions make sure that the value proposed is valid
        'processor' => array(), // Functions to call (in order). These functions make sure that the value set is properly handled/formatted before storing
        'setval' => array(), // Functions to call (in order). These function get/create/generate a value at scaffolding's load
      ),
      array(
        'field' => 'make',
        'type' => 'string',
        'formType' => 'input',
        'validation' => array(),
        'processor' => array(),
        'setval' => array(),
        'required' => true,
      ),
      array(
        'field' => 'model',
        'type' => 'string',
        'formType' => 'input',
        'validation' => array(),
        'processor' => array(),
        'setval' => array(),
        'required' => true,
      ),
    )
  )
);

$mfile = $moduleDetails['module'].'.php';
if(file_exists($file)) include_once $mfile;