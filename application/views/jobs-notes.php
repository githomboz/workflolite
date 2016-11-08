<div class="main-mid-section clearfix <?php echo page_file_name(__FILE__) ?>-page">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa fa-sticky-note-o"></i>Notes & Messages</h1>
    <h4>Keep track of important notes related to this job.</h4>

    <div class="main-column">
      <?php
      if($searchTerm = $this->input->get('s')){
        $notes = job()->searchNotes($searchTerm);
      } else {
        $notes = job()->getNotes();
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
      include 'widgets/_notes-list.php';
      ?>

    </div><!--/.main-content-->
    <div class="inset-widgets boxed sidepanel-bg">
      <h2><i class="fa fa-search"></i> Search Notes</h2>
      <form class="search-form clearfix" method="get">
        <span class="cs-search-notes"><input class="cs-search-notes-field" name="s" value="<?php echo isset($searchTerm) ? $searchTerm : '' ?>" /></span> <button class="js-search-notes btn-style submit"><i class="fa fa-search"></i></button>
      </form>
      <h2><i class="fa fa-tags"></i> Tags from this Job</h2>
        <ul class="used-tags-list">
          <?php foreach(job()->getNoteTags() as $tag => $count) { ?>
          <li><a href="?s=<?php echo $tag ?>" class="tag <?php echo 'tag-' . md5($tag) ?>"><?php echo $tag ?></a> (<span class="count <?php echo 'tag-count-' . md5($tag) ?>"><?php echo $count ?></span>)</li>
          <?php } ?>
        </ul>
    </div>
  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->

<script type="application/javascript">

  CS_EditableContentDivs.init();

</script>