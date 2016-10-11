<?php

require_once 'WorkflowFactory.php';

class Workflow extends WorkflowFactory
{

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'workflows';

  public function __construct(array $data)
  {
    parent::__construct();
    $this->_initialize($data);
  }

  public function addJob(Job $job){
    if($this->hasId()){
      $job->setValues(array('workflowId' => $this->id()))->save();
      return $this;
    } else {
      throw new Exception('Job can not be added without an _id');
    }
  }

  public function jobCount(){
    return 4;
  }

  public function getJobs(){
    $jobs = self::CI()->mdb->where('workflowId', $this->id())->get(Job::CollectionName());
    foreach($jobs as $i => $job) $jobs[$i] = new Job($job);
    return $jobs;
  }

  public static function Get($id){
    $record = static::LoadId($id, static::$_collection);
    $class = __CLASS__;
    return new $class($record);
  }

  public function getUrl(){
    return site_url('workflows/' . $this->id());
  }

  public function getJobsUrl(){
    return $this->getUrl() . '/jobs';
  }


}