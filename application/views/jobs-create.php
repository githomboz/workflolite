<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa fa-sticky-note-o"></i>Create a Job</h1>
    <h4>Create a new instance of a workflow</h4>

    <?php //var_dump($this->input->post()) ?>

    <form method="post" action="" class="create-job-form boxed">
      <input type="hidden" name="action" value="create-job" />
      <label>Select a Workflow: </label>
      <select name="workflowId">
        <?php foreach(Workflow::GetAll() as $i => $workflow) {?>
          <option value="<?php echo $workflow->id() ?>"
                  <?php if((string) $workflow->id() == $this->input->get('workflow')) echo 'selected="selected"'; ?>
          ><?php echo $workflow->getValue('name') ?></option>
        <?php } ?>
      </select>
      <div class="group">
        <label>Job Name: </label>
        <input name="name" type="text" placeholder="Job Name" />
      </div>
      <button type="submit" class="btn submit">Create Job</button>
    </form>
    </div>
  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->