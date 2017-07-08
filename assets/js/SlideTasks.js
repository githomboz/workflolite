/**
 * Created by benezerlancelot on 5/17/17.
 */
var SlideTasks = (function(){

    var
        _stateSlideActive = false,
        options = {
            slideName : 'tasks'
        }
        ;

    /**
     * Runs once upon startup
     * @private
     */
    function _initialize(){
        var reqId = BindedBox.addRequest('initializeModule', 'Initializing `SlideTasks` module');
        PubSub.subscribe('taskData.updates.updatedTask', _renderTaskTabbedContent);
        PubSub.subscribe(BindedBox.pubsubRoot + 'state', _handleStateChange);

        //_activate();
        BindedBox.addResponse(reqId, '`SlideTasks` module initialized' );
        return false;
    }

    var _LAMBDA_PROGRESS = 0;
    var _FORM_PROGRESS = 0;
    var _FORM_CACHE = {};
    var _DYNAMIC_CONTENT_CACHE = {};

    function _handleRunTriggerBtnClick(e){
        e.preventDefault();
        var reqId = BindedBox.addRequest('triggerBtnClick', 'The trigger button has been clicked');
        var
            taskId = BindedBox.getCurrent('task','id'),
            task = BindedBox.getTaskById(taskId),
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
        BindedBox.addResponse(reqId , taskType + '');
    }

    function _handleAdminClearDependencyCheck(e){
        e.preventDefault();

        var taskData = {};

        taskData.taskId = $(".tabbed-content.tasks .dynamic-content").attr('data-task_template_id');
        taskData.entityId = _CS_Get_Entity_ID();
        taskData.type = _CS_Get_Entity();

        CS_API.call('ajax/clear_dependency_checks',
            function(){
                // beforeSend
            },
            function(data){
                // success
                if(data.errors == false){
                    console.log('its getting here', data);
                    SlideTasks.validateAndApplyUpdates(data, true);
                } else {
                }
            },
            function(){
                // error
            },
            taskData,
            {
                method: 'POST',
                preferCache : false
            }
        );

    }

    function _setDynamicContent(taskId, content){
        _DYNAMIC_CONTENT_CACHE[taskId] = content;
    }

    function _getDynamicContent(taskId, flush){
        flush = flush || false;
        if(flush) {
            _DYNAMIC_CONTENT_CACHE[taskId] = null;
        }
        if(typeof _DYNAMIC_CONTENT_CACHE[taskId] != 'undefined' && _DYNAMIC_CONTENT_CACHE[taskId]){
            return _DYNAMIC_CONTENT_CACHE[taskId];
        }
        return null;
    }

    function _handleAdminMarkIncomplete(e){
        e.preventDefault();
        var taskData = {};

        taskData.taskId = $(".tabbed-content.tasks .dynamic-content").attr('data-task_template_id');
        taskData.entityId = _CS_Get_Entity_ID();
        taskData.type = _CS_Get_Entity();

        CS_API.call('ajax/mark_incomplete',
            function(){
                // beforeSend
            },
            function(data){
                // success
                console.log(data);
                if(data.errors == false){
                    SlideTasks.validateAndApplyUpdates(data, true);
                } else {
                }
            },
            function(){
                // error
            },
            taskData,
            {
                method: 'POST',
                preferCache : false
            }
        );
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
                    SlideTasks.validateAndApplyUpdates(data, true);
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
                            // $triggerStartBtn.html('<i class="fa fa-bolt"></i> Trigger Loaded');
                            // $(".dynamic-content").html(data.response._form);
                            _FORM_CACHE[post.taskTemplateId] = data.response._form;
                            _setDynamicContent(post.taskTemplateId, _FORM_CACHE[post.taskTemplateId]);
                            _setTaskTabbedContentDynamicContentHTML(_FORM_CACHE[post.taskTemplateId]);
                            BindedBox.setElementHTML('bb_trigger_start_btn', '<i class="fa fa-bolt"></i> Trigger Loaded', $triggerStartBtn);
                            _renderTaskActionBtns();
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
                    SlideTasks.validateAndApplyUpdates(data, true);
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

            BindedBox.setElementHTML('bb_step_icon', icon, $step, '.icon');
            BindedBox.setElementHTML('bb_step_verb', verb, $step, '.verb');
        }

    }

    function _handleCheckDependenciesClick(e){
        if(e) e.preventDefault();
        var $this = $(".check-dependencies-btn"),
            $tabbedContent = $this.parents('.tabbed-content.tasks'),
            post = {
                projectId : _CS_Get_Project_ID(),
                taskId : BindedBox.getCurrent('task','id'),
                returnReport : 'condensed'
            },
            task = BindedBox.getTaskById(post.taskId),
            triggerType = task.data.trigger.type,
            dependencyCount = task.data.dependencies.length;

        var errorMsg01 = '<i class="fa fa-exclamation-triangle"></i> ';
        errorMsg01 += 'Dependencies have not been satisfied. This task can not be started until (' + dependencyCount + ')';
        errorMsg01 += ' dependency check' + (dependencyCount == 1 ? '' : 's') + ' pass' + (dependencyCount == 1 ? 'es' : '');
        errorMsg01 += '<a href="#" class="check-dependencies-btn br"> Re-check</a>';
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

                    SlideTasks.validateAndApplyUpdates(data, true);

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

    function _generateAndRenderAdminTools(task){
        task = _prepareTask(task);

        if(BindedBox.allowed(5)){

            var $adminTools = $(".admin-tools"),
                adminToolsHTML = '';
            if(_taskHasDependencies(task) && !_taskIsLocked(task)) {
                adminToolsHTML += '<a href="#" class="tool clear-dependency-checks">Clear Dependency Checks</a>';
            }

            if(task.data.status == 'completed'){
                adminToolsHTML += '<a href="#" class="tool mark-incomplete">Mark Incomplete</a>';
            }

            $adminTools.html(adminToolsHTML);
            BindedBox.setElementHTML('bb_admin_tools', adminToolsHTML, $adminTools);
        }
    }

    function _taskHasDependencies(task){
        task = _prepareTask(task);

        if(task && typeof task.data != 'undefined'){
            return task.data.dependencies.length >= 1;
        }
        return false;
    }

    function _taskIsLocked(task){
        task = _prepareTask(task);

        if(task && typeof task.data != 'undefined'){
            return _taskHasDependencies(task) && !task.data.dependenciesOKTimeStamp;
        }
        return false;
    }

    function _validateAndApplyUpdates(data){
        var reqId = BindedBox.addRequest('validateAndApplyUpdates', 'Preparing to validate and apply task, meta, project and settings updates');
        _validateAndApplyTaskUpdates(data);
        _validateAndApplyMetaUpdates(data);
        _validateAndApplyProjectUpdates(data);
        BindedBox.addResponse(reqId, 'Completed validation and application of task, meta, project, and settings updates');
    }

    function _validateAndApplyTaskUpdates(data){
        // Validate
        var reqId = BindedBox.addRequest('validateApplyTaskUpdates','Checking for task updates to be validated and applied ');
        var _dataSet = typeof data.response != 'undefined',
            _idSet = typeof data.response.taskId != 'undefined',
            _updatesSet = _dataSet && typeof data.response.taskUpdates != 'undefined' && data.response.taskUpdates;

        if(_updatesSet){
            if(_idSet){
                BindedBox.addResponse(reqId , {
                    message : 'Task updates have been discovered',
                    data : data.response.taskUpdates
                });

                if(BindedBox.task().id == data.response.taskId){
                    // Publish TASK state change
                    BindedBox.stateChange('task', data.response.taskUpdates);
                }

                var tasksUpdate = data.response.taskUpdates;
                tasksUpdate.id = data.response.taskId;
                // Publish TASKS state change
                BindedBox.stateChange('tasks', tasksUpdate);

                //
                // // Update TASK_JSON
                //
                // console.log('BindedBox.TASK before', BindedBox.task());
                // console.log('then this (data.response)-- _validateAndApplyTaskUpdates data.response.taskUpdates', data.response.taskUpdates);
                // BindedBox.setTaskById(data.response.taskId, data.response.taskUpdates);
                // console.log('BindedBox.TASK.id', BindedBox.task().id, 'data.response.taskId', data.response.taskId, 'BindedBox.TASK after setTaskById', BindedBox.task());
                // //BindedBox.checkForChanges();
                // //if( typeof BindedBoxScreens != 'undefined' ) BindedBoxScreens.render();
                // // _setDynamicContent(data.response.taskId, null);
                // // if(data.response.taskId == BindedBox.getCurrent('task','id') && render){
                // //     // Optionally re-render
                // //     BindedBox.reload(true);
                // //     SlideTasks.reloadTabbedContent();
                // // }
                // //
                // // PubSub.publish('task.updated', {
                // //     taskId : data.response.taskId,
                // //     updates : data.response.taskUpdates
                // // });
            } else {
                BindedBox.addResponse(reqId , {message: 'The field `taskId` must be set for updates to be applied',messageType: 'error'});
            }
        } else {
            BindedBox.addResponse(reqId , 'No updates found');
        }
    }

    function _validateAndApplyMetaUpdates(data){
        // Validate
        var _dataSet = typeof data.response != 'undefined',
            _updatesSet = _dataSet && typeof data.response.metaUpdates != 'undefined' && data.response.metaUpdates;

        if(_updatesSet){
            // Update in-mem store
            // @todo

            // render if necessary

            PubSub.publish('meta.updated', {
                updates : data.response.metaUpdates
            });
        }
    }

    function _validateAndApplyProjectUpdates(data){
        // Validate
        var _dataSet = typeof data.response != 'undefined',
            _idSet = typeof data.response.projectId != 'undefined',
            _updatesSet = _dataSet && typeof data.response.projectUpdates != 'undefined' && data.response.projectUpdates;

        if(_updatesSet){
            if(_idSet){
                // Update in-mem store

                PubSub.publish('project.updated', {
                    projectId : data.response.projectId,
                    updates : data.response.projectUpdates
                });


            } else {
                console.error('The field `projectId` must be set for updates to be applied');
            }
        }
    }

    function _renderTaskTabbedContent(task, flushDynamicContent){

        var reqId = BindedBox.addRequest('renderTaskContent', 'Rendering task tabbed content');

        _activate();

        _LAMBDA_PROGRESS = 0;
        _FORM_PROGRESS = 0;

        var flush = flushDynamicContent || false;
        if(!task) {
            flush = true;
            task = _prepareTask();
        }
        console.log(task);

        var
            $taskTab = $('.binded-trigger-box .tabbed-content.tasks'),
            hasDependencies = _taskHasDependencies(task),
            locked = _taskIsLocked(task),
            dependenciesHTML = _generateDependenciesHTML(task),
            dynamicContent = _generateDynamicContentHTML(task);

        var content = _getDynamicContent(task.id, flush);
        if(!content) {
            content = dependenciesHTML + dynamicContent;
            _setDynamicContent(task.id, content);
        }

        _setTaskTabbedContentDynamicContentHTML(content);

        $taskTab.find('.dynamic-content').attr('data-task_template_id', task.data.taskId);
        $taskTab.attr('data-status', task.data.status);

        BindedBox.setElementHTML('bb_taskdata_vardump', JSON.stringify(task, undefined, 2), $taskTab, '.task-inset pre.task-data');
        BindedBox.setElementHTML('bb_task_h1_num', task.data.sortOrder, $taskTab, 'h1 .num');
        BindedBox.setElementHTML('bb_task_h1_group', task.data.taskGroup, $taskTab, 'h1 .group');
        BindedBox.setElementHTML('bb_task_h1_name', task.data.taskName, $taskTab, 'h1 .name');
        BindedBox.setElementHTML('bb_task_status', task.data.status.capitalize(), $taskTab, '.status-info .status');
        BindedBox.setElementHTML('bb_task_description', task.data.description, $taskTab, '.description');
        BindedBox.setElementHTML('bb_task_instructions', task.data.instructions, $taskTab, '.instructions');

        if(hasDependencies){
            var icon = '<i class="fa lock-status ' + (locked ? 'fa-lock':'fa-unlock') + '"></i>';
            BindedBox.setElementHTML('bb_task_h1_icon', icon, $taskTab, 'h1 .icon');
        } else {
            BindedBox.setElementHTML('bb_task_h1_icon', '', $taskTab, 'h1 .icon');
        }

        // Handle Task Counts
        var $lowerHeader = $(".lower-settings"),
            $taskCountText = $lowerHeader.find('.task-count-txt');

        // Show/hide task counts
        if(BindedBox.getOption('showTaskCount')
            && task.data.sortOrder
            && BindedBox.TASKS.length > 0){
            //console.log(task);
            BindedBox.setElementHTML('bb_task_num', task.data.sortOrder, $taskCountText, '.task-num');
            BindedBox.setElementHTML('bb_task_count', BindedBox.getCurrent('project','taskCount'), $taskCountText, '.task-count');
            $taskCountText.show();
        } else {
            $taskCountText.hide();
        }



        _generateAndRenderTriggerTypeAndDescription(task);

        _generateAndRenderAdminTools(task);

        _generateAndRenderCompletionTestLink(task);

        _renderTaskActionBtns(task);

        if( typeof BindedBoxScreens != 'undefined' ) BindedBoxScreens.activate();
        BindedBox.addResponse(reqId, 'Rendered task tabbed content');

    }

    function _generateAndRenderCompletionTestLink(task){

        var $taskTab = $('.binded-trigger-box .tabbed-content.tasks');
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
        BindedBox.setElementHTML('bb_task_bottom_links', completionTestHTML, $taskTab, '.bottom-links');
    }

    function _generateAndRenderTriggerTypeAndDescription(task){
        task = _prepareTask(task);

        var $taskTab = $('.binded-trigger-box .tabbed-content.tasks');
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

            BindedBox.setElementHTML('bb_task_trigger_type_name', triggerOptions[task.data.trigger.type].name, $taskTab, '.trigger-type-name');
            BindedBox.setElementHTML('bb_task_trigger_type_desc', triggerOptions[task.data.trigger.type].desc, $taskTab, '.trigger-type-desc');
        }

    }

    function _startRunningCompletionTest(task){
        task = _prepareTask(task);

        // Start the loading spinner
        // Change html to reflect loading
        //
    }

    function _setTaskTabbedContentDynamicContentHTML(content){
        var $taskTab = $('.binded-trigger-box .tabbed-content.tasks');
        BindedBox.setElementHTML('bb_task_dynamic_content', content, $taskTab, '.dynamic-content');
        return true;
    }

    function _saveDynamicContentState(){
        var $taskTab = $('.binded-trigger-box .tabbed-content.tasks');
        // Take the current dynamic content state, and store it in memory for the next time you come to this page
    }

    function _generateDependenciesHTML(task){
        // task = _prepareTask(task);
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
        task = _prepareTask(task);

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

    function _calculateActionBtns(task){
        task = _prepareTask(task);
        var
            prevIndex,
            nextIndex,
            btns = {
                curr : null,
                prev : null,
                next : null
            };
        for(var i in BindedBox.TASKS) {
            if(task.id == BindedBox.TASKS[i].id){
                btns.curr = task;
                prevIndex = (parseInt(i) - 1).toString();
                btns.prev = typeof BindedBox.TASKS[prevIndex] != 'undefined' ? BindedBox.getTaskById(BindedBox.TASKS[prevIndex].id) : null;
                nextIndex = (parseInt(i) + 1).toString();
                btns.next = typeof BindedBox.TASKS[nextIndex] != 'undefined' ? BindedBox.getTaskById(BindedBox.TASKS[nextIndex].id) : null;
            }
        }
        return btns;
    }

    function _prepareTask(task){
        // Check if topic or task
        task = typeof task != 'undefined' && typeof task.data != 'undefined' ? task : null;

        // If no task is passed, get task by id
        return task || BindedBox.getTaskById(BindedBox.task().id);
    }

    function _renderTaskActionBtns(task){
        task = _prepareTask(task);

        var $actionBtns = $(".action-btns");

        BindedBox.actionBtns = SlideTasks.calculateActionBtns(task);
        var output = '';
        output += '<button class="prev-task js-directional' + (BindedBox.actionBtns.prev ? '':' inactive') + '" ';
        output += 'data-target_id="' + (BindedBox.actionBtns.prev ? BindedBox.actionBtns.prev.id : '') + '">';
        output += '<i class="fa fa-fast-backward"></i>';
        output += '&nbsp; Prev. Task</button>';
        output += '<button class="next-task js-directional' + (BindedBox.actionBtns.next ? '':' inactive') + '" ';
        output += 'data-target_id="' + (BindedBox.actionBtns.next ? BindedBox.actionBtns.next.id : '')+ '">';
        output += '<i class="fa fa-fast-forward"></i>';
        output += '&nbsp; Next Task</button>';

        var classes = 'mark-complete inverse';
        var dependencyHold = task.data.dependencies && !task.data.dependenciesOKTimeStamp;
        var isForm = task.data.trigger.type == 'form',
            formCached = typeof _FORM_CACHE[task.id] != 'undefined' ? _FORM_CACHE[task.id] : false;

        //console.log(isForm, formCached);

        if(task.data.status == 'completed' || dependencyHold || (isForm && !formCached)) classes += ' inactive';
        output += '<button class="' + classes + '" data-task_id="' + task.id + '"><i class="fa fa-check"></i>&nbsp; Mark Complete</button>';
        // Add to html
        $actionBtns.html(output);
        BindedBox.setElementHTML('bb_actionBtns', output, $actionBtns);
        //return false;
    }

    function _setOption(option, value){
        options[option] = value;
        return true;
    }

    function _getOption(option){
        return typeof options[option] == 'undefined' ? undefined : options[option];
    }

    function _handleStateChange(topic, payload){
        var parsedTopic = BindedBox.parseAppTopic(topic);
        if(parsedTopic.isValid) {
            switch (parsedTopic.map.entity){
                case 'settings':
                        if(_isActiveSlide()) _activate();
                    break;
                case 'task':
                    if(_isActiveSlide()){
                        _renderTaskTabbedContent();
                    }
                    break;
                case 'tasks':
                    console.log('test2');
                    if(_isActiveSlide()) {

                    }
                    break;
            }
        }

    }

    function _isActiveSlide(){
        return _getOption('slideName') == BindedBox.getCurrent('settings','slide');
    }

    function _activate(){
            $(document).on('click', '.admin-tools .tool.clear-dependency-checks', _handleAdminClearDependencyCheck);
            $(document).on('click', '.admin-tools .tool.mark-incomplete', _handleAdminMarkIncomplete);
            $(document).on('click', '.tabbed-content.tasks .completion-test-btn', _handleTriggerBoxCompletionTestBtn);
            $(document).on('click', '.tabbed-content.tasks .completion-test-report-btn', _handleTriggerBoxCompletionTestReportBtn);
            $(document).on('click', '.tabbed-content.tasks .check-dependencies-btn', _handleCheckDependenciesClick);
            $(document).on('click', '.tabbed-content.tasks .trigger-start-btn', _handleRunTriggerBtnClick);
            PubSub.subscribe('bindedBox.newTaskActivated', _renderTaskTabbedContent);
            PubSub.subscribe('queueNextRunLambdaStep', _executeRunLambdaAjaxCalls);
            PubSub.subscribe('queueNextRunFormStep', _executeRunFormAjaxCalls);
            if (typeof BindedBoxScreens != 'undefined') BindedBoxScreens.activate();
    }

    function _deactivate(){
            $(document).off('click', '.admin-tools .tool.clear-dependency-checks', _handleAdminClearDependencyCheck);
            $(document).off('click', '.admin-tools .tool.mark-incomplete', _handleAdminMarkIncomplete);
            $(document).off('click', '.tabbed-content.tasks .completion-test-btn', _handleTriggerBoxCompletionTestBtn);
            $(document).off('click', '.tabbed-content.tasks .completion-test-report-btn', _handleTriggerBoxCompletionTestReportBtn);
            $(document).off('click', '.tabbed-content.tasks .check-dependencies-btn', _handleCheckDependenciesClick);
            $(document).off('click', '.tabbed-content.tasks .trigger-start-btn', _handleRunTriggerBtnClick);
            PubSub.unsubscribe('bindedBox.newTaskActivated', _renderTaskTabbedContent);
            PubSub.unsubscribe('queueNextRunLambdaStep', _executeRunLambdaAjaxCalls);
            PubSub.unsubscribe('queueNextRunFormStep', _executeRunFormAjaxCalls);
            if (typeof BindedBoxScreens != 'undefined') BindedBoxScreens.deactivate();
    }

    _initialize();

    return {
        activate : _activate,
        deactivate : _deactivate,
        reloadTabbedContent : _renderTaskTabbedContent,
        calculateActionBtns : _calculateActionBtns,
        hasDependencies : _taskHasDependencies,
        isLocked : _taskIsLocked,
        getOption : _getOption,
        setOption : _setOption,
        isActiveSlide : _isActiveSlide,
        renderActionBtns : _renderTaskActionBtns,
        validateAndApplyUpdates : _validateAndApplyUpdates,
        validateAndApplyTaskUpdates : _validateAndApplyTaskUpdates,
        validateAndApplyMetaUpdates : _validateAndApplyMetaUpdates,
        validateAndApplyProjectUpdates : _validateAndApplyProjectUpdates,
        handleCheckDependenciesClick : _handleCheckDependenciesClick
    };
})();

PubSub.subscribe('bindedBox.tabs.' + SlideTasks.getOption('slideName') + '.openTriggered', SlideTasks.activate);
PubSub.subscribe('bindedBox.tabs.' + SlideTasks.getOption('slideName') + '.closeTriggered', SlideTasks.deactivate);
PubSub.subscribe('bindedBox.closed', SlideTasks.deactivate);