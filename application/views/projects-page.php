<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa "></i>Projects</h1>

    <div class="inner-nav-btns">
      <a href="<?php echo Template::GetCreateProjectUrl() ?>" class="btn"><i class="fa fa-plus"></i> Create a Project</a>
    </div>

    <style class="js-job-view-styles" type="text/css"></style> <!--/ Under penalty of death, do not remove -->

    <div class="js-change-views">
      <i class="fa fa-th-list" data-view="glance"></i>
      <i class="fa fa-th" data-view="normal"></i>
      <i class="fa fa-th-large" data-view="full"></i>
    </div>

    <div class="jobs-list cs-workflow workflow-<?php //echo $workflow->id() ?>"
         data-workflow="<?php //echo $workflow->id() ?>"
         data-display_details='<?php // echo json_encode($workflow->displayDetails()); ?>'
    >

      <h2 class="workflow-name"><?php //echo $workflow->getValue('name'); ?></h2>

    <?php foreach(organization()->getProjects() as $j => $job) include 'widgets/_project-milestones-include.php'; ?>

    </div>

  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->
<script type="text/javascript">

</script>