<nav class="clearfix">
  <ul>
    <?php
    $navItems = array('dashboard','workflows','jobs','contacts','users','search');
    foreach($navItems as $navItem){
      $active = $this->navSelected == $navItem;
      if(!$active) {
        if($navItem == 'jobs' && $this->navSelected == 'jobsInner') $active = true;
      }
      ?>
      <li class="<?php if($active) echo 'active';?>"><a href="<?php echo site_url($navItem); ?>"><?php echo $navItem ?></a></li>
    <?php } ?>
  </ul>
</nav>