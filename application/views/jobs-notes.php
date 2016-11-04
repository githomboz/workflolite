<div class="main-mid-section clearfix <?php echo page_file_name(__FILE__) ?>-page">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa fa-sticky-note-o"></i>Notes & Messages</h1>
    <h4>Keep track of important notes related to this job.</h4>

    <div class="main-column">

      <div class="cs-add-note-form no-tags">
        <div class="inner-note">
          <div class="avatar">
            <img src="#" />
          </div>
          <div class="note-field" contenteditable="true" data-placeholder="Click here to <strong><em>leave a note</em></strong>" >
          </div>
          <div class="cs-note-tags tags-field clearfix">
            <span class="cs-note-tag">Tag 1 <a href="#" class="fa fa-times-circle"></a></span>
            <span class="cs-note-tag">Tag 2 <a href="#" class="fa fa-times-circle"></a></span>
            <span class="cs-note-tag">Tag 3 <a href="#" class="fa fa-times-circle"></a></span>
            <span class="cs-note-tag">Tag 4 <a href="#" class="fa fa-times-circle"></a></span>
          </div>
          <div class="tags-submit">
            <label class="tags-field title"><i class="fa fa-tags"></i> Tags: </label>
            <input class="tags-field field" type="text" />
            <button class="js-add-note-btn btn-style submit"><i class="fa fa-paper-plane"></i> Post</button>
          </div>
          <a class="toggle-no-tags"><i class="fa fa-tags"></i> Add Tags</a>
        </div>
      </div><!--/.cs-add-note-form-->

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

      $notes = array(
        array(
          'author' => 'Jim B.',
          'datetime' => '11/13/2016 3:30pm',
          'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed a fringilla nibh. Vivamus tempus risus rhoncus, lacinia orci eget, pulvinar ante. Duis euismod diam quis tellus maximus, vitae volutpat lacus ultricies. Ut eu condimentum tortor, vel posuere eros. Aliquam erat volutpat. Phasellus sem arcu, lobortis sed purus commodo, pellentesque volutpat velit. Aenean ornare porttitor mauris. Ut condimentum efficitur massa, at laoreet neque scelerisque in. Donec sit amet sem aliquet turpis',
          'tags' => array('Legal Theories','foreclosures','bankruptcy'),
          'verb' => '',
          'noun' => ''
        ),
        array(
          'author' => 'Deana C.',
          'datetime' => '11/11/2016 12:17pm',
          'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed a fringilla nibh. Vivamus tempus risus rhoncus, lacinia orci eget, pulvinar ante. Duis euismod diam quis tellus maximus, vitae volutpat lacus ultricies. Ut eu condimentum tortor, vel posuere eros. Aliquam erat volutpat. 

Phasellus sem arcu, lobortis sed purus commodo, pellentesque volutpat velit. Aenean ornare porttitor mauris. Ut condimentum efficitur massa, at laoreet neque scelerisque in. Donec sit amet sem aliquet turpis eleifend sollicitudin. Vestibulum non malesuada justo. Morbi eget augue eu mi rutrum semper. Sed iaculis non ante id feugiat. In sodales arcu non ornare bibendum. Curabitur feugiat consectetur blandit.',
          'tags' => array('delays'),
          'verb' => 'posted about',
          'noun' => 'Task #3'
        ),
        array(
          'author' => 'Deana C.',
          'datetime' => '11/10/2016 5:02pm',
          'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed a fringilla nibh. Vivamus tempus risus rhoncus, lacinia orci eget, pulvinar ante. Duis euismod diam quis tellus maximus, vitae volutpat lacus ultricies. Ut eu condimentum tortor, vel posuere eros. Aliquam erat volutpat. Phasellus sem arcu, lobortis sed purus commodo, pellentesque volutpat velit. Aenean ornare porttitor mauris. Ut condimentum efficitur massa, at laoreet neque scelerisque in. Donec sit amet sem aliquet turpis',
          'tags' => array('Legal Theories'),
          'verb' => 'posted about',
          'noun' => 'Task #2'
        ),
      );


      ?>

      <div class="cs-notes-list">
        <?php foreach($notes as $i => $note){  ?>
          <div class="cs-note">
            <div class="avatar">
              <img src="#" />
            </div>
            <div class="top-bar">
          <span class="author-narrative">
            <span class="author"><?php echo $note['author'] ?></span>
            <span class="verb"><?php echo $note['verb'] ?></span>
            <a href="#" class="noun"><?php echo $note['noun'] ?></a>
          </span>
          <span class="datetime">
            <span class="date"><?php echo date('m-d-Y', strtotime($note['datetime'])) ?></span>
            <span class="time-text">
              at <span class="time"><?php echo date('g:ia', strtotime($note['datetime'])) ?></span>
            </span>
          </span>
            </div>
            <div class="note-content">
              <?php

              $content = $note['content'];

              $content = explode("\n", $content);

              foreach($content as $paragraph){
                if(trim($paragraph) != ''){
                  echo '<p>';
                  echo $paragraph;
                  echo '</p>';
                }
              }

              ?>
            </div>
            <div class="tags">
              <i class="fa fa-tags"></i> Tags: <a href="#">Legal Theories</a>, <a href="#">foreclosures</a>, <a href="#">bankruptcy</a>
            </div>
          </div>
        <?php } ?>
      </div>
    </div><!--/.main-content-->
    <div class="inset-widgets boxed sidepanel-bg">
      <h2><i class="fa fa-search"></i> Search Notes</h2>
      <div class="search-form">
        <input class="cs-search-notes" /><button class="js-search-notes btn-style submit"><i class="fa fa-search"></i></button>
      </div>
      <h2><i class="fa fa-tags"></i> Tags</h2>
        <ul class="used-tags-list">
          <li><a href="#">bankruptcy</a> (1)</li>
          <li><a href="#">delays</a> (1)</li>
          <li><a href="#">foreclosures</a> (1)</li>
          <li><a href="#">Legal Theories</a> (2)</li>
        </ul>
    </div>
  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->

<script type="application/javascript">

  CS_EditableContentDivs.init();

</script>