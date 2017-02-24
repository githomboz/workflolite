<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa fa-eye"></i>Overview</h1>
    <h4>Create, delete, or edit templates.</h4>

    <div class="inner-nav-btns">
      <a href="#" class="btn create-workflow-btn"><i class="fa fa-plus"></i> Create a Workflow</a>
    </div>

    <?php $post = $this->input->post(); ?>
    <style type="text/css">
      .create-template-form.error {
        border-color: rgba(187, 41, 9, 0.63);
        background: rgba(187, 170, 171, 0.1);
      }
    </style>

    <form class="create-template-form boxed <?php echo (isset($formClass) ? $formClass : '') ?>" method="post" <?php if(!($formSubmitted && !$formSuccess)) echo 'style="display: none"'; ?> >
      <input type="hidden" name="organizationId" value="<?php echo UserSession::Get_Organization()->id() ?>"/>
      <label>Template: *</label> <input type="text" name="name" placeholder="Template Name" value="<?php echo $this->input->post('name') ?>" />
      <label>Description:</label> <textarea name="description" placeholder="Describe this workflow" value="<?php echo $this->input->post('description') ?>" ></textarea>
      <label>Category: *</label> <input type="text" name="group" value="<?php echo $this->input->post('group') ?>"  />
      <label>Noun: *</label> <input type="text" name="noun" placeholder='Ex. "new order", "phone call", "file received"' value="<?php echo $this->input->post('noun') ?>" />
      <button type="submit">Add Workflow</button>
    </form>
    <?php $templates = $this->organization->getTemplates();
    foreach((array) $templates as $workflow){
    ?>
    <div class="workflow entry boxed sidepanel-bg workflow-<?php echo $workflow->id() ?>">
      <div class="workflow-info">
        <span class="group">Category: <span class="group-name"><?php echo $workflow->getValue('group') ?></span></span>
        <span class="group">Task Count: <span class="group-name"><?php echo count($workflow->getValue('taskTemplates')) ?></span></span>
        <h2><a href="<?php echo $workflow->getUrl() ?>" class=""><?php echo $workflow->getValue('name'); ?></a> <a href="<?php echo $workflow->getUrl() ?>" class="js-edit"><i class=" fa fa-pencil"></i> Edit</a> </h2>
        <h3><?php echo $workflow->getValue('description'); ?></h3>
      </div>
      <div class="actions">
        <span class="job-count">Projects: <?php echo $workflow->projectCount(null); ?>  | <a href="<?php echo $workflow->getProjectsUrl() ?>">Browse</a> </span>
        <a href="<?php echo $workflow->createProjectUrl() ?>" class="btn icon submit"><i class="fa fa-plus"></i> New Project</a>
      </div>
    </div>
    <?php } ?>

  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->

<script type="text/javascript">
  $(document).ready(function(){
    $(document).on('click','.create-workflow-btn', function(e){
      e.preventDefault();
      $("form.create-template-form").toggle();
    });
  });
</script>