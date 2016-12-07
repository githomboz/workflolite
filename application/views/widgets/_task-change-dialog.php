<div class="modal-container js-job-change-modal">
  <div class="js-job-change-dialog dialog boxed options-not-active">
    <span class="current-template"><em>Current Template: </em><?php echo template()->name() ?></span>
    <div class="option" data-save_style="CONVERT_TO_CUSTOM_PROJECT">
      <h1>Convert to Custom Project</h1>
      <p>I want this to be a stand alone project.  I understand that this project will no longer be associated with or included into reporting or analytics related to other templated projects. <a href="#" class="js-job-change-action">[ choose ]</a></p>
    </div>
    <div class="option" data-save_style="SAVE_AS_NEW_TEMPLATE">
      <h1>Save as New Template</h1>
      <p>I want to preserve the current template and its corresponding projects and instead create a duplicate template with a new name. <a href="#" class="js-job-change-action">[ choose ]</a></p>
      <div class="form">
        <label for="">New Template Name: </label> <input type="text" /> <button class="btn-style submit"><i class="fa fa-save"></i> Save</button>
      </div>
    </div>
    <div class="option" data-save_style="SAVE_AS_NEW_PROJECT">
      <h1>Save as New / Duplicate Project</h1>
      <p>I want to make an identical copy of the current project and all of its current settings and give it another name. <a href="#" class="js-job-change-action">[ choose ]</a></p>
      <div class="form">
        <label for="">New Project Name: </label> <input type="text" /> <button class="btn-style submit"><i class="fa fa-save"></i> Save</button>
      </div>
    </div>
    <div class="option" data-save_style="UPDATE_TEMPLATE">
      <h1>Update this Template (Not Recommended)</h1>
      <p>I understand that updating this template can have a deleterious affect on past and present projects already using this template.  I want to update this template anyway. <a href="#" class="js-job-change-action">[ choose ]</a></p>
    </div>
    <div class="option" data-save_style="CANCEL_CHANGES">
      <h1>Cancel Changes</h1>
      <p>I don’t want to make any changes. We’ll keep things the way they are. <a href="#" class="js-job-change-action">[ choose ]</a></p>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(".js-job-change-modal .js-job-change-action").on('click', function(e){
    e.preventDefault();
    var $this = $(this),
      $option = $this.parents('.option'),
      $dialog = $this.parents('.dialog'),
      scheme = $option.data('save_style'),
      $modalContainer = $(".js-job-change-modal.modal-container");

    $(".js-job-change-dialog .option").removeClass('active');
    $option.addClass('active');
    $dialog.removeClass('options-not-active');

    if(scheme == 'CANCEL_CHANGES'){
      $modalContainer.removeClass('active');
      $(".js-job-change-dialog .option").removeClass('active');
    } else {

    }
  });
  
  
</script>