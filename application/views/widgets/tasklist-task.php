<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/3/16
 * Time: 1:39 PM
 */

$status = 'completed';
if($i >= 3) $status = '';
$checked = 'checked';
if($status != 'completed') $checked = '';
$overdue = '';
if($i == 3) $overdue = 'overdue';
?>

<div class="task <?php echo $status; echo ' ' . $overdue; ?>">
  <div class="col-title">
    <a href="#" class="checkbox <?php echo $checked ?>">
      <i class="fa fa-check"></i>
    </a>
    <span class="task-name">Task name <a href="#" class="fa fa-pencil"></a></span>
  </div>
  <div class="col-meta">
    <div class="cols">
      <div class="col-1 col text-center">8/28 <a href="#" class="fa fa-pencil"></a></div>
      <div class="col-2 col text-center">8/28 <a href="#" class="fa fa-pencil"></a></div>
      <div class="col-3 col text-left <?php if($i == 3) echo 'input' ?>">
        <?php if($i == 3) { ?>
          <input class="comments" placeholder="Enter a comment here" />
          <button type="submit"><i class="fa fa-save"></i></button>
          <button type="submit"><i class="fa fa-times"></i></button>
        <?php } else { ?>
          Letters Sent <a href="#" class="fa fa-pencil"></a>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

