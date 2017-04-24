<?php //var_dump($this->job->saveAsTemplate('New JNBPA workflow')); ?>
<?php //var_dump($this->job->getActionableTasks()); ?>
<div class="main-mid-section clearfix">
    <div class="main-mid-section-inner clearfix">

        <h1><i class="fa fa-tasks"></i> Tasks</h1>
        <?php if(template())  echo '<h4>'.template()->getValue('description') . '</h4>'; else {
            echo '<h4>A comprehensive list of the tasks required to complete this project.</h4>';
        }

        include_once 'widgets/_task-change-dialog.php';

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
                var _TASK_JSON = [];
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

    //console.log(_TASK_JSON);

    function _getTaskDataById(id){
        for(var i in _TASK_JSON){
            if(typeof _TASK_JSON[i].id != 'undefined' && _TASK_JSON[i].id == id){
                return _TASK_JSON[i];
            }
        }
        return false;
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
        if (!$(event.target).closest('.binded-trigger-box').length) {
            _triggerBoxClose();
        }
    });

    function _triggerBoxOpen(taskId){
        var $overlay = $(".binded-trigger-box-overlay");
        var task = _getTaskDataById(taskId);
        _LAMBDA_PROGRESS = 0;
        if(task){
            if(!$overlay.is('.show')){
                $(".binded-trigger-box-overlay").addClass('show');
                $(document).on('click', '.binded-trigger-box .item a', _handleTriggerBoxNavClick);
                $(document).on('click', '.tabbed-content.metadata .meta-fields .entry', _metadataEntrySelected);
                $(document).on('click', '.tabbed-content.tasks .task-data-block a', _handleTriggerBoxPreviewData);
                $(document).on('click', '.tabbed-content.tasks .completion-test-btn', _handleTriggerBoxCompletionTestBtn);
                $(document).on('click', '.tabbed-content.tasks .completion-test-report-btn', _handleTriggerBoxCompletionTestReportBtn);
                $(document).on('click', '.tabbed-content.tasks .check-dependencies-btn', _handleCheckDependenciesClick);
                $(document).on('click', '.tabbed-content.tasks .trigger-start-btn', _handleRunLambdaBtnClick);
                PubSub.subscribe('bindedBox.task.statusChange', _renderBindedBoxTaskStatusChanges);
                PubSub.subscribe('queueNextRunLambdaStep', _executeRunLambdaAjaxCalls);
                PubSub.subscribe('newDynamicContent', _setTaskTabbedContentDynamicContent);
            }
            _renderTriggerBoxProjectAndTaskData(task);
            _renderTaskTabbedContent(task);
            _renderMetaDataTabbedContent();
        }
    }

    function _triggerBoxClose(){
        var $overlay = $(".binded-trigger-box-overlay");
        if($overlay.is('.show')){
            //console.log('trigger box closed');
            $overlay.removeClass('show');
            $(document).off('click', '.binded-trigger-box .item a', _handleTriggerBoxNavClick);
            $(document).off('click', '.tabbed-content.metadata .meta-fields .entry', _metadataEntrySelected);
            $(document).off('click', '.tabbed-content.tasks .task-data-block a', _handleTriggerBoxPreviewData);
            $(document).off('click', '.tabbed-content.tasks .completion-test-btn', _handleTriggerBoxCompletionTestBtn);
            $(document).off('click', '.tabbed-content.tasks .completion-test-report-btn', _handleTriggerBoxCompletionTestReportBtn);
            $(document).off('click', '.tabbed-content.tasks .check-dependencies-btn', _handleCheckDependenciesClick);
            $(document).off('click', '.tabbed-content.tasks .trigger-start-btn', _handleRunLambdaBtnClick);
            PubSub.unsubscribe('bindedBox.task.statusChange', _renderBindedBoxTaskStatusChanges);
            PubSub.unsubscribe('queueNextRunLambdaStep', _executeRunLambdaAjaxCalls);
            PubSub.unsubscribe('newDynamicContent', _setTaskTabbedContentDynamicContent);
        }
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

    function _handleRunLambdaBtnClick(e){
        e.preventDefault();
        if(!_LAMBDA_PROGRESS){
            // Start ajax jumps
            _executeRunLambdaAjaxCalls()
        }
    }

    function _executeRunLambdaAjaxCalls(topic, payload){
        _LAMBDA_PROGRESS++;
        var post = {
            projectId : _CS_Get_Project_ID(),
            taskTemplateId : $('.dynamic-content').attr('data-task_template_id'),
            routine : 'step-' + _LAMBDA_PROGRESS
        };
        var $lambdaStartBtn = $('.lambda-start-btn');

        CS_API.call('ajax/run_lambda_routines',
          function(){
              // beforeSend
              _renderLambdaRoutineUIChanges(_LAMBDA_PROGRESS, 'checking');
              if(_LAMBDA_PROGRESS == 1){
                  $lambdaStartBtn.addClass('clicked');
                  $lambdaStartBtn.html('<i class="fa fa-spin fa-spinner"></i> Loading Trigger');
              }
          },
          function(data){
              // success
              if(data.errors == false){
                  _renderLambdaRoutineUIChanges(_LAMBDA_PROGRESS, 'done');
                  if(_LAMBDA_PROGRESS < 3){
                      PubSub.publish('queueNextRunLambdaStep', data.response);
                  }
                  if(_LAMBDA_PROGRESS == 3) {
                      $lambdaStartBtn.removeClass('clicked').addClass('complete');
                      $lambdaStartBtn.html('<i class="fa fa-bolt"></i> Trigger Loaded');
                  }
              } else {
                  alertify.error(data.errors[0]);
              }
          },
          function(){
              // error
              alertify.error('Error', 'An error has occurred.');
          },
          post,
          {
              method: 'POST',
              preferCache : false
          }
        );
    }

    function _renderLambdaRoutineUIChanges(stepNum, progress){
        var $steps = $('.dynamic-content .trigger-steps'),
        $step = $steps.find('[data-step=' + stepNum + ']'),
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
        },
        verbTenses = {
          validate : {
              do : "Validate",
              doing : "Validating",
              did : "Validated"
          },
          execute : {
              do : "Execute",
              doing : "Executing",
              did : "Executed"
          },
          analyze : {
              do : "Analyze",
              doing : "Analyzing",
              did : "Analyzed"
          },
          render : {
              do : "Render",
              doing : "Rendering",
              did : "Rendered"
          }
        },
          icons = {
              do : 'fa fa-square-o',
              doing : 'fa fa-spinner fa-spin',
              did : 'fa fa-check-square-o'
          },
          icon = null;


        switch (progress){
            case 'checking':
                icon = '<i class="' + icons.doing + '"></i> ';
                $step.find('.icon').html(icon);
                $step.find('.verb').html(verbTenses[textData[stepNum].verb].doing);
                //$step.html(icon +  + ' ' + textData[stepNum].noun);
                $step.removeClass('done');
                break;
            case 'done':
                icon = '<i class="' + icons.did + '"></i> ';
                $step.find('.icon').html(icon);
                $step.find('.verb').html(verbTenses[textData[stepNum].verb].doing);
                //$step.html(icon + verbTenses[textData[stepNum].verb].did + ' ' + textData[stepNum].noun);
                $step.addClass('done');
                break;
            default:
                icon = '<i class="' + icons.do + '"></i> ';
                $step.find('.icon').html(icon);
                $step.find('.verb').html(verbTenses[textData[stepNum].verb].doing);
                //$step.html(icon + verbTenses[textData[stepNum].verb].do + ' ' + textData[stepNum].noun);
                $step.removeClass('done');
                break;
        }
    }

    function _handleCheckDependenciesClick(e){
        e.preventDefault();
        var $this = $(this),
          $tabbedContent = $this.parents('.tabbed-content'),
          post = {
              projectId : _CS_Get_Project_ID(),
              taskTemplateId : $this.parents('.dynamic-content').attr('data-task_template_id')
          };

        CS_API.call('ajax/check_task_dependencies',
        function(){
          // beforeSend
            $this.parents('.dynamic-content-overlay').addClass('checking');
            _renderLambdaRoutineUIChanges(0, 'checking');
        },
        function(data){
          // success
          if(data.errors == false){
              $this.parents('.dynamic-content-overlay').removeClass('checking').addClass('checked');
              $tabbedContent.find('.lock-status').removeClass('fa-lock').addClass('fa-unlock');
              _renderLambdaRoutineUIChanges(0, 'done');

              // if autoRun, _executeRunLambdaAjaxCalls();
              if(_PROJECT.template.settings.autoRun) _executeRunLambdaAjaxCalls();

              //PubSub.publish('taskChange.taskComplete', data.response);
          } else {
              alertify.error(data.errors[0]);
          }
        },
        function(){
          // error
          alertify.error('Error', 'An error has occurred while checking dependencies. Please try again later.');
        },
        post,
        {
          method: 'POST',
          preferCache : false
        }
        );

        return false;
    }

    function _handleCheckDependencies(task){
        // Check if dependenciesOKTimeStamp is not false
        // Check if dependenciesOKTimeStamp is within recheckThreshold limit
        // If necessary, recheck dependencies
        // If dependencies ok, save dependenciesOKTimeStamp
        // Update and publish task updates
    }

    function _handleTriggerBoxCompletionTestBtn(e){
        e.preventDefault();

    }

    function _handleTriggerBoxCompletionTestReportBtn(e){
        e.preventDefault();

    }

    function _handleTriggerBoxPreviewData(e){
        e.preventDefault();
        var $el = $(e.target),
          $dataBlock = $el.parents('.task-data-block'),
          $pre = $dataBlock.find('pre');
        $pre.toggle();
    }

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
    }

    var _BoxTriggerSettings = {
        showTaskCount : true,
        showTimer : false,
        elapsedTime : null,
        settingsDropdown : []
    };

    function _renderTriggerBoxProjectAndTaskData(taskData){
        //console.log(projectData, taskData);
        var $triggerBox = $('.binded-trigger-box');
        var $markCompleteBtn = $(".action-btns .mark-complete");

        if(taskData.data.status == 'completed') {
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
        if(_BoxTriggerSettings.showTaskCount
          && taskData.data.sortOrder
          && _TASK_JSON.length > 0){
            $taskCountText.find('.task-num').html(taskData.data.sortOrder);
            $taskCountText.find('.task-count').html(_PROJECT.taskCount);
            $taskCountText.show();
        } else {
            $taskCountText.hide();
        }

        // Show/hide timer
        if(_BoxTriggerSettings.showTimer){
            if(!_BoxTriggerSettings.elapsedTime) _BoxTriggerSettings.elapsedTime = 0;
            $lowerHeader.find('.time-tracker-btn').show();
        } else {
            $lowerHeader.find('.time-tracker-btn').hide();
        }
        return false;
    }

    var _metadata_data = {
        listSelected : false,
        selectedEntry : null,
        selectedData : null,
        listChanged : true, //(typeof _METADATA != 'undefined')
        editMode : false
    };

    var $metadataTab = $('.tabbed-content.metadata');

    function _renderTaskTabbedContent(task){
        var $taskTab = $('.binded-trigger-box .tabbed-content.tasks');
        //console.log(task.data);
        //$taskTab.find('.dynamic-content').html('Loading content ... <i class="fa fa-spin fa-spinner"></i>');
        $taskTab.find('.dynamic-content').attr('data-task_template_id', task.data.taskId);
        $taskTab.attr('data-status', task.data.status);
        $taskTab.find('pre').html(JSON.stringify(task, undefined, 2));
        $taskTab.find('h1 .num').html(task.data.sortOrder);
        $taskTab.find('h1 .group').html(task.data.taskGroup);
        var hasDependencies = task.data.dependencies.length >= 1;
        var unlocked = !hasDependencies || task.data.dependenciesOKTimeStamp;
        var icon = '<i class="fa lock-status ' + (!unlocked ? 'fa-lock':'fa-unlock') + '"></i>';
        $taskTab.find('h1 .icon').html(icon);
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
            //console.log(_PROJECT, autoRun);
            if(autoRun){
                triggerOptions.lambda.desc = 'This task runs automatically. No action required.';
            } else {
                triggerOptions.lambda.desc = 'This task will run automatically once <span class="false-btn"><i class="fa fa-bolt"></i> Load</span> is clicked.';
            }

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
                        overlay += _generateActionText(dependencies[i]);
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

    function _generateActionText(dependency){
        var output = '<li>',
          assertionOperator = null,
          assertionValue = null;

        if(dependency.assertion){
            for(var operator in dependency.assertion){
                assertionValue = dependency.assertion[operator];
                switch (operator){
                    case '_eq': assertionOperator = 'equal to';
                        break;
                    case '_neq': assertionOperator = 'not equal to';
                        break;
                    case '_gt': assertionOperator = 'greater than';
                        break;
                    case '_lt': assertionOperator = 'less than';
                        break;
                }

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
        output += '</li>';
        return output;
    }

    function _generateDynamicContentHTML(task){
        var html = '';
        if(task.data.status == 'completed'){
            html += '<p>This task has already been completed. For more information, please review the summary report for details regarding this task.</p>';
        } else {
            var autoRun = _PROJECT.template.settings.autoRun;
            if(!autoRun && task.data.trigger) {
                html += '<button class="trigger-start-btn"><i class="fa fa-bolt"></i> Load Trigger</button>'
            }
            html += '<ul class="trigger-steps">';
            if(task.data.dependencies && task.data.dependencies.length > 0) {
                html += '<li data-step="0"><i class="fa fa-square-o"></i> <span class="verb">Validate</span> Task Dependencies</li>';
            }
//            console.log(task, typeof task.data.dependencies);
            switch (task.data.trigger.type){
                case 'lambda':
                    html += '<li data-step="1"><span class="icon"><i class="fa fa-square-o"></i></span> <span class="verb">Validate</span> Lambda Callback & Parameters</li>';
                    html += '<li data-step="2"><span class="icon"><i class="fa fa-square-o"></i></span> <span class="verb">Execute</span> Lambda Callback </li>';
                    html += '<li data-step="3"><span class="icon"><i class="fa fa-square-o"></i></span> <span class="verb">Analyze</span> Callback Results</li>';
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

    function _metadataEntrySelected(e){
        e.preventDefault();
        var
          $entry = $(this),
          slug = $entry.data('slug');

        // Mark list as selected
        _metadata_data.listSelected = true;
        // Mark current entry as selected
        _metadata_data.selectedEntry = slug;
        _metadata_data.selectedData = _METADATA[slug];
        // Run Render
        _renderMetaDataTabbedContent();
        return false;
    }

    function _renderMetaDataTabbedContent(){
        var $entries = $('.binded-trigger-box .tabbed-content.metadata .entries');
        // Render list
        if(_metadata_data.listChanged){
            var html = '';
            var metaDataCount = 0;
            for(var _slug in _METADATA){
                html += '<div class="entry clearfix" data-slug="' + _METADATA[_slug].slug + '">' + "\n";
                html += "\t" + '<span class="key truncate">' + _METADATA[_slug].field + '</span>' + "\n";
                html += "\t" + '<span class="value truncate">';
                var value = _METADATA[_slug].formatted || _METADATA[_slug].value;
                if(value != null){
                    switch(_METADATA[_slug].type){
                        case 'address':
                            html += value;
                            break;
                        case 'array':
                          html += JSON.stringify(value);
                            break;
                        default:
                            html += value;
                            break;
                    }
                } else {
                    html += '[ value not set ]';
                }
                html += '</span>' + "\n";
                html += "\t" + '<i class="fa fa-chevron-right"></i>' + "\n";
                html += '</div>' + "\n";
                metaDataCount ++;
            }

            $entries.html(html);
            $(".tabbed-nav .database-nav .num-flag").text(metaDataCount);

            // Reset listChanged
            _metadata_data.listChanged = false;
        }

        //console.log($entries.html());

        // Set list selected class
        if(_metadata_data.listSelected) {
            $entries.addClass('selected');
        } else {
            $entries.removeClass('selected');
        }

        // Set entry selected
        if(_metadata_data.selectedEntry){
            $entries.find('.entry').removeClass('selected');
            var $entry = $entries.find('[data-slug='+_metadata_data.selectedEntry+']');
            $entry.addClass('selected');
        } else {
            $entries.find('.entry').removeClass('selected');
        }

        // Render Details
        _renderMetaDataTabbedContent_Details();
    }

    function _renderMetaDataTabbedContent_Details(){
        var $details = $('.column-details');
        if(_metadata_data.selectedData){
            $details.find('h2').html(_metadata_data.selectedData.field);
            $details.find('.meta-entry.slug .val').html('job.' + _metadata_data.selectedData.slug);
            $details.find('.meta-entry.type .val').html(_metadata_data.selectedData.type.capitalize());
            switch(_metadata_data.selectedData.type){
                case 'array':
                case 'address':
                    $details.find('.meta-entry.value .val').html('<pre>' + JSON.stringify(_metadata_data.selectedData.value, undefined, 2) + '</pre>');
                    break;
                default:
                    $details.find('.meta-entry.value .val').html(_metadata_data.selectedData.value);
                    break;
            }
            if(_metadata_data.selectedData.formatted){
                $details.find('.meta-entry.formatted').addClass('show');
                $details.find('.meta-entry.formatted .val').html(_metadata_data.selectedData.formatted);
            } else {
                $details.find('.meta-entry.formatted').removeClass('show');
                $details.find('.meta-entry.formatted .val').html('');
            }
            if(_metadata_data.selectedData.format){
                $details.find('.meta-entry.format').addClass('show');
                $details.find('.meta-entry.format .val').html(_metadata_data.selectedData.format);
            } else {
                $details.find('.meta-entry.format').removeClass('show');
                $details.find('.meta-entry.format .val').html('');
            }
            $details.find('.inner-details').addClass('show');
        } else {
            $details.find('.inner-details').removeClass('show');
        }
    }




</script>
