<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 1/7/17
 * Time: 10:52 AM
 */
class WFMessaging
{

  public static function _ValidateSimpleEmailRecipient($recipient){
    if(is_array($recipient)){
      CI()->load->helper('email');
      $name_valid = !isset($recipient['name']) || isset($recipient['name']) && trim($recipient['name']) != '';
      $email_valid = isset($recipient['email']) && valid_email($recipient['email']);
      return $name_valid && $email_valid;
    }
    return false;
  }

  public static function _ValidateSimpleEmailPayload($payload, $return_results = false){
    /**
     * Ordered list of tests to run. Does not run all tests. Stops once failure occurs.
     */
    $tests = array(
      'Recipients Set',
      'Recipients TO Validated',
      'Recipients CC Validated',
      'Recipients BCC Validated',
      'Sender Validated',
      //'Template Not Set / Valid',
      'Subject Validated',
      'Text & HTML Messages Validated',
      'Caller Not Set / Valid',
    );

    foreach($tests as $i => $testName) {
      unset($tests[$i]);
      $tests[$testName] = null;
    }

    /**
     * Start Tests
     */

    // Test 1
    $tests['Recipients Set'] = isset($payload['recipients']) && is_array($payload['recipients']);
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 2
    $setCorrectly = isset($payload['recipients']['to']) && is_array($payload['recipients']['to']);
    if($setCorrectly) {
      foreach($payload['recipients']['to'] as $recipient) if(!self::_ValidateSimpleEmailRecipient($recipient)) $setCorrectly = false;
    }
    $tests['Recipients TO Validated'] = $setCorrectly;
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 3
    $setCorrectly = !isset($payload['recipients']['cc']) || isset($payload['recipients']['cc']) && is_array($payload['recipients']['cc']);
    if($setCorrectly && isset($payload['recipients']['cc'])) {
      foreach($payload['recipients']['cc'] as $recipient) if(!self::_ValidateSimpleEmailRecipient($recipient)) $setCorrectly = false;
    }
    $tests['Recipients CC Validated'] = $setCorrectly;
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 4
    $setCorrectly = !isset($payload['recipients']['bcc']) || isset($payload['recipients']['bcc']) && is_array($payload['recipients']['bcc']);
    if($setCorrectly && isset($payload['recipients']['bcc'])) {
      foreach($payload['recipients']['bcc'] as $recipient) if(!self::_ValidateSimpleEmailRecipient($recipient)) $setCorrectly = false;
    }
    $tests['Recipients BCC Validated'] = $setCorrectly;
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 5
    $tests['Sender Validated'] = isset($payload['sender']) && self::_ValidateSimpleEmailRecipient($payload['sender']);
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 6
    $tests['Subject Validated'] = isset($payload['subject']) && trim($payload['subject']) != '';
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 7
    $html = isset($payload['html_message']) && !empty($payload['html_message']);
    $message = isset($payload['text_message']) && !empty($payload['text_message']);
    $tests['Text & HTML Messages Validated'] = $html || $message;
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 8
    $validJobId = !isset($payload['jobId']) || isset($payload['jobId']) && strlen($payload['jobId']) == 24;
    $tests['Caller Not Set / Valid'] = $validJobId;
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }


    /**
     * End Tests
     */

    if($return_results) return $tests;
    return !in_array(false, $tests);
  }

  public static function SimpleEmail($payload){
    $response = array(
      'response' => null,
      'logs' => ['debug'=>[], 'errors'=>[]],
      'errors' => false
    );

    $tests = self::_ValidateSimpleEmailPayload($payload, true);

    if(!in_array(false, $tests)){
      $to = [];
      $cc = null;
      $bcc = null;
      $from = $payload['sender']['email'];
      $from_name = isset($payload['sender']['name']) ? $payload['sender']['name'] : null;

      foreach($payload['recipients']['to'] as $recipient){
        $to[] = $recipient['email'];
      }

      if(isset($payload['recipients']['cc'])){
        foreach($payload['recipients']['cc'] as $recipient){
          $cc[] = $recipient['email'];
        }
      }

      if(isset($payload['recipients']['bcc'])){
        foreach($payload['recipients']['bcc'] as $recipient){
          $bcc[] = $recipient['email'];
        }
      }

      $html = false;
      $message = null;
      $alt_message = null;
      if(isset($payload['html_message'])) {
        $html = true;
        $message = $payload['html_message'];
        if(isset($payload['text_message'])) $alt_message = $payload['text_message'];
      }

      if(!$message){
        if(isset($payload['text_message'])) $message = $payload['text_message'];
      }

      $subject = $payload['subject'];

      CI()->load->helper('communications');

      $response['response'] = emailer($to, $from, $subject, $message, $from_name, $cc, $bcc, $html, $alt_message);
    } else {
      $errMsg = 'Tests have failed [';
      foreach($tests as $test => $success) if(!$success) $errMsg .= $test . ';';
      $response['logs']['errors'][] = $errMsg . ']';
      $response['errors'] = true;
    }

    return $response;
  }

  public static function SimpleSMS(){

  }

  public static function ConfirmationEmail(){

  }

  public static function GetConfirmationEmail($confirmationId){
    
  }


}