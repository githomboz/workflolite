<?php $jobStats = $job->stats(); //var_dump($job); ?>
<div class="job-entry cs-job clearfix job-<?php echo $job->id() ?> task-selected view-full"
     data-job="<?php echo $job->id() ?>"
     data-display_details='<?php echo json_encode($job->displayDetails()); ?>'
>
  <div class="job-title">
    <h2><a href="<?php echo $job->getUrl() ?>"><?php echo $job->getValue('name') ?></a></h2>
    <span class="completion-count">Created: <?php echo date('n-d-Y h:ia', $job->getValue('dateAdded')->sec) ?> </span>
    <span class="completion-count">Completed Tasks: <?php echo $jobStats['completed'] ?> / <?php echo $jobStats['total'] ?></span>
    <span class="estimated-hours">Est. Hours Completed: <?php echo $jobStats['completedTime'] ?> / <?php echo $jobStats['totalEstimatedTime'] ?></span>
  </div>
  <div class="cs-job-tasks clearfix">
    <?php $taskGroup = null; $taskGroupIncrementor = 0; ?>
    <?php foreach($job->getShowableTasks() as $t => $task) { ?>
      <div class="cs-job-task cs-task status-<?php echo $task->getValue('status') ?> <?php  echo (string) $task->id() == (string) $job->getNextTask()->id() ? 'selected next-step' : '' ?>">
        <div class="status <?php echo $task->getValue('status') ?>">
              <span class="content"><?php echo $task->statusText() ?>
                <i class="fa fa-times js-"
                   data-job="<?php echo $job->id() ?>"
                   data-task="<?php echo $task->id() ?>"
                ></i>
              </span>
        </div>
        <div class="job-inner task-group-<?php if($task->getValue('taskGroup') != $taskGroup) { $taskGroupIncrementor ++; $taskGroup = $task->getValue('taskGroup'); } echo $taskGroupIncrementor; ?>">
          <div class="dates content clearfix">
            <span class="start-date"><?php $date = $task->getValue('startDate'); echo $date instanceof MongoDate ? date('m-d-Y', $date->sec) : 'N/A'; ?></span>
            <span class="completion-date"><?php $date = $task->getValue('completeDate'); echo $date instanceof MongoDate ? date('m-d-Y', $date->sec) : 'N/A'; ?></span>
          </div>
          <h2 class="content task-name"><?php echo $task->getValue('name') ?></h2>
          <div class="task-details content">
            <a href="#assignee" class="assignee">Deanna Courtney</a>
            <span class="estimated-time">~<?php echo $task->getValue('estimatedTime')?>hrs</span>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
</div>