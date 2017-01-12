<?php

function emailer($to, $from, $subject, $message, $from_name = '', $cc = NULL, $bcc = NULL, $html = false, $alt_message = null){
  $CI =& get_instance();
  $CI->load->library('email');

  if(!$html) {
    $CI->config->set_item('mailtype', 'text');
  }

  $CI->email->from($from, (string) $from_name);
  $CI->email->to($to);
  if($cc) $CI->email->cc($cc);
  if($bcc) $CI->email->bcc($bcc);

  $CI->email->subject($subject);
  $CI->email->message($message);

  if($alt_message) $CI->email->set_alt_message($alt_message);

  $CI->email->send();

  return $CI->email->print_debugger();
}

function email_template($email_address, $data, $template_name, $cc = null, $bcc = null){
  $response = array(
    'errors' => false,
    'response' => null
  );
  $CI =& get_instance();
  $CI->load->helper('email');
  if(valid_email($email_address)){
    $email_senders = config_item('email_senders');
    if(isset($email_senders[$template_name])){
      $template = $email_senders[$template_name];
      $d = array('subject' => null, 'message' => null);
      foreach($d as $k => $v){
        // Grab raw subject or message field from $template
        if(isset($template[$k])) $d[$k] = $template[$k];
        // Grab parsed subject or message template from file if filename matching subject_template or message_template exists
        if(empty($d[$k]) && isset($template[$k.'_template']) && file_exists($template[$k.'_template'])){
          $d[$k] = get_include($template[$k.'_template'], $data);
        }
        // Grab parsed subject or message template from file by testing if the  $template_name in the default directory exists
        $test_filename = APPPATH.'views/templates/email_'.$template_name.'_'.$k.'.php';
        if(empty($d[$k]) && file_exists($test_filename)){
          $d[$k] = get_include($test_filename, $data);
        }

      }
      $response['response'] = array(
        'template_content' => $d,
      );
      // generate emailer fields
      $response['response']['send_response'] = emailer($email_address, $template['from'], $d['subject'], $d['message'], $template['name'], $cc, $bcc);

    } else {
      $response['errors'][] = 'Invalid template name given: ('.$template_name.')';
    }
  } else {
    $response['errors'][] = 'Invalid Email address given: ('.$email_address.')';
  }

  return $response;
}