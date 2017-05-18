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

        <div class="tasklist">
            <script class="projectData">
                var _PROJECT = <?php echo json_encode($this->project->getProjectData()) ?>;
                _PROJECT.triggerBoxOpen = false; // Whether the triggerBoxShould be open or not
                var _TASK_JSON = []; // Task data
                var _BINDED_BOX = { // Popup data
                    activeTaskId : null,
                    activeTabId : null,
                    /**
                    * When this is set, all click events are subject to the lock. This is to avoid the accidental loss
                    * of information that hasn't yet been persisted.
                    */
                    activeLock : null,
                  /**
                   * Keep the binding box open even when has been clicked outside or anything else.
                   */
                  keepOpen : false,
                    userAcc : {acc:5}
                };
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

    var
        $bindedBox = $(".binded-trigger-box"),
        $bindedBoxInnerHead = $bindedBox.find('.inner-head'),
        dimensions  = {
            padding : 10,
            actionBtnHeight : 46,
            slideNavWidth : $(".tabbed-nav .item").outerWidth(),
    };

    $(window).load(function(){
        $(window).resize(function(){
            triggerResize();
        });
    });

    function triggerResize(){
        var payload = {
            padding : dimensions.padding,
            windowWidth : $(window).width(),
            windowHeight : $(window).height(),
            windowChanges : {
                width: null,
                height: null
            },
            boxOuterWidth : $bindedBox.outerWidth(),
            boxOuterHeight : $bindedBox.outerHeight(),
            headerOuterHeight : $bindedBoxInnerHead.outerHeight(),
            actionBtnHeight : dimensions.actionBtnHeight,
            slideNavWidth : dimensions.slideNavWidth
        };

        payload.newTaskTabHeight = payload.boxOuterHeight - payload.headerOuterHeight - payload.actionBtnHeight - (payload.padding * 3) - 2;
        payload.tabContainerWidth = payload.boxOuterWidth - payload.slideNavWidth - (payload.padding * 2) - 2;
        payload.preElementHeight = payload.newTaskTabHeight - (payload.padding * 4) - 6;

        if(typeof _PROJECT.dimensions != 'undefined'){
            payload.windowChanges.width = null;
            if(payload.windowWidth != _PROJECT.dimensions.windowWidth){
                payload.windowChanges.width = (payload.windowWidth > _PROJECT.dimensions.windowWidth) ? 'grow' : 'shrink';
            }
            payload.windowChanges.height = null;
            if(payload.windowHeight != _PROJECT.dimensions.windowHeight){
                payload.windowChanges.height = (payload.windowHeight > _PROJECT.dimensions.windowWidth) ? 'grow' : 'shrink';
            }
        }



        PubSub.publish('bindedBox.resize', payload);
        _PROJECT.dimensions = payload
    }

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
<script type="text/javascript">
    /****************************************************************************************************************/


    function _getTaskDataById(id){
        for(var i in _TASK_JSON){
            if(typeof _TASK_JSON[i].id != 'undefined' && _TASK_JSON[i].id == id){
                return _TASK_JSON[i];
            }
        }
        return false;
    }

    function _getTaskDataByNumber(num){
        for(var i in _TASK_JSON){
            if(typeof _TASK_JSON[i].data.sortOrder != 'undefined' && _TASK_JSON[i].data.sortOrder == num){
                return _TASK_JSON[i];
            }
        }
        return false;
    }

    function _getTaskIdByTaskNumber(num){
        var task  = _getTaskDataByNumber(num);
        if(typeof task.id != 'undefined') return task.id;
    }

    // Update task data on page. This does not change task data values in db. This is only for triggering front end
    // related tasks.
    function _setTaskDataById(id, data){
        var updates = {}, newTask = null;
        // Update
        for(var i in _TASK_JSON){
            if(typeof _TASK_JSON[i].id != 'undefined' && _TASK_JSON[i].id == id){
                if(data){
                    for(var field in data){
                        var fieldIsNew = typeof _TASK_JSON[i].data[field] == 'undefined';
                        var fieldIsDifferent = fieldIsNew || (!fieldIsNew && _TASK_JSON[i].data[field] != data[field]);
                        if(fieldIsDifferent){
                            _TASK_JSON[i].data[field] = data[field];
                            updates[field] = data[field];
                            newTask = _TASK_JSON[i];
                        }
                    }
                }
            }
        }
        if(newTask) {
            var payload = {
                id : id,
                updates : updates,
                newTask : newTask,
                updatesMade : newTask !== null
            };
            _handleTaskUpdatesAirTrafficControl(payload);
        }
    }

    function _setTaskDataByNum(num, data){
        var id = _getTaskIdByTaskNumber(num);
        if(id){
            return _setTaskDataById(id, data);
        }
    }

    function _handleTaskUpdatesAirTrafficControl(payload){
        var sent = false; // Whether or not payload has been sent or not.
        // Check if active task is the task that has changed
        var isActiveTask = typeof _BINDED_BOX.activeTaskId != 'undefined' && _BINDED_BOX.activeTaskId == payload.id;
        if(isActiveTask) {
            sent = true;
            PubSub.publish('taskData.updates.activeTask', payload);
        }

        if(!sent){
            sent = true;
            PubSub.publish('taskData.updates.updatedTask', payload);
        }

        return sent;
    }

    function _handleTaskBindedTrigger(e){
        e.preventDefault();
        var $this = $(this),
          $task = $this.parents('.task-style'),
          taskId = $task.data('task_id');

        _triggerBoxOpen(taskId);
        return false;
    }

    $(document).on('click', '.task-name', _handleTaskBindedTrigger);

    $(document).on('click', function(event){
        if (!$(event.target).closest(BindedBox.selector).length) {
            _triggerBoxClose();
        }
    });

    function _triggerBoxOpen(taskId){
        var task = _getTaskDataById(taskId);
        _LAMBDA_PROGRESS = 0;
        _FORM_PROGRESS = 0;
        if(task){
            _BINDED_BOX.activeTaskId = taskId;
            if(!_PROJECT.triggerBoxOpen){
                //console.log('trigger box opened');
                $(".binded-trigger-box-overlay").addClass('show');
                $(document).on('click', '.binded-trigger-box .item a', _handleTriggerBoxNavClick);
                //$(document).on('click', '.tabbed-content.tasks .task-data-block a', _handleTriggerBoxPreviewData);
                $(document).on('click', '.tabbed-content.tasks .completion-test-btn', _handleTriggerBoxCompletionTestBtn);
                $(document).on('click', '.tabbed-content.tasks .completion-test-report-btn', _handleTriggerBoxCompletionTestReportBtn);
                $(document).on('click', '.tabbed-content.tasks .check-dependencies-btn', _handleCheckDependenciesClick);
                $(document).on('click', '.tabbed-content.tasks .trigger-start-btn', _handleRunTriggerBtnClick);
                $(document).on('click', '.binded-trigger-box button.js-directional', _handleDirectionalBtnClick);
                $(document).on('click', '.binded-trigger-box .action-btns .mark-complete', _handleMarkCompleteClick);
                PubSub.subscribe('bindedBox.task.statusChange', _renderBindedBoxTaskStatusChanges);
                PubSub.subscribe('queueNextRunLambdaStep', _executeRunLambdaAjaxCalls);
                PubSub.subscribe('queueNextRunFormStep', _executeRunFormAjaxCalls);
                PubSub.subscribe('newDynamicContent', _setTaskTabbedContentDynamicContent);
                PubSub.subscribe('bindedBox.resize', _handleBindedBoxViewportResize);
                PubSub.subscribe('bindedBox.activeLockCollision', _handleActiveLockCollision);
                _PROJECT.triggerBoxOpen = true;
                PubSub.publish('bindedBox.opened', null);
            }
            _renderTriggerBoxProjectAndTaskData(task);
            _renderTaskTabbedContent(task);
            _activateTriggerBoxSlide('tasks'); // Default back to tasks slide
            triggerResize();
            PubSub.publish('bindedBox.newTaskActivated', {
                activeTaskId : taskId
            });
        }
    }

    function _triggerBoxClose(){
        console.log(_BINDED_BOX);
//        if(_BINDED_BOX.activeLock && !_BINDED_BOX.keepOpen){
//            PubSub.publish('bindedBox.activeLockCollision.action.closeBindedBox', {
//                continueCallback : _triggerBoxClose
//            });
//            return;
//        }
        var $overlay = $(".binded-trigger-box-overlay");
        if($overlay.is('.show') || _PROJECT.triggerBoxOpen){
            //console.log('trigger box closed');
            $overlay.removeClass('show');
            $(document).off('click', '.binded-trigger-box .item a', _handleTriggerBoxNavClick);
            //$(document).off('click', '.tabbed-content.tasks .task-data-block a', _handleTriggerBoxPreviewData);
            $(document).off('click', '.tabbed-content.tasks .completion-test-btn', _handleTriggerBoxCompletionTestBtn);
            $(document).off('click', '.tabbed-content.tasks .completion-test-report-btn', _handleTriggerBoxCompletionTestReportBtn);
            $(document).off('click', '.tabbed-content.tasks .check-dependencies-btn', _handleCheckDependenciesClick);
            $(document).off('click', '.tabbed-content.tasks .trigger-start-btn', _handleRunTriggerBtnClick);
            $(document).off('click', '.binded-trigger-box button.js-directional', _handleDirectionalBtnClick);
            $(document).off('click', '.binded-trigger-box .action-btns .mark-complete', _handleMarkCompleteClick);
            PubSub.unsubscribe('bindedBox.task.statusChange', _renderBindedBoxTaskStatusChanges);
            PubSub.unsubscribe('queueNextRunLambdaStep', _executeRunLambdaAjaxCalls);
            PubSub.unsubscribe('queueNextRunFormStep', _executeRunFormAjaxCalls);
            PubSub.unsubscribe('newDynamicContent', _setTaskTabbedContentDynamicContent);
            PubSub.unsubscribe('bindedBox.resize', _handleBindedBoxViewportResize);
            PubSub.unsubscribe('bindedBox.activeLockCollision', _handleActiveLockCollision);
            _PROJECT.triggerBoxOpen = false;
            _BINDED_BOX.activeTaskId = null;
            PubSub.publish('bindedBox.closed', null);

        }
    }

    function _handleActiveLockCollision(topic, payload){
        console.log(topic, payload);
        if(_BINDED_BOX.activeLock){
            if(typeof _BINDED_BOX.activeLock.message != 'undefined'){
                alertify.confirm(
                    'Data Loss Warning!',
                    _BINDED_BOX.activeLock.message,
                    function(){
                        _BINDED_BOX.activeLock = null;
                        if(typeof payload.continueCallback != 'undefined') payload.continueCallback();
                    },
                    function(){
                        switch(topic){
                            case 'bindedBox.activeLockCollision.action.closeBindedBox':
                            _BINDED_BOX.keepOpen = true;
                            break;
                        }
                    }
                ).set('labels', {ok: 'I understand', cancel: 'Cancel'});
            }
        }
        return false;
    }

    function _handleBindedBoxViewportResize(topic, payload){
        // Change pre max-height to be full height minus header and action buttons
        var $tabContainer = $bindedBox.find('.tabbed-content-container'),
          $taskTab = $bindedBox.find('.tabbed-content');

        $tabContainer.css({width : payload.tabContainerWidth});

        $taskTab.css({height : payload.newTaskTabHeight});
        $taskTab.find('.column-list.meta').css({maxHeight : (payload.newTaskTabHeight - 53)});
        $taskTab.find('.column-details.meta').css({height : (payload.newTaskTabHeight - 53)});
        $taskTab.find('.meta-fields .entries').css({maxHeight : (payload.newTaskTabHeight - 78)});
        $taskTab.find('.task-inset .inset-tab').css({height: payload.preElementHeight});
    }

    function _handleProjectUpdates(topic, payload){
        if(typeof payload.projectId != 'undefined'){
            if(typeof payload.updates != 'undefined'){

            } else {
                console.error('updates is not defined');
            }
        } else {
            console.error('projectId is not defined');
        }

    }

    function _handleMetaUpdates(projectId, updates){
        // Publish PubSub
        // Update the meta array
        // Redraw meta slide
        // Update counts
        // Update project details sidebar
        //
    }

    function _handleTaskUpdates(topic, payload){
        // Publish PubSub
        // Update the given task in _TASK_JSON
        // Update the UI for task slide
        if(typeof payload.taskId != 'undefined'){
            if(typeof payload.updates != 'undefined'){

//                console.log(_TASK_JSON, payload);
                for(var i in _TASK_JSON){
                    if(_TASK_JSON[i].data.taskId == payload.taskId){
                        for(var field in payload.updates){
                            _TASK_JSON[i].data[field] = payload.updates[field];
                        }
                        $('.task-inset pre.task-data').html(JSON.stringify(_TASK_JSON[i], undefined, 2));
                    }
                }
//                console.log(_TASK_JSON, payload);

            } else {
                console.error('updates is not defined');
            }
        } else {
            console.error('taskId is not defined');
        }

    }

    function _handleMarkCompleteClick(e){
        e.preventDefault();
        var $this = $(this),
          taskId = $this.data('task_id'),
          task = _getTaskDataById(taskId);

        console.log(taskId, task);

        if(task){
            // Check current status
            if(task.data.status != 'completed'){
                // Create visual confirmation. Using anything other than custom will break .closest() js.
                // Check if dependencies have been reconciled
                if(task.data.dependencies) _handleCheckDependenciesClick();
                // If autoRun, attempt to autoRun
                // Check if completion scripts have been run successfully
                // Make sure completionReport is added to task
                // Reload task
            }
        }
        return false;
    }

    function _renderBindedBoxTaskStatusChanges(topic, payload){
        // Validate taskId, status, and currentStatus
        var
          projectId = _PROJECT.projectId,
          taskId = typeof payload.taskId == 'undefined' ? null : payload.taskId,
          status = typeof payload.status == 'undefined' ? null : payload.status,
          task = _getTaskDataById(taskId),
          currentStatus = task.data.status;

        console.log(payload, projectId, task, status, currentStatus);

        if(projectId && taskId && status && currentStatus && (currentStatus != status)){
            // Update _TASK_JSON
            for(var i in _TASK_JSON){
                if(_TASK_JSON[i].id === taskId){
                    _TASK_JSON[i].data.status = status;
                    task = _TASK_JSON[i];
                    console.log('new task', task);
                }
            }
            _renderTriggerBoxProjectAndTaskData(task);
            _renderTaskTabbedContent(task);
        }
    }

    var _LAMBDA_PROGRESS = 0;
    var _FORM_PROGRESS = 0;

    function _handleRunTriggerBtnClick(e){
        e.preventDefault();
        var
          taskId = _BINDED_BOX.activeTaskId,
          task = _getTaskDataById(taskId),
          taskType = task.data.trigger.type;

        //console.log(taskId, taskType);

        if(!_LAMBDA_PROGRESS){
            // Start ajax jumps
            switch (taskType){
                case 'form':
                    _executeRunFormAjaxCalls();
                    break;
                case 'lambda':
                    _executeRunLambdaAjaxCalls();
                    break;
                case 'applet':
                    break;
            }
        }
    }

    function _executeRunFormAjaxCalls(topic, payload){
        _FORM_PROGRESS++;
        var triggerType = 'form';
        var
          routineSlugs = [
            'validate_dependencies',
            'validate_form',
            'render_form'
          ],
          post = {
            projectId : _CS_Get_Project_ID(),
            taskTemplateId : $('.dynamic-content').attr('data-task_template_id'),
            routine : 'step-' + _FORM_PROGRESS,
              slug : routineSlugs[_FORM_PROGRESS],
        };
        var $triggerStartBtn = $('.trigger-start-btn');

        CS_API.call('ajax/run_form_routines',
          function(){
              // beforeSend
              _renderTriggerRoutineUIChanges(_FORM_PROGRESS, 'checking', triggerType);
              if(_FORM_PROGRESS == 1){
                  $triggerStartBtn.addClass('clicked');
                  $triggerStartBtn.html('<i class="fa fa-spin fa-spinner"></i> Loading Trigger');
              }
          },
          function(data){
              // success
              if(data.errors == false && data.response.success){
                  console.log(data);
                  switch (data.response.slug){
                      case routineSlugs[1]: //'validate_lambda_callback':
                          _renderTriggerRoutineUIChanges(_FORM_PROGRESS, 'done', triggerType);
                          PubSub.publish('queueNextRunFormStep', data.response);
                          break;
                      case routineSlugs[2]: //'execute_lambda_callback':
                          _renderTriggerRoutineUIChanges(_FORM_PROGRESS, 'done', triggerType);
                          //PubSub.publish('queueNextRunLambdaStep', data.response);
                          $triggerStartBtn.removeClass('clicked').addClass('complete');
                          $triggerStartBtn.html('<i class="fa fa-bolt"></i> Trigger Loaded');
                        $(".dynamic-content").html(data.response._form);
                          break;
//                      case routineSlugs[3]: //'analyze_callback_results':
//                          _renderTriggerRoutineUIChanges(_FORM_PROGRESS, 'done', triggerType);
//                          break;
                  }
                  console.log(_FORM_PROGRESS, data);
              } else {
                  _renderTriggerRoutineUIChanges(_FORM_PROGRESS, 'error', triggerType);
                  if(data.errors && typeof data.errors[0] != 'undefined') alertify.error(data.errors[0]);
                  $triggerStartBtn.html('<i class="fa fa-exclamation-triangle"></i> Trigger Error');
              }
          },
          function(){
              // error
              _renderTriggerRoutineUIChanges(_FORM_PROGRESS, 'error', triggerType);
              alertify.error('Error', 'An error has occurred.');
              $triggerStartBtn.html('<i class="fa fa-exclamation-triangle"></i> Trigger Error');
          },
          post,
          {
              method: 'POST',
              preferCache : false
          }
        );
    }

    function _executeRunLambdaAjaxCalls(topic, payload){
        _LAMBDA_PROGRESS++;
        var triggerType = 'lambda';
        var
          routineSlugs = [
            'validate_dependencies',
            'validate_lambda_callback',
            'execute_lambda_callback',
            'analyze_callback_results'
          ],
          post = {
            projectId : _CS_Get_Project_ID(),
            taskTemplateId : $('.dynamic-content').attr('data-task_template_id'),
            routine : 'step-' + _LAMBDA_PROGRESS,
              slug : routineSlugs[_LAMBDA_PROGRESS],
        };
        var $triggerStartBtn = $('.trigger-start-btn');

        CS_API.call('ajax/run_lambda_routines',
          function(){
              // beforeSend
              _renderTriggerRoutineUIChanges(_LAMBDA_PROGRESS, 'checking', triggerType);
              if(_LAMBDA_PROGRESS == 1){
                  $triggerStartBtn.addClass('clicked');
                  $triggerStartBtn.html('<i class="fa fa-spin fa-spinner"></i> Loading Trigger');
              }
          },
          function(data){
              // success
              if(data.errors == false && data.response.success){
                  console.log(data);
                  switch (data.response.slug){
                      case routineSlugs[1]: //'validate_lambda_callback':
                          _renderTriggerRoutineUIChanges(_LAMBDA_PROGRESS, 'done', triggerType);
                          PubSub.publish('queueNextRunLambdaStep', data.response);
                          break;
                      case routineSlugs[2]: //'execute_lambda_callback':
                          _renderTriggerRoutineUIChanges(_LAMBDA_PROGRESS, 'done', triggerType);
                          //PubSub.publish('queueNextRunLambdaStep', data.response);
                          $triggerStartBtn.removeClass('clicked').addClass('complete');
                          $triggerStartBtn.html('<i class="fa fa-bolt"></i> Trigger Loaded');
                          break;
                      case routineSlugs[3]: //'analyze_callback_results':
                          _renderTriggerRoutineUIChanges(_LAMBDA_PROGRESS, 'done', triggerType);
                          break;
                  }
                  console.log(_LAMBDA_PROGRESS, data);
              } else {
                  _renderTriggerRoutineUIChanges(_LAMBDA_PROGRESS, 'error', triggerType);
                  if(data.errors && typeof data.errors[0] != 'undefined') alertify.error(data.errors[0]);
                  $triggerStartBtn.html('<i class="fa fa-exclamation-triangle"></i> Trigger Error');
              }
          },
          function(){
              // error
              _renderTriggerRoutineUIChanges(_LAMBDA_PROGRESS, 'error', triggerType);
              alertify.error('Error', 'An error has occurred.');
              $triggerStartBtn.html('<i class="fa fa-exclamation-triangle"></i> Trigger Error');
          },
          post,
          {
              method: 'POST',
              preferCache : false
          }
        );
    }

    function _renderTriggerRoutineUIChanges(stepNum, progress, type){
        if(['form','lambda','applet'].indexOf(type) >= 0) {
            var $steps = $('.dynamic-content .trigger-steps'),
                $step = $steps.find('[data-step=' + stepNum + ']'),
                verbTenses = {
                  validate: {
                      do: "Validate",
                      doing: "Validating",
                      did: "Validated",
                      doh: "Issues Validating"
                  },
                  execute: {
                      do: "Execute",
                      doing: "Executing",
                      did: "Executed",
                      doh: "Issues Executing"
                  },
                  analyze: {
                      do: "Analyze",
                      doing: "Analyzing",
                      did: "Analyzed",
                      doh: "Issues Analyzing"
                  },
                  render: {
                      do: "Render",
                      doing: "Rendering",
                      did: "Rendered",
                      doh: "Issues Rendering"
                  },
                  load: {
                      do: "Load",
                      doing: "Loading",
                      did: "Loaded",
                      doh: "Issues Loading"
                  },
                  verify: {
                      do: "Verify",
                      doing: "Verifying",
                      did: "Verified",
                      doh: "Issues Verifying"
                  }
                },
                icons = {
                  do: 'fa fa-square-o',
                  doing: 'fa fa-spinner fa-spin',
                  did: 'fa fa-check-square-o',
                  doh: 'fa fa-exclamation-triangle'
                },
                icon, verb, textData;

            switch(type){
                case 'form':
                    textData = {
                          0 : {
                              verb : "validate",
                              noun : "Task Dependencies"
                          },
                          1 : {
                              verb : "validate",
                              noun : "Form Settings & Options"
                          },
                          2 : {
                              verb : "render",
                              noun : "Form"
                          },
                      };
                    break;
                case 'lambda':
                    textData = {
                          0 : {
                              verb : "validate",
                              noun : "Task Dependencies"
                          },
                          1 : {
                              verb : "validate",
                              noun : "Lambda Callback & Parameters"
                          },
                          2 : {
                              verb : "execute",
                              noun : "Lambda Callback"
                          },
                          3 : {
                              verb : "analyze",
                              noun : "Callback Results"
                          },
                      };
                    break;
                case 'applet':
                    textData = {
                          0 : {
                              verb : "validate",
                              noun : "Task Dependencies"
                          },
                          1 : {
                              verb : "verify",
                              noun : "Applet"
                          },
                          2 : {
                              verb : "load",
                              noun : "Applet"
                          },
                      };
                    break;
            }


            switch (progress) {
                case 'error':
                    icon = '<i class="' + icons.doh + '"></i> ';
                    verb = verbTenses[textData[stepNum].verb].doh;
                    //$step.html(icon +  + ' ' + textData[stepNum].noun);
                    $step.removeClass('done').addClass('error');
                    break;
                case 'checking':
                    icon = '<i class="' + icons.doing + '"></i> ';
                    verb = verbTenses[textData[stepNum].verb].doing;
                    //$step.html(icon +  + ' ' + textData[stepNum].noun);
                    $step.removeClass('done error');
                    break;
                case 'done':
                    icon = '<i class="' + icons.did + '"></i> ';
                    verb = verbTenses[textData[stepNum].verb].did;
                    //$step.html(icon + verbTenses[textData[stepNum].verb].did + ' ' + textData[stepNum].noun);
                    $step.removeClass('error').addClass('done');
                    break;
                default:
                    icon = '<i class="' + icons.do + '"></i> ';
                    verb = verbTenses[textData[stepNum].verb].do;
                    //$step.html(icon + verbTenses[textData[stepNum].verb].do + ' ' + textData[stepNum].noun);
                    $step.removeClass('done error');
                    break;
            }

            $step.find('.icon').html(icon);
            $step.find('.verb').html(verb);
        }

    }

    function _handleCheckDependenciesClick(e){
        if(e) e.preventDefault();
        var $this = $(".check-dependencies-btn"),
          $tabbedContent = $this.parents('.tabbed-content.tasks'),
          post = {
              projectId : _CS_Get_Project_ID(),
              taskId : _BINDED_BOX.activeTaskId,
              returnReport : 'condensed'
          },
          task = _getTaskDataById(post.taskId),
          triggerType = task.data.trigger.type;

        var errorMsg01 = '<i class="fa fa-exclamation-triangle"></i> Dependencies have not been satisfied. This task can not be started until dependency checks pass. <a href="#" class="check-dependencies-btn br"> Re-check</a>';
        CS_API.call('ajax/check_task_dependencies',
        function(){
          // beforeSend
            $this.parents('.dynamic-content-overlay').addClass('checking');
            _renderTriggerRoutineUIChanges(0, 'checking', triggerType);
        },
        function(data){
          // success
            if(data.errors == false){
                $this.parents('.dynamic-content-overlay').removeClass('checking').addClass('checked');
                $tabbedContent.find('.lock-status').removeClass('fa-lock').addClass('fa-unlock');
                _renderTriggerRoutineUIChanges(0, 'done', triggerType);

                // if autoRun, _executeRunLambdaAjaxCalls();
                if(_PROJECT.template.settings.autoRun) _executeRunLambdaAjaxCalls();

                if(typeof data.response.taskUpdates != 'undefined'){
                    PubSub.publish('task.updated', {
                        taskId : data.response.taskId,
                        updates : data.response.taskUpdates
                    });
                }
                if(typeof data.response.metaUpdates != 'undefined'){
                    PubSub.publish('meta.updated', {
                        projectId : data.response.projectId,
                        updates : data.response.metaUpdates
                    });
                }
                if(typeof data.response.projectUpdates != 'undefined'){
                    PubSub.publish('project.updated', {
                        projectId : data.response.projectId,
                        updates : data.response.projectUpdates
                    });
                }
            } else {
                if(typeof data.errors[0] != 'undefined') alertify.error(data.errors[0]);
                _renderTriggerRoutineUIChanges(0, 'error', triggerType);
                $this.parents('.dynamic-content-overlay').find('.checking-text').html(errorMsg01);

                for(var i in data.response.report.response.callbacks){
                    var callback = data.response.report.response.callbacks[i];
                    var $icon = $tabbedContent.find('.dependency-item[rel=' + i + ']');
                    var icon = callback.success ? 'fa-thumbs-up' : 'fa-thumbs-down';
                    $icon.addClass(icon);
                }

            }
        },
        function(){
            // error
            alertify.error('Error', 'An error has occurred while checking dependencies. Please try again later.');
            _renderTriggerRoutineUIChanges(0, 'error', triggerType);
            $this.parents('.dynamic-content-overlay').find('.checking-text').html(errorMsg01);
        },
        post,
        {
            method: 'POST',
            preferCache : false
        }
        );

        return false;
    }

    function _handleTriggerBoxCompletionTestBtn(e){
        e.preventDefault();
        _startRunningCompletionTest();
    }

    function _handleTriggerBoxCompletionTestReportBtn(e){
        e.preventDefault();

    }

//    function _handleTriggerBoxPreviewData(e){
//        e.preventDefault();
//        var $el = $(e.target),
//          $dataBlock = $el.parents('.task-inset'),
//          $pre = $dataBlock.find('pre');
//        $pre.toggle();
//    }

    function _handleTriggerBoxNavClick(e){
        e.preventDefault();
        var $this = $(this);
        var $activeSlide = $(".tabbed-content.show");
        var activeSlideName = $activeSlide.data('slide');
        var clickedSlide = $this.attr('rel');
        if(activeSlideName != clickedSlide){
            _activateTriggerBoxSlide(clickedSlide);
        }
        _setTriggerBoxOverlayData();
    }

    function _setTriggerBoxOverlayData(data, opts){
        var
          data = data || {},
          errors = [],
          requiredFields = ['projectName','templateName','deadline','logoSrc','taskNumber','taskCount','projectId','elapsedTime'];
        for(var i in requiredFields) {
            if(typeof data[requiredFields[i]] == 'undefined') errors.push(requiredFields[i]);
        }
        if(errors.length <= 0){
            var $innerHead = $(".binded-trigger-box .inner-head");
            if(typeof data.logoSrc != 'undefined') {
                $innerHead.find('.thumb').html('<img src="' + data.logoSrc + '" />');
            } else {
                $innerHead.find('.thumb').hide();
            }
            if(typeof data.projectName != 'undefined') $innerHead.find('h2').html(data.projectName);
        }
    }

    function _activateTriggerBoxSlide(slide){
        // activate clicked slide
        $(".tabbed-content.show").removeClass('show');
        $(".tabbed-content." + slide).addClass('show');
        // activate slide button
        $(".tabbed-nav .item").removeClass('selected');
        $(".tabbed-nav .item a[rel=" + slide + "]").parents('.item').addClass('selected');
        var oldSlide = typeof _BINDED_BOX.activeTabId == 'undefined' ? null : _BINDED_BOX.activeTabId;
        _BINDED_BOX.activeTabId = slide;
        var topic = null;
        if(oldSlide) {
            topic = 'bindedBox.tabs.' + oldSlide + '.closeTriggered';
            PubSub.publish(topic, null);
        }
        topic = 'bindedBox.tabs.' + slide + '.openTriggered';
        PubSub.publish(topic, null);
    }

    PubSub.subscribe('bindedBox.tabs', function(topic, payload){
       console.log(topic, payload)
    });

    function _renderTriggerBoxProjectAndTaskData(taskData){
        //console.log(projectData, taskData);
        var $triggerBox = $('.binded-trigger-box');
        var $markCompleteBtn = $(".action-btns .mark-complete");

        if($markCompleteBtn.length > 0 && taskData.data.status == 'completed') {
            $markCompleteBtn.addClass('inactive');
        } else {
            $markCompleteBtn.removeClass('inactive');
        }

        $triggerBox.find('header .titles h2').html(_PROJECT.projectName);
        $triggerBox.find('header .titles h3').html(_PROJECT.templateName);
        var $headerContent = $triggerBox.find('header .upper-settings');
        if(typeof _PROJECT.projectCompletionDateString == 'string') {
            $headerContent.find('.deadline-txt').show();
            $headerContent.find('.date').html(_PROJECT.projectCompletionDateString);
        } else {
            $headerContent.find('.deadline-txt').hide();
        }

        var $lowerHeader = $(".lower-settings"),
          $taskCountText = $lowerHeader.find('.task-count-txt');

        // Show/hide task counts
        if(BindedBox.getOption('showTaskCount')
          && taskData.data.sortOrder
          && _TASK_JSON.length > 0){
            $taskCountText.find('.task-num').html(taskData.data.sortOrder);
            $taskCountText.find('.task-count').html(_PROJECT.taskCount);
            $taskCountText.show();
        } else {
            $taskCountText.hide();
        }

        // Show/hide timer
        if(BindedBox.getOption('showTimer')){
            if(!BindedBox.getOption('elapsedTime')) BindedBox.setOption('elapsedTime', 0);
            $lowerHeader.find('.time-tracker-btn').show();
        } else {
            $lowerHeader.find('.time-tracker-btn').hide();
        }

        // Render directional and 'mark complete' buttons
        _renderTaskActionBtns(taskData);
        //return false;
    }


    function _renderTaskActionBtns(task){
        var
          prevTaskId = null,
          currTaskId = null,
          nextTaskId;
        var $actionBtns = $(".action-btns");

        var output = '';
        for(var i in _TASK_JSON){
            if(currTaskId) prevTaskId = currTaskId;
            currTaskId = _TASK_JSON[i].id;
            var nextIndex = (parseInt(i) + 1).toString();
            nextTaskId = typeof _TASK_JSON[nextIndex] != 'undefined' ? _TASK_JSON[nextIndex].id : null ;
            if(task.id == currTaskId){
                //console.log(prevTaskId, currTaskId, nextTaskId);
                var prevTask = _getTaskDataById(prevTaskId);
                var prevTask = prevTask ? prevTask : null;
                var nextTask = _getTaskDataById(nextTaskId);
                var nextTask = nextTask ? nextTask : null;
                    output += '<button class="prev-task js-directional' + (prevTask ? '':' inactive') + '" ';
                    output += 'data-target_id="' + prevTaskId + '">';
                    output += '<i class="fa fa-fast-backward"></i>';
                    output += '&nbsp; Prev. Task</button>';
                    output += '<button class="next-task js-directional' + (nextTask ? '':' inactive') + '" ';
                    output += 'data-target_id="' + nextTaskId + '">';
                    output += '<i class="fa fa-fast-forward"></i>';
                    output += '&nbsp; Next Task</button>';
            }
        }

        var classes = 'mark-complete inverse';
        var dependencyHold = task.data.dependencies && !task.data.dependenciesOKTimeStamp;
        if(task.data.status == 'completed' || dependencyHold) classes += ' inactive';
        output += '<button class="' + classes + '" data-task_id="' + task.id + '"><i class="fa fa-check"></i>&nbsp; Mark Complete</button>';
        // Add to html
        $actionBtns.html(output);
        //return false;
    }

    function _handleDirectionalBtnClick(e){
        e.preventDefault();
        var $this = $(this),
          taskId = $this.data('target_id');
        _triggerBoxOpen(taskId);
        return false;
    }

    PubSub.subscribe('taskData.updates.activeTask', _handleActiveTaskUpdated);

    function _handleActiveTaskUpdated(topic, payload){
        _renderTaskTabbedContent(payload.newTask);
        PubSub.publish('taskData.updates.updatedTask', payload);
        _renderTaskActionBtns(payload.newTask);
    }

    function _renderTaskTabbedContent(task){
        var $taskTab = $('.binded-trigger-box .tabbed-content.tasks');
        //console.log(task.data);
        //$taskTab.find('.dynamic-content').html('Loading content ... <i class="fa fa-spin fa-spinner"></i>');
        $taskTab.find('.dynamic-content').attr('data-task_template_id', task.data.taskId);
        $taskTab.attr('data-status', task.data.status);
        $taskTab.find('.task-inset pre.task-data').html(JSON.stringify(task, undefined, 2));
        $taskTab.find('h1 .num').html(task.data.sortOrder);
        $taskTab.find('h1 .group').html(task.data.taskGroup);
        var hasDependencies = task.data.dependencies.length >= 1;
        var unlocked = !hasDependencies || task.data.dependenciesOKTimeStamp;
        var icon = '<i class="fa lock-status ' + (!unlocked ? 'fa-lock':'fa-unlock') + '"></i>';
        $taskTab.find('h1 .icon').html(icon);
        //if(task.data.dependencies.length >= 1 && unlocked) _renderTriggerRoutineUIChanges(0, 'done'); // Mark "Validate Task Dependencies" done
        $taskTab.find('h1 .name').html(task.data.taskName);
        $taskTab.find('.status-info .status').html(task.data.status.capitalize());
        $taskTab.find('.description').html(task.data.description);
        $taskTab.find('.instructions').html(task.data.instructions);

        if(!task.data.trigger){
            $taskTab.find('.trigger-type').hide();
        } else {
            $taskTab.find('.trigger-type').show();
            var triggerOptions = {
                lambda : {
                    name : 'Lambda Function'
                },
                form : {
                    name : 'Dynamic Form',
                    desc : 'Fill out the following form to complete the task.'
                },
                applet : {
                    name : 'Visual Applet',
                    desc : 'Utilize custom applet to complete this task.'
                }
            };

            var autoRun = _PROJECT.template.settings.autoRun;

            triggerOptions.lambda.desc = autoRun ? 'This task runs automatically. No action required.' : 'This task will run automatically once <span class="false-btn"><i class="fa fa-bolt"></i> Load</span> is clicked.';

            $taskTab.find('.trigger-type-name').html(triggerOptions[task.data.trigger.type].name);
            $taskTab.find('.trigger-type-desc').html(triggerOptions[task.data.trigger.type].desc);
        }


        var dependenciesContent = _generateDependenciesHTML(task);
        var dynamicContent = _generateDynamicContentHTML(task);
        if(dynamicContent){

            PubSub.publish('newDynamicContent', {
                task : task,
                content : dependenciesContent + dynamicContent
            });
        }




        var completionTestHTML = '';
        if(task.data.completionTests){
            completionTestHTML += '<i class="fa ' + (task.data.completionReport ? 'success fa-heart':'fa-heartbeat') + '"></i>';
            completionTestHTML += '<span class="info-data"> ';

            // If completionReport, "Completion scripts successful"
            if(task.data.completionReport){
                completionTestHTML += 'Completion scripts successful ';
            }
            // If not status completed, += "Run (2) completion scripts. "
            if(task.data.status != 'completed'){
                completionTestHTML += 'Run (' + task.data.completionTests.length + ') completion script' + (task.data.completionTests.length == 1 ? '':'s') + '. ';
            }
            // If not completionReport or not status completed), += "Generate report."
            if(!task.data.completionReport || task.data.status != 'completed'){
                completionTestHTML += '<a href="#" class="completion-test-btn">';
                completionTestHTML += 'Generate report ';
                completionTestHTML += '</a>';
            }

            completionTestHTML += '</span>';
            completionTestHTML += ' <span class="ajax-response ' + (task.data.completionReport ? 'show':'') + ' success">[ <i class="fa fa-check"></i> ';
            completionTestHTML += '<a href="#" class="completion-test-report-btn">Report</a>';
            completionTestHTML += ' ]</span>';
        }
        $taskTab.find('.bottom-links').html(completionTestHTML);

    }

    function _startRunningCompletionTest(task){
        // Start the loading spinner
        // Change html to reflect loading
        //
    }

    function _setTaskTabbedContentDynamicContent(topic, data){
        var $taskTab = $('.binded-trigger-box .tabbed-content.tasks');
        $taskTab.find('.dynamic-content').html(data.content);
        return true;
    }

    function _generateDependenciesHTML(task){
        // dependenciesOK field must be set to true or dependencies must be null to bypass dependencies overlay
        // Check if dependencies exists
        // Check if dependencies have been satisfied
        // Display dependency list
        var dependencies = task.data.dependencies,
          dependenciesOKTimeStamp = task.data.dependenciesOKTimeStamp,
          hasDependencies = dependencies.length >= 1,
          overlay = '',
          date = new Date(),
          fiveMinutes = 60*60*5,
          currentDifferenceGreaterThanThreshold = (date.getTime() - dependenciesOKTimeStamp) >= fiveMinutes;

        if(task.data.status != 'completed'){
            //console.log(dependencies, dependenciesOKTimeStamp, hasDependencies, currentDifferenceGreaterThanThreshold);
            if(hasDependencies){
                if(!dependenciesOKTimeStamp){
                    overlay += '<div class="dynamic-content-overlay clearfix">';
                    overlay += '<i class="fa fa-lock super-icon"></i>';
                    overlay += '<div class="dependency-list">';
                    overlay += '<a href="#" class="check-dependencies-btn br"><i class="fa fa-gear"></i> Check Dependencies</a>';
                    overlay += '<p class="explanation">Dependencies are small macro functions that assure that the current task is ready to be started.</p>';
                    overlay += '<span class="checking-text br"><i class="fa fa-gear fa-spin"></i> Checking Dependencies. Please wait.</span>';
                    overlay += '<ol>';
                    for(var i in dependencies){
                        overlay += _generateActionText(i, dependencies[i]);
                    }
                    overlay += '</ol>';
                    overlay += '</div><!--/.dependency-list-->';
                    overlay += '</div><!--/.dynamic-content-overlay-->';
                }
            }
            if(overlay == ''){

            }
        }
        return overlay;
    }

    function _generateActionText(itemNum, dependency){
        var output = '<li>',
          assertionOperator = null,
          assertionValue = null;

        if(dependency.assertion){
            //console.log(dependency.assertion);
                assertionValue = dependency.assertion._val;
                switch (dependency.assertion._op){
                    case '==': assertionOperator = 'equal to';
                        break;
                    case '!=': assertionOperator = 'not equal to';
                        break;
                    case '>': assertionOperator = 'greater than';
                        break;
                    case '>=': assertionOperator = 'greater than or equal to';
                        break;
                    case '<': assertionOperator = 'less than';
                        break;
                    case '<=': assertionOperator = 'less than or equal to';
                        break;
                }

        }

        switch(dependency.callback){
            case 'WF::MetaDataIsSet':
              output += 'Confirming that meta data `' + dependency.paramsMap[0].value + '` has been set';
                break;
            default:
              output += 'Confirming that callback ' + dependency.callback + ' returns response ';
              output += assertionOperator + ' \'' + assertionValue + '\'';
              if(dependency.paramsMap){
                  output += ' when passed [' ;
                  for(var i in dependency.paramsMap){
                      output += dependency.paramsMap[i].value + ' (' + dependency.paramsMap[i].type + ')';
                      if(i < (dependency.paramsMap.length - 1)) output += ', '
                  }
                  output += ']';
              }
                break;
        }
        output += '<i class="fa dependency-item" rel="' + itemNum+ '"></i></li>';
        return output;
    }

    function _generateDynamicContentHTML(task){
        var html = '';
        if(task.data.status == 'completed'){
            html += '<p>This task has already been completed. ';
            if(task.data.completionReport) html += 'For more information, please review the summary report for details regarding this task.';
            html += '</p>';
        } else {
            var autoRun = _PROJECT.template.settings.autoRun;
            if(!autoRun && task.data.trigger) {
                html += '<button class="trigger-start-btn"><i class="fa fa-bolt"></i> Load Trigger</button>'
            }
            html += '<ul class="trigger-steps">';
            var hasDependencies = task.data.dependencies && task.data.dependencies.length > 0,
              unlocked = !hasDependencies || task.data.dependenciesOKTimeStamp;
            if(hasDependencies) {
                if(unlocked){
                    html += '<li class="done" data-step="0"><span class="icon"><i class="fa fa-check-square-o"></i></span> <span class="verb">Validated</span> Task Dependencies</li>';
                } else {
                    html += '<li data-step="0"><span class="icon"><i class="fa fa-square-o"></i></span> <span class="verb">Validate</span> Task Dependencies</li>';
                }
            }
//            console.log(task, typeof task.data.dependencies);
            switch (task.data.trigger.type){
                case 'lambda':
                    html += '<li data-step="1"><span class="icon"><i class="fa fa-square-o"></i></span> <span class="verb">Validate</span> Lambda Callback & Parameters</li>';
                    html += '<li data-step="2"><span class="icon"><i class="fa fa-square-o"></i></span> <span class="verb">Execute</span> Lambda Callback </li>';
                    //html += '<li data-step="3"><span class="icon"><i class="fa fa-square-o"></i></span> <span class="verb">Analyze</span> Callback Results</li>';
                    break;
                case 'form':
                    html += '<li data-step="1"><span class="icon"><i class="fa fa-square-o"></i></span> <span class="verb">Validate</span> Form</li>';
                    html += '<li data-step="2"><span class="icon"><i class="fa fa-square-o"></i></span> <span class="verb">Render</span> Custom Form </li>';
                    break;
                case 'applet':
                    html += '<li data-step="1"><span class="icon"><i class="fa fa-square-o"></i></span> <span class="verb">Validate</span> Applet</li>';
                    html += '<li data-step="2"><span class="icon"><i class="fa fa-square-o"></i></span> <span class="verb">Load</span> Applet </li>';
                    break;
            }
            html += '</ul>';
        }
        return html;
    }




    /****************************************************************************************************************/



    /****************************************************************************************************************/

    PubSub.subscribe('task.updated', _handleTaskUpdates);
    PubSub.subscribe('meta.updated', _handleMetaUpdates);
    PubSub.subscribe('project.updated', _handleProjectUpdates);


</script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/BindedBoxScreens.js"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/SlideMetadata.js"></script>
