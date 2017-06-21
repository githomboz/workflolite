/**
 * Created by benezerlancelot on 5/17/17.
 */
var SlideTasks = (function(){

    var options = {
    };

    /**
     * Runs once upon startup
     * @private
     */
    function _initialize(){
        PubSub.subscribe('bindedBox.newTaskActivated', _renderTaskTabbedContent);
        PubSub.subscribe('taskData.updates.updatedTask', _renderTaskTabbedContent);
        return false;
    }

    var _LAMBDA_PROGRESS = 0;
    var _FORM_PROGRESS = 0;

    function _handleRunTriggerBtnClick(e){
        e.preventDefault();
        var
            taskId = _BINDED_BOX.activeTaskId,
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
                console.log(data);
                if(data.errors == false){
                    if(typeof data.response.taskUpdates != 'undfined'){
                        BindedBox.setTaskById(taskData.taskId, data.response.taskUpdates);
                        //SlideTasks.reloadTabbedContent();
                        //_triggerBoxOpen(taskData.taskId);
                    }
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
                    BindedBox.setTaskById(taskData.taskId, {
                        completeDate : null,
                        status: 'active'
                    });
                    _triggerBoxOpen(taskData.taskId);
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
                taskId : _BINDED_BOX.activeTaskId,
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

    function _generateAndRenderAdminTools(task){
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
        if(task && typeof task.data != 'undefined'){
            return task.data.dependencies.length >= 1;
        }
        return false;
    }

    function _taskIsLocked(task){
        if(task && typeof task.data != 'undefined'){
            return _taskHasDependencies(task) && !task.data.dependenciesOKTimeStamp;
        }
        return false;
    }

    function _renderTaskTabbedContent(task){

        // Check if topic or task
        task = typeof task.data != 'undefined' ? task : null;

        // If no task is passed, get task by id
        task = task || BindedBox.getTaskById(_BINDED_BOX.activeTaskId);

        console.log(task);

        var
            $taskTab = $('.binded-trigger-box .tabbed-content.tasks'),
            hasDependencies = _taskHasDependencies(task),
            locked = _taskIsLocked(task),
            dependenciesContent = _generateDependenciesHTML(task),
            dynamicContent = _generateDynamicContentHTML(task);

        if(dynamicContent){
            PubSub.publish('newDynamicContent', {
                task : task,
                content : dependenciesContent + dynamicContent
            });
        }


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

        _generateAndRenderTriggerTypeAndDescription(task);

        _generateAndRenderAdminTools(task);

        _generateAndRenderCompletionTestLink(task);

        _renderTaskActionBtns(task);
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
        // Start the loading spinner
        // Change html to reflect loading
        //
    }

    function _setTaskTabbedContentDynamicContent(topic, data){
        var $taskTab = $('.binded-trigger-box .tabbed-content.tasks');
        BindedBox.setElementHTML('bb_task_dynamic_content', data.content, $taskTab, '.dynamic-content');
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

    function _calculateActionBtns(task){
        var
            prevIndex,
            nextIndex,
            btns = {
                curr : null,
                prev : null,
                next : null
            };
        for(var i in _TASK_JSON) {
            if(task.id == _TASK_JSON[i].id){
                btns.curr = task;
                prevIndex = (parseInt(i) - 1).toString();
                btns.prev = typeof _TASK_JSON[prevIndex] != 'undefined' ? BindedBox.getTaskById(_TASK_JSON[prevIndex].id) : null;
                nextIndex = (parseInt(i) + 1).toString();
                btns.next = typeof _TASK_JSON[nextIndex] != 'undefined' ? BindedBox.getTaskById(_TASK_JSON[nextIndex].id) : null;
            }
        }
        return btns;
    }

    function _renderTaskActionBtns(task){
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
        if(task.data.status == 'completed' || dependencyHold) classes += ' inactive';
        output += '<button class="' + classes + '" data-task_id="' + task.id + '"><i class="fa fa-check"></i>&nbsp; Mark Complete</button>';
        // Add to html
        $actionBtns.html(output);
        BindedBox.setElementHTML('bb_actionBtns', output, $actionBtns);
        //return false;
    }

    function _renderBindedBoxTaskStatusChanges(topic, payload){
        // Validate taskId, status, and currentStatus
        var
            projectId = _PROJECT.projectId,
            taskId = typeof payload.taskId == 'undefined' ? null : payload.taskId,
            status = typeof payload.status == 'undefined' ? null : payload.status,
            task = BindedBox.getTaskById(taskId),
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
            BindedBox.reload(true);
            SlideTasks.reloadTabbedContent(task);
        }
    }

    function _activate(){
        _LAMBDA_PROGRESS = 0;
        _FORM_PROGRESS = 0;
        $(document).on('click', '.admin-tools .tool.clear-dependency-checks', _handleAdminClearDependencyCheck);
        $(document).on('click', '.admin-tools .tool.mark-incomplete', _handleAdminMarkIncomplete);
        $(document).on('click', '.tabbed-content.tasks .completion-test-btn', _handleTriggerBoxCompletionTestBtn);
        $(document).on('click', '.tabbed-content.tasks .completion-test-report-btn', _handleTriggerBoxCompletionTestReportBtn);
        $(document).on('click', '.tabbed-content.tasks .check-dependencies-btn', _handleCheckDependenciesClick);
        $(document).on('click', '.tabbed-content.tasks .trigger-start-btn', _handleRunTriggerBtnClick);
        PubSub.subscribe('bindedBox.task.statusChange', _renderBindedBoxTaskStatusChanges);
        PubSub.subscribe('queueNextRunLambdaStep', _executeRunLambdaAjaxCalls);
        PubSub.subscribe('queueNextRunFormStep', _executeRunFormAjaxCalls);
        PubSub.subscribe('newDynamicContent', _setTaskTabbedContentDynamicContent);
        return false;
    }

    function _deactivate(){
        $(document).off('click', '.admin-tools .tool.clear-dependency-checks', _handleAdminClearDependencyCheck);
        $(document).off('click', '.admin-tools .tool.mark-incomplete', _handleAdminMarkIncomplete);
        $(document).off('click', '.tabbed-content.tasks .completion-test-btn', _handleTriggerBoxCompletionTestBtn);
        $(document).off('click', '.tabbed-content.tasks .completion-test-report-btn', _handleTriggerBoxCompletionTestReportBtn);
        $(document).off('click', '.tabbed-content.tasks .check-dependencies-btn', _handleCheckDependenciesClick);
        $(document).off('click', '.tabbed-content.tasks .trigger-start-btn', _handleRunTriggerBtnClick);
        PubSub.unsubscribe('bindedBox.task.statusChange', _renderBindedBoxTaskStatusChanges);
        PubSub.unsubscribe('queueNextRunLambdaStep', _executeRunLambdaAjaxCalls);
        PubSub.unsubscribe('queueNextRunFormStep', _executeRunFormAjaxCalls);
        PubSub.unsubscribe('newDynamicContent', _setTaskTabbedContentDynamicContent);
        return false;
    }

    PubSub.subscribe('bindedBox.tabs.tasks.openTriggered', _activate);
    PubSub.subscribe('bindedBox.tabs.tasks.closeTriggered', _deactivate);
    PubSub.subscribe('bindedBox.closed', _deactivate);

    _initialize();

    return {
        reloadTabbedContent : _renderTaskTabbedContent,
        calculateActionBtns : _calculateActionBtns
    };
})();