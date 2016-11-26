<div class="main-mid-section clearfix <?php echo page_file_name(__FILE__) ?>-page">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa fa-sticky-note-o"></i>Start a new Project</h1>
    <h4>Create a new instance of a template</h4>

    <?php //var_dump($this->input->post()) ?>

    <form method="post" action="" class="create-job-form boxed <?php $posted = $this->input->get('created'); if($posted) echo 'form-submitted' ?>">
      <input type="hidden" name="action" value="create-project" />
      <input type="hidden" class="js-submitted-job-name" value="<?php if($posted) echo $this->input->get('name') ?>" />
      <label>Select a Template: </label>
      <select name="workflowId">
        <option value="">Custom Project</option>
        <?php foreach(Template::GetAll() as $i => $workflow) {?>
          <option value="<?php echo $workflow->id() ?>"
                  <?php if((string) $workflow->id() == $this->input->get('workflow')) echo 'selected="selected"'; ?>
          ><?php echo $workflow->getValue('name') ?></option>
        <?php } ?>
      </select>
      <div class="group">
        <label>Project Name: </label>
        <input name="name" type="text" placeholder="Project" />
      </div>
      <button type="submit" class="btn submit">Start Project</button>
    </form>
    </div>
  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->

<script type="text/javascript">
  if($(".jobs-create-page .create-job-form.form-submitted").length > 0){
    alertify.notify('Job: [' + $(".js-submitted-job-name").val() + '] created successfully', 'success', 6);
  }
</script>