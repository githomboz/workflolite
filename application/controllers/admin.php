<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends Admin_Controller {

  private static $CI = null;
  private static $nav_items = array();
  private static $styles = array();
  private static $scripts = array();
  private static $dashboard_widgets = array();

  public function __construct(){
    parent::__construct();
    add_action('admin_nav','Admin::init_nav', 1);
    add_action('admin_nav','Admin::get_nav_html', 100);
    add_action('load_footer_styles','Admin::init_styles', 1);
    add_action('load_footer_styles','Admin::get_registered_styles_html', 100);
    add_action('load_footer_scripts','Admin::init_scripts', 1);
    add_action('load_footer_scripts','Admin::get_registered_scripts_html', 100);
    register_script('jquery');
    register_script('lodash');
    register_script('eventemitter2');
    register_script('standardizr');
    register_script('admin');
  }

  public function index(){
    if(admin_logged_in()){
      $data = array();

      add_action('dashboard_widgets', 'Admin::init_dashboard_widgets', 1);
      add_action('dashboard_widgets', 'Admin::sort_dashboard_widgets', 100);

      self::discover_module_dashboard_widgets();

      do_action('dashboard_widgets');

      $data['dashboard_widgets'] = self::get_dashboard_widgets();

      $this->p('dashboard', $data, true);
      //$this->view('admin/admin_dashboard', $data);
    } else redirect('admin/login');
  }

  private function _get_admin_page($path, $core = false){
    $file = APPPATH.'views/admin/pages/'.($core ? 'core/':'custom/').str_replace('.php', '', $path).'.php';
    if(file_exists($file)) return $file;
    return false;
  }

  public function p($p, $data = array(), $core = false){
    if(admin_logged_in()){
      $file = $this->_get_admin_page($p, $core);
      if($file){
        $data['admin_page'] = $file;
        $this->view('admin/admin_page', $data);
      } else {
        show_404();
      }
    } else redirect('admin');
  }

  public function modules(){
    if(admin_logged_in()){
      $data = array(
        'modules' => $this->modules
      );

      if($action = $this->input->get('action')){
        if($module = $this->input->get('mod')){
          if($action === 'feedback'){
            $data['notification_box']['message'] = 'The <strong>'.$module.'</strong> module has been successfully ';
            switch($this->input->get('done')){
              case 'activate':
                $data['notification_box']['message'] .= 'activated.';
                break;
              case 'deactivate':
                $data['notification_box']['message'] .= 'deactivated.';
                break;
              case 'uninstall':
                $data['notification_box']['message'] .= 'uninstalled.';
                break;
              case 'install':
                $data['notification_box']['message'] .= 'installed.';
                break;
            }
            $data['notification_box']['classes'] = 'success';
          } elseif($action === 'settings'){
            // Handle redirect to modules settings page
            $admin_section = $this->modules[$module]->getMeta('admin_section');
            $default = isset($admin_section['default_section']) ? '/'.$admin_section['default_section'] : '';
            redirect('admin/settings/'.$module.$default);
          } else {
            $flags = '?action=feedback&mod='.$module.'&done='.$action;
            switch($action){
              case 'activate':
                $this->modules[$module]->activate();
                break;
              case 'deactivate':
                $this->modules[$module]->deactivate();
                break;
              case 'uninstall':
                $this->modules[$module]->uninstall();
                break;
              case 'install':
                $this->modules[$module]->install();
                break;
            }
            redirect('admin/modules'.$flags);
          }
        }
      }

      $this->p('modules', $data, true);
      //$this->view('admin/admin_modules', $data);
    } else redirect('admin');
  }

  public function users(){
    if(admin_logged_in()) {
      $data = array();

      $this->p('users', $data, true);
      //$this->view('admin/admin_users', $data);
    } else redirect('admin');
  }

  public function settings($module = null, $sectionId = null){
    if(admin_logged_in()){
      if($module){
        $data = array();
        $data['module'] =& $this->modules[$module];
        if($data['module']){
          $data['meta'] = $meta = $data['module']->getMeta();

          // Register Common Styles
          $scripts = $data['module']->getRegisteredSettingsScripts();
          if(!empty($scripts)){
            if(isset($scripts[0])){
              // Loop through
              foreach($scripts as $script){
                self::$scripts[] = $script;
              }
            } else {
              // Set directly
              self::$scripts[] = $scripts;
            }
          }

          // Register Common Scripts
          $styles = $data['module']->getRegisteredSettingsStyles();
          if(!empty($styles)){
            if(isset($styles[0])){
              // Loop through
              foreach($styles as $style){
                self::$styles[] = $style;
              }
            } else {
              // Set directly
              self::$styles[] = $styles;
            }
          }

          // Register Sub Nav
          $data['sub_nav'] = $meta['admin_section']['sub_nav'];

          // If page not set, use default
          $data['default_section'] = $default_section = isset($meta['admin_section']['default_section']) ? $meta['admin_section']['default_section'] : 1;

          $grabSection = $sectionId ? $sectionId : $default_section;

          foreach($meta['admin_section']['sections'] as $section){
            if($section['sectionId'] == $grabSection){
              $data['section'] = $section;

              // Register Specific Styles
              // Register Specific Scripts



            }
          }

          $this->p('settings', $data, true);
          //$this->view('admin/admin_settings', $data);
        } else {
          show_404();
        }
      } else {
        show_404();
      }
    } else redirect('admin');
  }

  public function module_files($module, $file_path){
    $mime_types = array(
      'css' => 'text/css',
      'js' => 'application/javascript',
      'csv' => 'text/csv',
      'html' => 'text/html',
      'xml' => 'text/xml',
      'rtf' => 'text/rtf',
      'png' => 'image/png',
    );

    $extension = substr($file_path, (strrpos($file_path,'.') + 1));
    $module_path = $this->modules[$module]->dir();
    $file_path = $module_path . '/' . str_replace(':','/', $file_path);
    if(file_exists($file_path)){
      if(isset($mime_types[$extension])) header('Content-type: ' . $mime_types[$extension]);
      echo get_include($file_path);
    }
    echo '';
    //var_dump($module, $module_path, $file_path, file_exists($file_path));
  }

  public static function init_nav(){
    self::_init_nav();
    self::add_nav_item(array(
      'text' => 'Dashboard',
      'relurl' => 'admin',
      'position' => -8,
    ));
    self::add_nav_item(array(
      'text' => 'Modules',
      'relurl' => 'admin/modules',
      'position' => -7,
      'access' => 20
    ));
    self::add_nav_item(array(
      'text' => 'Users',
      'relurl' => 'admin/users',
      'position' => -6,
      'access' => 20
    ));

    return self::get_nav_items();
  }

  private static function _init_nav(){
    self::$CI =& get_instance();
    if(!isset(self::$CI->AdminNav)) self::$CI->AdminNav = array();
  }

  public static function get_nav_items(){
    self::_init_nav();
    self::add_nav_item(array(
      'text' => 'Log Out',
      'relurl' => 'admin/logout',
      'position' => 100
    ));
    foreach(self::$CI->AdminNav as $i => $link) {
      if(strpos(current_url(), $link['relurl']) !== false) {
        self::$CI->AdminNav[$i]['classes'] = 'active';
      }
    }
    return self::$CI->AdminNav;
  }

  public static function add_nav_item($item){
    self::_init_nav();

    // Set href
    if(!isset($item['href'])) $item['href'] = _url($item['relurl']);

    // If position not set
    if(!isset($item['position'])) $item['position'] = 10;

    // If access not set
    if(!isset($item['access'])) $item['access'] = 10;

    // Check if already exists
    $found = false;
    foreach(self::$CI->AdminNav as $navItem){
      if($navItem['href'] == $item['href']) $found = true;
    }

    // Add to array if access is authorized
    if(!$found && admin_data('access') >= $item['access']) self::$CI->AdminNav[] = $item;

    // Reorder the array by position
    $ordered = array();
    foreach(self::$CI->AdminNav as $i => $navItem){
      if(!isset($ordered[$navItem['position']])) $ordered[$navItem['position']] = array();
      $ordered[$navItem['position']][] = $navItem;
    }

    // Sort items by key
    ksort($ordered);

    // Reformat and normalize ordered back to AdminNav
    $AdminNav = array();
    foreach($ordered as $position => $items){
      foreach($items as $item){
        $AdminNav[] = $item;
      }
    }

    self::$CI->AdminNav = $AdminNav;
  }

  public static function set_nav_items($items){
    self::_init_nav();
    foreach($items as $item) self::add_nav_item($item);
  }

  public static function get_nav_html(){
    self::_init_nav();
    $output = "\n".'<nav class="admin-nav clearfix">'."\n\t".'<ul>';
    foreach(self::get_nav_items() as $i => $item) {
      $output .= "\n\t\t".'<li><a href="' . $item['href'] . '">';
      $output .= $item['text'];
      $output .= '</a></li>';
    }
    $output .= "\n\t".'</ul>'."\n".'</nav>'."\n";
    echo $output;
  }

  public static function init_styles(){
    self::$styles[] = array(
      'href' => 'admin_styles.css',
      'sequence' => 0,
    );
  }

  public static function get_registered_styles_html(){
    $sorted = array();

    // Reorder files
    foreach(self::get_registered_styles() as $style){
      if(!isset($style['sequence'])) $style['sequence'] = 20;
      if(!isset($sorted[$style['sequence']])) $sorted[$style['sequence']] = array();
      $sorted[$style['sequence']][] = $style;
    }

    // Sort data
    ksort($sorted);

    // Reflow into registered styles array
    self::$styles = array();
    foreach($sorted as $tier => $styles){
      foreach($styles as $style) self::$styles[] = $style;
    }

    $output = '';
    foreach(self::get_registered_styles() as $i => $style){
      if(isset($style['href'])){
        $output .= "\n".'<link ';
        $output .= 'type="' . (isset($style['type']) ? $style['type'] : 'text/css') . '" ';
        $output .= 'rel="' . (isset($style['rel']) ?  $style['rel'] : 'stylesheet') . '" ';
        $output .= 'href="' . ($style['href'][0] == '/' ? $style['href'] : _url('assets/css/'.$style['href'])) . '" ';
        $output .= '>';
      }
    }
    echo $output;
  }

  public static function get_registered_styles(){
    return self::$styles;
  }

  public static function set_registered_styles($styles){
    self::$styles = $styles;
  }

  public static function init_scripts(){
    self::$scripts[] = array(
      'src' => 'admin_main.js',
      'sequence' => 0,
    );
  }

  public static function get_registered_scripts_html(){
    $sorted = array();

    // Reorder files
    foreach(self::get_registered_scripts() as $style){
      if(!isset($style['sequence'])) $style['sequence'] = 20;
      if(!isset($sorted[$style['sequence']])) $sorted[$style['sequence']] = array();
      $sorted[$style['sequence']][] = $style;
    }

    // Sort data
    ksort($sorted);

    // Reflow into registered styles array
    self::$scripts = array();
    foreach($sorted as $tier => $styles){
      foreach($styles as $style) self::$scripts[] = $style;
    }

    $output = '';
    foreach(self::get_registered_scripts() as $i => $script){
      if(isset($script['src'])){
        $output .= "\n".'<script ';
        $output .= 'type="' . (isset($script['type']) ? $script['type'] : 'text/javascript') . '" ';
        $output .= 'src="' . ($script['src'][0] == '/' ? $script['src'] : _url('assets/js/'.$script['src'])) . '" ';
        $output .= '></script>';
      }
    }
    echo $output;
  }

  public static function get_registered_scripts(){
    return self::$scripts;
  }

  public static function set_registered_scripts($scripts){
    self::$scripts = $scripts;
  }

  public static function init_dashboard_widgets(){
    $system_widgets = array(
      array(
        'title' => 'Automatic Updates',
        'sequence' => 0, // The order you want the widget to appear in.  0 - 1 are reserved for system widgets
        'style' => 'alert', // The type of style you want added to the parent container of the widget,,
        'type' => 'system', // What type of module is this? Could be system, module, or other
        'collapse' => false, // Allow user to hide this widget
        'filepath' => 'dashboard_automatic_updates',
        'filedata' => 'Admin::dashboard_automatic_updates',
      ),
      array(
        'title' => 'User Account',
        'sequence' => 0.1,
        'type' => 'system',
        'collapse' => true,
        'filepath' => 'dashboard_user_profile',
        'filedata' => 'Admin::dashboard_user_profile',
      ),
    );
    if(empty(self::$dashboard_widgets)) self::$dashboard_widgets = $system_widgets; else self::$dashboard_widgets = array_merge(self::$dashboard_widgets, $system_widgets);
  }

  public static function sort_dashboard_widgets(){
    // return true;

    $sorted = array();
    foreach(self::get_dashboard_widgets() as $i => $widget){
      if(!isset($widget['sequence'])) $widget['sequence'] = 20;
      // convert sequence to float
      $widget['sortOrder'] = (float) $widget['sequence'];
      unset($widget['sequence']);
      if(!isset($sorted[$widget['sortOrder']])) $sorted[(string)$widget['sortOrder']] = array();
      // Add each widget to multi-dimensional array where the sequence is the key and the value is an array of widgets assigned to that sequence number
      $sorted[(string)$widget['sortOrder']][] = $widget;
    }

    // Sort the array by keys
    ksort($sorted);

    // grab array values and set that value back to $dashboard_widgets
    self::$dashboard_widgets = array();
    foreach($sorted as $sequence => $widgets){
      foreach($widgets as $widget){
        self::$dashboard_widgets[] = $widget;
      }
    }
    return self::get_dashboard_widgets();
  }

  public static function get_dashboard_widgets(){
    return self::$dashboard_widgets;
  }

  public static function add_dashboard_widget($widget){
    self::$dashboard_widgets[] = $widget;
    return self::$dashboard_widgets;
  }

  public static function dashboard_automatic_updates(){
    return array(
      'updates_available' => true,
      'update_count' => 3,
      'updates' => array(

      )
    );
  }

  public static function discover_module_dashboard_widgets(){
    $CI =& get_instance();
    foreach($CI->modules->getAllModules() as $module){
      if($module->isActive()){
        if($widget = $module->getDashboardWidget()){
          self::add_dashboard_widget($widget);
        }
      }
    }
  }

  public static function register_module_settings_scripts(){
    $CI =& get_instance();
    foreach($CI->modules->getAllModules() as $module){
      if($module->isActive()){
      }
    }
  }

  public static function dashboard_user_profile(){
    return array(

    );
  }


}