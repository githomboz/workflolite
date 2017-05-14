<div class="main-mid-section clearfix <?php echo page_file_name(__FILE__) ?>-page">
  <div class="main-mid-section-inner clearfix">


    <?php
    //var_dump($this->input->post())
    $workflows = (array) Template::GetAll();
    $pageState = isset($pageState) ? $pageState : [];
    //var_dump($pageState);

    // @todo: Check if workflows exists for the current organization, if not, DO NOT SHOW FORM;
    if(!empty($workflows)){ ?>

      <h1><i class="fa fa-sticky-note-o"></i>Start a new Project</h1>
      <h4>Create a new instance of a workflow template</h4>


    <form method="post" action="<?php echo site_url('projects/create') ?>" class="create-job-form boxed <?php $posted = $this->input->get('created'); if($posted) echo 'form-submitted' ?>">
      <?php if($pageState['formSubmitted']['errors']){ ?>
        <div class="message-box error"><?php echo join('; ', $pageState['formSubmitted']['errors']); ?></div>
      <?php } ?>
      <?php if($pageState['successPageValid']) { ?>
        <div class="message-box success">Project "<?php echo '<a href="' . $pageState['projectUrl'] . '" style="color:#4992D0">'.$pageState['projectName'].'</a>'; ?>" created successfully.</div>
      <?php } ?>

        <input type="hidden" name="action" value="create-project" />
        <input type="hidden" class="js-submitted-job-name" value="<?php if($posted) echo $this->input->get('name') ?>" />
        <select name="templateId">
          <option value="">Select a Workflow</option>
          <?php foreach($workflows as $i => $workflow) {?>
            <option value="<?php echo $workflow->id() ?>"
              <?php if(isset($pageState['templateId']) && (string) $workflow->id() == $pageState['templateId']) echo 'selected="selected"'; ?>
            ><?php echo $workflow->getValue('name') ?></option>
          <?php } ?>
        </select>
        <br />
        <br />
        <div class="group">
          <label>Project Name: </label>
          <input name="name" type="text" placeholder="Project" />
        </div>
        <button type="submit" class="btn submit">Start Project</button>
      </form>

      <?php
    } else { ?>
      <h1><i class="fa fa-sticky-note-o"></i>Opps! No workflows found.</h1>
      <h4>Before you can create a project, you must first create a workflow.  <a href="<?php echo site_url('/templates') ?>">Create your first workflow now here</a>.</h4>
    <?php
    }
    ?>

    </div>
  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->

<script type="text/javascript">
  if($(".jobs-create-page .create-job-form.form-submitted").length > 0){
    alertify.notify('Job: [' + $(".js-submitted-job-name").val() + '] created successfully', 'success', 6);
  }
</script>