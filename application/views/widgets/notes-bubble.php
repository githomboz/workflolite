<div class="notes-box cs-notes-box">
  <div class="bubble">
    <?php
    $show_tags = true;
    include '_notes-form.php'; ?>
    <?php
    $notes = job()->getNotes();
    $notes_limit = 5;
    ?>
    <?php include '_notes-list.php'; ?>
    <a href="<?php echo site_url('jobs/' . $this->job->id() . '/notes') ?>" class="more">more notes <i class="fa fa-caret-down"></i></a>
  </div>
</div>
<script type="application/javascript">
  $tag_adder = CS_RenderBuddyInstances.newInstance({
    inputSelector                   : '.cs-notes-box .tags-field.field',
    itemsContainerSelector          : '.cs-notes-box .cs-note-tags',
    closeSelector                   : '.cs-notes-box .fa-times-circle',
    itemSelector                    : '.cs-notes-box .cs-note-tag',
    itemClass                       : 'cs-note-tag',
    itemTypePlural                  : 'tags',
    itemType                        : 'tag',
    overallWidgetContainer          : '.cs-add-note-form'
  });

  $jobs_page_tag_adder = CS_RenderBuddyInstances.newInstance({
    inputSelector                   : '.jobs-notes-page .tags-field.field',
    itemsContainerSelector          : '.jobs-notes-page .cs-note-tags',
    closeSelector                   : '.jobs-notes-page .fa-times-circle',
    itemSelector                    : '.jobs-notes-page .cs-note-tag',
    itemClass                       : 'cs-note-tag',
    itemTypePlural                  : 'tags',
    itemType                        : 'tag',
    overallWidgetContainer          : '.cs-add-note-form'
  });


  var processNoteSubmit = function(e){
    var $el = $(this),
      $form = $el.parents('.cs-add-note-form'),
      $tagsContainer = $form.find('.cs-note-tags'),
      $inputField = $form.find('.note-field'),
      placeholder = $form.data('placeholder'),
      instanceId = $tagsContainer.attr('data-instance'),
      renderBuddy = null,
      val = $inputField.text(),
      itemsJSON = $tagsContainer.attr('data-items'),
      POST = {
        jobId             : _CS_Get_Job_ID(),
        author            : {
          id              : _CS_Get_User_ID(),
          type            : 'user'
        },
        reference         : null, // {}
        note              : null,
        tags              : null
      };

    if(instanceId != '') {
      renderBuddy = CS_RenderBuddyInstances.getInstance(instanceId);
      if(renderBuddy) {
        renderBuddy = renderBuddy.instance;
      }
    }

    if(itemsJSON) POST.tags = JSON.parse(itemsJSON);

    if(val == placeholder) val = '';

    POST.note = val ? val.trim() : '';

    if(val != ''){
      CS_API.call('/ajax/post_note',
        function(){
          if($el.is('button')){
            $el.find('fa').removeClass('fa-paper-plane').addClass('fa-spinner fa-spin');
          }
        },
        function(data){
          if(data.errors == false && data.response.success){

            if($el.is('button')){
              $el.find('fa').addClass('fa-paper-plane').removeClass('fa-spinner fa-spin');
            }

            // Clear tags
            if(renderBuddy) {
              renderBuddy.clearItems().render();
            }
            // Clear note
            $inputField.html($inputField.data('placeholder'));
            PubSub.publish('jobNote.posted', data.response.payload);
            var newNote = $(data.response.noteHTML).html();

            $(".cs-notes-list").each(function(){
              $(this).prepend(newNote);
            });
            alertify.notify('Note posted successfully', 'success');

            // Register tags additions
            for(var i in data.response.payload.tags){
              PubSub.publish('jobChange.noteAdded', data.response.payload.tags[i]);
            }

          } else {
            alertify.notify('An error has occurred', 'error', 5);
          }
        },
        function(){
          alertify.notify('An error has occurred', 'error', 5);
        },
        POST,
        {
          method: 'POST',
          preferCache : false
        }
      );
    } else {
      alertify.error('Invalid note value');
    }

  };

  var handleTagAdded = function(topic, tag){
    // Increment tag count
    var
      $tag = $(".tag.tag-" + md5(tag)),
      $tagCount = $(".count.tag-count-" + md5(tag)),
      count = 0;

    if($tag.length){
        count = $tagCount.text();

      count++;
      $tagCount.text(count);
    } else {
      count++;
      var html = '<li><a href="?s='+tag+'" class="tag tag-' + md5(tag) + '">';
        html += tag;
        html += '</a> (<span class="count tag-count-' + md5(tag) + '">';
        html += count;
        html += '</span>)</li>';

      $(".used-tags-list").prepend(html);
    }

  };

  $(document).on('click', '.cs-notes-box .js-add-note-btn', processNoteSubmit);
  $(document).on('click', '.jobs-notes-page .js-add-note-btn', processNoteSubmit);

  PubSub.subscribe('jobChange.noteAdded', handleTagAdded);

</script>