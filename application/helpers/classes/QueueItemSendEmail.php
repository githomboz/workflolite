<?php

require_once 'TriggerQueueItem.php';

class QueueItemSendEmail extends TriggerQueueItem
{

  protected static $_trigger = 'messaging-email';

  public function __construct(array $data){
    parent::__construct($data);
  }

  protected static function _validatePayload($payload, $return_results = false){
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
      'Message Validated',
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
      foreach($payload['recipients']['to'] as $recipient) if(!self::validateEmailRecipient($recipient)) $setCorrectly = false;
    }
    $tests['Recipients TO Validated'] = $setCorrectly;
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 3
    $setCorrectly = !isset($payload['recipients']['cc']) || isset($payload['recipients']['cc']) && is_array($payload['recipients']['cc']);
    if($setCorrectly && $payload['recipients']['cc']) {
      foreach($payload['recipients']['cc'] as $recipient) if(!self::validateEmailRecipient($recipient)) $setCorrectly = false;
    }
    $tests['Recipients CC Validated'] = $setCorrectly;
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 4
    $setCorrectly = !isset($payload['recipients']['bcc']) || isset($payload['recipients']['bcc']) && is_array($payload['recipients']['bcc']);
    if($setCorrectly && isset($payload['recipients']['bcc'])) {
      foreach($payload['recipients']['bcc'] as $recipient) if(!self::validateEmailRecipient($recipient)) $setCorrectly = false;
    }
    $tests['Recipients BCC Validated'] = $setCorrectly;
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 5
    $tests['Sender Validated'] = isset($payload['sender']) && self::validateEmailRecipient($payload['sender']);
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 6
    $tests['Subject Validated'] = isset($payload['subject']) && trim($payload['subject']) != '';
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 7
    $tests['Message Validated'] = isset($payload['message']) && trim($payload['message']) != '';
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

  public static function validateEmailRecipient($recipient){
    self::CI()->load->helper('email');
    $name_valid = !isset($recipient['name']) || isset($recipient['name']) && trim($recipient['name']) != '';
    $email_valid = isset($recipient['email']) && valid_email($recipient['email']);
    return $name_valid && $email_valid;
  }

  public function broadcast(){
    if($this->isValid()){
      $post = array(
        'time' => date('c'),
        'type' => static::$_trigger,
        'payload' => json_encode($this->getPayload()),
        'notified' => array('BOOL' => false)
      );

    }
    $results = addToDynamoDBTable('send_email', $post);
    return $results;
  }

}