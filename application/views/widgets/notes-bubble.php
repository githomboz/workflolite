<div class="notes-box cs-notes-box">
  <div class="bubble">
    <?php
    $show_tags = true;
    include '_notes-form.php'; ?>
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
    ?>
    <?php include '_notes-list.php'; ?>
    <a href="<?php echo site_url('jobs/' . $this->job->id() . '/notes') ?>" class="more">more notes <i class="fa fa-caret-down"></i></a>
  </div>
</div>
