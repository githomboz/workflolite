<div class="main-mid-section clearfix <?php echo page_file_name(__FILE__) ?>-page">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa fa-sticky-note-o"></i>Notes & Messages</h1>
    <h4>Keep track of important notes related to this job.</h4>

    <div class="main-column">

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
          'verb' => 'on',
          'noun' => 'Task #3'
        ),
        array(
          'author' => 'Deana C.',
          'datetime' => '11/10/2016 5:02pm',
          'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed a fringilla nibh. Vivamus tempus risus rhoncus, lacinia orci eget, pulvinar ante. Duis euismod diam quis tellus maximus, vitae volutpat lacus ultricies. Ut eu condimentum tortor, vel posuere eros. Aliquam erat volutpat. Phasellus sem arcu, lobortis sed purus commodo, pellentesque volutpat velit. Aenean ornare porttitor mauris. Ut condimentum efficitur massa, at laoreet neque scelerisque in. Donec sit amet sem aliquet turpis',
          'tags' => array('Legal Theories'),
          'verb' => 'on',
          'noun' => 'Task #2'
        ),
      );

      include 'widgets/_notes-list.php';
      ?>

    </div><!--/.main-content-->
    <div class="inset-widgets boxed sidepanel-bg">
      <h2><i class="fa fa-search"></i> Search Notes</h2>
      <div class="search-form clearfix">
        <span class="cs-search-notes"><input class="cs-search-notes-field" /></span> <button class="js-search-notes btn-style submit"><i class="fa fa-search"></i></button>
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