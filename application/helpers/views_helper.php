<?php

function string_to_array($string){
  $r = array_map(function($n){ return trim($n); }, explode(',', $string));
  foreach($r as $i => $x) if(empty($x)) unset($r[$i]);
  return $r;
}

function array_to_string($array){
  return trim(implode(', ', $array));
}

function get_include($include, $data = array(), $extract = false){
  ob_start();
  if($extract) extract($data);
  if(file_exists($include)) include $include;
  return ob_get_clean();
}

function curl_get($url){
  $CI =& get_instance();
  $CI->load->library('curl');
  $CI->curl->create($url);
  $data = $CI->curl->execute();
  return $data;
}

function base64_remote_file($path_or_url){
  return base64_encode(file_get_contents($path_or_url));
}

function _url($url_relative_to_root, $relative_to_root_override = null){
  $relative = config_item('use_relative_urls');
  if(is_bool($relative_to_root_override)) $relative = $relative_to_root_override;
  if($relative){
    return '/' . ltrim($url_relative_to_root, '/');
  } else {
    $site_url = site_url();
    return rtrim($site_url, '/') . '/' . ltrim($url_relative_to_root, '/');
  }
}

/**
 * Find position of Nth occurance of search string
 * @param string $search The search string
 * @param string $string The string to seach
 * @param int $offset The Nth occurance of string
 * @return int or false if not found
 */
function strposOffset($string,$needle,$nth,$return_parts = false){
  $max = strlen($string);
  $n = 0;
  for($i=0;$i<$max;$i++){
    if($string[$i]==$needle){
      $n++;
      if($n>=$nth){
        break;
      }
    }
  }
  $arr[] = substr($string,0,$i);
  $arr[] = substr($string,$i+1,$max);

  return $return_parts ? $arr : $arr[0].$needle;
}

function register_script($script_relative_to_js_folder){
  $CI =& get_instance();
  if(!isset($CI->loadScripts)) $CI->loadScripts = array();

  $toAdd = array();

  if(is_array($script_relative_to_js_folder)){
    foreach($script_relative_to_js_folder as $script){
      $toAdd[] = clean_js_filename($script);
    }
  } elseif(is_string($script_relative_to_js_folder)){
    $toAdd[] = clean_js_filename($script_relative_to_js_folder);
  }

  foreach($toAdd as $script){
    if(!in_array($script, $CI->loadScripts)) {
      $CI->loadScripts[] = $script;
    } else {
      log_message('debug', 'Attempting to register an already registered script ('.$script.')');
    }
  }

  return get_registered_scripts();
}

function get_registered_scripts(){
  $CI =& get_instance();
  if(isset($CI->loadScripts)) return $CI->loadScripts;
  return array();
}

function get_registered_scripts_tags($use_relative_urls = true){
  $html = '';
  foreach (get_registered_scripts() as $script){
    if(is_relative_path($script)){
      $html .= '<script type="application/javascript" src="'._url('assets/js/'.$script, $use_relative_urls).'"></script>'."\n";
    } else {
      $html .= '<script type="application/javascript" src="'.$script.'"></script>'."\n";
    }

  }
  return $html;
}

function clean_js_filename($filename){
  if(substr($filename, -3, 3) !== '.js') return $filename . '.js';
  return $filename;
}

function is_relative_path($path_or_url){
  // check if first 2 charectors are "//" or if first 7 are "http://" or first 8 are "https://" to find out if absolute
  if(substr($path_or_url, 0, 2) == '//') return false;
  if(substr($path_or_url, 0, 7) == 'http://') return false;
  if(substr($path_or_url, 0, 8) == 'https://') return false;
  return true;
}

function offline_mode(){
  return config_item('work_offline_mode');
}