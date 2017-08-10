<h1><i class="fa fa-list"></i> Job<?php  //echo project() ? 'Project' : 'Job' ?> Details</h1>
<?php
$metaRecords = $entity->getMeta();
$totalMeta = 0;
$hideMeta = 0;
foreach($metaRecords as $key => $meta){
  $totalMeta ++;
  if($meta['hide']) $hideMeta ++;
}

//var_dump($metaRecords);

?>
<div class="job-meta cs-job-meta <?php if(!$hideMeta) echo 'show-all';?>">
  <script class="metadata-script">
    var _METADATA = <?php echo json_encode($entity->getMetaArray()); ?>;
  </script>
  <?php
  foreach($metaRecords as $key => $meta){
    //$metaSettings = template()->getMetaSettings($key);
    //var_dump($metaRecords, $meta, $key);
    ?>
    <div class="meta-pair meta-<?php echo $meta['slug'] ?> <?php if($meta['hide']) echo 'not-priority '; else echo 'priority '; ($value = $meta['value'] instanceof MetaObject ? $meta['value']->get() : null); if(is_null($value) || ($meta['value'] instanceof MetaArray && empty($value))) echo 'edit-mode '?>">
      <span class="meta-title truncate"><?php echo $meta['field'] ?>: </span>
      <?php
      $valueContent = '';
      $multiLine = false;
      if($meta['value'] instanceof MetaObject){
        $valueLength = $meta['value']->displayLength();
        $valueContent = $meta['value']->flush()->display();
        if($meta['value'] instanceof MetaUrl) {
          $valueLength = $meta['value']->displayLength();
          $valueContent = $meta['value']->flush()->display();
        }
        if($meta['value'] instanceof MetaUrl) {
          $valueLength = $meta['value']->displayLength();
          $valueContent = $meta['value']->flush()->display();
        }
        if($meta['value'] instanceof MetaDateTime) {
          //var_dump($meta);
          $valueLength = $meta['value']->displayLength($meta['formatDefault']);
          $valueContent = $meta['value']->flush()->display($meta['formatDefault']);
        }
        $multiLine = is_string($valueContent) ? ((strlen($key) + $valueLength + 2) > 33) : true;

      }
      ?>
      <span class="meta-value truncate <?php if($multiLine) echo 'multi-line'?>"><?php echo $valueContent ?>&nbsp; <a href="#editMeta-<?php echo $meta['slug']?>" class="fa fa-pencil js-edit-mode"></a></span>
      <?php echo get_include(MetaObject::getFormHtmlPath($meta['type']), array('meta' => $meta, 'entity' => $entity), true);?>
    </div><!--/.meta-pair-->
  <?php } ?>
  <a href="#toggleMetaShowAll" class="js-toggle-meta link-blue"><i class="fa fa-caret-down"></i> <span class="action">Show All</span> Meta (<span class="meta-data-count"><?php echo $totalMeta ?></span>) </a>
</div><!--/.job-meta-->
