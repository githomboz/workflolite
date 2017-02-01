<?php

class ScriptEngine
{

  private $_logger = null;
  private $_steps = null;
  private $_projectId = null;
  private $_project = null;

  public function __construct($projectId){
    $this->_projectId = $projectId;
  }

  public function logger($__METHOD__ = null){
    if(!$this->_logger) $this->_logger = new WFLogger($__METHOD__, __FILE__);
    $this->_logger->setScope(__CLASS__);
    return $this->_logger;
  }

  public function registerScript($script){
    $this->logger(__METHOD__)->addDebug('Entering ...');
    if($this->_validScript($script)){
      $this->logger(__METHOD__)->addDebug('Valid script');
      foreach($script as $step){
        $this->_steps[] = new ScriptStep($step, $this->_projectId);
      }
    } else {
      $this->logger(__METHOD__)->addError('Invalid script provided');
    }
    $this->logger(__METHOD__)->addDebug('Exiting ...');
    $this->logger()->sync();
    return $this;
  }

  public function addStep(array $step, $position = null, $targetStep = null){
    $this->logger(__METHOD__)->addDebug('Entering ...');
    if(ScriptStep::Validate($step)){
      $this->logger(__METHOD__)->addDebug('Step is valid', $step);
      if(!$position && !$targetStep){
        $this->_steps[] = new ScriptStep($step, $this->_projectId);
      }

    } else {
      $this->logger(__METHOD__)->addError('Invalid step provided');
    }
    $this->logger(__METHOD__)->addDebug('Exiting ...');
    $this->logger()->sync();
    return $this;
  }

  private function _validScript($script){
    if(!is_array($script)) return false;
    foreach($script as $step){
      $valid = ScriptStep::Validate($step);
      if(!$valid) return false;
    }
    return true;
  }

  public function getSteps(){
    return $this->_steps;
  }

  public function getStepsRaw(){
    $return = [];
    foreach($this->getSteps() as $step){
      $return[] = $step->get();
    }
    return $return;
  }

  public function save(){
    $this->project()->saveScript($this);
  }

  public function updateStep(ScriptStep $newStep){
    // Update Step
    foreach($this->getSteps() as $i => $step) {
      if($step->id() == $newStep->id()) $this->_steps[$i] = $newStep;
    }
    // Save Script
    $this->save();
  }

  public function removeStep(ScriptStep $removeStep){
    // Remove Step
    foreach($this->getSteps() as $i => $step) {
      if($step->id() == $removeStep->id()) unset($this->_steps[$i]);
      $this->_steps = array_values($this->_steps);
    }
    // Save Script
    $this->save();
  }

  public function project(){
    if(!$this->_project) $this->_project = Project::Get($this->_projectId);
    return $this->_project;
  }

  public function run(){
    $this->logger(__METHOD__)->addDebug('Entering ...');

    // Check for next step
    if($this->getSteps()){
      // If next step:
      // Execute the next step

      // Get headless curl request for $project->ScriptEngine->run()
      $this->runHeadless();
    } else {
      // If no next step, mark project complete
      $this->logger(__METHOD__)->addDebug('Request for "run()" but no steps available. Marked Complete.');
      $this->project()->setStatus('completed');
    }
    $this->logger(__METHOD__)->addDebug('Exiting ...');
    $this->logger()->sync();
    return $this;
  }

  public function runHeadless(){
    // Get headless curl request for $project->ScriptEngine->run()
  }

  public function getLogs($context = null){
    return $this->logger()->getMessages($context);
  }

}

class ScriptStep {

  private $_logs = ['errors' => [], 'debug' => [], 'info' => []];
  private $_logger = null;

  private $_updates = [];
  private $_raw = null;
  private $_current = null;
  private $_projectId = null;
  private $_project = null;

  public function __construct(array $step, $projectId)
  {
    if(self::Validate($step)){
      $this->_projectId = $projectId;
      $this->_initialize($step);
    }
  }

  public function logger($scope = null){
    if(!$this->_logger) $this->_logger = new WFLogger($scope, __FILE__);
    $this->_logger->setScope(__CLASS__);
    return $this->_logger;
  }

  private function _initialize($step){
    $this->_raw = $this->_current = $step;
  }

  public static function Validate($step){
    $requiredFields = ['callback','status','id'];
    foreach($requiredFields as $field){
      if(!isset($step[$field])) return false;
    }
    return true;
  }

  public function id(){
    return $this->_current['id'];
  }

  public function get($field = null){
    if($field){
      if(is_string($field) && isset($this->_current[$field])) return $this->_current[$field];
      return null;
    }
    return $this->_current;
  }

  public function update(){
    // Get project
    // Get Script engine
    // Update step
    return $this->project()->ScriptEngine()->updateStep($this);
  }

  public function delete(){
    // Get project
    // Get Script engine
    // delete step
    return $this->project()->ScriptEngine()->deleteStep($this);
  }

  public function registerUsage($context, $resource, $amount){
    $response = WFClientInterface::GetPayloadTemplate();

    $context = trim((string) $context);

    if(in_array($resource, ['duration','memory'])){
      $this->logger(__METHOD__)->addDebug('Resource is valid', $resource);
      if(is_numeric($amount)){
        $this->logger(__METHOD__)->addDebug('Amount is numeric', $amount);
        if(!empty($context)){
          $this->logger(__METHOD__)->addDebug('Context is set', $context);
          if(!isset($this->_current['usage'])) $this->_current['usage'] = [$resource => []];
          if(!isset($this->_current['usage'][$resource])) $this->_current['usage'][$resource] = [];
          $this->_current['usage'][$resource][] = [
            'context' => $context,
            'amount' => $amount
          ];
        } else {
          $this->logger(__METHOD__)->addError('Invalid context provided (`'.$context.'`)');
        }
      } else {
        $this->logger(__METHOD__)->addError('Amount must be numeric value. (`'.$amount.'`)');
      }
    } else {
      $this->logger(__METHOD__)->addError('Resource provided is invalid (`'.$resource.'`).');
    }

    $this->logger(__METHOD__)->addDebug('Exiting ...');
    $this->logger()->sync();
    $response['logger'] = $this->logger();
    $response['logs'] = $this->logger()->getLogsArray();
    $response['errors'] = $this->logger()->hasErrors(__METHOD__);
    return $response;
  }

  public function run(){
    $logs = WFClientInterface::GetLogsTemplate();
    $response = WFClientInterface::GetPayloadTemplate();

    $this->logger(__METHOD__)->addDebug('Entering ...');
    // Status "running"
    $this->setStatus('running');

    $isReadyResponse = $this->isReady();
    if(WFClientInterface::Valid_WFResponse($isReadyResponse)){
      $this->logger(__METHOD__)->merge($isReadyResponse['logger']);
      $this->logger(__METHOD__)->addDebug('Valid response `$this->isReady()`');

      if(!$isReadyResponse['errors']){
        $this->logger(__METHOD__)->addDebug('No errors in dependencies');
        // Verify Callback
        $callback = $this->get('callback');
        if(is_callable($callback)){
          $this->logger(__METHOD__)->addDebug('Callback valid', $callback);
          $updates = [
            // Add Execution Time
            'executeTime' => new MongoDate(),
          ];

          // Add Marker
          WFClientInterface::BenchmarkMarker('startCallbackExecution');

          // Execute Callback
          $payload = $this->get('payload');
          if(!$payload) $payload = $this->project()->payload();

          // In case there was no payload defined, define it for debug purposes
          $updates['payload'] = $payload;

          $callbackResponse = call_user_func_array($callback, array($payload));

          // Get Elapsed Time
          WFClientInterface::BenchmarkMarker('endCallbackExecution');
          $callbackElapsedTime = WFClientInterface::BenchmarkElapsedTime('startCallbackExecution','endCallbackExecution');

          // Add Usage
          $registerUsageResponse = $this->registerUsage($callback, 'duration', $callbackElapsedTime);
          if(WFClientInterface::Valid_WFResponse($registerUsageResponse)){
            $this->logger()->merge($registerUsageResponse['logger']);
            $this->logger(__METHOD__)->addDebug('Valid response `$this->registerUsage()`');
          } else {
            $this->logger(__METHOD__)->addError('Invalid response `$this->registerUsage()`');
          }

          // Validate Response
          if(WFClientInterface::Valid_WFResponse($callbackResponse)){
            $this->logger()->merge($callbackResponse['logger']);
            $this->logger(__METHOD__)->addDebug('Valid response `'.$callback.'`');

            if(!$callbackResponse['errors']){
              $this->logger(__METHOD__)->addDebug('No errors from callback `'.$callback.'`');
              // Add Results
              $updates['response'] = $callbackResponse['response'];

              // Add CompleteTime
              $updates['completeTime'] = new MongoDate();

              // Status "complete"
              $updates['status'] = 'complete';

              // Do update and reset updates array
              $this->setValue($updates);
            } else {
              $this->logger(__METHOD__)->addDebug('Step not ready. Scheduled for += 1 hour');
              // Scheduled Time Set += 1 Hour
              $updates['scheduleTime'] = new MongoDate(strtotime('+ 1 hour'));
              // Status "paused"
              $updates['status'] = 'paused';
              $this->setValue($updates);
            }
          } else {
            $this->logger(__METHOD__)->addError('Invalid callback response (`'.$callback.'`).');
          }
        } else {
          $this->logger(__METHOD__)->addError('Invalid callback provided (`'.$callback.'`).');
        }
      } else {
        // Depending on whether this was explicitly called or automatically called decides whether an error should be returned.
        $this->logger(__METHOD__)->addDebug('This step is not ready and will not run');
      }

    } else {
      $this->logger(__METHOD__)->addError('Invalid response `$this->isReady()`');
    }
    $this->setValue([
      'status' => 'running',
      'logs' => $this->logger()->getLogsArray()
    ]);


    $this->setValue(['logs' => $logs]);

    $this->logger(__METHOD__)->addDebug('Exiting ...');
    $this->logger()->sync();
    $response['logger'] = $this->logger();
    $response['logs'] = $this->logger(__METHOD__)->getLogsArray();
    $response['errors'] = $this->logger()->hasErrors(__METHOD__);
    return $response;
  }

  public function project(){
    if(!isset($this->_project)) $this->_project = Project::Get($this->_projectId);
    return $this->_project;
  }

  public function setStatus($status){
    $statuses = [
      'ready', // Hasn't been executed yet, but is ready
      'running', // Currently executing the given callback
      'error', // Error occurred. Stops run() from further executions
      'complete', // Callback has been executed successfully
      'waiting', // Awaiting a response from an external source
      'paused', // Stops run() from being able to process further steps of the script for a period of time
    ];
    if(in_array($status, $statuses)){
      $this->setValue('status', $status);
    }
    return $this;
  }

  public function setValue($key, $value = null){
    if(is_string($key)) $this->_current[$key] = $value;
    if(is_array($key)) $this->_current = array_merge((array) $this->_current, $key);
    $this->update();
    return $this;
  }

  public function isReady(){
    $response = WFClientInterface::GetPayloadTemplate();
    $this->logger(__METHOD__)->addDebug('Entering ...');
    $response['response']['isReady'] = false;

    // Dependency check
    $dependencies = $this->get('dependencies');
    if(empty($dependencies)) {
      $this->logger(__METHOD__)->addDebug('No dependencies found for this script');
      $response['response']['success'] = true;
      $response['response']['isReady'] = true;
    }
    else {
      // Check Dependencies

      // If Dependencies Set, Add Marker
      WFClientInterface::BenchmarkMarker('startDependencyCheck');
      // Convert if necessary
      if(isset($dependencies[0])) {
        $temp = [];
        foreach($dependencies as $i => $dependency) $temp[$dependency] = null;
        $dependencies = $temp;
      }

      $project = $this->project();
      $projectPayload = $project->payload();

      foreach($dependencies as $dependency => $result){
        $dependencyResponse = WFRequestParser::ProcessRequest($projectPayload, $dependency);
        if(WFClientInterface::Valid_WFResponse($dependencyResponse)){
          $this->logger()->merge($dependencyResponse['logger']);
          if(!$dependencyResponse['errors']){
            $dependencies[$dependency] = (bool) $dependencyResponse['response'];
            $this->logger(__METHOD__)->addDebug('Dependency `'.$dependency.'` returned (json encoded): ', $dependencyResponse['response']);
          } else {
            $this->logger(__METHOD__)->addError('Merging in process request logs (Includes errors)');
          }
        } else {
          $this->logger(__METHOD__)->addError('Invalid dependency response', $dependency);
        }
      }

      $dependencyErrors = !in_array(null, $dependencies) && !in_array(false, $dependencies);
      $response['response']['success'] = true;

      $this->logger(__METHOD__)->addDebug('Dependency errors exists', $dependencyErrors);

      if(!$dependencyErrors){
        $response['response']['isReady'] = true;
      } else {
        $response['response']['isReady'] = false;
      }

      WFClientInterface::BenchmarkMarker('endDependencyCheck');
      $elapsedTime = WFClientInterface::BenchmarkElapsedTime('startDependencyCheck', 'endDependencyCheck');
      // Set Dependency Usage
      $registerUsageResponse = $this->registerUsage('Dependency Check', 'duration', $elapsedTime);
      if(WFClientInterface::Valid_WFResponse($registerUsageResponse)){
        $this->logger()->merge($registerUsageResponse['logger']);
        $this->logger(__METHOD__)->addDebug('Valid response `$this->registerUsage()`');

      } else {
        $this->logger(__METHOD__)->addError('Invalid Response `$this->registerUsage`');
      }
    }

    $this->logger(__METHOD__)->addDebug('Exiting ...');
    $this->logger()->sync();
    $response['logger'] = $this->logger();
    $response['logs'] = $this->logger()->getLogsArray();
    $response['errors'] = $this->logger()->hasErrors(__METHOD__);
    return $response;
  }

  /**
   * Make sure updates, raw, and current are consistent with each other
   */
  public function reconciled(){

  }

}