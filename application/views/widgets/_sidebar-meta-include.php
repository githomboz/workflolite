<div class="panel">
  <h1><i class="fa fa-list"></i> Job Details</h1>
  <div class="job-meta">
    <?php foreach($this->job->getMeta() as $key => $meta){
      $metaSettings = $this->workflow->getMetaSettings($key);
      ?>
      <div class="meta-pair">
        <span class="title"><?php echo $metaSettings['field'] ?>: </span>
        <?php
        $valueLength = strlen($meta['value']->display());
        $valueContent = $meta['value']->display();
        if($meta['value'] instanceof MetaUrl) {
          $valueLength = strlen($meta['value']->get());
          $valueContent = $meta['value']->display();
        }
        if($meta['value'] instanceof MetaUrl) {
          $valueLength = strlen($meta['value']->get());
          $valueContent = $meta['value']->display();
        }
        if($meta['value'] instanceof MetaDateTime) {
          $valueLength = (strlen($meta['value']->display()) - strlen(' <a href=#add_to_calendar"><i class="fa fa-calendar-plus-o"></i></a>')) + 2;
          $valueContent = $meta['value']->display();
        }
        $multiLine = is_string($valueContent) ? ((strlen($key) + $valueLength + 2) > 33) : true;

        ?>
        <span class="value <?php if($multiLine) echo 'multi-line'?>"><?php echo $valueContent ?></span>
      </div><!--/.meta-pair-->
    <?php } ?>
  </div><!--/.job-meta-->
</div><!--/.panel-->
