<?php //var_dump($this->job); ?>
<?php //var_dump($this->job->getActionableTasks()); ?>
<div class="main-mid-section clearfix">
    <div class="main-mid-section-inner clearfix">

        <h1><i class="fa fa-tasks"></i> Tasks</h1>
        <h4>A comprehensive list of the tasks required to complete this job.</h4>

        <form action="" method="post">
            <input type="hidden" name="jobId" value="<?php echo $this->job->id()?>" />
            <input type="hidden" name="action" value="add-task" />
            <input type="hidden" name="organizationId" value="<?php echo $this->job->getValue('organizationId')?>" />
            <input type="hidden" name="workflowId" value="<?php echo $this->job->getValue('workflowId')?>" />
            <div class="form-input"><input type="text" name="taskGroup" value="" placeholder="Task Group" /> </div>
            <div class="form-input"><input type="text" name="name" value="" placeholder="Task Name" /> </div>
            <div class="form-input"><input type="text" name="estimatedTime" value="" placeholder="Estimated Time (In Hours)" /> </div>
            <div class="form-input form-checkbox"><input type="checkbox" name="visibility" /> Display on Client View</div>
            <div class="form-input form-select">
                Add task after the following task:
                <select name="sortOrderAfter">
                    <option></option>
                </select>
            </div>
            <button type="submit" class="btn submit"><i class="fa fa-plus"></i> Add Task</button>
        </form>

        <?php //var_dump($this->input->post()) ?>

        <div class="tasklist">

            <?php
            $actionableTasks = $this->job->getActionableTasks(true);
            foreach($actionableTasks as $taskGroup => $tasks) { ?>
                <div class="task-head task">
                    <a href="#" class="expander fa fa-minus-square-o"></a>
                    <div class="col-title"><?php echo $taskGroup ?> (<span class="count"><?php echo count($actionableTasks[$taskGroup])?></span>)</div>
                    <div class="col-meta">
                        <div class="cols">
                            <div class="col-1 col text-center">Start</div>
                            <div class="col-2 col text-center">End</div>
                            <div class="col-3 col text-left">Comments</div>
                        </div>
                    </div>
                </div>

                <?php
                foreach($tasks as $i => $task){
                    include 'widgets/tasklist-task.php';
                }
            }?>

        </div>
    </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->