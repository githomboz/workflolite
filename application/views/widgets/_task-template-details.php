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
    <?php if(isset($this->messageBox) && isset($this->messageBox['content']) && (string) $template->id() == $this->messageBox['taskTemplateId']){ ?>
      <div class="message-box <?php echo $this->messageBox['class'] ?>">
        <div class="content"><?php echo $this->messageBox['content'] ?></div>
      </div>
    <?php } ?>
    <form method="post">
      <?php //var_dump($template); ?>
      <div class="aside">
        <input type="hidden" name="formData" />
        <input type="hidden" name="taskTemplateId" class="task-template-id" value="<?php echo $template->id() ?>" />
        <input type="hidden" name="templateId" value="<?php echo template()->id() ?>" />
        <input type="hidden" name="formAction" value="updateTaskTemplate" />
        <div class="form-input"><input type="checkbox" class="milestone" id="milestoneField-<?php echo $template->id() ?>" <?php if($template->getValue('milestone') == true) echo 'checked="checked"' ?> /> <label for="milestoneField-<?php echo $template->id() ?>">Is this a milestone?</label></div>
        <div class="form-input"><input type="checkbox" class="clientView" id="clientViewField-<?php echo $template->id() ?>" <?php if($template->getValue('clientView') == true) echo 'checked="checked"' ?>/> <label for="clientViewField-<?php echo $template->id() ?>">Display in client portal?</label></div>
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
        <select class="sortOrder">
          <option value="<?php echo $templateCount ?>" <?php if($templateCount == $template->getSortOrder()) echo 'selected="selected"'; ?>>Last (<?php echo $templateCount ?>)</option>
          <?php
          for($i = ($templateCount-1); $i > 1; $i --){
            echo '<option value="' . $i . '" ';
            if($i == $template->getSortOrder()) echo 'selected="selected"';
            echo '>' . $i . '</option>';
          }
          ?>
          <option value="1" <?php if($templateCount == 0 || $template->getSortOrder() == 1) echo 'selected="selected"' ?>>First (1)</option>
        </select>
        <span class="sort-hint">Change task position</span>
      </div>
    </form>
  </div>
</div>
<?php } else {
  $taskTemplateId = md5(_generate_id(8).time());
  $templateId = (isset($templateId)) ? $templateId : (template() ? (string) template()->id() : null);
  $formUrl = site_url('templates/' . $templateId);
  $formUrl .= isset($version) ? '?ver=' . $version : '';
  $formUrl .= '#tasktemplate-'.$taskTemplateId;
  ?>
  <div class="template entry new template-<?php echo $taskTemplateId ?> form-mode" >
    <a href="#tasktemplate-<?php echo $taskTemplateId ?>" class="dark new boxed preview entry clearfix">
      <h2><i class="fa fa-chevron-right"></i> New Task Template</h2>
    </a>
    <div class="boxed form entry clearfix">
      <h2><i class="fa fa-chevron-down"></i>  New Task Template</h2>
      <div class="link-group">
        <a href="#" class="js-cancel-edit"><i class="fa fa-times"></i> Cancel</a>
      </div>
      <?php if(isset($this->messageBox)){ ?>
      <div class="message-box <?php echo $this->messageBox['class'] ?>">
        <div class="content"><?php echo $this->messageBox['content'] ?></div>
      </div>
      <?php } ?>
      <form method="post" action="<?php echo $formUrl ?>">
        <div class="aside">
          <input type="hidden" name="formData" />
          <input type="hidden" name="taskTemplateId" class="task-template-id" value="<?php echo $taskTemplateId ?>" />
          <input type="hidden" name="templateId" value="<?php echo $templateId; ?>" />
          <input type="hidden" name="formAction" value="addNewTaskTemplate" />
          <div class="form-input"><input type="checkbox" class="milestone" id="milestoneField-<?php echo $taskTemplateId ?>" /> <label for="milestoneField-<?php echo $taskTemplateId ?>">Is this a milestone?</label></div>
          <div class="form-input"><input type="checkbox" class="clientView" id="clientViewField-<?php echo $taskTemplateId ?>"/> <label for="clientViewField-<?php echo $taskTemplateId ?>">Display in client portal?</label></div>
          <button type="submit" class="btn submit"><i class="fa fa-save"></i> Add Task</button>
        </div>
        <div class="form-group">
          <label for="field-taskGroup-<?php echo $taskTemplateId ?>">Group Label: </label>
          <input id="field-taskGroup-<?php echo $taskTemplateId ?>" type="text" placeholder="Enter a group name to categorize this task" value="<?php ?>" />
        </div>
        <div class="form-group">
          <label for="field-name-<?php echo $taskTemplateId ?>">Task Name: </label>
          <input id="field-name-<?php echo $taskTemplateId ?>" type="text" placeholder="Enter a task name" value="<?php ?>" />
        </div>
        <div class="form-group">
          <label for="field-description-<?php echo $taskTemplateId ?>">Description: </label>
          <textarea id="field-description-<?php echo $taskTemplateId ?>" placeholder="Explain what this task is in layman's terms"><?php ?></textarea>
        </div>
        <div class="form-group">
          <label for="field-instructions-<?php echo $taskTemplateId ?>">Instructions: </label>
          <textarea id="field-instructions-<?php echo $taskTemplateId ?>" placeholder="Chart out what needs to be done"><?php ?></textarea>
        </div>
        <div class="form-group">
          <label for="field-estimatedTime-<?php echo $taskTemplateId ?>">Est. Time (hrs): </label>
          <input id="field-estimatedTime-<?php echo $taskTemplateId ?>" type="text" placeholder="Number of hours this task should take" value="<?php ?>" />
        </div>
        <div class="form-group">
          <label>Sort Order: </label>
          <select class="sortOrder">
            <option value="<?php echo ($templateCount+1) ?>">Last (<?php echo ($templateCount+1) ?>)</option>
            <?php
            for($i = $templateCount; $i > 1; $i --){
              echo '<option value="' . $i . '">' . $i . '</option>';
            }
            ?>
            <option value="1">First (1)</option>
          </select>
          <span class="sort-hint">Define task position</span>
        </div>
      </form>
    </div>
  </div>
<?php } ?>
