<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa "></i>Jobs Queue</h1>
    <h4>List of your current jobs and their status relative to completion.</h4>

    <div class="inner-nav-btns">
      <a href="#" class="btn"><i class="fa fa-plus"></i> Create a Job</a>
    </div>

    <style class="js-job-view-styles" type="text/css"></style> <!--/ Under penalty of death, do not remove -->

    <div class="js-change-views">
      <i class="fa fa-th-list" data-view="glance"></i>
      <i class="fa fa-th" data-view="normal"></i>
      <i class="fa fa-th-large" data-view="full"></i>
    </div>
    <?php foreach(organization()->getWorkflows() as $w => $workflow) : ?>


    <div class="jobs-list cs-workflow workflow-<?php echo $workflow->id() ?>"
         data-workflow="<?php echo $workflow->id() ?>"
         data-display_details='<?php echo json_encode($workflow->displayDetails()); ?>'
    >

      <h2 class="workflow-name"><?php echo $workflow->getValue('name'); ?></h2>

    <?php foreach($workflow->getJobs() as $j => $job) {

        $jobStats = $job->stats();

        ?>

      <div class="job-entry cs-job clearfix job-<?php echo $job->id() ?> task-selected view-full"
           data-job="<?php echo $job->id() ?>"
           data-display_details='<?php echo json_encode($job->displayDetails()); ?>'
      >
        <div class="job-title">
          <h2><a href="<?php echo $job->getUrl() ?>"><?php echo $job->getValue('name') ?></a></h2>
          <span class="completion-count">Completed <?php echo $jobStats['completed'] ?>/<?php echo $jobStats['total'] ?></span>
        </div>
        <div class="cs-job-tasks clearfix">
          <?php $taskGroup = null; $taskGroupIncrementor = 0; ?>
          <?php foreach($job->getShowableTasks() as $t => $task) { ?>
          <div class="cs-job-task cs-task status-<?php echo $task->getValue('status') ?> <?php  echo (string) $task->id() == (string) $job->getNextTask()->id() ? 'selected' : '' ?>">
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
              <h2 class="content"><?php echo $task->getValue('name') ?></h2>
              <div class="task-details content">
                <a href="#assignee" class="assignee">Deanna Courtney</a>
                <span class="estimated-time">~<?php echo $task->getValue('estimatedTime')?>hrs</span>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>

    <?php } ?>

    </div>

    <?php endforeach; ?>

  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->
<script type="text/javascript">

</script>