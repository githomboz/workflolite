<div class="main-mid-section clearfix <?php echo page_file_name(__FILE__) ?>-page">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa fa-sticky-note-o"></i>Notes & Messages</h1>
    <h4>Keep track of important notes related to this job.</h4>

    <?php //var_dump(project()->saveTasks()) ?>

    <div class="main-column">
      <?php
      if($searchTerm = $this->input->get('s')){
        $notes = project()->searchNotes($searchTerm);
      } else {
        $notes = project()->getNotes();
      }


      if($searchTerm){
        echo '<h2 class="notes-search-results-message">Showing results for search term "' . $searchTerm . '"</h2>';
      }

      ?>
      <?php include 'widgets/_notes-form.php'; ?>
      <script>
        $(document).on('click', '.toggle-no-tags', function(e){
          e.preventDefault();
          var $el = $(".cs-add-note-form.no-tags");
          if($el.length){
            $el.addClass('show-tags').removeClass('no-tags');
          } else {
            $(".cs-add-note-form").addClass('no-tags').removeClass('show-tags');
          }
        });
      </script>


      <?php
      if(UserSession::loggedIn()){
        $current_author_id = user()->id();
        include 'widgets/_notes-list.php';
      }
      ?>

    </div><!--/.main-content-->
    <div class="inset-widgets boxed sidepanel-bg">
      <h2><i class="fa fa-search"></i> Search Notes</h2>
      <form class="search-form clearfix" method="get">
        <span class="cs-search-notes"><input class="cs-search-notes-field" name="s" value="<?php echo isset($searchTerm) ? $searchTerm : '' ?>" /></span> <button class="js-search-notes btn-style submit"><i class="fa fa-search"></i></button>
      </form>
      <h2><i class="fa fa-tags"></i> Tags from this Job</h2>
        <ul class="used-tags-list">
          <?php foreach(project()->getNoteTags() as $tag => $count) { ?>
          <li><a href="?s=<?php echo $tag ?>" class="tag <?php echo 'tag-' . md5($tag) ?>"><?php echo $tag ?></a> (<span class="count <?php echo 'tag-count-' . md5($tag) ?>"><?php echo $count ?></span>)</li>
          <?php } ?>
        </ul>
    </div>
  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->

<script type="application/javascript">

  CS_EditableContentDivs.init();

  function _handleDeleteNoteClicked(e){
    e.preventDefault();
    var $el = $(this),
      json =  $el.parents('.cs-note').data('payload'),
      post = {
        entityId : _CS_Get_Entity_ID(),
        type : _CS_Get_Entity(),
        noteId : $el.data('id'),
        tags : typeof json.tags != 'undefined' ? json.tags : []
      };
    //console.log(post);
    alertify.confirm('Are you sure you want to delete this note?',
      function(){
        CS_API.call(
          '/ajax/delete_note',
          function(){
            // before
          },
          function(data){
            if(data.errors == false) {
              // Get tags

              PubSub.publish('jobNote.deleted', post);

              for(var i in post.tags) PubSub.publish('jobChange.tagDeleted', post.tags[i]);

              alertify.success('Note deleted');
            } else {
              alertify.error('An error has occurred while attempting to delete this note');
            }
          },
          function(){
            // error
            alertify.error('An error has occurred while attempting to delete this note');
          },
          post
          ,
          {
            method: 'POST',
            preferCache : false
          }
        );
      },
      function () {

      }
    )
  }

  var handleTagDeleted = function(topic, tag){
    // Increment tag count
    var
      $tag = $(".tag.tag-" + md5(tag)),
      $tagCount = $(".count.tag-count-" + md5(tag)),
      count;

    function closeTag(tag){
      var $tag = $(".tag.tag-" + md5(tag));
      $tag.parent().fadeOut();
    }

    if($tag.length){
      count = $tagCount.text();
      if(count >= 2){
        count--;
        $tagCount.text(count);
      } else {
        closeTag(tag);
      }
    } else {
      closeTag(tag);
    }

  };


  var _handleNoteDeleted = function(topic, data){
    $("."+(_CS_Get_Entity() == 'Project' ? 'projects' : 'jobs')+"-notes-page .note-" + data.noteId).fadeOut();
  };

  PubSub.subscribe('jobNote.deleted', _handleNoteDeleted);

  $(document).on('click', '.js-delete-note', _handleDeleteNoteClicked);

  PubSub.subscribe('jobChange.tagDeleted', handleTagDeleted);

</script>