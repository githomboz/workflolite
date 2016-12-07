<?php if(isset($template)) { ?>
<div class="template entry template-<?php echo $template->id() ?>"
     data-current='<?php echo json_encode($template->getSettingsData()) ?>'
>
  <a href="#tasktemplate-<?php echo $template->id() ?>" class="dark sidepanel-bg boxed preview entry clearfix">
    <h2><i class="fa fa-chevron-right"></i> <?php $group = (string) $template->getValue('taskGroup'); echo (trim($group) == '' ? '' : $group . ': ') . $template->getValue('name') ?></h2>
  </a>
  <div class="sidepanel-bg boxed form entry clearfix">
    <h2><i class="fa fa-chevron-down"></i>  <?php $group = (string) $template->getValue('taskGroup'); echo (trim($group) == '' ? '' : $group . ': ') .$template->getValue('name') ?></h2>
    <div class="link-group">
      <a href="#" class="js-delete-task"><i class="fa fa-trash"></i> Delete</a>
      <a href="#" class="js-cancel-edit"><i class="fa fa-times"></i> Cancel</a>
    </div>
    <form method="post">
      <div class="aside">
        <input type="hidden" name="formData" />
        <input type="hidden" class="id-field" value="<?php echo $template->id() ?>" />
        <div class="form-input"><input type="checkbox" name="milestone" id="milestoneField-<?php echo $template->id() ?>" /> <label for="milestoneField-<?php echo $template->id() ?>">Is this a milestone?</label></div>
        <div class="form-input"><input type="checkbox" name="clientView" id="clientViewField-<?php echo $template->id() ?>"/> <label for="clientViewField-<?php echo $template->id() ?>">Display in client portal?</label></div>
        <button type="submit" class="btn submit js-update-task-template-btn"><i class="fa fa-save"></i> Update</button>
      </div>
      <div class="form-group">
        <label for="field-taskGroup-<?php echo $template->id() ?>">Group Label: </label>
        <input id="field-taskGroup-<?php echo $template->id() ?>" type="text" placeholder="Enter a group name to categorize this task" value="<?php echo $template->getValue('taskGroup') ?>" />
      </div>
      <div class="form-group">
        <label for="field-name-<?php echo $template->id() ?>">Task Name: </label>
        <input id="field-name-<?php echo $template->id() ?>" type="text" placeholder="Enter a task name" value="<?php echo $template->getValue('name') ?>" />
      </div>
      <div class="form-group">
        <label for="field-description-<?php echo $template->id() ?>">Description: </label>
        <textarea id="field-description-<?php echo $template->id() ?>" placeholder="Explain what this task is in layman's terms"><?php echo $template->getValue('description') ?></textarea>
      </div>
      <div class="form-group">
        <label for="field-instructions-<?php echo $template->id() ?>">Instructions: </label>
        <textarea id="field-instructions-<?php echo $template->id() ?>" placeholder="Chart out what needs to be done"><?php echo $template->getValue('instructions') ?></textarea>
      </div>
      <div class="form-group">
        <label for="field-estimatedTime-<?php echo $template->id() ?>">Est. Time (hrs): </label>
        <input id="field-estimatedTime-<?php echo $template->id() ?>" type="text" placeholder="Number of hours this task should take" value="<?php echo $template->getValue('estimatedTime') ?>" />
      </div>
      <div class="form-group">
        <label>Sort Order: </label>
        <select class="sortPosition">
          <option value="after">After</option>
          <option value="before">Before</option>
        </select>
        <select class="sortTask">
          
          <?php foreach($templates as $i => $template){?>
            <option value="<?php echo $template->id() ?>" <?php if($i == (count($templates)-1)) echo 'selected="selected"'?>><?php echo $template->getValue('name') ?></option>
          <?php } ?>
        </select>

      </div>
    </form>
  </div>
</div>
<?php } else {
  $templateId = md5(_generate_id(8).time());
  ?>
  <div class="template entry new template-<?php echo $templateId ?> form-mode" >
    <a href="#tasktemplate-<?php echo $templateId ?>" class="dark new boxed preview entry clearfix">
      <h2><i class="fa fa-chevron-right"></i> New Task Template</h2>
    </a>
    <div class="boxed form entry clearfix">
      <h2><i class="fa fa-chevron-down"></i>  New Task Template</h2>
      <div class="link-group">
        <a href="#" class="js-cancel-edit"><i class="fa fa-times"></i> Cancel</a>
      </div>
      <form method="post">
        <div class="aside">
          <input type="hidden" name="formData" />
          <input type="hidden" class="id-field" value="<?php echo $templateId ?>"/>
          <div class="form-input"><input type="checkbox" name="milestone" id="milestoneField-<?php echo $templateId ?>" /> <label for="milestoneField-<?php echo $templateId ?>">Is this a milestone?</label></div>
          <div class="form-input"><input type="checkbox" name="clientView" id="clientViewField-<?php echo $templateId ?>"/> <label for="clientViewField-<?php echo $templateId ?>">Display in client portal?</label></div>
          <button type="submit" class="btn submit"><i class="fa fa-save"></i> Add Task</button>
        </div>
        <div class="form-group">
          <label for="field-taskGroup-<?php echo $templateId ?>">Group Label: </label>
          <input id="field-taskGroup-<?php echo $templateId ?>" type="text" placeholder="Enter a group name to categorize this task" value="<?php ?>" />
        </div>
        <div class="form-group">
          <label for="field-name-<?php echo $templateId ?>">Task Name: </label>
          <input id="field-name-<?php echo $templateId ?>" type="text" placeholder="Enter a task name" value="<?php ?>" />
        </div>
        <div class="form-group">
          <label for="field-description-<?php echo $templateId ?>">Description: </label>
          <textarea id="field-description-<?php echo $templateId ?>" placeholder="Explain what this task is in layman's terms"><?php ?></textarea>
        </div>
        <div class="form-group">
          <label for="field-instructions-<?php echo $templateId ?>">Instructions: </label>
          <textarea id="field-instructions-<?php echo $templateId ?>" placeholder="Chart out what needs to be done"><?php ?></textarea>
        </div>
        <div class="form-group">
          <label for="field-estimatedTime-<?php echo $templateId ?>">Est. Time (hrs): </label>
          <input id="field-estimatedTime-<?php echo $templateId ?>" type="text" placeholder="Number of hours this task should take" value="<?php ?>" />
        </div>
      </form>
    </div>
  </div>
<?php } ?>
