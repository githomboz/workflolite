<?php

class ScriptEngine
{

  private $_steps = null;
  private $_status = null;
  private $_projectId = null;
  private $_project = null;
  private $_script = null;

  private $_updates = [];

  private $_scriptsRegistered = false;

  public function __construct($projectId){
    $this->_projectId = $projectId;
    $this->loadProjectScript(false);
  }

  public function loadProjectScript($run = true){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    if(!$this->_scriptsRegistered){
      $logger->setLine(__LINE__)->addDebug('Loading script data from project ...');
      $script = $this->project()->getValue('script');
      $logger->setLine(__LINE__)->addDebug('Preparing to register script ...', $script);
      $this->registerScript($script);
      if($run) {
        $logger->setLine(__LINE__)->addDebug('Preparing to attempt to run() script');
        $this->run();
      }
    } else {
      $logger->setLine(__LINE__)->addDebug('Project script has already been loaded');
    }
    $logger->setLine(__LINE__)->addDebug('Exiting ...');
  }

  public function stepExists($compareStep){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ... `$compareStep`', $compareStep);
    if($compareStep instanceof ScriptStep) $compareStep = $compareStep->get();
    $compareStep = ScriptStep::Validate((array) $compareStep) ? (array) $compareStep : null;

    if($compareStep){
      foreach($this->_steps as $step){

        if((string) $step->get('id') == (string) $compareStep['id']) {
          $logger->setLine(__LINE__)->addDebug('Exiting ... `[$step->get(), $compareStep]`', [$step->get(), $compareStep]);
          return true;
        }
      }
      $logger->setLine(__LINE__)->addDebug('No matches found; Returning false.');
      $logger->setLine(__LINE__)->addDebug('Exiting ...');
      return false;
    }
    $logger->setLine(__LINE__)->addDebug('Exiting ... `$compareStep`', $compareStep);
    return null;
  }

  /**
   * Accepts a script array the features both the status, and the steps for the given script and sets instance variables
   * _script, _status, & _steps.
   * @param $script The array featuring the indexes status and steps
   * @return $this
   */
  public function registerScript($script){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...', $script);
    if(self::ValidScript($script)){
      $logger->setLine(__LINE__)->addDebug('Valid script');
      $this->_script = $script;
      $this->_status = $script['status'];
      if(!empty($this->_script['steps'])){
        foreach($this->_script['steps'] as $step){
          if($this->stepExists($step) === false){
            $logger->setLine(__LINE__)->addDebug('New step added');
            $this->_steps[] = new ScriptStep($step, $this->_projectId);
          } else {
            $logger->setLine(__LINE__)->addDebug('New step already exists');
          }
        }
        $this->_scriptsRegistered = true;
        $logger->setLine(__LINE__)->addDebug('Response $this->_steps[0] instanceof ScriptStep', [$this->_steps[0] instanceof ScriptStep]);
        $logger->setLine(__LINE__)->addDebug(count($this->_steps) . ' step(s) registered');
      } else {
        $logger->setLine(__LINE__)->addDebug('No steps to register in this script');
      }
    } else {
      $logger->setLine(__LINE__)->addError('Invalid script provided');
    }
    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $logger->sync();
    return $this;
  }

  public static function ValidScript($script){
    if(!is_array($script)) return false;
    if(!isset($script['steps'])) return false;
    if(!isset($script['status'])) return false;
    foreach($script['steps'] as $step){
      $valid = ScriptStep::Validate($step);
      if(!$valid) return false;
    }
    return true;
  }

  public static function TransformScriptForDb($script){
    foreach($script['steps'] as $i => $step){
      $script['steps'][$i]['payload'] = json_decode(json_encode($step['payload']), true);
    }
    return $script;
  }

  /**
   * Whether or not the script is ready to run or is paused or complete
   */
  public function ready(){
    return in_array($this->getStatus(), ['ready','running']);
  }

  public function statusPause(){
    $this->setStatus('paused')->save();
  }

  public function statusComplete(){
    $this->setStatus('completed')->save();
  }

  public function statusCancelled(){
    $this->setStatus('cancelled')->save();
  }

  public function statusReady(){
    $this->setStatus('ready')->save();
  }

  public function statusRunning(){
    $this->setStatus('running')->save();
  }

  public function getSteps(){
    return $this->_steps;
  }

  public function getStatus(){
    return $this->_status;
  }

  public function setStatus($status){
    if(in_array($status, self::GetStatuses())) {
      $this->_status = $status;
      $this->_updates['statusUpdated'] = $status;
    }
    return $this;
  }

  public function getStepsRaw(){
    $return = ['steps' => [], 'status' => $this->_status];
    foreach($this->getSteps() as $step){
      $return['steps'][] = $step->get();
    }
    return $return;
  }

  public function save(){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $logger->setLine(__LINE__)->addDebug('$this->_updates', $this->_updates);
    if(!empty($this->_updates)){
      $result = $this->project()->saveScript($this->getStepsRaw());
      if($result){
        $logger->setLine(__LINE__)->addDebug('Overwriting current script and status');
        $this->registerScript($this->getStepsRaw());
      } else {
        $logger->setLine(__LINE__)->addDebug('Script not saved. See next message.');
      }
      $logger->setLine(__LINE__)->setScope('save -> $this->project->saveScript')->addDebug('Response', $result);
      $logger->sync();
      $this->_updates = [];
    } else {
      $logger->setLine(__LINE__)->addDebug('No script updates to save');
    }
    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    return $this;
  }

  public function addStep(array $step, $position = null, $targetStep = null){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    if(ScriptStep::Validate($step)){
      $logger->setLine(__LINE__)->addDebug('Step is valid', $step);
      if(!$position && !$targetStep){
        $step = new ScriptStep($step, $this->_projectId);
        $this->_steps[] = $step;
      }
      $this->_updates['stepAdded'][] = $step;
    } else {
      $logger->setLine(__LINE__)->addError('Invalid step provided');
    }
    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $logger->sync();
    if(!empty($this->_updates)) $this->save(); // @todo If updates
    return $this;
  }

  public function updateStep(ScriptStep $newStep){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    // Update Step
    foreach($this->getSteps() as $i => $step) {
      if($step->id() == $newStep->id()) {
        if(json_encode($step->get()) != json_encode($newStep->get())){
          $this->_steps[$i] = $newStep;
          $this->_updates['stepUpdated'][] = $step;
          $logger->setLine(__LINE__)->addDebug('Step has been updated [from, to, same]', [$step->get(), $newStep->get(), json_encode($step->get()) == json_encode($newStep->get())]);
        } else {
          $logger->setLine(__LINE__)->addDebug('Step has not changed [from, to, same]', [$step->get(), $newStep->get(), json_encode($step->get()) == json_encode($newStep->get())]);
        }
      }
    }
    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $logger->sync();
    if(!empty($this->_updates)) $this->save(); // @todo If updates
    return $this;
  }

  public function removeStep(ScriptStep $removeStep){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    // Remove Step
    foreach($this->getSteps() as $i => $step) {
      if($step->id() == $removeStep->id()) {
        unset($this->_steps[$i]);
        $this->_updates['stepRemoved'][] = $step;
      }
      $this->_steps = array_values($this->_steps);
    }
    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $logger->sync();
    if(!empty($this->_updates)) $this->save(); // @todo If updates
    return $this;
  }

  public function project(){
    if(!$this->_project) $this->_project = Project::Get($this->_projectId);
    return $this->_project;
  }

  public function run(){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');

    if($this->ready()){
      // Check for next step
      if($this->getSteps()){
        $logger->setLine(__LINE__)->addDebug('Script available and ready. Running next isReady step.');
        // If next step:
        $limitPerRequestLimit = 1;
        $limitPerRequestCount = 0;
        foreach($this->getSteps() as $i => $step){
          if($limitPerRequestCount < $limitPerRequestLimit && $step->ready()){
            $step->run();
            $limitPerRequestCount ++;
          }
        }

        // Get headless curl request for $project->ScriptEngine->run()
        $headless = $this->runHeadless();

      } else {
        // If no next step, mark project complete
        $logger->setLine(__LINE__)->addDebug('Request for "run()" but no steps available. Marked Complete.');
        $this->project()->setStatus('completed');
      }
    } else {
      $logger->setLine(__LINE__)->addDebug('Script not ready. Current status `' . $this->getStatus() . '`');
    }
    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $logger->sync();
    return $this;
  }

  public function runHeadless(){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Performing header only api request to run script');
    $apiURL = site_url('/api/v1/webhooks/run_script?projectId=' . $this->_projectId);
    $logger->setLine(__LINE__)->addDebug('API Url', $apiURL);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $apiURL);
    curl_setopt ($curl, CURLOPT_POST, FALSE);
    //curl_setopt ($curl, CURLOPT_POSTFIELDS, $post);

    //curl_setopt($curl, CURLOPT_USERAGENT, 'api');
    //curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl,  CURLOPT_RETURNTRANSFER, false);
    curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 10);

    curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);

    $data = curl_exec($curl);
    $logger->setLine(__LINE__)->addDebug('Curl execute complete', $data);
    curl_close($curl);
    // Get headless curl request for $project->ScriptEngine->run()
    return $data;
  }

  public function getLogs($context = null){
    $logger = new WFLogger(__METHOD__, __FILE__);
    return $logger->getMessages($context);
  }

  public static function GetStatuses(){
    return ['ready','running','paused','cancelled','completed'];
  }

}

class ScriptStep implements ArrayAccess {

  private $_raw = null;
  private $_current = null;
  private $_projectId = null;
  private $_project = null;

  public function __construct(array $step, $projectId)
  {
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $logger->setLine(__LINE__)->addDebug('Preparing to validate step', $step);
    if(self::Validate($step)){
      $logger->setLine(__LINE__)->addDebug('Step valid', $step);
      $this->_projectId = $projectId;
      $this->_initialize($step);
    } else {
      $logger->setLine(__LINE__)->addDebug('Step provided is invalid', $step);
    }
    $logger->sync();
  }

  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
      $this->_raw[] = $value;
    } else {
      $this->_raw[$offset] = $value;
    }
  }

  public function offsetExists($offset) {
    return isset($this->_raw[$offset]);
  }

  public function offsetUnset($offset) {
    unset($this->_raw[$offset]);
  }

  public function offsetGet($offset) {
    return isset($this->_raw[$offset]) ? $this->_raw[$offset] : null;
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
    asort($this->_current);
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
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->setLine(__LINE__)->addDebug('Entering ...', [$context, $resource, $amount]);
    $response = WFClientInterface::GetPayloadTemplate();

    $context = trim((string) $context);

    if(in_array($resource, ['duration','memory'])){
      $logger->setLine(__LINE__)->addDebug('Resource is valid', $resource);
      if(is_numeric($amount)){
        $logger->setLine(__LINE__)->addDebug('Amount is numeric', $amount);
        if(!empty($context)){
          $logger->setLine(__LINE__)->addDebug('Context is set', $context);
          if(!isset($this->_current['usage'])) $this->_current['usage'] = [$resource => []];
          if(!isset($this->_current['usage'][$resource])) $this->_current['usage'][$resource] = [];
          $this->_current['usage'][$resource][] = [
            'context' => $context,
            'amount' => $amount
          ];
        } else {
          $logger->setLine(__LINE__)->addError('Invalid context provided (`'.$context.'`)');
        }
      } else {
        $logger->setLine(__LINE__)->addError('Amount must be numeric value. (`'.$amount.'`)');
      }
    } else {
      $logger->setLine(__LINE__)->addError('Resource provided is invalid (`'.$resource.'`).');
    }

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $logger->sync();
    $response['logger'] = $logger;
    $response['logs'] = $logger->getLogsArray();
    $response['errors'] = $logger->hasErrors(__METHOD__);
    return $response;
  }

  public function run(){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logs = WFClientInterface::GetLogsTemplate();
    $response = WFClientInterface::GetPayloadTemplate();

    $logger->setLine(__LINE__)->addDebug('Entering ...');
    // Status "running"
    $this->setStatus('running');

    $isReadyResponse = $this->isReady();
    if(WFClientInterface::Valid_WFResponse($isReadyResponse)){
      $logger->merge($isReadyResponse['logger']);
      $logger->setScope('ScriptStep::run -> $this->isReady')->setLine(__LINE__)->addDebug('Valid response', $isReadyResponse);

      if(!$isReadyResponse['errors']){
        $logger->setLine(__LINE__)->addDebug('No errors in dependencies');
        // Verify Callback
        $callback = $this->get('callback');
        if(is_callable($callback)){
          $logger->setLine(__LINE__)->addDebug('Callback valid', $callback);
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
            $logger->merge($registerUsageResponse['logger']);
            $logger->setScope('ScriptStep::run -> $this->registerUsage')->setLine(__LINE__)->addDebug('Valid response', $registerUsageResponse);
          } else {
            $logger->setScope('ScriptStep::run -> $this->registerUsage')->setLine(__LINE__)->addError('Invalid response', $registerUsageResponse);
          }

          // Validate Response
          if(WFClientInterface::Valid_WFResponse($callbackResponse)){
            $logger->merge($callbackResponse['logger']);
            $logger->setScope('ScriptStep::run -> $callback')->setLine(__LINE__)->addDebug('Valid response `'.$callback.'`', $callbackResponse);

            if(!$callbackResponse['errors']){
              $logger->setLine(__LINE__)->addDebug('No errors from callback `'.$callback.'`');
              // Add Results
              $updates['response'] = $callbackResponse['response'];

              // Add CompleteTime
              $updates['completeTime'] = new MongoDate();

              // Status "complete"
              $updates['status'] = 'complete';

              // Do update and reset updates array
              $this->setValue($updates);
            } else {
              $logger->setLine(__LINE__)->addDebug('Step not ready. Scheduled for += 1 hour');
              // Scheduled Time Set += 1 Hour
              $updates['scheduleTime'] = new MongoDate(strtotime('+ 1 hour'));
              // Status "paused"
              $updates['status'] = 'paused';
              $this->setValue($updates);
            }
          } else {
            $logger->setLine(__LINE__)->addError('Invalid callback response (`'.$callback.'`).');
          }
        } else {
          $logger->setLine(__LINE__)->addError('Invalid callback provided (`'.$callback.'`).');
        }
      } else {
        // Depending on whether this was explicitly called or automatically called decides whether an error should be returned.
        $logger->setLine(__LINE__)->addDebug('This step is not ready and will not run');
      }

    } else {
      $logger->setLine(__LINE__)->addError('Invalid response `$this->isReady()`');
    }
    $this->setValue([
      'status' => 'running',
      'logs' => $logger->getLogsArray()
    ]);


    $this->setValue(['logs' => $logs]);

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $logger->sync();
    $response['logger'] = $logger;
    $response['logs'] = $logger->getLogsArray();
    $response['errors'] = $logger->hasErrors(__METHOD__);
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

  public function ready(){
    $readyResponse = $this->isReady();
    if(WFClientInterface::Valid_WFResponse($readyResponse)){
      if(isset($readyResponse['response']['isReady'])){
        return $readyResponse['response']['isReady'];
      }
      return false;
    }
    return false;
  }

  public function isReady(){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $response = WFClientInterface::GetPayloadTemplate();
    $logger->setLine(__LINE__)->addDebug('Entering ...');
    $response['response']['isReady'] = false;

    // Check number of attempts is less than or equal to 2;
    // Check if status is
    // Dependency check
    $dependencies = $this->get('dependencies');
    if(empty($dependencies)) {
      $logger->setLine(__LINE__)->addDebug('No dependencies found for this step');
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
          $logger->merge($dependencyResponse['logger']);
          $logger->setScope('isReady -> ' . $dependency)->setLine(__LINE__)->addDebug('Response valid', $dependencyResponse);
          if(!$dependencyResponse['errors']){
            $dependencies[$dependency] = (bool) $dependencyResponse['response'];
          } else {
            $logger->setLine(__LINE__)->addError('Merging in process request logs (Includes errors)');
          }
        } else {
          $logger->setLine(__LINE__)->addError('Invalid dependency response', $dependency);
        }
      }

      $dependencyErrors = !in_array(null, $dependencies) && !in_array(false, $dependencies);
      $response['response']['success'] = true;

      $logger->setLine(__LINE__)->addDebug('Dependency errors exists', $dependencyErrors);

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
        $logger->merge($registerUsageResponse['logger']);
        $logger->setScope('isReady -> $this->registerUsage')->setLine(__LINE__)->addDebug('Valid response', $registerUsageResponse);

      } else {
        $logger->setScope('isReady -> $this->registerUsage')->setLine(__LINE__)->addError('Invalid Response', $registerUsageResponse);
      }
    }

    $logger->setLine(__LINE__)->addDebug('Exiting ...');
    $logger->sync();
    $response['logger'] = $logger;
    $response['logs'] = $logger->getLogsArray();
    $response['errors'] = $logger->hasErrors(__METHOD__);
    return $response;
  }

  /**
   * Make sure updates, raw, and current are consistent with each other
   */
  public function reconciled(){

  }

}