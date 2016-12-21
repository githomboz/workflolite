<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><?php echo template()->name() ?></h1>
    <h4><?php echo template()->getValue('description') ?></h4>

    <div class="inner-nav-btns">
      <a href="#" class="btn"><i class="fa fa-plus"></i> Create a Project</a>
    </div>

    <?php $versions = $this->versions;
    ?>
    <?php //var_dump(json_decode($this->input->post('formData')), $this->input->post('templateId'));

//    var_dump(template()->applyUpdates(array(
//      'noun'=>'title file',
//      'taskTemplateChanges' => array(
//        '57fa9e82239409c44d0041ab' => array(
//          'name' => 'Title Owners Insurance Policy'
//        ),
//        '57fa92ac239409c44d0041a9' => array(
//          'estimatedTime' => 4,
//          'name' => 'Engagement Letters Sent to Parties',
//          'sortOrder' => 1,
//        )
//      )
//    ), $version));

    ?>
    <div class="template-versions widget">
      <h2>Template Versions: <span class="versions">
          <?php for($i = 1; $i < $versions['highest']; $i ++) {
            $active = $versions['save'] == $i;
            echo ' <a href="?ver='.$i.'"';
            if($active) echo ' class="active"';
            echo '>v' . ($i);
            if($active) echo ' (active)';
            echo '</a>, ';
          } ?>
          <?php if($versions['save'] != $versions['highest']) {
            echo '<a href="?ver='.$versions['highest'].'">Create New Version</a>';
          } else {
            echo '<a href="?ver='. $versions['highest'].'" class="active">v' . $versions['save'] . ' (pending)</a>';
          }
          ?>
        </span> </h2>
    </div>

    <?php //var_dump() ?>

    <div class="templates-list widget">
      <h2>Task Templates: (<?php echo template()->taskCount(); ?>) <a href="#" class="js-add-task-template-btn">+ Add Task</a> </h2>
      <div class="task-single"></div>
      <div class="task-list">
        <?php $templates = template()->setVersion($this->version)->getTemplates();
        //var_dump($templates);
        $templateCount = count($templates);
        foreach($templates as $template) include 'widgets/_task-template-details.php'; ?>
      </div>
    </div><!--/.templates-list-->

    <div class="roles-list entities-list widget">
      <h2>Manage Roles: </h2>
      <form method="post" class="boxed sidepanel-bg">
        <input type="hidden" name="formAction" value="addRole" />
        <div class="form-group">
          <label for="roleField">New Role</label> <input type="text" id="roleField" name="role" placeholder="Enter a Role" />
        </div>
        <button class="btn submit"><i class="fa fa-plus"></i> Add Role</button>
        <?php $roles = (array) template()->getValue('roles'); ?>
        <?php if(!empty($roles)){?>
        <div class="roles entities dynamic-list">
          <?php foreach($roles as $i => $role) {?>
          <div class="list-item entity role" data-role_key="<?php echo $i ?>" data-role="<?php echo $role; ?>"><span class="text"><?php echo $role; ?></span> <a href="#close" data-role="<?php echo $role; ?>" class="close fa fa-times delete-role-btn"></a></div>
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
              <div class="list-item entity meta-field" data-slug="<?php echo $metaField['slug'] ?>">
                <span class="text"><?php echo $metaField['field'] . " ( {$metaField['type']}" . (isset($metaField['_']) ? '[' . $metaField['_'] . ']' : '') .  " )";
                  ?></span>
                <a href="#close" data-slug="<?php echo $metaField['slug'] ?>" class="close fa fa-times remove-meta-btn"></a></div>
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

  $(document).on('click', '.dynamic-list .delete-role-btn', function(){
    var $link = $(this),
      role = $link.data('role'),
      inAction = false,
      post = {
        role : role,
        templateId : _CS_Get_Template_ID(),
        version : _CS_Get_Template_Version()
      };
    alertify.confirm('Are you sure you want to remove this role?', function(){
      if(!inAction){
        CS_API.call('/ajax/remove_role',
          function(){
            $link.removeClass('fa-times error').addClass('fa-spin fa-spinner');
            inAction = true;
          },
          function(data){
            inAction = false;
            console.log(data);
            if(data.errors == false){
              $link.addClass('fa-times').removeClass('fa-spin fa-spinner error');
              if(data.response.success){
                $('.list-item.entity.role[data-role="'+role+'"]').fadeOut();
              } else {
                handleListLinkError($link, 'ER03: An error has occurred while attempting to remove role');
              }
            } else {
              handleListLinkError($link, 'ER02: An error has occurred while attempting to remove role');
            }
          },
          function(){
            handleListLinkError($link, 'ER01: An error has occurred while attempting to remove role');
            inAction = false;
          },
          post
          ,
          {
            method: 'POST',
            preferCache : false
          }
        );
      }
    });
  });

  $(document).on('click', '.dynamic-list .remove-meta-btn', function(){
    var $link = $(this),
      slug = $link.data('slug'),
      inAction = false,
      post = {
        metaKey : slug,
        templateId : _CS_Get_Template_ID(),
        version : _CS_Get_Template_Version()
      };
    console.log($link, post);
    alertify.confirm('Are you sure you want to remove this meta value?',
    function(){
      if(!inAction){
        CS_API.call('/ajax/remove_meta',
          function(){
            $link.removeClass('fa-times error').addClass('fa-spin fa-spinner');
            inAction = true;
          },
          function(data){
            inAction = false;
            if(data.errors == false){
              $link.addClass('fa-times').removeClass('fa-spin fa-spinner error');
              if(data.response.success){
                $('.list-item.entity.meta-field[data-slug="'+slug+'"]').fadeOut();
              } else {
                handleListLinkError($link, 'ER03: An error has occurred while attempting to remove meta field');
              }
            } else {
              handleListLinkError($link, 'ER02: An error has occurred while attempting to remove meta field');
            }
          },
          function(){
            handleListLinkError($link, 'ER01: An error has occurred while attempting to remove meta field');
            inAction = false;
          },
          post
          ,
          {
            method: 'POST',
            preferCache : false
          }
        );
      }
    });

  });

  function handleListLinkError($link, message){
    alertify.error(message || 'An error has occurred');
    $link.addClass('fa-warning error').removeClass('fa-spin fa-spinner fa-times');
  }

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
    var $taskSingle = $(".templates-list .task-single"),
      post = {
        templateId : _CS_Get_Template_ID(),
        version : _CS_Get_Template_Version()
      };

    console.log(post);

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
        alertify.error('An error has occurred while attempting to add a new task');
        newTaskFormVisible = false;
      },
      post
      ,
      {
        method: 'POST',
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

  function validateTaskTemplateChange(currentData, newData){
    var rtn = {
      hasChanges : false,
      fieldsAffected : [],
      updates : {}
    };

    for( var field in newData ){
      if(currentData[field] !== newData[field]){
        rtn.fieldsAffected.push(field);
        rtn.updates[field] = newData[field];
      }
    }

    rtn.hasChanges = rtn.fieldsAffected.length > 0;
    return rtn;
  }

  $(document).on('click','.js-update-task-template-btn', function(e){
    e.preventDefault();
    var $this = $(this),
      $template = $this.parents('.template.entry'),
      currentData = $template.data('current'),
      post = {
        id : $template.find('.task-template-id').val(),
        name : $template.find('input[id^=field-name-]').val(),
        taskGroup : $template.find('input[id^=field-taskGroup-]').val(),
        description: $template.find('textarea[id^=field-description-]').val(),
        instructions: $template.find('textarea[id^=field-instructions-]').val(),
        milestone : $template.find('[type=checkbox].milestone').is(':checked'),
        clientView : $template.find('[type=checkbox].clientView').is(':checked'),
        estimatedTime : $template.find('input[id^=field-estimatedTime-]').val(),
        sortOrder : $template.find('select.sortOrder :selected').val()
      };


    if(post.estimatedTime.trim() != '') post.estimatedTime = parseInt(post.estimatedTime); else post.estimatedTime = null;
    if(post.sortOrder.trim() != '') post.sortOrder = parseInt(post.sortOrder);

    var formValidation = validateTaskTemplateChange(currentData, post);
    if(formValidation.hasChanges){
      $this.find('.fa-save').removeClass('fa-save').addClass('fa-spin fa-spinner');
      $template.find('[name=formData]').val(JSON.stringify(post));
      $template.find('[name=templateId]').val(_CS_Get_Template_ID());
      $template.find('form').submit();
    } else {
      alertify.alert('No updates made to this task template.');
    }
  });



</script>