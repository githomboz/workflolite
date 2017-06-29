<?php //var_dump($this->job->saveAsTemplate('New JNBPA workflow')); ?>
<?php //var_dump($this->job->getActionableTasks()); ?>
<div class="main-mid-section clearfix">
    <div class="main-mid-section-inner clearfix">

        <h1><i class="fa fa-tasks"></i> Tasks</h1>
        <?php if(template())  echo '<h4>'.template()->getValue('description') . '</h4>'; else {
            echo '<h4>A comprehensive list of the tasks required to complete this project.</h4>';
        }

        include_once 'widgets/_task-change-dialog.php';
        //var_dump(WF::GetMetaDataBySlug($this->project, 'job.closingDate'));
        //var_dump(WF::Add(2, 4, 'a'));
        //var_dump(WF::GenerateCallbackReport($this->project->getTaskByNumber(6)->getValue('dependencies'), $this->project, (string) $this->project->getTaskByNumber(6)->id()));
        ;?>


        <div class="inner-nav-btns">
            <a href="#" class="btn js-main-add-task-btn"><i class="fa fa-plus"></i> Add a Task</a>
        </div>

        <?php
            $showableTasksGrouped = $this->project->getShowableTasks(true);
            $showableTasks = array();
            foreach($showableTasksGrouped as $taskGroup => $tasks){ foreach($tasks as $task){ $showableTasks[] = $task; }}
            $lastTaskId = $showableTasks[(count($showableTasks)-1)]->id();
        ?>
        <form action="" method="post" class="form-add-task" style="display: none;">
            <input type="hidden" name="projectId" value="<?php echo $this->project->id()?>" />
            <input type="hidden" name="action" value="add-task" />
            <input type="hidden" name="organizationId" value="<?php echo $this->project->getValue('organizationId')?>" />
            <input type="hidden" name="workflowId" value="<?php echo $this->project->getValue('workflowId')?>" />
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

        <?php //var_dump($this->project->getMetaArray()); ?>

        <div class="tasklist">
            <script class="projectData">
                var _PROJECT = <?php echo json_encode($this->project->getProjectData()) ?>;
                _PROJECT.triggerBoxOpen = false; // Whether the triggerBoxShould be open or not
                var _TASK_JSON = []; // Task data
            </script>
            <?php
            //var_dump($showableTasksGrouped);
            foreach($showableTasksGrouped as $taskGroup => $tasks) { ?>
                <div class="task-head task-style">
                    <a href="#" class="expander cs-group-expander fa fa-minus-square-o" data-group="<?php echo md5($taskGroup) ?>"></a>
                    <div class="col-title"><?php echo $taskGroup ?> (<span class="count"><?php echo count($showableTasksGrouped[$taskGroup])?></span>)</div>
                    <div class="col-meta">
                        <div class="cols">
                            <div class="col-1 col text-center">Start</div>
                            <div class="col-2 col text-center">End</div>
                            <div class="col-3 col text-left">Comments</div>
                        </div>
                    </div>
                </div>
                <div class="task-body group-<?php echo md5($taskGroup) ?>">
                    <?php
                    $users = organization()->getUsers();
                    //var_dump($users);
                    foreach($tasks as $i => $task){
                        include 'widgets/tasklist-task.php';
                    } ?>
                </div>

            <?php
            }?>

        </div>
    </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/WFMetaData.js"></script>
<script type="text/javascript">

    var TASK_CACHE = {};

    $(document).on('click', ".task-option-links .task-settings", function(e){
        var $btn = $(this),
          $task = $btn.parents('.task-style'),
          $widget = $task.find('.task-settings-widget'),
          $linksContainer = $task.find('.task-option-links');
        e.preventDefault();


        if($btn.is('.active')){
            // Unclick
            $btn.removeClass('active');
            $widget.removeClass('active');
            $linksContainer.removeClass('clicked');
        } else {
            // Close all others
            closeAllTaskSettingWidgets();
            // Click
            $btn.addClass('active');
            $widget.addClass('active');
            $linksContainer.addClass('clicked');
        }
    });

    function closeAllTaskSettingWidgets(){
        $('.task-option-links .task-settings').removeClass('active');
        $('.task-settings-widget').removeClass('active');
        $('.task-option-links').removeClass('clicked');
    }

    $(document).on('click', function(event) {
        if (!$(event.target).closest('.task-settings-widget').length) {
//            $('.task-option-links .task-settings').removeClass('active');
//            $('.task-settings-widget').removeClass('active');
//            $('.task-option-links').removeClass('clicked');
        } else {
        }
    });

    $(document).on('click', ".cs-group-expander", function(){
        var
          $this = $(this),
          isOpen = $this.is('.fa-minus-square-o'),
          group = $this.data('group'),
          $taskGroup = $(".task-body.group-" + group);
        
        if(isOpen){
            $this.addClass('fa-plus-square-o').removeClass('fa-minus-square-o');
            $taskGroup.addClass('collapse');
        } else {
            $this.addClass('fa-minus-square-o').removeClass('fa-plus-square-o');
            $taskGroup.removeClass('collapse');
        }
        return false;
    });

    $(document).on('click', ".checkbox", function(){
        var $checkbox = $(this), $task = $checkbox.parents('.task-style');
        if($checkbox.is('.clickable') && _validateMarkTaskCompleteData()){
            if(alertify.confirm('Confirm Request', 'Are you sure you want to mark task "' + $task.find('.task-name').text() + '" complete?', function(){
                  _ajaxMarkTaskComplete({
                      taskId : $task.data('task_id'),
                      entityId : _CS_Get_Entity_ID(),
                      type : _CS_Get_Entity()
                  });
              }, function(){

              }));
        }
        return false;
    });

    $(document).on('click', ".js-mark-complete", function(){
        var $this = $(this), $task = $this.parents('.task-style');
        if(_validateMarkTaskCompleteData()){
            if(alertify.confirm('Confirm Request', 'Are you sure you want to mark task "' + $task.find('.task-name').text() + '" complete?', function(){
                  _ajaxMarkTaskComplete({
                      taskId : $task.data('task_id'),
                      entityId : _CS_Get_Entity_ID(),
                      type : _CS_Get_Entity()
                  });
              }, function(){

              }));
        }
        return false;
    });

    $(document).on('click', '.task-style .start-task', function () {
        var $this = $(this), taskId = $this.attr('href').split('-')[1];
        _ajaxStartTask({
            taskId : taskId,
            entityId : _CS_Get_Entity_ID(),
            type : _CS_Get_Entity()
        });
    });

    $(document).on('click', '.js-leave-comment', function () {
        var $this = $(this), taskId = $this.attr('href').split('-')[1];
        var $task = _getTaskRow_JobTasks(taskId);
        var data = {
            task : $task,
            entityId : _CS_Get_Entity_ID(),
            type : _CS_Get_Entity()
        };
        _htmlUpdateShowCommentForm(data);
    });

    $(document).on('click', '.js-close-comments', function () {
        var $this = $(this), taskId = $this.attr('id').split('-')[1];
        _htmlUpdateHideCommentForm(_getTaskRow_JobTasks(taskId));
    });

    $(document).on('click', '.js-save-comment', function () {
        var $this = $(this), taskId = $this.attr('id').split('-')[1];

        var $task = _getTaskRow_JobTasks(taskId);
        var val = $task.find('input.comments').val();
        if(val.trim() != ''){
            _ajaxSaveTaskComment({
                taskId : taskId,
                comments : val,
                entityId : _CS_Get_Entity_ID(),
                type : _CS_Get_Entity()
            });
        } else {
            alertify.alert('Invalid Action Taken', 'You must enter a comment to save');
        }
    });

    function _handleMarkTaskCompleteError($task, errors){
        console.log('//@todo: error handling')
    }

    function _validateMarkTaskCompleteData(){
        return true;
    }

    function _ajaxSaveTaskComment(data){
        var $task = _getTaskRow_JobTasks(data.taskId),
          $btn = $task.find('.js-save-comment');

          data.entityId = _CS_Get_Entity_ID();
          data.type = _CS_Get_Entity();

        CS_API.call('ajax/save_comment',
          function(){ // beforeSend
              $btn.addClass('sidepanel-bg');
              $btn.find('.fa').removeClass('fa-save').addClass('fa-spin fa-spinner');
          },
          function(data){
              // success
              if(data.errors == false){
                  $btn.find('.fa').addClass('fa-save').removeClass('fa-spin fa-spinner');
                  data.response.task = $task;
                  PubSub.publish('taskChange.commentSaved', data.response);
              }
          },
          function(){ // error
          },
          data,
          {
              method: 'POST',
              preferCache : false
          }
        );
    }

    function _ajaxStartTask(taskData){
        taskData.entityId = _CS_Get_Entity_ID();
        taskData.type = _CS_Get_Entity();
        CS_API.call('ajax/start_task',
          function(){ // beforeSend
          },
          function(data){
              // success
              if(data.errors == false){
                  PubSub.publish('taskChange.taskStarted', data.response);
              } else {

              }
          },
          function(){ // error
          },
          taskData,
          {
              method: 'POST',
              preferCache : false
          }
        );
    }

    function _ajaxMarkTaskComplete(taskData){
        var $task = _getTaskRow_JobTasks(taskData.taskId);
        var $checkbox = $task.find('.checkbox');
        taskData.entityId = _CS_Get_Entity_ID();
        taskData.type = _CS_Get_Entity();

        CS_API.call('ajax/mark_complete',
          function(){
              // beforeSend
              $checkbox.html('<i class="fa fa-spin fa-spinner"></i>');
          },
          function(data){
              // success
              if(data.errors == false){
                  data.response.task = $task;
                  data.response.checkbox = $checkbox;
                  data.response.taskId = taskData.taskId;
                  PubSub.publish('taskChange.taskComplete', data.response);
              } else {
                  _handleMarkTaskCompleteError($task, data.errors);
              }
          },
          function(){
              // error
              _handleMarkTaskCompleteError($task);
          },
          taskData,
          {
              method: 'POST',
              preferCache : false
          }
        );
    }

    function _htmlUpdateStartTask(topic, taskData){
        var $task = _getTaskRow_JobTasks(taskData.taskId);
        if($task){
            // Add date to start column
            var html = taskData.startDate + ' <a href="#editStart-' + taskData.taskId + '" class="fa fa-pencil"></a>';
            $task.find('.col.start').html(html);
            // Add clickable complete button
            html = ' <a href="#markComplete-' + taskData.taskId + '" class="fa fa-check link-blue js-mark-complete"></a>';
            $task.find('.col.complete').html(html);
        }
    }

    function _htmlUpdateMarkComplete(topic, taskData){
        taskData.checkbox
          .html('<i class="fa fa-check"></i>')
          .addClass('checked')
          .removeClass('clickable');
        taskData.task.addClass('completed');
        var html = taskData.endDate + ' <a href="#editComplete-' + taskData.taskId + '" class="fa fa-pencil"></a>';
        taskData.task.find('.col.complete').html(html);
        if(typeof taskData.startDate != 'undefined'){
            var html = taskData.startDate + ' <a href="#editStart-' + taskData.taskId + '" class="fa fa-pencil"></a>';
            taskData.task.find('.col.start').html(html);
        }
    }

    function _htmlUpdateShowCommentForm(taskData){
        var startingVal = taskData.task.find('.comment-content .comment').html();
        taskData.task.find('.col-3 input.comments').val(startingVal);
        taskData.task.find('.col-3').addClass('input');
    }

    function _htmlUpdateHideCommentForm($task){
        $task.find('.col-3').removeClass('input');
    }

    function _htmlUpdateExpandFullComment($task){
        
    }

    function _getTaskRow_JobTasks(taskId){
        if(typeof TASK_CACHE[taskId] != 'undefined') return TASK_CACHE[taskId];
        var $task = $(".task-style.task-" + taskId);
        TASK_CACHE[taskId] = $task;
        return $task;
    }

    function _htmlUpdateCommentSaved(topic, taskData){
        taskData.task.find('.col-3').removeClass('input');
        var $commentContent = taskData.task.find('.comment-content');
        $commentContent.removeClass('no-comment');
        $commentContent.find('.comment').html(taskData.comments);
    }

    PubSub.subscribe('taskChange.taskStarted', _htmlUpdateStartTask);
    PubSub.subscribe('taskChange.taskComplete', _htmlUpdateMarkComplete);
    PubSub.subscribe('taskChange.commentSaved', _htmlUpdateCommentSaved);

    $(".js-main-add-task-btn").on('click', function(e){
        e.preventDefault();
        var $modalContainer = $(".js-job-change-modal.modal-container");
        $modalContainer.addClass('active');
        $modalContainer.find('.dialog').addClass('options-not-active');
    });

    if(!alertify.triggerUILoad){
        //define a new dialog
        alertify.dialog('triggerUILoad',function factory(){

            return {
                main:function(message){
                    this.message = message;

                },
                setup:function(){
                    return {
                        buttons:[{text: "cool!", key:27/*Esc*/}],
                        focus: { element:0 }
                    };
                },
                prepare:function(){
                    this.setContent(this.message);
                }
            }
        });
    }

</script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/ProjectTaskBindedBox.js"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/SlideTasks.js"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/BindedBoxScreens.js"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/SlideMetadata.js"></script>
