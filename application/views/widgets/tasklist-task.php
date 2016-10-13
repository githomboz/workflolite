<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/3/16
 * Time: 1:39 PM
 */
?>

<div class="task-style ">
  <div class="col-title">
    <a href="#" class="checkbox clickable <?php echo $task->isComplete() ? 'checked' : ''?>">
      <i class="fa fa-check"></i>
    </a>
    <span class="task-name"><?php echo $task->getValue('name') ?> <a href="#" class="fa fa-pencil"></a></span>
  </div>
  <div class="col-meta">
    <div class="cols">
      <div class="col-1 col text-center"><?php if($date = $task->getStartDate('m/d/y')) echo $date; else echo '-'?> <a href="#" class="fa fa-pencil"></a></div>
      <div class="col-2 col text-center"><?php if($date = $task->getCompleteDate('m/d/y')) echo $date; else echo '-'?> <a href="#" class="fa fa-pencil"></a></div>
      <?php $isInput = ($task->isStarted() && !$task->isComplete()); ?>
      <div class="col-3 col text-left <?php if($isInput) echo 'input' ?>">
        <?php if($isInput) { ?>
          <input class="comments" placeholder="Enter a comment here" />
          <button type="submit"><i class="fa fa-save"></i></button>
          <button type="submit"><i class="fa fa-times"></i></button>
        <?php } else { ?>
          <?php echo $task->getValue('comments'); ?><a href="#" class="fa fa-pencil"></a>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

