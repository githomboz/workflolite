<div class="panel">
  <h1><i class="fa fa-list"></i> Job Details</h1>
  <div class="job-meta">
    <?php foreach($this->job->getMeta() as $key => $meta){
      $metaSettings = $this->workflow->getMetaSettings($key);
      ?>
      <div class="meta-pair meta-<?php echo $meta['slug'] ?> <?php if($meta['hide']) echo 'not-priority '; else echo 'priority '; if(is_null($meta['value']->get()) || ($meta['value'] instanceof MetaArray && empty($meta['value']->get()))) echo 'edit-mode '?>">
        <span class="meta-title"><?php echo $metaSettings['field'] ?>: </span>
        <?php
        $valueLength = strlen($meta['value']->display());
        $valueContent = $meta['value']->display();
        if($meta['value'] instanceof MetaUrl) {
          $valueLength = strlen($meta['value']->get());
          $valueContent = $meta['value']->display();
        }
        if($meta['value'] instanceof MetaUrl) {
          $valueLength = strlen($meta['value']->get());
          $valueContent = $meta['value']->display();
        }
        if($meta['value'] instanceof MetaDateTime) {
          $valueLength = (strlen($meta['value']->display()) - strlen(' <a href=#add_to_calendar"><i class="fa fa-calendar-plus-o"></i></a>')) + 2;
          $valueContent = $meta['value']->display();
        }
        $multiLine = is_string($valueContent) ? ((strlen($key) + $valueLength + 2) > 33) : true;

        ?>
        <span class="meta-value <?php if($multiLine) echo 'multi-line'?>"><?php echo $valueContent ?> <a href="#editMeta-<?php echo $meta['slug']?>" class="fa fa-pencil js-edit-mode"></a></span>
        <?php echo get_include(MetaObject::getFormHtmlPath($meta['type']), array('meta' => $meta), true);?>
      </div><!--/.meta-pair-->
    <?php } ?>
  </div><!--/.job-meta-->
</div><!--/.panel-->
<script type="text/javascript">
  $(document).on('click', '.js-edit-mode', function(){
    var $this = $(this),
      $metaContainer = $this.parents('.meta-pair');
    $metaContainer.toggleClass('edit-mode');
    if($metaContainer.is(".edit-mode")){
      $metaContainer.find('input').focus();
    }
    return false;
  });

  $(document).on('click', '.meta-pair button.submit', function() {
    var $btn = $(this),
      $metaPair = $btn.parents('.meta-pair'),
      $metaContainer = $btn.parents('.meta-type-form'),
      metaType = $metaContainer.data('type'),
      post = {
        record : $metaContainer.data('record'),
        collection : $metaContainer.data('collection'),
        field : $metaContainer.data('slug'),
        metaObject : $metaContainer.data('interface')
      },
      valueChange;

    switch (metaType) {
      case 'address':
        post.value = {
          street : $metaContainer.find('input[name=meta_' + post.field + '_street]').val(),
          city : $metaContainer.find('input[name=meta_' + post.field + '_city]').val(),
          state : $metaContainer.find('[name=meta_' + post.field + '_state]').find(":selected").attr('value'),
          zip : $metaContainer.find('input[name=meta_' + post.field + '_zip]').val()
        };
        break;
      case 'array':
        post.value = getArrayData(post.field);
        break;
      default:
        post.value = $metaContainer.find('input').val();
        break;
    }

    valueChange = $metaContainer.attr('data-saved') != JSON.stringify(post.value);

    function handleSaveError(error){
      alertify.alert(error);
    }

    if(valueChange){
      CS_API.call(
        '/ajax/save_meta',
        function(){
          // before
          $btn.find('.fa').removeClass('fa-save').addClass('fa-spinner fa-spin');
        },
        function(data){
          if(data.errors == false){
            $metaContainer.attr('data-saved', JSON.stringify(post.value));
            console.log(data);
            $metaPair.removeClass('edit-mode');
            switch (metaType) {
              default:
                $metaPair.find('.meta-value').html((data.response.display || 'Invalid Value') + ' <a href="#editMeta-fileNumber" class="fa fa-pencil js-edit-mode"></a>');
                break;
            }
          } else {
            console.log(data);
            if(typeof data.errors[0] != 'undefined') handleSaveError(data.errors[0]);
          }
          $btn.find('.fa').addClass('fa-save').removeClass('fa-spinner fa-spin');
        },
        function(){
          // error
          handleSaveError('An error has occurred while trying to save.');
        },
        post,
        {
          method: 'POST',
          preferCache : false
        }
      );
      console.log(post);
    }

    return false;
  });

  function getArrayData(slug){
    var arrayData = {};
    $('.meta-' + slug).find(".array-list .array-group").each(function(){
      var $thisGroup = $(this),
        key = $thisGroup.find(".array-key input").val(),
        val = $thisGroup.find(".array-value input").val();
      if($thisGroup.is(":visible")){
        if(key.trim() != ''){
          arrayData[key] = val;
        }
      }
    });
    return arrayData;
  }

  $(document).on('click', '.meta-type-form.array .fa-minus-circle', function(){
    var $btn = $(this), $group = $btn.parents('.array-group'), $metaTypeForm = $group.parents('.meta-type-form');

    alertify.confirm('Are you sure you want to remove this item from the array?', function(){
      $group.hide();
      alertify.notify('Item removed');
    }, function(){});
    return false;
  });

  $(document).on('click', '.meta-type-form.array .fa-plus-circle', function(){
    var $btn = $(this),
      $group = $btn.parents('.array-group'),
      $metaTypeForm = $group.parents('.meta-type-form'),
      key = $group.find('input:first').val(),
      value = $group.find('input:last').val();

    if(key.trim() != ''){

      function addNewArrayItem(){
        var $newGroup = $group.clone();
        $newGroup.find('.fa-plus-circle').removeClass('fa-plus-circle').addClass('fa-minus-circle');
        $newGroup.remove('main');

        $metaTypeForm.find('.array-list').append($newGroup);
        $group.find('input').val('');
        $group.find('input:first').focus();
      }

      if(value.trim() == ''){
        alertify.confirm('Are you sure you want to add this item with an empty value?', function(){
          addNewArrayItem();
        },
        function(){
          alertify.notify('Insertion cancelled');
        });
      } else {
        addNewArrayItem();
      }
    } else {
      alertify.alert('Invalid array key provided');
    }

//    console.log(getArrayData($metaTypeForm.data('slug')), $metaTypeForm.find('.array-list').html());
    return false;
  });


</script>