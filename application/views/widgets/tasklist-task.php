<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/3/16
 * Time: 1:39 PM
 */

//var_dump($task->getValue('trigger'));
?>

<div class="task-style task-<?php echo $task->id(); ?> <?php echo $task->isComplete() ? 'completed' : ''?> <?php echo $task->isStarted() ? 'started' : ''?> <?php echo $task->isErrored() ? 'errors' : ''?>"
     data-task_id="<?php echo $task->id(); ?>"
>
  <div class="task-settings-widget bubble">
    <form method="post" class="clearfix">
      <div class="field"><label>Est. Completion (hrs): </label> <span class="form-field"><input type="text" placeholder="Ex. 2.5" /></span></div>
      <div class="field"><label>Due Date: </label> <span class="form-field"><input type="text" placeholder="Ex. <?php echo date('m/d/Y') ?>" /></span></div>
      <div class="field select"><label>Assigned To: </label>
        <span class="form-field">
          <select>
            <option value="">Select Assignee</option>
            <?php
            if(!isset($users)) $users = array();
            foreach((array)$users as $user){
              echo '<option value="'. (string) $user->id() .'">' . $user->getName() . '</option>';
            }
            ?>
          </select>
        </span>
      </div>
      <div class="field links">
        <a class="link-blue" href="#"><i class="fa fa-fast-forward"></i> Skip</a> |
        <a class="link-blue" href="#"><i class="fa fa-trash"></i> Delete</a> <?php //@todo unset templateId or update template ?>
      </div>
      <button type="submit" class="btn submit"><i class="fa fa-save"></i> Save Settings</button>
    </form>
  </div>
  <span class="task-option-links">
    <a href="#" class="fa fa-reorder task-drag"></a>
    <a href="#" class="fa fa-cog task-settings"></a>
  </span>
  <div class="col-title">
    <a href="#" class="checkbox <?php echo $task->isComplete() ? 'checked' : 'clickable'?>">
      <i class="fa fa-check"></i>
    </a>
    <span class="task-name <?php echo $task->getValue('trigger') ? 'has-trigger':'' ?>"><?php echo $task->getValue('name') ?> <a href="#taskOptions-<?php echo $task->id(); ?>" class="fa fa-pencil"></a></span>
  </div>
  <div class="col-meta">
    <div class="cols">
      <div class="col-1 col text-center start">
        <?php if($task->isStarted()) { $date = $task->getStartDate('m/d/y'); ?>
        <?php if($date) echo $date . ' <a href="#editStart-'. $task->id() . '" class="fa fa-pencil"></a>'; ?>
        <?php } else  { ?>
          <a href="#startTask-<?php echo $task->id() ?>" class="fa fa-clock-o start-task"></a>
        <?php } ?>
      </div>
      <div class="col-2 col text-center complete">
        <?php if($date = $task->getCompleteDate('m/d/y')) { ?>
        <?php if($date) echo $date . ' <a href="#editEnd-'. $task->id() . '" class="fa fa-pencil"></a>'; ?>
        <?php } else  { ?>
          <?php if($task->isStarted()) { ?><a href="#markComplete-<?php echo $task->id(); ?>" class="fa fa-check link-blue js-mark-complete"></a><?php } ?>
        <?php } ?>
        <div class="time-changer">
          &nbsp;
        </div>
      </div>
      <div class="col-3 col text-left">
          <div class="comment-form">
            <input class="comments" placeholder="Enter a comment here" />
            <button id="#saveComment-<?php echo $task->id() ?>" class="js-save-comment" type="submit"><i class="fa fa-save"></i></button>
            <button id="#closeComments-<?php echo $task->id() ?>" class="js-close-comments" type="submit"><i class="fa fa-times"></i></button>
          </div>
        <?php $comment = trim($task->getValue('comments'));?>
          <div class="comment-content <?php if($comment == '') echo 'no-comment'; ?>">
            <div class="no-comment container">
              <a href="#leaveComment-<?php echo $task->id() ?>" class="js-leave-comment" >Leave a comment</a> &nbsp;
            </div>
            <div class="has-comment container">
              <span class="comment"><?php echo $comment;?></span> <a href="#editComment-<?php echo $task->id(); ?>" class="fa fa-pencil js-leave-comment"></a>
            </div>
          </div>
      </div>
    </div>
  </div>
</div>

