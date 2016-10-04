<?php

namespace Scaffolding_Module {

  function add_dashboard_widget($module){
    $widget = array(
      'title' => 'Active Scaffolds',
      'sequence' => 11.123,
      'type' => 'module',
      'collapse' => true,
      'filepath' => APPPATH.'/modules/'.$module.'/widgets/dashboard_active_scaffolds.php',
      'filedata' => __NAMESPACE__.'\dashboard_widget_data'
    );
    return $widget;
  }

  function dashboard_widget_data(){
    return array(
      'active' => array(),
      'scaffolds' => array('adfa','adfa')
    );
  }

  function register_styles(){
    return array(
      array(
        'href' => '/admin/module_files/Scaffolding/assets:css:admin-scaffolding.css',
        'sequence' => 10
      ),
      array(
        'href' => '/admin/module_files/Scaffolding/assets:css:jquery.ui.css',
      )
    );
  }

  function register_scripts(){

  }

}

