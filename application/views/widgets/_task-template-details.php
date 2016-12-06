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
        <div class="form-input"><input type="checkbox" name="milestone" id="milestoneField-<?php echo $template->id() ?>" /> <label for="milestoneField-<?php echo $template->id() ?>">Is this a milestone?</label></div>
        <div class="form-input"><input type="checkbox" name="clientView" id="clientViewField-<?php echo $template->id() ?>"/> <label for="clientViewField-<?php echo $template->id() ?>">Display in client portal?</label></div>
        <button type="submit" class="btn submit"><i class="fa fa-save"></i> Update</button>
      </div>
      <div class="form-group">
        <label>Group Label: </label>
        <input type="text" placeholder="Enter a group name to categorize this task" value="<?php echo $template->getValue('taskGroup') ?>" />
      </div>
      <div class="form-group">
        <label>Task Name: </label>
        <input type="text" placeholder="Enter a task name" value="<?php echo $template->getValue('name') ?>" />
      </div>
      <div class="form-group">
        <label>Description: </label>
        <textarea placeholder="Explain what this task is in layman's terms"><?php echo $template->getValue('description') ?></textarea>
      </div>
      <div class="form-group">
        <label>Instructions: </label>
        <textarea placeholder="Chart out what needs to be done"><?php echo $template->getValue('instructions') ?></textarea>
      </div>
      <div class="form-group">
        <label>Est. Time (hrs): </label>
        <input type="text" placeholder="Number of hours this task should take" value="<?php echo $template->getValue('estimatedTime') ?>" />
      </div>
      <div class="form-group">
        <label>Sort Order: </label>
        <select name="sortPosition">
          <option value="after">After</option>
          <option value="before">Before</option>
        </select>
        <select name="sortTask">
          
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
          <input type="hidden" name="id" value="<?php echo $templateId ?>"/>
          <div class="form-input"><input type="checkbox" name="milestone" id="milestoneField-<?php echo $templateId ?>" /> <label for="milestoneField-<?php echo $templateId ?>">Is this a milestone?</label></div>
          <div class="form-input"><input type="checkbox" name="clientView" id="clientViewField-<?php echo $templateId ?>"/> <label for="clientViewField-<?php echo $templateId ?>">Display in client portal?</label></div>
          <button type="submit" class="btn submit"><i class="fa fa-save"></i> Add Task</button>
        </div>
        <div class="form-group">
          <label>Group Label: </label>
          <input type="text" placeholder="Enter a group name to categorize this task" value="<?php ?>" />
        </div>
        <div class="form-group">
          <label>Task Name: </label>
          <input type="text" placeholder="Enter a task name" value="<?php ?>" />
        </div>
        <div class="form-group">
          <label>Description: </label>
          <textarea placeholder="Explain what this task is in layman's terms"><?php ?></textarea>
        </div>
        <div class="form-group">
          <label>Instructions: </label>
          <textarea placeholder="Chart out what needs to be done"><?php ?></textarea>
        </div>
        <div class="form-group">
          <label>Est. Time (hrs): </label>
          <input type="text" placeholder="Number of hours this task should take" value="<?php ?>" />
        </div>
      </form>
    </div>
  </div>
<?php } ?>
