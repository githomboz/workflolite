/**
 * Created by benezerlancelot on 5/17/17.
 */
var BindedBox = (function(){

    var
        __REQUESTS                  = {},
        __CURRENT                   = {
            __TASK                  : null,
            __TASKS                 : null,
            __PROJECT               : null,
            __SETTINGS              : {
                panelOpen           : false
            },
            __CACHE                 : null,
            __USER                  : null
        },
        __RENDERED                  = {},
        __REQUEST_COUNTER           = 0,
        __pubsubRoot                = 'APP.BB.',
        activeTaskId = null,
        activeTabId = null,
        /**
         * When this is set, all click events are subject to the lock. This is to avoid the accidental loss
         * of information that hasn't yet been persisted.
         */
        activeLock = null,
        /**
         * Keep the binding box open even when has been clicked outside or anything else.
         */
        keepOpen = false,
        userAcc = {
            acc : 5
        },
        elementSelector =  '.binded-trigger-box',
        $bindedBox = $(elementSelector),
        $bindedBoxInnerHead = $bindedBox.find('.inner-head'),
        dimensions  = {
            padding : 10,
            actionBtnHeight : 46,
            slideNavWidth : $(".tabbed-nav .item").outerWidth()
        },
        actionBtns = null,
        options = {
            showTaskCount : true,
            showTimer : false,
            elapsedTime : null,
            settingsDropdown : [],
            keyboardDirectionalBtnsActive : true,
            issetH2 : false,
            issetH3 : false
        },
        registeredSlideListeners = {} // An object of slides and the topics, and listeners to activate/deactivate
        ;

    function _init(){
        __CURRENT.__PROJECT = _PROJECT;
        __CURRENT.__TASKS = _TASK_JSON;

        PubSub.publish(__pubsubRoot + 'note.app', 'Binded box initialized');
        PubSub.subscribe('task.updated', _handleTaskUpdates);
        PubSub.subscribe('meta.updated', _handleMetaUpdates);
        PubSub.subscribe('project.updated', _handleProjectUpdates);
        // PubSub.subscribe('bindedBox.tabs', function(topic, payload){
        //     // console.log(topic, payload)
        // });

        $(document).on('click', '.col-title .task-name', _handleTaskBindedTrigger); // Project list title js click event
        $(document).on('click', '.col-title .task-name', __handleClickTaskBtn); // Project list title js click event
        // $(window).load(function(){
        //     $(window).resize(function(){
        //         _triggerResize();
        //     });
        // });
    }

    function _triggerResize(){
        var reqId = __addRequest('resizeBB', null);
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

        // Only resize if data has changed
        _PROJECT.dimensions = typeof _PROJECT.dimensions == 'undefined' ? null : _PROJECT.dimensions;
        if(JSON.stringify(payload) != JSON.stringify(_PROJECT.dimensions)){
            _PROJECT.dimensions = payload;
            payload.windowChanges.width = null;
            if(payload.windowWidth != _PROJECT.dimensions.windowWidth){
                payload.windowChanges.width = (payload.windowWidth > _PROJECT.dimensions.windowWidth) ? 'grow' : 'shrink';
            }
            payload.windowChanges.height = null;
            if(payload.windowHeight != _PROJECT.dimensions.windowHeight){
                payload.windowChanges.height = (payload.windowHeight > _PROJECT.dimensions.windowWidth) ? 'grow' : 'shrink';
            }

            PubSub.publish(__pubsubRoot + 'state.app.dimensions', payload);
            PubSub.publish('bindedBox.resize', payload);
            __addResponse(reqId, 'Binded box resized');
        } else {
            __addResponse(reqId, 'Dimensions have not changed');
        }
    }

    function _setOption(option, value){
        options[option] = value;
        return true;
    }

    function _getOption(option){
        return typeof options[option] == 'undefined' ? undefined : options[option];
    }

    function _getElement(){

    }

    function _accessAllowed(level){
        return BindedBox.userAcc.acc >= level;
    }

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

    function _handleTaskUpdates(topic, payload){
        // Publish PubSub
        // Update the given task in _TASK_JSON
        // Update the UI for task slide
        if(typeof payload.taskId != 'undefined'){
            if(typeof payload.updates != 'undefined'){

                console.log(_TASK_JSON, payload);
                for(var i in _TASK_JSON){
                    if(_TASK_JSON[i].data.taskId == payload.taskId){
                        for(var field in payload.updates){
                            _TASK_JSON[i].data[field] = payload.updates[field];
                        }
                        BindedBox.setElementHTML('bb_taskdata_vardump', JSON.stringify(_TASK_JSON[i], undefined, 2), $('.task-inset pre.task-data'));
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

    function _handleTaskUpdatesAirTrafficControl(payload){
        var sent = false; // Whether or not payload has been sent or not.
        // Check if active task is the task that has changed
        var isActiveTask = typeof BindedBox.activeTaskId != 'undefined' && BindedBox.activeTaskId == payload.id;
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

        //__activate();
        //_triggerBoxOpen2(taskId);
        return false;
    }


    function _handleBindBoxCloseClick(e){
        if (!$(e.target).closest(BindedBox.selector).length) {
            _triggerBoxClose();
        }
    }

    function _reloadBindedBox(reloadProject){
        if(BindedBox.activeTaskId){
            var task = BindedBox.getTaskById(BindedBox.activeTaskId);
            if(reloadProject) _renderProjectData(task);
            PubSub.publish('bindedBox.newTaskActivated', {
                activeTaskId : task.id
            });
        }
    }

    function _triggerBoxOpen(taskId){
        var task = BindedBox.getTaskById(taskId);
        if(task){
            BindedBox.activeTaskId = task.id;
            _activateTriggerBoxSlide('tasks'); // Default back to tasks slide
            if(!_PROJECT.triggerBoxOpen){
                //console.log('trigger box opened');
                $(".binded-trigger-box-overlay").addClass('show');
                $(document).on('click', _handleBindBoxCloseClick);
                $(document).on('click', '.binded-trigger-box .item a', _handleTriggerBoxNavClick);
                $(document).on('click', '.binded-trigger-box button.js-directional', _handleDirectionalBtnClick);
                $(document).on('click', '.binded-trigger-box .action-btns .mark-complete', _handleMarkCompleteClick);
                $(document).on('keydown', _handleBindedBoxKeydown);
                $(window).on('load', __handleBindedBoxResize);
                PubSub.subscribe('bindedBox.resize', _handleBindedBoxViewportResize);
                PubSub.subscribe('bindedBox.activeLockCollision', _handleActiveLockCollision);
                _PROJECT.triggerBoxOpen = true;
                PubSub.publish('bindedBox.opened', null);
            }
            _reloadBindedBox(true);
            _triggerResize();
        }
    }

    function _triggerBoxClose(){
        //console.log(BindedBox);
//        if(BindedBox.activeLock && !BindedBox.keepOpen){
//            PubSub.publish('bindedBox.activeLockCollision.action.closeBindedBox', {
//                continueCallback : _triggerBoxClose
//            });
//            return;
//        }
        var $overlay = $(".binded-trigger-box-overlay");
        if(_PROJECT.triggerBoxOpen){
            //console.log('trigger box closed');
            $overlay.removeClass('show');
            $(document).off('click', _handleBindBoxCloseClick);
            $(document).off('click', '.binded-trigger-box .item a', _handleTriggerBoxNavClick);
            $(document).off('click', '.binded-trigger-box button.js-directional', _handleDirectionalBtnClick);
            $(document).off('click', '.binded-trigger-box .action-btns .mark-complete', _handleMarkCompleteClick);
            $(document).off('keydown', _handleBindedBoxKeydown);
            $(window).off('load', __handleBindedBoxResize);
            PubSub.unsubscribe('bindedBox.resize', _handleBindedBoxViewportResize);
            PubSub.unsubscribe('bindedBox.activeLockCollision', _handleActiveLockCollision);
            _PROJECT.triggerBoxOpen = false;
            BindedBox.activeTaskId = null;
            PubSub.publish('bindedBox.closed', null);
        }
    }

    function _handleBindedBoxKeydown(e){
        switch(e.which){
            case 37: // left
                //case 38: // up
                //if(BindedBox.actionBtns.prev && BindedBox.getOption('keyboardDirectionalBtnsActive')) _triggerBoxOpen(BindedBox.actionBtns.prev.id);
                if(BindedBox.actionBtns.prev && BindedBox.getOption('keyboardDirectionalBtnsActive')) _triggerBoxOpen(BindedBox.actionBtns.prev.id);
                break;
            case 39: // right
                //case 40: // down
                if(BindedBox.actionBtns.next && BindedBox.getOption('keyboardDirectionalBtnsActive')) _triggerBoxOpen(BindedBox.actionBtns.next.id);
                break;
        }
    }

    function _handleActiveLockCollision(topic, payload){
        //console.log(topic, payload);
        if(BindedBox.activeLock){
            if(typeof BindedBox.activeLock.message != 'undefined'){
                alertify.confirm(
                    'Data Loss Warning!',
                    BindedBox.activeLock.message,
                    function(){
                        BindedBox.activeLock = null;
                        if(typeof payload.continueCallback != 'undefined') payload.continueCallback();
                    },
                    function(){
                        switch(topic){
                            case 'bindedBox.activeLockCollision.action.closeBindedBox':
                                BindedBox.keepOpen = true;
                                break;
                        }
                    }
                ).set('labels', {ok: 'I understand', cancel: 'Cancel'});
            }
        }
        return false;
    }

    function _checkMarkCompleteReady(task){
        // Check dependencies
        // Check for or generate completion report
        // If completion report generated, mark complete
    }

    function _handleMarkCompleteClick(e){
        e.preventDefault();
        var $this = $(this),
            taskId = $this.data('task_id');

        _handleMarkComplete(taskId);
        return false;
    }

    function _handleMarkComplete(taskId){
        var $this = $(this),
            taskId = $this.data('task_id'),
            task = _getTaskDataById(taskId);

        console.log(taskId, task);

        if(task){
            // Check current status
            if(task.data.status != 'completed'){
                // Create visual confirmation. Using anything other than custom will break .closest() js.
                // Check if dependencies have been reconciled
                if(task.data.dependencies) SlideTasks.handleCheckDependenciesClick();
                // If autoRun, attempt to autoRun
                // Check if completion scripts have been run successfully
                // Make sure completionReport is added to task
                // Reload task
            }
        }
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
        var oldSlide = typeof BindedBox.activeTabId == 'undefined' ? null : BindedBox.activeTabId;
        BindedBox.activeTabId = slide;
        if(slide != oldSlide){
            _deactivateRegisteredSlideListeners(oldSlide);
            _activateRegisteredSlideListeners(slide);
            var topic = null;
            if(oldSlide) {
                topic = 'bindedBox.tabs.' + oldSlide + '.closeTriggered';
                PubSub.publish(topic, null);
            }
            topic = 'bindedBox.tabs.' + slide + '.openTriggered';
            PubSub.publish(topic, null);
        }
    }


    /**
     * Set html value and store in memory what that value is to avoid having to re-render identical content
     * @param key
     * @param value
     * @param $element
     * @param elementFindStr
     * @private
     */
    function _setBindedBoxElementHTML(key, html, $element, elementFindStr){
        var preKey = '_renderCache_';
        if(_getOption(preKey + key) != html) {
            if($element.length >= 1){
                if(elementFindStr){
                    var $newElement = $element.find(elementFindStr);
                    if($newElement.length >= 1){
                        $newElement.html(html);
                        _setOption(preKey + key, html);
                    } else {
                        console.error('Invalid $element.find() query')
                    }
                } else {
                    $element.html(html);
                    _setOption(preKey + key, html);
                }
            } else {
                console.error('Invalid $element');
            }
        }
    }

    function _renderProjectData(task){
        BindedBox.setElementHTML('bb_h2', _PROJECT.projectName, $bindedBox, 'header .titles h2');
        BindedBox.setElementHTML('bb_h3', _PROJECT.templateName, $bindedBox, 'header .titles h3');
        var $headerContent = $bindedBox.find('header .upper-settings');
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
            && task.data.sortOrder
            && _TASK_JSON.length > 0){
            BindedBox.setElementHTML('bb_task_num', task.data.sortOrder, $taskCountText, '.task-num');
            BindedBox.setElementHTML('bb_task_count', _PROJECT.taskCount, $taskCountText, '.task-count');
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
        PubSub.publish('taskData.updates.updatedTask', payload);
    }

    function _registerSlideListener(slide, message, func){
        if(typeof registeredSlideListeners[slide] == 'undefined') registeredSlideListeners[slide] = {};
        if(typeof registeredSlideListeners[slide][message] == 'undefined') registeredSlideListeners[slide][message] = [];
        registeredSlideListeners[slide][message].push(func);
    }
    
    function _unregisterSlideListener(slide, message, func){
        if (slide && message && func){
            // Unset specific listener
        } else if(slide && message){
            // Unset all for specific message
        } else if(slide) {
            // Unset all for specific slide
        }
    }

    function _activateRegisteredSlideListeners(activeSlide){
        for(var slide in registeredSlideListeners){
            if(slide == activeSlide){
                for(var message in registeredSlideListeners[slide]){
                    for(var func in registeredSlideListeners[slide][message]){
                        PubSub.subscribe(message, func);
                    }
                }
            }
        }
    }

    function _deactivateRegisteredSlideListeners(activeSlide){
        for(var slide in registeredSlideListeners){
            if(slide == activeSlide){
                for(var message in registeredSlideListeners[slide]){
                    for(var func in registeredSlideListeners[slide][message]){
                        PubSub.unsubscribe(message, func);
                    }
                }
            }
        }
    }

    /***************************************************************************************/

    function __handleBindedBoxResize(){
        $(window).resize(function(){
            _triggerResize();
        });
    }

    function __addRequest( slug, data ) {
        __REQUEST_COUNTER ++;
        var type = 'req';
        var topic = __pubsubRoot + type + '.' + slug + '._' + __REQUEST_COUNTER;
        if(typeof data == 'string') data = { message : data };
        __REQUESTS[__REQUEST_COUNTER] = {
            slug : slug,
            data : data
        };
        PubSub.publish(topic, data);
        // Start timeout
        return __REQUEST_COUNTER;
    }

    function __addResponse ( requestId , data ) {
        // Cancel timeout
        // Get slug from __REQUESTS data
        var type = 'res';
        var slug = typeof __REQUESTS[requestId] != 'undefined' ? __REQUESTS[requestId].slug : null;
        var topic = __pubsubRoot + type + '.' + slug + '._' + requestId;
        if ( typeof data == 'string' ) data = { message : data };
        if( !slug ) {
            console.error( 'Unable to find the slug for this request' );
        } else {
            PubSub.publish(topic, data);
        }
    }

    function __handleClickTaskBtn ( e ) {
        e.preventDefault();

        // Publish request
        var reqId = __addRequest('taskBtnClicked', null),
            $this = $( this ),
            $task = $this.parents( '.task-style' ),
            taskId = $task.data( 'task_id' );

        // Set active task
        __setNewActiveTask(taskId);

        // Activate BB
        __activate();

        // Publish response
        __addResponse(reqId, 'Activate invoked');
        return false;
    }

    /**
     * Entry Point; The function that sets a new task in the
     * @param taskId
     * @private
     */
    function __setNewActiveTask(taskId){
        if(!__CURRENT.__TASK || taskId != __CURRENT.__TASK.id){
            var task = _getTaskDataById(taskId);
            __CURRENT.__TASK = task;
        }

        __auditChanges();
    }

    /**
     * Routine that is invoked on a timer, or based upon an event that attempts to apply state data if out of date
     * @private
     */
    function __auditChanges() {
        // Publish request
        var reqId = __addRequest('auditChanges', null);
        if ( __CURRENT.__SETTINGS.panelOpen ) {
            // Compare data state of app, tasks, project, meta, user, cache against the __RENDERED state to identify changes
            var dataCategories = ['__TASKS','__TASK','__PROJECT','__SETTINGS','__USER','__CACHE'],
                dCat,
                renderSuccessful = false,
                dataChanges = {};
            for(var i in dataCategories){
                dCat = dataCategories[i];
                if(JSON.stringify( __CURRENT[ dCat ] ) != JSON.stringify( __RENDERED[ dCat ] ) ) {
                    // Perform HTML updates to data that has been discovered
                    switch ( dCat ){
                        case '__TASK':
                            break;
                        case '__TASKS':
                            break;
                        case '__PROJECT':
                            break;
                        case '__SETTINGS':
                            break;
                        case '__USER':
                            break;
                        case '__CACHE':
                            break;
                    }

                    renderSuccessful = true;
                }
            }

            // Publish response
            if(renderSuccessful){
                __addResponse(reqId, {
                    message : 'Update renders complete.',
                    changes : dataChanges
                });
            } else {
                __addResponse(reqId, 'No updates to render.');
            }
        } else {
            // Publish response
            __addResponse(reqId, {
                message : 'Panel not open. Ignoring render command.',
                type    : 'debug'
            });
        }
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setProject(data){
        // Publish request
        // Check for changes
        // If changes, apply changes, re-render html, update __RENDERED.__PROJECT
        // Publish response
    }

    /**
     * Returns data if there are changes, and null if no changes exists
     * @param data The data that is to be compared to existing data
     * @return object Returns the fields affected and data
     * @private
     */
    function __checkProjectUpdates(data){
        // Publish request
        // Publish response
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setMeta(data){
        // Publish request
        // Check for changes
        // If changes, apply changes, re-render html, update __RENDERED.__META
        // Publish response
    }

    /**
     * Returns data if there are changes, and null if no changes exists
     * @param data The data that is to be compared to existing data
     * @return object Returns the fields affected and data
     * @private
     */
    function __checkMetaUpdates(data){
        // Publish request
        // Publish response
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setTask(data){
        // Publish request
        // Check for changes
        // If changes, apply changes, re-render html, update __RENDERED.__TASK
        // Publish response
    }

    /**
     * Returns data if there are changes, and null if no changes exists
     * @param data The data that is to be compared to existing data
     * @return object Returns the fields affected and data
     * @private
     */
    function __checkTaskUpdates(data){
        // Publish request
        // Publish response
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setTasks(data){
        // Publish request
        // Check for changes
        // If changes, apply changes, re-render html, update __RENDERED.__TASKS
        // Publish response
    }

    /**
     * Returns data if there are changes, and null if no changes exists
     * @param data The data that is to be compared to existing data
     * @return object Returns the fields affected and data
     * @private
     */
    function __checkTasksUpdates(data){
        // Publish request
        // Publish response
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setSettings(data){
        // Publish request
        // Check for changes
        // If changes, apply changes, re-render html, update __RENDERED.__APP
        // Publish response
    }

    /**
     * Returns data if there are changes, and null if no changes exists
     * @param data The data that is to be compared to existing data
     * @return object Returns the fields affected and data
     * @private
     */
    function __checkSettingsUpdates(data){
        // Publish request
        // Publish response
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setUser(data){
        // Publish request
        // Check for changes
        // If changes, apply changes, re-render html, update __RENDERED.__USER
        // Publish response
    }

    /**
     * Returns data if there are changes, and null if no changes exists
     * @param data The data that is to be compared to existing data
     * @return object Returns the fields affected and data
     * @private
     */
    function __checkUserUpdates(data){
        // Publish request
        // Publish response
    }

    /**
     * Initiates BindedBox popup box in the user's browser
     * Renders the HTML
     * Starts BindedBox event listeners
     * @private
     */
    function __activate(){
        // Publish request
        // Check health state
        // Handle error || continue
        // Render & apply listeners
        // Publish response

        BindedBox.activeTaskId = __CURRENT.__TASK.id;
        _activateTriggerBoxSlide(BindedBox.activeTabId); // Default back to tasks slide
        if(!_PROJECT.triggerBoxOpen){
            //console.log('trigger box opened');
            $(".binded-trigger-box-overlay").addClass('show');
            $(document).on('click', _handleBindBoxCloseClick);
            $(document).on('click', '.binded-trigger-box .item a', _handleTriggerBoxNavClick);
            $(document).on('click', '.binded-trigger-box button.js-directional', _handleDirectionalBtnClick);
            $(document).on('click', '.binded-trigger-box .action-btns .mark-complete', _handleMarkCompleteClick);
            $(document).on('keydown', _handleBindedBoxKeydown);
            $(window).on('load', __handleBindedBoxResize);
            PubSub.subscribe('bindedBox.resize', _handleBindedBoxViewportResize);
            PubSub.subscribe('bindedBox.activeLockCollision', _handleActiveLockCollision);
            _PROJECT.triggerBoxOpen = true;
            PubSub.publish('bindedBox.opened', null);
        }
        _reloadBindedBox(true);
        _triggerResize();
        __auditChanges();
    }

    _init();

    console.log(_PROJECT);

    return {
        activeTaskId : activeTaskId,
        activeTabId : activeTabId,
        activeLock : activeLock,
        keepOpen : keepOpen,
        userAcc : userAcc,
        actionBtns : actionBtns,
        allowed : _accessAllowed,
        selector : elementSelector,
        $el : $bindedBox,
        options : options,
        registerSlideListeners : _registerSlideListener,
        unregisterSlideListeners : _unregisterSlideListener,
        reload : _reloadBindedBox,
        loadTriggerBox : _triggerBoxOpen,
        unloadTriggerBox : _triggerBoxClose,
        getOption : _getOption,
        setOption : _setOption,
        getTaskById : _getTaskDataById,
        getTaskByNum : _getTaskDataByNumber,
        getTaskIdByNum : _getTaskIdByTaskNumber,
        setTaskById : _setTaskDataById,
        setTaskByNum : _setTaskDataByNum,
        activateSlide : _activateTriggerBoxSlide,
        triggerResize : _triggerResize,
        setElementHTML : _setBindedBoxElementHTML
    }
})();
