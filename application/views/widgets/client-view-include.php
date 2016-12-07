<div class="client-view clearfix">
  <header>
    <a class="cv-logo">
      <img src="<?php echo base_url() . $this->organization->getValue("image"); ?>" />
    </a>
  <span class="ref-id">
    Reference ID: <?php $entity = project() ? project() : job(); echo $entity->id(); ?>
  </span>
  </header>
  <section class="cv-main-body">
    <div class="title">File Progress</div>
    <div class="progress-bar">
      <?php
      $showableTasksGrouped = $entity->getClientViewableTasks(true);
      $showableTasks = array();
      foreach($showableTasksGrouped as $taskGroup => $tasks){ foreach($tasks as $task){ $showableTasks[] = $task; }}
      $completionPercentage = Job::CompletionPercentage($showableTasks); ?>
      <div class="bar" style="width: <?php echo $completionPercentage; ?>%"><?php echo $completionPercentage; ?>%</div>
    </div>
    <?php
    $lastTaskId = $showableTasks[(count($showableTasks)-1)]->id();
    ?>

    <?php if($entity->meta()->clientMeta()){ ?>

      <div class="job-meta">
        <h2>Job Details</h2>
        <?php foreach($entity->meta()->getAll() as $slug => $meta){ if(isset($meta['clientView']) && $meta['clientView'] === true) { ?>
          <div class="meta-group">
            <span class="meta-title"><?php echo $meta['field'] ?>: </span>
            <span class="meta-value"><?php echo strip_tags($meta['value']->display()) ?></span>
          </div>
        <?php } } ?>

      </div>
    <?php } ?>

    <?php foreach($showableTasks as $task){?>
      <div class="task clearfix">
        <span href="#" class="checkbox <?php if($task->isComplete()) echo 'checked' ?>">
          <i class="fa fa-check"></i>
        </span>
        <span class="name"><?php echo $task->getValue('name')?></span>
        <span class="description"><?php $description = $task->getValue('description'); if($description) echo $description; else echo '[ No description available for this task ]' ?></span>
      </div>
    <?php } ?>
  </section>
</div><!--/.client-view-->
<script type="text/javascript">
  $(document).ready(function(){
    $(".client-view .task").click(function(){
      var $this = $(this);
      if($this.is('.show-description')) $this.removeClass('show-description'); else $this.addClass('show-description');
    });
  });
</script>
