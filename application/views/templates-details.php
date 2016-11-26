<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><?php echo $this->template->getValue('name') ?></h1>
    <h4><?php echo $this->template->getValue('description') ?></h4>

    <div class="inner-nav-btns">
      <a href="#" class="btn"><i class="fa fa-plus"></i> Create a Template</a>
    </div>
    <div class="templates-list widget">
      <h2>Task Templates: </h2>

    <?php $templates = $this->template->getTemplates(); //var_dump($templates);
    foreach($templates as $template){?>
      <div class="template entry template-<?php echo $template->id() ?>" >
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
    <?php } ?>
    </div><!--/.templates-list-->

    <div class="roles-list entities-list widget">
      <h2>Manage Roles: </h2>
      <form method="post" class="boxed sidepanel-bg">
        <div class="form-group">
          <label for="roleField">New Role</label> <input type="text" id="roleField" name="role" placeholder="Enter a Role" />
        </div>
        <button class="btn submit"><i class="fa fa-plus"></i> Add Role</button>
        <?php $roles = (array) $this->template->getValue('roles'); ?>
        <?php if(!empty($roles)){?>
        <div class="roles entities dynamic-list">
          <?php foreach($roles as $i => $role) {?>
          <div class="list-item entity role" data-role_key="<?php echo $i ?>"><span class="text"><?php echo $role; ?></span> <a href="#close" class="close fa fa-times"></a></div>
          <?php } ?>
        </div>
        <?php } ?>
      </form>
    </div><!--/.widget-->

    <?php //$this->workflow->addMeta('Buyer Price') ?>
    <div class="meta-list entities-list widget">
      <h2>Job Meta Data: </h2>
      <div class="boxed sidepanel-bg">
        <div class="form-group">
          <label for="labelField">Label Name</label> <input type="text" id="labelField" name="metaFieldName" placeholder="Label the field name" />
          <select name="dataType">
            <option value="">Select</option>
            <?php $metaDataTypes = Workflow::MetaDataTypes(); foreach($metaDataTypes as $dataType => $dataTypeData){ ?>
            <option value="<?php echo $dataType ?>"><?php echo ucwords($dataType); ?></option>
            <?php } ?>
          </select>
          <form class="type-form <?php $dataType = 'string'; echo $dataType; ?>">
            <div class="main-fields">
              <label for="">Max String Length</label>
              <input type="text" name="maxLengthDefault" value="<?php ?>" />
              <span class="helpful-tip">The maximum length allowable for this string</span>
            </div>
            <a href="#" class="set-default-link">Add / Update Default Values</a>
            <div class="set-default ">
              <label id="">Default Value: </label><input name="defaultValue" />
            </div>
          </form>
          <form class="type-form <?php $dataType = 'integer'; echo $dataType; ?>">
            <a href="#" class="set-default-link">Add / Update Default Values</a>
            <div class="set-default ">
              <label id="">Default Value: </label><input name="defaultValue" />
            </div>
          </form>
          <form class="type-form <?php $dataType = 'date'; echo $dataType; ?>">
            <div class="main-fields">
              <?php //var_dump($metaDataTypes[$dataType]); ?>
              <label for="">Format</label>
              <select name="format">
                <?php foreach($metaDataTypes[$dataType]['options']['formats'] as $format){ ?>
                <option value="<?php echo $format ?>"><?php echo "'{$format}' => " . date($format, strtotime('4/9/2016 3:02 pm')) ?></option>
                <?php } ?>
              </select>
            </div>
          </form>
          <form class="type-form <?php $dataType = 'time'; echo $dataType; ?>">
            <div class="main-fields">
              <?php //var_dump($metaDataTypes[$dataType]); ?>
              <label for="">Format</label>
              <select name="format">
                <?php foreach($metaDataTypes[$dataType]['options']['formats'] as $format){ ?>
                <option value="<?php echo $format ?>"><?php echo "'{$format}' => " . date($format, strtotime('4/9/2013 3:02 pm')) . ' & ' . date($format, strtotime('8/18/2010 5:35 am')) ?></option>
                <?php } ?>
              </select>
            </div>
          </form>
          <form class="type-form <?php $dataType = 'address'; echo $dataType; ?>">
            <div class="main-fields">
              <?php //var_dump($metaDataTypes[$dataType]); ?>
              <label for="">Country</label>
              <select name="country">
                <option value="us">United States</option>
              </select>
            </div>
            <a href="#" class="set-default-link">Add / Update Default Values</a>
            <div class="set-default ">
              <div class="data-type-address data-type-form no-labels">
                <div class="group">
                  <label>Address</label><input type="input" name="address" placeholder="Address" />
                </div>
                <div class="group">
                  <label>Address 2</label><input type="input" name="address2" placeholder="Address 2" />
                </div>
                <div class="group">
                  <input type="input" name="city" placeholder="City" />
                  <input type="input" name="state" placeholder="State" />
                  <input type="input" name="zip" placeholder="Zip" />
                </div>
              </div>
            </div>
          </form>
        </div>
        <button class="btn submit"><i class="fa fa-plus"></i> Add Meta Field</button>
        <?php $metaFields = (array) $this->template->getValue('metaFields'); ?>
        <?php if(!empty($metaFields)){  ?>
          <div class="meta-fields entities dynamic-list">
            <?php foreach($metaFields as $i => $metaField) { ?>
              <div class="list-item entity meta-field" data-slug="<?php $metaField['slug'] ?>"><span class="text"><?php echo $metaField['field'] . " ( {$metaField['type']}" . (isset($metaField['_']) ? '[' . $metaField['_'] . ']' : '') .  " )";
                  ?></span> <a href="#close" class="close fa fa-times"></a></div>
            <?php } ?>
          </div>
        <?php } ?>
      </div>
    </div><!--/.widget-->


  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->
<?php //var_dump($metaFields) ?>
<?php //var_dump($metaDataTypes); ?>
<?php //var_dump($this->workflow); ?>

<script type="text/javascript">

  var $templateList = $('.templates-list');

  $(".template .entry.preview").click(function(){
    var $link = $(this),
      templateId = $link.attr('href').split('-')[1],
      $template = $(".template-" + templateId);
      selectTemplate($template, 300);
  });
  $(".form .js-cancel-edit").click(function(){
    var $this = $(this), $templateList = $this.parents('.templates-list');
    if($templateList.is(".selected")){
      $this.parents('.template').toggleClass('form-mode');
      $templateList.removeClass('selected');
      return false;
    }
  });

  function selectTemplate($template, slide){
    $templateList.find('.template').removeClass('form-mode');
    $template.toggleClass('form-mode');
    $template.parents('.templates-list').addClass('selected');

    $('html, body').animate({
      scrollTop: ($template.offset().top - 14)
    }, slide || 0);
    if(!$templateList.is(".selected")){
      $template.toggleClass('form-mode');
      $templateList.addClass('selected');
    }

    console.log(templateId);
  }

  var selectedTemplate = window.location.hash.substr(1);
  if(selectedTemplate.search('tasktemplate-') >= 0){
    var templateId = selectedTemplate.split('-')[1];
    var $template = $(".template-" + templateId);
    selectTemplate($template);
  }

  $( "select[name=dataType]" )
    .change(function () {
      var val = $(this).val();
      $(".type-form").removeClass('active');
      if(val.trim() != '') $(".type-form." + val.trim()).addClass('active');
    })
    .change();

  $(document).on('click', ".set-default-link", function(){
    var $this = $(this);
    $this.parents('form').find('.set-default').show();
    return false;
  });
</script>