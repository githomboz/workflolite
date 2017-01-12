<?php

require_once 'Step.php';

class Steps
{

  protected $_task = null;

  private $_steps = array();

  public function __construct(Task2 $task){
    $this->_steps = $task->getValue('steps');
    foreach($this->_steps as $i => $step) $this->_steps[$i] = new Step($step);
    $this->_task = $task;
  }

  public function process(){

  }

  public function getAvailableNextStepId(){

  }

  public function getStep($id){

  }

  public static function _processTestSteps(){
    // System listens for post to registered webhook
    // System validates incoming data
    // Job is created
    // Client is looked up or created
    // Client is added to job
    // Job is Started
      // Order Request Analyzed
        // Validate Social Handle
        // Validate Order Amount
        // Validate Order Price
      // Fraud Analysis
        // Analyze Country
        // Analyze IP Address
        // Analyze order ip vs customer ip
        // Analyze blacklist for ip address
        // Analyze blacklist for email address
      // Manual Fraud Approval
        // Email Fraud Report
        // Mark Pending
        // Register long poll for fraud approval received
      // Process Order
        // Place Order
        // Email is sent to client
      // Mark In Progress
        // Change Status
      // Register Long Poll subscription for Completion
        // Upon completion, send completion email
      // Text message
  }

}