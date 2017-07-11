/**
 * Created by benezerlancelot on 5/17/17.
 */
var SlideTasks = (function(){

    var
        _listenersActive = false,
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
        PubSub.subscribe(BindedBox.pubsubRoot + 'state', _handleStateChange);

        //_activate();
        BindedBox.addResponse(reqId, '`SlideTasks` module initialized' );
        return false;
    }

    //var _LAMBDA_PROGRESS = 0;
    //var _FORM_PROGRESS = 0;

    /**
     * The progress of each task is kept for the life of the page load
     * @private
     */
    var _PROGRESS_MGR = {
        // {_taskId_} : { step: 0, key: value [,] }
    };
    var _FORM_CACHE = {};
    var _DYNAMIC_CONTENT_CACHE = {},

        _workTableInitialized = false,

    /**
     * @param _expireProgress The maximum amount of time that a tasks progress is valid for in seconds.
     * @type {{number}}
     * @private
     */
        _expireProgress = 30;

    function _task(){
        return BindedBox.task();
    }

    function _triggerProgressComplete(){
        var allComplete = true, task = _task();
        if(typeof _PROGRESS_MGR[task.id] != 'undefined'){
            for(var i in _PROGRESS_MGR[task.id]._STEPS){
                if(_PROGRESS_MGR[task.id]._STEPS[i].verb != 'did') allComplete = false;
            }
        }
        return allComplete;
    }

    /**
     *
     * @param expireProgress The maximum amount of time that a tasks progress is valid for in minutes.
     * @returns {*}
     * @private
     */
    function _getTriggerProgressData(){
        var found = null, task = _task();

        if(typeof _PROGRESS_MGR[task.id] != 'undefined'){
            var found = _PROGRESS_MGR[task.id];
                console.log(found, Date.now(), _expireProgress);

            // check if trigger progress route already complete is already complete or it is still in transition
            if(_expireProgress && !_triggerProgressComplete()) {
                var expirationMS = _expireProgress * 60 * 60,
                    currentDiffMS = Date.now() - found.ts;

                console.log(expirationMS, currentDiffMS);

                // Expire progress if eligible
                if(currentDiffMS > expirationMS) {
                    found = null;
                    _PROGRESS_MGR[task.id] = null;
                }
            }
        }

        if(!found) {
            _PROGRESS_MGR[task.id] = {
                taskNum : task.data.sortOrder,
                step : 0,
                type : typeof task.data.trigger.type == 'undefined' ? null : task.data.trigger.type,
                ts: Date.now(),
            }
        }
        return _PROGRESS_MGR[task.id];
    }

    function _getTriggerProgress(){
        return _getTriggerProgressData().step;
    }

    function _setTriggerProgress(step, progress, haltRender){
        console.log('first _setTriggerProgress');
        var task = _task();
        if(typeof _PROGRESS_MGR[task.id] == 'undefined'){
            _PROGRESS_MGR[task.id] = {
                taskNum : task.data.sortOrder,
                step : null,
                type : typeof task.data.trigger.type == 'undefined' ? null : task.data.trigger.type,
                ts: Date.now()
            };
        }
        _PROGRESS_MGR[task.id].step = step;
        _PROGRESS_MGR[task.id]._STEPS[step].verb = progress;
        if(!Boolean(haltRender)){
            PubSub.publish('_ui_render.dynamicContent.steps', {
                step : step,
                progress : progress,
                _mgr : _PROGRESS_MGR[task.id]
            });
        }
        return false;
    }

    function _incrementTriggerProgress(){
        var task = _task();
        if(typeof _PROGRESS_MGR[task.id] != 'undefined' && _PROGRESS_MGR[task.id]) {
            _PROGRESS_MGR[task.id].step ++;
            _PROGRESS_MGR[task.id].ts = Date.now();
        }
    }

    function _handleRunTriggerBtnClick(e){
        e.preventDefault();
        var reqId = BindedBox.addRequest('triggerBtnClick', 'The trigger button has been clicked');
        var task = _task();
        var taskType = task.data.trigger.type;

        _PROGRESS_MGR[task.id].step = 0;
        //console.log(_getTriggerProgressData(), taskType, _getTriggerProgress());

        if(!_triggerProgressComplete()){
            //console.log('test');
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
        return false;
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

    function _getDynamicContent(taskId){
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
            _incrementTriggerProgress();
        var
            routineSlugs = [
                'validate_dependencies',
                'validate_form',
                'render_form'
            ],
            _triggerProgress = _getTriggerProgress(),
            post = {
                projectId : _CS_Get_Project_ID(),
                taskTemplateId : $('.dynamic-content').attr('data-task_template_id'),
                routine : 'step-' + _triggerProgress,
                slug : routineSlugs[_triggerProgress],
            };
        var $triggerStartBtn = $('.trigger-start-btn');

        CS_API.call('ajax/run_form_routines',
            function(){
                // beforeSend
                _renderTriggerRoutineUIChanges(_triggerProgress, 'doing');
                if(_triggerProgress == 1){
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
                            _renderTriggerRoutineUIChanges(_triggerProgress, 'did');
                            PubSub.publish('queueNextRunFormStep', data.response);
                            break;
                        case routineSlugs[2]: //'execute_lambda_callback':
                            //_renderTriggerRoutineUIChanges((_triggerProgress - 1), 'did', true);
                            _renderTriggerRoutineUIChanges(_triggerProgress, 'did');
                            //PubSub.publish('queueNextRunLambdaStep', data.response);
                            $triggerStartBtn.removeClass('clicked').addClass('complete');
                            // $triggerStartBtn.html('<i class="fa fa-bolt"></i> Trigger Loaded');
                            // $(".dynamic-content").html(data.response._form);
                            _FORM_CACHE[post.taskTemplateId] = data.response._form;
                            _setDynamicContent(post.taskTemplateId, _FORM_CACHE[post.taskTemplateId]);
                            _renderDynamicContentHTML(null, {content: _FORM_CACHE[post.taskTemplateId]});
                            BindedBox.setElementHTML('bb_trigger_start_btn', '<i class="fa fa-bolt"></i> Trigger Loaded', $triggerStartBtn);
                            _renderTaskActionBtns();
                            break;
                     case routineSlugs[3]: //'analyze_callback_results':
                         _renderTriggerRoutineUIChanges(_triggerProgress, 'did');
                         break;
                    }
                    console.log(_triggerProgress, data);
                } else {
                    _renderTriggerRoutineUIChanges(_triggerProgress, 'doh');
                    if(data.errors && typeof data.errors[0] != 'undefined') alertify.error(data.errors[0]);
                    $triggerStartBtn.html('<i class="fa fa-exclamation-triangle"></i> Trigger Error');
                }
            },
            function(){
                // error
                _renderTriggerRoutineUIChanges(_triggerProgress, 'doh');
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
        _incrementTriggerProgress();
        var
            routineSlugs = [
                'validate_dependencies',
                'validate_lambda_callback',
                'execute_lambda_callback',
                'analyze_callback_results'
            ],
            _triggerProgress = _getTriggerProgress(),
            post = {
                projectId : _CS_Get_Project_ID(),
                taskTemplateId : $('.dynamic-content').attr('data-task_template_id'),
                routine : 'step-' + _triggerProgress,
                slug : routineSlugs[_triggerProgress]
            };
        var $triggerStartBtn = $('.trigger-start-btn');

        CS_API.call('ajax/run_lambda_routines',
            function(){
                // beforeSend
                _renderTriggerRoutineUIChanges(_triggerProgress, 'doing');
                if(_triggerProgress == 1){
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
                            _renderTriggerRoutineUIChanges(_triggerProgress, 'did');
                            PubSub.publish('queueNextRunLambdaStep', data.response);
                            break;
                        case routineSlugs[2]: //'execute_lambda_callback':
                            //_renderTriggerRoutineUIChanges((_triggerProgress - 1), 'did');
                            _renderTriggerRoutineUIChanges(_triggerProgress, 'did');
                            PubSub.publish('queueNextRunLambdaStep', data.response);
                            $triggerStartBtn.removeClass('clicked').addClass('complete');
                            $triggerStartBtn.html('<i class="fa fa-bolt"></i> Trigger Loaded');
                            break;
                        case routineSlugs[3]: //'analyze_callback_results':
                            _renderTriggerRoutineUIChanges(_triggerProgress, 'did');
                            break;
                    }
                    console.log(_triggerProgress, data);
                } else {
                    _renderTriggerRoutineUIChanges(_triggerProgress, 'doh');
                    if(data.errors && typeof data.errors[0] != 'undefined') alertify.error(data.errors[0]);
                    $triggerStartBtn.html('<i class="fa fa-exclamation-triangle"></i> Trigger Error');
                }
            },
            function(){
                // error
                _renderTriggerRoutineUIChanges(_triggerProgress, 'doh');
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

    function _initializeTriggerProgressData(){
        var task = _task();
        var type = typeof task.data.trigger.type != 'undefined' ? task.data.trigger.type : null;
        if(['form','lambda','applet'].indexOf(type) >= 0) {

            var stepsCreated = typeof _PROGRESS_MGR[task.id] != 'undefined';
            var stepsValid = stepsCreated && typeof _PROGRESS_MGR[task.id]._stepsSet != 'undefined' && _PROGRESS_MGR[task.id]._stepsSet;

            if(stepsValid) return;
            var verbTenses = {
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
                textData;

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
                            noun : "Custom Form"
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

            if(typeof _PROGRESS_MGR['_iconSet'] == 'undefined') _PROGRESS_MGR['_iconSet'] = icons;

            // _PROGRESS_MGR[task.id];
            if(typeof _PROGRESS_MGR[task.id] == 'undefined') {
                _PROGRESS_MGR[task.id] = {
                    step : 0,
                    steps : {},
                    _STEPS : {},
                    _stepsSet : false,
                    taskNum : task.data.sortOrder,
                    type : typeof task.data.trigger.type == 'undefined' ? null : task.data.trigger.type,
                    ts : Date.now()
                }
            } else {
                if(typeof _PROGRESS_MGR[task.id]._STEPS == 'undefined'){
                    _PROGRESS_MGR[task.id]._STEPS = {};
                }
            }

            var hasDependencies = _taskHasDependencies();
            var isLocked = _taskIsLocked();

            for(var i in textData){


                var addStep = false;
                var dependenciesCheckComplete = hasDependencies && !isLocked;

                // check if there are dependencies, if so, add 0
                if(i == 0){
                    if(hasDependencies){
                        addStep = true;
                    }
                } else {
                    addStep = true;
                }

                if(addStep){
                    _PROGRESS_MGR[task.id]._STEPS[i] = {
                        verb : 'do',
                        verbs : {},
                        noun : textData[i].noun
                    };

                    for( var v in verbTenses[textData[i].verb]){
                        _PROGRESS_MGR[task.id]._STEPS[i].verbs[v] =  verbTenses[textData[i].verb][v];
                    }

                    if(dependenciesCheckComplete && i == 0) {
                        //_PROGRESS_MGR[task.id].step = 1;
                        _PROGRESS_MGR[task.id]._STEPS[0].verb = 'did';
                    }
                }

                console.log(_PROGRESS_MGR);
            }

            _PROGRESS_MGR[task.id]._stepsSet = true;

            // console.log(stepNum, progress, textData[stepNum]);
            // switch (progress) {
            //     case 'error':
            //         icon = '<i class="' + icons.doh + '"></i> ';
            //         verb = verbTenses[textData[stepNum].verb].doh;
            //         break;
            //     case 'checking':
            //         icon = '<i class="' + icons.doing + '"></i> ';
            //         verb = verbTenses[textData[stepNum].verb].doing;
            //         break;
            //     case 'done':
            //         icon = '<i class="' + icons.did + '"></i> ';
            //         verb = verbTenses[textData[stepNum].verb].did;
            //         break;
            //     default:
            //         icon = '<i class="' + icons.do + '"></i> ';
            //         verb = verbTenses[textData[stepNum].verb].do;
            //         break;
            // }
            //
            // return {
            //     type : type,
            //     verbTenses : verbTenses,
            //     icons : icons,
            //     textData : textData,
            //     // icon : icon,
            //     // verb  : verb,
            //     // $step : $step
            // }
        }
    }

    function _renderTriggerRoutineUIChanges(stepNum, progress, haltRender){

        _setTriggerProgress(stepNum, progress, haltRender);

    }

    function _handleCheckDependenciesClick(e){
        if(e) e.preventDefault();
        var $this = $(".check-dependencies-btn"),
            $tabbedContent = $this.parents('.tabbed-content.tasks'),
            task = _task(),
            post = {
                projectId : _CS_Get_Project_ID(),
                taskId : task.id,
                returnReport : 'condensed'
            },
            dependencyCount = task.data.dependencies.length;

        var errorMsg01 = '<i class="fa fa-exclamation-triangle"></i> ';
        errorMsg01 += 'Dependencies have not been satisfied. This task can not be started until (' + dependencyCount + ')';
        errorMsg01 += ' dependency check' + (dependencyCount == 1 ? '' : 's') + ' pass' + (dependencyCount == 1 ? 'es' : '');
        errorMsg01 += '<a href="#" class="check-dependencies-btn br"> Re-check</a>';
        CS_API.call('ajax/check_task_dependencies',
            function(){
                // beforeSend
                $this.parents('.dynamic-content-overlay').addClass('checking');
                _renderTriggerRoutineUIChanges(0, 'doing');
            },
            function(data){
                // success
                if(data.errors == false){
                    $this.parents('.dynamic-content-overlay').removeClass('checking').addClass('checked');
                    $tabbedContent.find('.lock-status').removeClass('fa-lock').addClass('fa-unlock');
                    _renderTriggerRoutineUIChanges(0, 'did');

                    // if autoRun, _executeRunLambdaAjaxCalls();
                    if(_PROJECT.template.settings.autoRun) _executeRunLambdaAjaxCalls();

                    SlideTasks.validateAndApplyUpdates(data, true);

                } else {
                    if(typeof data.errors[0] != 'undefined') alertify.error(data.errors[0]);
                    _renderTriggerRoutineUIChanges(0, 'doh');
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
                _renderTriggerRoutineUIChanges(0, 'doh');
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
    
    function _generateAndRenderAdminTools(){
        if(BindedBox.allowed(5)){

            var $adminTools = $(".admin-tools"),
                adminToolsHTML = '';
            if(_taskHasDependencies() && !_taskIsLocked()) {
                adminToolsHTML += '<a href="#" class="tool clear-dependency-checks">Clear Dependency Checks</a>';
            }

            if(_task().data.status == 'completed'){
                adminToolsHTML += '<a href="#" class="tool mark-incomplete">Mark Incomplete</a>';
            }

            $adminTools.html(adminToolsHTML);
            BindedBox.setElementHTML('bb_admin_tools', adminToolsHTML, $adminTools);
        }
    }

    function _taskHasDependencies(){
        var task = _task();
        if(task.id){
            return task.data.dependencies.length >= 1;
        }
        return false;
    }

    function _taskIsLocked(){
        var task = _task();
        if(task.id){
            return _taskHasDependencies() && !task.data.dependenciesOKTimeStamp;
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

    function _renderWorkTable(){

        var reqId = BindedBox.addRequest('renderTaskSlide', 'Rendering task tabbed content');

        _activateListeners();

        // @todo: clear if change occurred
        // attempt to call from cache




        // If completed, show appropriate screen / completion report
        // If not completed, show appropriate trigger work table

        //_setTriggerProgress(0);
        //_FORM_PROGRESS = 0;

        var $triggerStartBtn = $('.trigger-start-btn');

        var task = _task(),
            $taskTab = $('.binded-trigger-box .tabbed-content.tasks'),
            hasDependencies = _taskHasDependencies(),
            locked = _taskIsLocked();

        _renderDynamicContentHTML();

        $taskTab.find('.dynamic-content').attr('data-task_template_id', task.data.taskId);
        $taskTab.attr('data-status', task.data.status);

        BindedBox.setElementHTML('bb_trigger_start_btn', '<i class="fa fa-bolt"></i> Trigger Loaded', $triggerStartBtn);

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

        _generateAndRenderTriggerTypeAndDescription();

        _generateAndRenderAdminTools();

        _generateAndRenderCompletionTestLink();

        _renderTaskActionBtns();

        if( typeof BindedBoxScreens != 'undefined' ) BindedBoxScreens.activate();

        _workTableInitialized = true;

        BindedBox.addResponse(reqId, 'Rendered task tabbed content');
        return false;

    }

    function _generateAndRenderCompletionTestLink(){
        var $taskTab = $('.binded-trigger-box .tabbed-content.tasks');
        var task = _task();
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

    function _generateAndRenderTriggerTypeAndDescription(){
        var task = _task();
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

    function _startRunningCompletionTest(){
        var task = _prepareTask();

        // Start the loading spinner
        // Change html to reflect loading
        //
    }

    function _renderDynamicContentHTML(topic, payload){
        var task = _task();
        var $el = $('.binded-trigger-box .tabbed-content.tasks .dynamic-content');
        var content = payload && typeof payload.content != 'undefined' ? payload.content : null;
        if(!content){
            content = '';
            // Find out if task is completed or not
            if(task.data.status == 'completed'){
                content += _generateWorkTableHTML();
            } else {
                content = _getDynamicContent(task.id);
                content = [false, null,''].indexOf(content) >= 0 ? '' : content;
                if(content == ''){
                    content += _generateWorkTableHTML();
                }
            }

        }
        $el.html(content);
        //BindedBox.setElementHTML('bb_task_dynamic_content', content, $el);
        return false;
    }

    function _generateDependenciesHTML(){
        var task = _task();
        // dependenciesOK field must be set to true or dependencies must be null to bypass dependencies overlay
        var dependencies = task.data.dependencies,
            dependenciesOKTimeStamp = task.data.dependenciesOKTimeStamp,
            // Check if dependencies exists
            hasDependencies = dependencies.length >= 1,
            overlay = '';

        // Check if dependencies have been satisfied
        if(hasDependencies && !dependenciesOKTimeStamp){
            overlay += '<div class="dynamic-content-overlay clearfix">';
            overlay += '<i class="fa fa-lock super-icon"></i>';
            overlay += '<div class="dependency-list">';
            overlay += '<a href="#" class="check-dependencies-btn br"><i class="fa fa-gear"></i> Check Dependencies</a>';
            overlay += '<p class="explanation">Dependencies are small macro functions that assure that the current task is ready to be started.</p>';
            overlay += '<span class="checking-text br"><i class="fa fa-gear fa-spin"></i> Checking Dependencies. Please wait.</span>';
            // Display dependency list
            overlay += '<ol>';
            for(var i in dependencies){
                overlay += _generateActionText(i, dependencies[i]);
            }
            overlay += '</ol>';
            overlay += '</div><!--/.dependency-list-->';
            overlay += '</div><!--/.dynamic-content-overlay-->';
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

    function _generateWorkTableHTML(){
        var task = _task();
        var html = '';
        if(task.data.status == 'completed'){
            html += '<p>This task has already been completed. ';
            if(task.data.completionReport) html += 'For more information, please review the summary report for details regarding this task.';
            html += '</p>';
        } else {
            var autoRun = _PROJECT.template.settings.autoRun;

            // Add dependencies overlay
            html += _generateDependenciesHTML();

            if(!autoRun && task.data.trigger) {
                html += '<button class="trigger-start-btn"><i class="fa fa-bolt"></i> Load Trigger</button>';
            }

            _initializeTriggerProgressData();

            html += _generateTriggerStepsHTML();

        }
        return html;
    }

    function _generateTriggerStepsHTML(){
        var task = _task();
        var html = '<ul class="trigger-steps">';

        if(typeof _PROGRESS_MGR[task.id] != 'undefined') {
            if(_PROGRESS_MGR[task.id]._STEPS){
                for( var i in _PROGRESS_MGR[task.id]._STEPS ){
                    var step = _PROGRESS_MGR[task.id]._STEPS[i];
                    html += '<li data-step="' + i + '"';
                    if(step.verb == 'did') html += ' class="done"';
                    html += '>';
                    html += '<span class="icon"><i class="' + _PROGRESS_MGR['_iconSet'][step.verb] +  '"></i></span> ';
                    html += '<span class="verb">' + step.verbs[step.verb] + '</span> ';
                    html += step.noun + '</li>';

                }
            }
        }

        html += '</ul>';
        return html;
    }

    function _renderTriggerStepsHTML(){
        $("ul.trigger-steps").html(_generateTriggerStepsHTML());
    }

    function _calculateActionBtns(){
        var
            task = _task(),
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

    function _renderTaskActionBtns(){
        var task = _task();
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
            if(_isActiveSlide()) _activateListeners(); else _deactivateListeners();
            switch (parsedTopic.map.entity){
                case 'settings':
                    break;
                case 'task':
                    if(_isActiveSlide()){
                        payload.refreshDynamicContent = typeof payload.refreshDynamicContent != 'undefined' ? payload.refreshDynamicContent : false;
                        if(payload.refreshDynamicContent) {
                            _renderDynamicContentHTML();
                        } else {
                            _renderWorkTable();
                        }
                    }
                    break;
                case 'tasks':
                    if(_isActiveSlide()) {

                    }
                    break;
            }
        }

    }

    function _isActiveSlide(){
        return _getOption('slideName') == BindedBox.getCurrent('settings','slide');
    }

    function _activateListeners(){
        if(!_listenersActive){
            _listenersActive = true;
            $(document).on('click', '.admin-tools .tool.clear-dependency-checks', _handleAdminClearDependencyCheck);
            $(document).on('click', '.admin-tools .tool.mark-incomplete', _handleAdminMarkIncomplete);
            $(document).on('click', '.tabbed-content.tasks .completion-test-btn', _handleTriggerBoxCompletionTestBtn);
            $(document).on('click', '.tabbed-content.tasks .completion-test-report-btn', _handleTriggerBoxCompletionTestReportBtn);
            $(document).on('click', '.tabbed-content.tasks .check-dependencies-btn', _handleCheckDependenciesClick);
            $(document).on('click', '.tabbed-content.tasks .trigger-start-btn', _handleRunTriggerBtnClick);
            PubSub.subscribe('_ui_render.dynamicContent.steps', _renderTriggerStepsHTML);
            PubSub.subscribe('queueNextRunLambdaStep', _executeRunLambdaAjaxCalls);
            PubSub.subscribe('queueNextRunFormStep', _executeRunFormAjaxCalls);
            if (typeof BindedBoxScreens != 'undefined') BindedBoxScreens.activate();
        }
    }

    function _deactivateListeners(){
        if(_listenersActive){
            _listenersActive = false;
            $(document).off('click', '.admin-tools .tool.clear-dependency-checks', _handleAdminClearDependencyCheck);
            $(document).off('click', '.admin-tools .tool.mark-incomplete', _handleAdminMarkIncomplete);
            $(document).off('click', '.tabbed-content.tasks .completion-test-btn', _handleTriggerBoxCompletionTestBtn);
            $(document).off('click', '.tabbed-content.tasks .completion-test-report-btn', _handleTriggerBoxCompletionTestReportBtn);
            $(document).off('click', '.tabbed-content.tasks .check-dependencies-btn', _handleCheckDependenciesClick);
            $(document).off('click', '.tabbed-content.tasks .trigger-start-btn', _handleRunTriggerBtnClick);
            PubSub.unsubscribe('_ui_render.dynamicContent.steps', _renderTriggerStepsHTML);
            PubSub.unsubscribe('queueNextRunLambdaStep', _executeRunLambdaAjaxCalls);
            PubSub.unsubscribe('queueNextRunFormStep', _executeRunFormAjaxCalls);
            if (typeof BindedBoxScreens != 'undefined') BindedBoxScreens.deactivate();
        }
    }

    _initialize();

    return {
        render : _renderDynamicContentHTML,
        activate : _activateListeners,
        deactivate : _deactivateListeners,
        calculateActionBtns : _calculateActionBtns,
        hasDependencies : _taskHasDependencies,
        _: _setTriggerProgress,
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