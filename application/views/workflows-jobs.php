<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa fa-eye"></i>Jobs from Workflow <span class="workflow-name font-size-sm">(
        <a href="<?php echo $this->workflow->getUrl() ?>">
        <?php echo $this->workflow->getValue('name') ?>
        </a>
        )</span> </h1>
    <h4></h4>

    <div class="inner-nav-btns">
      <a href="#" class="btn"><i class="fa fa-plus"></i> Create a Job</a>
    </div>
    <?php $jobs = $this->workflow->getJobs();
    foreach($jobs as $job) include 'widgets/_job-milestones-include.php'?>

  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->

