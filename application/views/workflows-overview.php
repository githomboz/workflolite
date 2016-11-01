<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa fa-eye"></i>Overview</h1>
    <h4>Create, delete, or edit workflow templates.</h4>

    <div class="inner-nav-btns">
      <a href="#" class="btn"><i class="fa fa-plus"></i> Create a Workflow</a>
    </div>
    <?php $workflows = $this->organization->getWorkflows();
    foreach($workflows as $workflow){

    ?>
    <div class="workflow entry boxed sidepanel-bg workflow-<?php echo $workflow->id() ?>">
      <div class="workflow-info">
        <span class="group">Category: <span class="group-name"><?php echo $workflow->getValue('group') ?></span></span>
        <span class="group">Task Count: <span class="group-name"><?php echo $workflow->taskCount() ?></span></span>
        <h2><a href="<?php echo $workflow->getUrl() ?>" class=""><?php echo $workflow->getValue('name'); ?></a> <a href="<?php echo $workflow->getUrl() ?>" class="js-edit"><i class=" fa fa-pencil"></i> Edit</a> </h2>
        <h3><?php echo $workflow->getValue('description'); ?></h3>
      </div>
      <div class="actions">
        <span class="job-count">Jobs: <?php echo $workflow->jobCount(); ?>  | <a href="<?php echo $workflow->getJobsUrl() ?>">Browse</a> </span>
        <a href="<?php echo $workflow->createJobUrl() ?>" class="btn icon submit"><i class="fa fa-plus"></i> New Job</a>
      </div>
    </div>
    <?php } ?>

  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->

