<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><?php echo $this->template->getValue('name') ?></h1>
    <h4><?php echo $this->template->getValue('description') ?></h4>

    <div class="inner-nav-btns">
      <a href="#" class="btn"><i class="fa fa-plus"></i> Create a Project</a>
    </div>

    <?php var_dump(json_decode($this->input->post('formData'))); ?>
    <div class="templates-list widget">
      <h2>Task Templates: <a href="#" class="js-add-task-template-btn">+ Add Task</a> </h2>
      <div class="task-single"></div>
      <div class="task-list">
        <?php $templates = template()->getTemplates(); //var_dump($templates);
        foreach($templates as $template) include 'widgets/_task-template-details.php'; ?>
      </div>
    </div><!--/.templates-list-->

    <div class="roles-list entities-list widget">
      <h2>Manage Roles: </h2>
      <form method="post" class="boxed sidepanel-bg">
        <div class="form-group">
          <label for="roleField">New Role</label> <input type="text" id="roleField" name="role" placeholder="Enter a Role" />
        </div>
        <button class="btn submit"><i class="fa fa-plus"></i> Add Role</button>
        <?php $roles = (array) template()->getValue('roles'); ?>
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
        <?php $metaFields = (array) template()->getValue('metaFields'); ?>
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

  var $templateList = $('.templates-list'),
    newTaskFormVisible = false;

  $(document).on('click', ".template .entry.preview", function(){
    var $link = $(this),
      templateId = $link.attr('href').split('-')[1],
      $template = $(".template-" + templateId);
      selectTemplate($template, 300);
  });

  $(document).on('click', ".form .js-cancel-edit", function(){
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

  function addNewTask(callback){
    var $taskSingle = $(".templates-list .task-single");

    CS_API.call(
      '/ajax/task_template_form',
      function(){
        $taskSingle.html('<i class="fa fa-spinner fa-spin" style="margin-bottom: 18px;"></i>');
        $(".template.entry").removeClass('form-mode');
        newTaskFormVisible = true;
      },
      function(data){
        if(data.errors == false){
          $taskSingle.html(data.response);
          if(typeof callback == 'function'){
            callback();
          }
        }
      },
      function(){
        alertify.error('An error has occured while attempting to add a new task');
        newTaskFormVisible = false;
      },
      null,
      {
        method: 'GET',
        preferCache : false
      }
    );
  }

  $(".js-add-task-template-btn").on('click', function(e){
    e.preventDefault();
    if(!newTaskFormVisible){
      addNewTask(function(){
        $(".templates-list.widget").addClass('selected');
      });
    }
  });

  $(document).on('click','.js-update-task-template-btn', function(e){
    e.preventDefault();
    var $this = $(this),
      $template = $this.parents('.template.entry');
      post = {
        id : $template.find('.id-field').val(),
        name : $template.find('input[id^=field-name-]').val(),
        taskGroup : $template.find('input[id^=field-taskGroup-]').val(),
        description: $template.find('textarea[id^=field-description-]').val(),
        instructions: $template.find('textarea[id^=field-instructions-]').val(),
        milestone : null,
        clientView : null,
        estimatedTime : $template.find('input[id^=field-estimatedTime-]').val()
      };
    console.log(post);
    $template.find('[name=formData]').val(JSON.stringify(post));
    //$template.find('form').submit();
  });

</script>