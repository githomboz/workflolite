<?php

require_once '../Trigger.php';

class TriggerMessagingEmail extends Trigger
{

  protected $recipients = array();

  public function __construct(array $data, Step $step){
    parent::__construct($data, $step);
  }

  public function initialize(){
    parent::initialize();

    if(!isset($this->_current['name'])){
      $this->_current['name'] = 'Email';
    }

    if(!isset($this->_current['description'])){
      $this->_current['description'] = <<<DESCRIPTION
Send email messages to recipients
DESCRIPTION;
    }

    if(!isset($this->_current['instructions'])){
      $this->_current['instructions'] = <<<INSTRUCTIONS
Add recipients and a message to add trigger to queue.
INSTRUCTIONS;

    }

  }

  public function addRecipient(Contact $contact){
    $this->recipients[$contact->getRecipientData()];
    return $this;
  }

  public function removeRecipient(Contact $contact){
    $this->recipients[$contact->getRecipientData()];
    return $this;
  }

  public static function sampleData(){
  }


}