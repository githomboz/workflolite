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
      foreach($payload['recipients']['to'] as $recipient) if(!self::validateEmailRecipient($recipient)) $setCorrectly = false;
    }
    $tests['Recipients TO Validated'] = $setCorrectly;
    if(!in_array(false, $tests)) { if($return_results) return $tests; else return false; }

    // Test 3
    $setCorrectly = !isset($payload['recipients']['cc']) || isset($payload['recipients']['cc']) && is_array($payload['recipients']['cc']);
    if($setCorrectly && isset($payload['recipients']['cc'])) {
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

  public static function validateEmailRecipient($recipient){
    self::CI()->load->helper('email');
    $name_valid = !isset($recipient['name']) || isset($recipient['name']) && trim($recipient['name']) != '';
    $email_valid = isset($recipient['email']) && valid_email($recipient['email']);
    //var_dump($recipient, $name_valid, $email_valid);
    return $name_valid && $email_valid;
  }

  public function broadcast(){
    if($this->isValid()){
      $broadcast = $this->getValue('broadcast');
      $post = array(
        'time' => date('c'),
        'type' => static::$_trigger,
        'triggerId' => (string) $this->id(),
        'payload' => json_encode($this->getPayload()),
        'notified' => array('BOOL' => false),
        'callback' => $broadcast['webhook'].'?triggerId='.(string) $this->id()
      );

    }
    $results = addToDynamoDBTable('send_email', $post);
    return $results;
  }

  public static function ParseEmailRecipients($recipients, $return_errors = false){
    $recipientsData = array();

    // Handle recipients as string email address
    if(is_string($recipients)) $recipientsData[] = array('email' => $recipients);

    // Handle recipients as array of email addresses or properly formed addresses
    if(empty($recipientsData)) {
      if (is_array($recipients) && !empty($recipients)){
        // Check if format is correct
        if(is_string($recipients[0])) foreach($recipients as $recipient) $recipientsData[] = array('email' => $recipient);

        if(empty($recipientsData)){
          // Handle recipients as array of well formed recipients
          foreach($recipients as $recipient) {
            if(is_array($recipient) && isset($recipient['email'])) $recipientsData[] = $recipient;
          }
        }
      }
    }

    return !empty($recipientsData) ? $recipientsData : null;
  }

}