<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa "></i>Jobs Queue</h1>
    <h4>List of your current jobs and their status relative to completion.</h4>

    <div class="inner-nav-btns">
      <a href="<?php echo Workflow::GetCreateJobUrl() ?>" class="btn"><i class="fa fa-plus"></i> Create a Job</a>
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

    <?php foreach($workflow->getJobs() as $j => $job) include 'widgets/_job-milestones-include.php'; ?>

    </div>

    <?php endforeach; ?>

  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->
<script type="text/javascript">

</script>