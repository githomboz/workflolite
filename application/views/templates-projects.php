<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa fa-eye"></i>Projects using this Template <span class="workflow-name font-size-sm">(
        <a href="<?php echo $this->template->getUrl() ?>">
        <?php echo $this->template->getValue('name') ?>
        </a>
        )</span> </h1>
    <h4></h4>

    <div class="inner-nav-btns">
      <a href="<?php echo Template::GetCreateProjectUrl(); ?>" class="btn"><i class="fa fa-plus"></i> Create a Project</a>
    </div>


    <style class="js-job-view-styles" type="text/css"></style> <!--/ Under penalty of death, do not remove -->

    <div class="js-change-views no-float">
      <i class="fa fa-th-list" data-view="glance"></i>
      <i class="fa fa-th" data-view="normal"></i>
      <i class="fa fa-th-large" data-view="full"></i>
    </div>

    <div class="jobs-list cs-workflow workflow-<?php echo $this->template->id() ?>"
         data-workflow="<?php echo $this->template->id() ?>"
         data-display_details='<?php echo json_encode($this->template->displayDetails()); ?>'
    >

    <?php $jobs = $this->template->getProjects();
    foreach($jobs as $job) include 'widgets/_project-milestones-include.php'?>

      </div>
  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->

