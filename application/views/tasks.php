<?php //var_dump($this->job); ?>
<?php //var_dump($this->job->getActionableTasks()); ?>
<div class="main-mid-section clearfix">
    <div class="main-mid-section-inner clearfix">

        <h1><i class="fa fa-tasks"></i> Tasks</h1>
        <h4>A comprehensive list of the tasks required to complete this job.</h4>

        <?php
            $showableTasksGrouped = $this->job->getShowableTasks(true);
            $showableTasks = array();
            foreach($showableTasksGrouped as $taskGroup => $tasks){ foreach($tasks as $task){ $showableTasks[] = $task; }}
            $lastTaskId = $showableTasks[(count($showableTasks)-1)]->id();
        ?>
        <form action="" method="post">
            <input type="hidden" name="jobId" value="<?php echo $this->job->id()?>" />
            <input type="hidden" name="action" value="add-task" />
            <input type="hidden" name="organizationId" value="<?php echo $this->job->getValue('organizationId')?>" />
            <input type="hidden" name="workflowId" value="<?php echo $this->job->getValue('workflowId')?>" />
            <input type="hidden" name="lastTaskId" value="<?php echo $lastTaskId;?>" />
            <div class="form-input"><input type="text" name="taskGroup" value="" placeholder="Task Group" /> </div>
            <div class="form-input"><input type="text" name="name" value="" placeholder="Task Name" /> </div>
            <div class="form-input"><input type="text" name="estimatedTime" value="" placeholder="Estimated Time (In Hours)" /> </div>
            <div class="form-input form-checkbox"><input type="checkbox" name="visibility" id="visibilityField" /> <label for="visibilityField">Display on Client View</label></div>
            <div class="form-input form-select">
                Add task after the following task:
                <select name="sortOrderAfter">
                    <?php foreach($showableTasks as $task){?>
                    <option <?php if((string) $task->id() == (string) $lastTaskId) echo 'selected="selected"'; ?> value="<?php echo $task->id(); ?>"><?php echo $task->getValue('name'); ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="btn submit"><i class="fa fa-plus"></i> Add Task</button>
        </form>

        <?php //var_dump($this->input->post()) ?>

        <div class="tasklist">

            <?php
            //var_dump($showableTasksGrouped);
            foreach($showableTasksGrouped as $taskGroup => $tasks) { ?>
                <div class="task-head task-style">
                    <a href="#" class="expander fa fa-minus-square-o"></a>
                    <div class="col-title"><?php echo $taskGroup ?> (<span class="count"><?php echo count($showableTasksGrouped[$taskGroup])?></span>)</div>
                    <div class="col-meta">
                        <div class="cols">
                            <div class="col-1 col text-center">Start</div>
                            <div class="col-2 col text-center">End</div>
                            <div class="col-3 col text-left">Comments</div>
                        </div>
                    </div>
                </div>
                <div class="task-body">
                    <?php
                    foreach($tasks as $i => $task){
                        include 'widgets/tasklist-task.php';
                    } ?>
                </div>

            <?php
            }?>

        </div>
    </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->