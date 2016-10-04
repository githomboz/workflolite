<?php

function vdump(){
  if(ENVIRONMENT == 'production') echo '<pre>';
  foreach(func_get_args() as $var){
    var_dump($var);
  }
  if(ENVIRONMENT == 'production') echo '</pre>';
}

function debug_mode(){
  return config_item('site_debug_mode');
}
