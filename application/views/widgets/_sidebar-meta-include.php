<div class="panel metadata-panel">
  <?php $entity = entity(); ?>
  <?php include '_sidebar-meta-inner-include.php'; ?>
</div><!--/.panel-->
<script type="text/javascript">
  $(document).on('click', '.js-toggle-meta', function(){
    var $this = $(this),
      showAll = $this.find('.action').html() == 'Show All';

    console.log(showAll);

    if(showAll){
      $(".cs-job-meta").addClass('show-all');
      $this.find('.action').html('Hide');
      $this.find('.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
    } else {
      $(".cs-job-meta").removeClass('show-all');
      $this.find('.action').html('Show All');
      $this.find('.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
    }

    return false;
  });

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
        type : _CS_Get_Entity(),
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
      alertify.alert('Error', error);
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

    alertify.confirm('Confirm', 'Are you sure you want to remove this item from the array?', function(){
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
      alertify.alert('Error', 'Invalid array key provided');
    }

//    console.log(getArrayData($metaTypeForm.data('slug')), $metaTypeForm.find('.array-list').html());
    return false;
  });


</script>