<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/10/16
 * Time: 4:20 PM
 */

if(!isset($object)) $object = 'project';

$seg1 = isset($this->$object) ? $this->$object->id() : null;
$navItems = _get_inner_nav($this->navSelected, $seg1);
$defaultItem = _get_inner_nav_default($navItems);
?>
<ul class="inner-nav clearfix">
  <?php foreach($navItems as $i => $navItem) { $this->innerNavSelected = isset($this->innerNavSelected) ? $this->innerNavSelected : $defaultItem['slug'];?>
    <?php $hide = isset($navItem['hide']) && $navItem['hide'] == true; if(!$hide) {?>
    <li class="<?php
    if($this->innerNavSelected == $navItem['slug']) echo 'active ';
    if($navItem['slug'] == 'notes') echo ' notes-btn'; ?>">
      <a href="<?php echo site_url($navItem['href']) ?>" class="<?php if($navItem['slug'] == 'notes') echo 'double-click' ?>"><?php echo $navItem['name'] ?></a></li>
  <?php } } ?>
</ul>
