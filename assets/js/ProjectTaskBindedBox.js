/**
 * Created by benezerlancelot on 5/17/17.
 */
var BindedBox = (function(){

    var
        __REQUESTS                  = {},
        __CURRENT                   = {
            __TASK                  : null,
            __TASKS                 : null,
            __META                  : null,
            __PROJECT               : null,
            __SETTINGS              : {
                slide               : 'tasks',
                panelOpen           : false
            },
            __CACHE                 : null,
            __USER                  : null
        },
        __UNSAVED_CHANGES           = {},
        __RENDERED                  = {},
        __REQUEST_COUNTER           = 0,
        __pubsubRoot                = 'APP.BB.',
        dataCategories = ['__TASK','__TASKS','__PROJECT','__SETTINGS','__USER','__CACHE'],
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
            issetH3 : false,
            logThreshold : 200
        },
        /**
         * This is an array of screen logs. This is to capture logging that occurs before the screens module is loaded.
         * @type {Object}
         */
        __screenLogRepo = {},

        __screenLogCount = 1,
        registeredSlideListeners = {} // An object of slides and the topics, and listeners to activate/deactivate
        ;

    function _init(){
        PubSub.subscribe( __pubsubRoot.substr(0, (__pubsubRoot.length - 1)) , __captureLoggableTraffic );
        var reqId = __addRequest( 'initiateMainModule' , 'Initializing `BindedBox` module' );
        __CURRENT.__PROJECT = _PROJECT;
        __CURRENT.__TASKS = _TASK_JSON;

        // Handle updates from other modules. @todo: Needs to be updated with new BindedBox.pubsubRoot style topic
        PubSub.subscribe('task.updated', _handleTaskUpdates);
        PubSub.subscribe('meta.updated', _handleMetaUpdates);
        PubSub.subscribe('project.updated', _handleProjectUpdates);


        //$(document).on('click', '.col-title .task-name', _handleTaskBindedTrigger); // Project list title js click event
        $(document).on('click', '.col-title .task-name', __handleClickTaskBtn); // Project list title js click event
        __addResponse( reqId , '`BindedBox` module initialized' );
    }

    function _triggerResize(){
        var reqId = __addRequest('resizeBB', 'Checking for change in dimensions');
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

            // Change pre max-height to be full height minus header and action buttons
            var $tabContainer = $bindedBox.find('.tabbed-content-container'),
                $taskTab = $bindedBox.find('.tabbed-content');

            $tabContainer.css({width : payload.tabContainerWidth});

            $taskTab.css({height : payload.newTaskTabHeight});
            $taskTab.find('.column-list.meta').css({maxHeight : (payload.newTaskTabHeight - 53)});
            $taskTab.find('.column-details.meta').css({height : (payload.newTaskTabHeight - 53)});
            $taskTab.find('.meta-fields .entries').css({maxHeight : (payload.newTaskTabHeight - 78)});
            $taskTab.find('.task-inset .inset-tab').css({height: payload.preElementHeight});

            PubSub.publish(__pubsubRoot + 'state.settings.dimensions', {
                applied : true,
                origin : '_triggerResize()',
                payload: payload
            });
            PubSub.publish('bindedBox.resize', payload);
            __addResponse(reqId, 'Binded box resized');
        } else {
            __addResponse(reqId, 'Dimensions have not changed');
        }

        return payload;
    }

    function _setOption(option, value){
        options[option] = value;
        return true;
    }

    function _getOption(option){
        return typeof options[option] == 'undefined' ? undefined : options[option];
    }

    function _accessAllowed(level){
        return BindedBox.userAcc.acc >= level;
    }

    function _getTaskDataById(id){
        for(var i in __CURRENT.__TASKS){
            if(typeof __CURRENT.__TASKS[i].id != 'undefined' && __CURRENT.__TASKS[i].id == id){
                return __CURRENT.__TASKS[i];
            }
        }
        return false;
    }

    function _getTaskDataByNumber(num){
        for(var i in __CURRENT.__TASKS){
            if(typeof __CURRENT.__TASKS[i].data.sortOrder != 'undefined' && __CURRENT.__TASKS[i].data.sortOrder == num){
                return __CURRENT.__TASKS[i];
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
        for(var i in __CURRENT.__TASKS){
            if(typeof __CURRENT.__TASKS[i].id != 'undefined' && __CURRENT.__TASKS[i].id == id){
                if(data){
                    for(var field in data){
                        var fieldIsNew = typeof __CURRENT.__TASKS[i].data[field] == 'undefined';
                        var fieldIsDifferent = fieldIsNew || (!fieldIsNew && __CURRENT.__TASKS[i].data[field] != data[field]);
                        if(fieldIsDifferent){
                            __CURRENT.__TASKS[i].data[field] = data[field];
                            //console.log('change registered');

                            // If target task is current task, update it
                            if(__CURRENT.__TASK && __CURRENT.__TASKS[i].id == __CURRENT.__TASK.id){
                                //console.log('current task change registered');
                                __CURRENT.__TASK.data[field] = data[field];
                            }

                            updates[field] = data[field];
                            //newTask = __CURRENT.__TASKS[i];

                        }
                    }
                }
            }
        }
        // if(newTask) {
        //     var payload = {
        //         id : id,
        //         updates : updates,
        //         newTask : newTask,
        //         updatesMade : newTask !== null
        //     };
        //     //_handleTaskUpdatesAirTrafficControl(payload);
        // }
    }

    function _setTaskDataByNum(num, data){
        var id = _getTaskIdByTaskNumber(num);
        if(id){
            return _setTaskDataById(id, data);
        }
    }

    function _handleTaskUpdates(topic, payload){
        // Publish PubSub
        // Update the given task in __CURRENT.__TASKS
        // Update the UI for task slide
        if(typeof payload.taskId != 'undefined'){
            if(typeof payload.updates != 'undefined'){

                console.log(__CURRENT.__TASKS, payload);
                for(var i in __CURRENT.__TASKS){
                    if(__CURRENT.__TASKS[i].data.taskId == payload.taskId){
                        for(var field in payload.updates){
                            __CURRENT.__TASKS[i].data[field] = payload.updates[field];
                        }
                        BindedBox.setElementHTML('bb_taskdata_vardump', JSON.stringify(__CURRENT.__TASKS[i], undefined, 2), $('.task-inset pre.task-data'));
                    }
                }
//                console.log(__CURRENT.__TASKS, payload);

            } else {
                console.error('updates is not defined');
            }
        } else {
            console.error('taskId is not defined');
        }

    }

    // function _handleTaskUpdatesAirTrafficControl(payload){
    //     var sent = false; // Whether or not payload has been sent or not.
    //     // Check if active task is the task that has changed
    //     var isActiveTask = typeof BindedBox.activeTaskId != 'undefined' && BindedBox.activeTaskId == payload.id;
    //     if(isActiveTask) {
    //         sent = true;
    //         PubSub.publish('taskData.updates.activeTask', payload);
    //     }
    //
    //     if(!sent){
    //         sent = true;
    //         PubSub.publish('taskData.updates.updatedTask', payload);
    //     }
    //
    //     return sent;
    // }

    // function _handleTaskBindedTrigger(e){
    //     e.preventDefault();
    //     var $this = $(this),
    //         $task = $this.parents('.task-style'),
    //         taskId = $task.data('task_id');
    //
    //     //__activate();
    //     __setNewActiveTask(taskId);
    //     //_triggerBoxOpen2(taskId);
    //     return false;
    // }


    function _handleBindBoxCloseClick(e){
        if (!$(e.target).closest(BindedBox.selector).length) {
            //_triggerBoxClose();
            __deactivate();
        }
    }

    // function _reloadBindedBox(reloadProject){
    //     if(BindedBox.activeTaskId){
    //         var task = BindedBox.getTaskById(BindedBox.activeTaskId);
    //         if(reloadProject) _renderProjectData(task);
    //         PubSub.publish('bindedBox.newTaskActivated', {
    //             activeTaskId : task.id
    //         });
    //     }
    // }

//     function _triggerBoxOpen(taskId){
//         var task = BindedBox.getTaskById(taskId);
//         if(task){
//             BindedBox.activeTaskId = task.id;
//             _activateTriggerBoxSlide('tasks'); // Default back to tasks slide
//             if(!_PROJECT.triggerBoxOpen){
//                 //console.log('trigger box opened');
//                 $(".binded-trigger-box-overlay").addClass('show');
//                 $(document).on('click', _handleBindBoxCloseClick);
//                 $(document).on('click', '.binded-trigger-box .item a', _handleTriggerBoxNavClick);
//                 $(document).on('click', '.binded-trigger-box button.js-directional', _handleDirectionalBtnClick);
//                 $(document).on('click', '.binded-trigger-box .action-btns .mark-complete', _handleMarkCompleteClick);
//                 $(document).on('keydown', _handleBindedBoxKeydown);
//                 $(window).on('load', __handleBindedBoxResize);
//                 //PubSub.subscribe('bindedBox.resize', _handleBindedBoxViewportResize);
//                 PubSub.subscribe('bindedBox.activeLockCollision', _handleActiveLockCollision);
//                 _PROJECT.triggerBoxOpen = true;
//                 PubSub.publish('bindedBox.opened', null);
//             }
//             _reloadBindedBox(true);
//             _triggerResize();
//         }
//     }
//
//     function _triggerBoxClose(){
//         //console.log(BindedBox);
// //        if(BindedBox.activeLock && !BindedBox.keepOpen){
// //            PubSub.publish('bindedBox.activeLockCollision.action.closeBindedBox', {
// //                continueCallback : _triggerBoxClose
// //            });
// //            return;
// //        }
//         var $overlay = $(".binded-trigger-box-overlay");
//         if(_PROJECT.triggerBoxOpen){
//             //console.log('trigger box closed');
//             $overlay.removeClass('show');
//             $(document).off('click', _handleBindBoxCloseClick);
//             $(document).off('click', '.binded-trigger-box .item a', _handleTriggerBoxNavClick);
//             $(document).off('click', '.binded-trigger-box button.js-directional', _handleDirectionalBtnClick);
//             $(document).off('click', '.binded-trigger-box .action-btns .mark-complete', _handleMarkCompleteClick);
//             $(document).off('keydown', _handleBindedBoxKeydown);
//             $(window).off('load', __handleBindedBoxResize);
//             //PubSub.unsubscribe('bindedBox.resize', _handleBindedBoxViewportResize);
//             PubSub.unsubscribe('bindedBox.activeLockCollision', _handleActiveLockCollision);
//             _PROJECT.triggerBoxOpen = false;
//             BindedBox.activeTaskId = null;
//             PubSub.publish('bindedBox.closed', null);
//         }
//     }

    function _handleBindedBoxKeydown(e){
        switch(e.which){
            case 37: // left
                //case 38: // up
                //if(BindedBox.actionBtns.prev && BindedBox.getOption('keyboardDirectionalBtnsActive')) _triggerBoxOpen(BindedBox.actionBtns.prev.id);
                if(BindedBox.actionBtns.prev && BindedBox.getOption('keyboardDirectionalBtnsActive')) __setNewActiveTask(BindedBox.actionBtns.prev.id);
                break;
            case 39: // right
                //case 40: // down
                if(BindedBox.actionBtns.next && BindedBox.getOption('keyboardDirectionalBtnsActive')) __setNewActiveTask(BindedBox.actionBtns.next.id);
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
        var reqId = __addRequest( 'viewPortResized' , 'Viewport resizing' );
        // Change pre max-height to be full height minus header and action buttons
        var $tabContainer = $bindedBox.find('.tabbed-content-container'),
            $taskTab = $bindedBox.find('.tabbed-content');

        $tabContainer.css({width : payload.tabContainerWidth});

        //$taskTab.css({height : payload.newTaskTabHeight});
        $taskTab.find('.column-list.meta').css({maxHeight : (payload.newTaskTabHeight - 53)});
        $taskTab.find('.column-details.meta').css({height : (payload.newTaskTabHeight - 53)});
        $taskTab.find('.meta-fields .entries').css({maxHeight : (payload.newTaskTabHeight - 78)});
        $taskTab.find('.task-inset .inset-tab').css({height: payload.preElementHeight});
        __addResponse( reqId , 'Viewport resized');
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
        var oldSlide = typeof __CURRENT.__SETTINGS.slide == 'undefined' ? null : __CURRENT.__SETTINGS.slide;
        __CURRENT.__SETTINGS.slide = slide;
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
        var reqId = __addRequest( 'renderProjectData' , 'Preparing to render project data' );

        _setBindedBoxElementHTML('bb_h2', __CURRENT.__PROJECT.projectName, $bindedBox, 'header .titles h2');
        _setBindedBoxElementHTML('bb_h3', __CURRENT.__PROJECT.templateName, $bindedBox, 'header .titles h3');
        var $headerContent = $bindedBox.find('header .upper-settings');
        if(typeof __CURRENT.__PROJECT.projectCompletionDateString == 'string') {
            $headerContent.find('.deadline-txt').show();
            $headerContent.find('.date').html(__CURRENT.__PROJECT.projectCompletionDateString);
        } else {
            $headerContent.find('.deadline-txt').hide();
        }

        var $lowerHeader = $(".lower-settings");

        // Show/hide timer
        if(_getOption('showTimer')){
            if(!_getOption('elapsedTime')) _setOption('elapsedTime', 0);
            $lowerHeader.find('.time-tracker-btn').show();
        } else {
            $lowerHeader.find('.time-tracker-btn').hide();
        }
        __addResponse( reqId , 'Project data rendered' );
        //return false;
    }


    function _handleDirectionalBtnClick(e){
        e.preventDefault();
        var $this = $(this),
            taskId = $this.data('target_id');
        //_triggerBoxOpen(taskId);
        __setNewActiveTask(taskId);
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

    function __activeTask(){
        return __CURRENT.__TASK;
    }

    function __getScreenLogs(){
        return __screenLogRepo;
    }

    function __screenLog(message, type, context){
        type = type && typeof type != 'undefined' ? (['debug','error'].indexOf(type.toLowerCase()) >= 0 ? type.toLowerCase() : 'info') : 'info' ;
        var entry = {
                date : new Date(),
                message : message,
                type : type
            };

        // Will have a context if a .res or .req
        if(context) {
            entry.context = context;

            // Makes sure it is not already set. reqId makes these values unique.
            var logCount = Object.keys( __screenLogRepo ).length ;
            var key = __screenLogCount + '_' + context.topic ;
            if(typeof __screenLogRepo[ key ] == 'undefined') {
                __screenLogRepo[ key ] = entry;
                __screenLogCount ++;
                if( logCount >= _getOption( 'logThreshold' )) {

                    // Discern how many elements to remove
                    var difference = logCount - _getOption( 'logThreshold' );

                    // Remove elements from logs repo
                    for( var i in __screenLogRepo ){
                        if( difference >= 0 ){
                            delete __screenLogRepo[ i ];
                            difference --;
                        }
                    }
                }
            }

            // Attempt to render
            if(typeof BindedBoxScreens != 'undefined'){
                BindedBoxScreens.renderLogs();
            }
        }

    }

    function __setCurrent( type , field , value ) {
        var _type = '__' + type.toUpperCase();
        __CURRENT[ _type ][ field ] = value;
    }

    function __getCurrent( type , field ) {
        var _type = '__' + type.toUpperCase();
        var _found = false;
        if ( field ){
            _found = typeof __CURRENT[ _type ] != 'undefined' && typeof __CURRENT[ _type ][ field ] != 'undefined' ;
            return _found ? __CURRENT[ _type ][ field ] : null ;
        } else {
            _found = typeof __CURRENT[ _type ] != 'undefined' ;
            return _found ? __CURRENT[ _type ] : null ;
        }
    }

    function __handleBindedBoxResize() {
        $( window ).resize( function() {
            _triggerResize();
        });
    }

    function __addRequest( slug , data ) {
        __REQUEST_COUNTER ++;
        var type = 'req';
        var topic = __pubsubRoot + type + '.' + slug + '._' + __REQUEST_COUNTER;
        if( typeof data == 'string' ) data = { message : data };
        __REQUESTS[ __REQUEST_COUNTER ] = {
            slug : slug,
            data : data
        };
        PubSub.publish( topic , data );
        // Start timeout
        return __REQUEST_COUNTER;
    }

    function __addResponse ( requestId , data ) {
        // Cancel timeout
        // Get slug from __REQUESTS data
        var type = 'res';
        var slug = typeof __REQUESTS[ requestId ] != 'undefined' ? __REQUESTS[ requestId ].slug : null;
        var topic = __pubsubRoot + type + '.' + slug + '._' + requestId;
        if ( typeof data == 'string' ) data = { message : data };
        if( !slug ) {
            console.error( 'Unable to find the slug for this request' );
        } else {
            PubSub.publish( topic , data );
        }
    }

    function __handleClickTaskBtn ( e ) {
        e.preventDefault();

        // Publish request
        var reqId = __addRequest( 'taskBtnClicked' , 'Panel button clicked' ),
            $this = $( this ),
            $task = $this.parents( '.task-style' ),
            taskId = $task.data( 'task_id' );

        // Set active task
        __setNewActiveTask( taskId );

        // Activate BB
        __activate();

        // Publish response
        __addResponse( reqId , 'Panel invocation complete' );
        return false;
    }

    /**
     * Entry Point; The function that sets a new task in the
     * @param taskId
     * @private
     */
    function __setNewActiveTask( taskId ){
        var reqId = __addRequest( 'setNewTask' , 'Attempting to set a new task' );
        if( !__CURRENT.__TASK || taskId != __CURRENT.__TASK.id ){
            var task = _getTaskDataById( taskId );
            __CURRENT.__TASK = task;
            // PubSub.publish(__pubsubRoot + 'state.task.' + taskId , {
            //     applied : true,
            //     origin : '_setNewActiveTask()',
            //     payload: taskId
            // });
            __addResponse( reqId , 'New task set' );
        } else {
            __addResponse( reqId , 'Task requested already active' );
        }

        if(typeof BindedBoxScreens != 'undefined') BindedBoxScreens.renderTaskList();

        __auditChanges();
    }

    /**
     * Routine that is invoked on a timer, or based upon an event that attempts to apply state data if out of date
     * @private
     */
    function __auditChanges( topic , payload ) {
        // Publish request
        var reqId = __addRequest( 'auditChanges' , 'Checking for data changes' );

        // Check to see if payload is set and if the changes to the state have already been updated and applied
        var issetPayload = typeof payload != 'undefined' && typeof payload.applied != 'undefined';

        // Bypass audit if changes are just being passed for
        if( issetPayload && payload.applied ) {
            __addResponse( reqId , 'Changes already rendered' );
            return;
        }

        // Check if panel is open or not
        var panelOpen = __CURRENT.__SETTINGS.panelOpen;
        if(!panelOpen) {
            __addResponse(reqId, {
                message : 'Panel not open; Ignoring render command',
                type    : 'debug'
            });
            return;
        }

        // Compare data state of app, tasks, project, meta, user, cache against the __RENDERED state to identify changes
        var
            dCat,
            renderSuccessful = false;

        for( var i in dataCategories ){
            dCat = dataCategories[ i ];
            console.log( dCat );
            console.log( __CURRENT[ dCat ] );
            console.log( __RENDERED[ dCat ] );
            console.log( JSON.stringify( __CURRENT[ dCat ] ) == JSON.stringify( __RENDERED[ dCat ] ) );
            if( JSON.stringify( __CURRENT[ dCat ] ) != JSON.stringify( __RENDERED[ dCat ] ) ) {
                // Perform HTML updates to data that has been discovered
                switch ( dCat ){
                    case '__TASK':
                        __checkTaskUpdates();
                        __setTask();
                        renderSuccessful = true;
                        break;
                    case '__TASKS':
                        __checkTasksUpdates();
                        __setTasks();
                        renderSuccessful = true;
                        break;
                    case '__PROJECT':
                        __checkForDataUpdates('project');
                        __setProject();
                        renderSuccessful = true;
                        break;
                    case '__SETTINGS':
                        break;
                    case '__USER':
                        break;
                    case '__CACHE':
                        break;
                }

            }
        }

        // Publish response
        if( renderSuccessful ) {
            _triggerResize();
            __addResponse( reqId , {
                message : 'Data changes found have been rendered' ,
                changes : __UNSAVED_CHANGES
            } );
        } else {
            __addResponse( reqId , 'No updates to render' );
        }
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setProject() {
        // Publish request
        var
            entity = 'project',
            __entity = '__' + entity.toUpperCase() ,
            reqId = __addRequest( 'setAndRender' + entity.capitalize() , 'Attempting to set and render ' + entity + ' data' ),
            wasRendered = false;

        // Check for changes
        if( typeof __UNSAVED_CHANGES[ __entity ] == 'undefined' ) __checkForDataUpdates(entity);

        if( __UNSAVED_CHANGES[ __entity ].fields.length >= 1 ){

            // Render __CURRENT html
            _renderProjectData(__CURRENT.__TASK);

            // Update __RENDERED[__entity]
            __RENDERED[__entity] = __CURRENT[__entity] ;

            // Unset __UNSAVED_CHANGES[__entity]
            __UNSAVED_CHANGES[ __entity ] = {
                fields : [],
                updates : {}
            };

            wasRendered = true;

        }

        // Publish response
        if( wasRendered ) {
            __addResponse( reqId , entity.capitalize() + ' changes rendered' );
        } else {
            __addResponse( reqId , 'No ' + entity + ' changes rendered' );
        }
    }

    /**
     * Returns data if there are changes, and null if no changes exists
     * @param data The data that is to be compared to existing data
     * @return object Returns the fields affected and data
     * @private
     */
    function __checkForDataUpdates(entity) {
        // Publish request
        var
            __entity = '__' + entity.toUpperCase() ,
            reqId = __addRequest( 'check' + entity.capitalize() + 'Updates' , 'Attempting to check for `' + entity + '` updates' ),
            response = {
                fields : [],
                updates : {}
            },
            isRendered = typeof __RENDERED[__entity] != 'undefined';

        for ( var field in __CURRENT[__entity] ) {

            if( isRendered ) {
                // Check if data matches
                if( __CURRENT[__entity][ field ] != __RENDERED[__entity][ field ] ){
                    response.fields.push( field );
                    response.updates[ field ] = __CURRENT[__entity][ field ];
                }
            } else {
                response.fields.push( field );
                response.updates[ field ] = __CURRENT[__entity][ field ];
            }
        }
        // Publish response
        __addResponse( reqId , {
            message : 'Finished checking for changes; ' + response.fields.length + ' `' + entity + '` changes found',
            response : response
        } );
        __UNSAVED_CHANGES[ __entity ] = response;
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setMeta() {
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
    function __checkMetaUpdates() {
        // Publish request
        // Publish response
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setTask() {
        // Publish request
        var
            entity = 'task',
            __entity = '__' + entity.toUpperCase() ,
            reqId = __addRequest( 'setAndRender' + entity.capitalize() , 'Attempting to set and render ' + entity + ' data' ),
            wasRendered = false;

        // Check for changes
        if( typeof __UNSAVED_CHANGES[ __entity ] == 'undefined' ) __checkTaskUpdates();

        if( __UNSAVED_CHANGES[ __entity ].fields.length >= 1 ){

            // Check if module has loaded
            if( typeof SlideTasks != 'undefined' ) {
                // Render __CURRENT html
                SlideTasks.reloadTabbedContent(__CURRENT[__entity]);

                // Update __RENDERED[__entity]
                __RENDERED[__entity] = __CURRENT[__entity] ;

                // Unset __UNSAVED_CHANGES[__entity]
                __UNSAVED_CHANGES[ __entity ] = {
                    fields : [],
                    updates : {}
                };

                wasRendered = true;

            } else {

                __addResponse( reqId , 'Module not loaded' );
                return;
            }

        }

        // Publish response
        if( wasRendered ) {
            __addResponse( reqId , entity.capitalize() + ' changes rendered' );
        } else {
            __addResponse( reqId , 'No ' + entity + ' changes rendered' );
        }
    }

    /**
     * Returns data if there are changes, and null if no changes exists
     * @param data The data that is to be compared to existing data
     * @return object Returns the fields affected and data
     * @private
     */
    function __checkTaskUpdates() {
        // Publish request
        var
            entity = 'task',
            __entity = '__' + entity.toUpperCase() ,
            reqId = __addRequest( 'check' + entity.capitalize() + 'Updates' , 'Attempting to check for `' + entity + '` updates' ),
            response = {
                fields : [],
                updates : {}
            },
            isRendered = typeof __RENDERED[__entity] != 'undefined' && typeof __RENDERED[__entity].id == 'undefined' ;

        // console.log(isRendered, __CURRENT, __RENDERED);

        for ( var field in __CURRENT[__entity].data ) {

            if( isRendered ) {
                // Check if data matches
                if( __CURRENT[__entity].data[ field ] != __RENDERED[__entity].data[ field ] ){
                    response.fields.push( field );
                    response.updates[ field ] = __CURRENT[__entity].data[ field ];
                }
            } else {
                response.fields.push( field );
                response.updates[ field ] = __CURRENT[__entity].data[ field ];
            }
        }
        // Publish response
        __addResponse( reqId , {
            message : 'Finished checking for `' + entity + '` changes; ' + response.fields.length + ' changes found',
            response : response
        } );
        __UNSAVED_CHANGES[ __entity ] = response;
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setTasks() {
        // Publish request
        var
            entity = 'tasks',
            __entity = '__' + entity.toUpperCase() ,
            reqId = __addRequest( 'setAndRender' + entity.capitalize() , 'Attempting to set and render ' + entity + ' data' ),
            wasRendered = false;

        // Check for changes
        if( typeof __UNSAVED_CHANGES[ __entity ] == 'undefined' ) __checkTasksUpdates();


        // If changes, apply changes, re-render html, update __RENDERED.__TASKS
        if( __UNSAVED_CHANGES[ __entity ].fields.length >= 1 ){

            // Notify listeners
            //PubSub.publish(__pubsubRoot + 'state.tasks', __CURRENT.__TASKS);

            // Update __RENDERED[__entity]
            __RENDERED[__entity] = __CURRENT[__entity] ;

            // Hardcode for temp use
            if(typeof BindedBoxScreens != 'undefined') BindedBoxScreens.renderTaskList();
        }

        // Publish response
        if( wasRendered ) {
            __addResponse( reqId , entity.capitalize() + ' changes rendered' );
        } else {
            __addResponse( reqId , 'No ' + entity + ' changes rendered' );
        }
    }

    /**
     * Returns data if there are changes, and null if no changes exists
     * @param data The data that is to be compared to existing data
     * @return object Returns the fields affected and data
     * @private
     */
    function __checkTasksUpdates() {
        // Publish request
        var
            entity = 'tasks',
            __entity = '__' + entity.toUpperCase() ,
            reqId = __addRequest( 'check' + entity.capitalize() + 'Updates' , 'Attempting to check for `' + entity + '` updates' ),
            response = {
                fields : [],
                updates : {}
            },
            isRendered = typeof __RENDERED[__entity] != 'undefined' && typeof __RENDERED[__entity].id == 'undefined' ;

        // console.log(isRendered, __CURRENT, __RENDERED);

        for ( var i in __CURRENT[__entity] ) {
            //console.log(__CURRENT[__entity][ i ]);

            // Check if data matches
            if( isRendered ) {
                if( __CURRENT[__entity][ i ] != __RENDERED[__entity][ i ] ){
                    response.fields.push( __CURRENT[__entity][ i ] );
                }
            } else {
                response.fields.push( __CURRENT[__entity][ i ] );
            }
        }
        // Publish response
        __addResponse( reqId , {
            message : 'Finished checking for `' + entity + '` changes; ' + response.fields.length + ' changes found',
            response : response
        } );
        __UNSAVED_CHANGES[ __entity ] = response;
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setSettings() {
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
    function __checkSettingsUpdates() {
        // Publish request
        // Publish response
    }

    /**
     * Check for data changes, apply those changes, and re-render the page elements affected.
     * @param data The data that will be checked and applied
     * @private
     */
    function __setUser() {
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
    function __checkUserUpdates() {
        // Publish request
        // Publish response
    }

    function __captureLoggableTraffic(topic, data){
        //console.log(message, {_:(data && typeof data.message != 'undefined' ? data.message : data)});
        var message = (data && typeof data.message != 'undefined' ? data.message : false),
            messageType = (data && typeof data.messageType != 'undefined' ? data.messageType : undefined),
            context = (data && typeof data.messageContext != 'undefined' ? data.messageContext : {});

        context['topic'] = topic;

        if(message){
            __screenLog(message, messageType, context);
        }
    }

    /**
     * Initiates BindedBox popup box in the user's browser
     * Renders the HTML
     * Starts BindedBox event listeners
     * @private
     */
    function __activate() {
        // Publish request
        // Check health state
        // Handle error || continue
        // Render & apply listeners
        // Publish response


        _activateTriggerBoxSlide( __CURRENT.__SETTINGS.slide ); // Default back to tasks slide

        if( !__CURRENT.__SETTINGS.panelOpen ){
            __CURRENT.__SETTINGS.panelOpen = true;
            $( '.binded-trigger-box-overlay' ).addClass( 'show' );
            $( document ).on( 'click' , _handleBindBoxCloseClick );
            $( document ).on( 'click' , '.binded-trigger-box .item a' , _handleTriggerBoxNavClick );
            $( document ).on( 'click' , '.binded-trigger-box button.js-directional' , _handleDirectionalBtnClick );
            $( document ).on( 'click' , '.binded-trigger-box .action-btns .mark-complete' , _handleMarkCompleteClick );
            $( document ).on( 'keydown' , _handleBindedBoxKeydown );
            $( window ).on( 'load' , __handleBindedBoxResize );
            //PubSub.subscribe( __pubsubRoot + 'state' , __auditChanges );
            PubSub.subscribe( 'bindedBox.resize' , _handleBindedBoxViewportResize );
            PubSub.subscribe( 'bindedBox.activeLockCollision' , _handleActiveLockCollision );
            PubSub.publish( 'bindedBox.opened' , null );
        }

        //_reloadBindedBox( true );
        __auditChanges();

    }

    function __stateChange(entity, keyValuePairs){
        var __ = '__' + entity.toUpperCase();
        var scData = {
            __ENTITY    : __,
            entity      : entity.toLowerCase(),
            values      : {}
        };
        for( var key in keyValuePairs){
            scData.values[key] = {
                oldVal : typeof __CURRENT[scData.entity] != 'undefined' && typeof __CURRENT[scData.entity][key] != 'undefined' ? __CURRENT[scData.entity][key] : undefined,
                newVal : keyValuePairs[key]
            };
            scData.values[key].same = scData.values[key].oldVal == scData.values[key].newVal;
        }

        // Report request
        var reqId = __addRequest('stateChange', {
            message : 'Change state of `' + scData.entity + '` ',
            scData : scData
        });

        // Apply the state change
        var __entity = __applyStateChange(scData);
        //console.log(__entity);

        // Publish state change
        PubSub.publish(__pubsubRoot + 'state.' + scData.entity, __entity);

        __addResponse(reqId, 'The `' + scData.entity + '` state has been changed');
    }

    function __applyStateChange(scData){
        var newData = null;
        if(typeof scData.__ENTITY != 'undefined'){
            switch (scData.__ENTITY){
                case '__TASK': // Merge current data with scData.values
                    newData = __CURRENT.__TASK;
                    // Merge in new data
                    for(var key in scData.values){
                        newData.data[key] = scData.values[key].newVal;
                    }
                    // update current with new data
                    __CURRENT.__TASK = newData;
                    break;
                case '__TASKS':
                    newData = __CURRENT.__TASKS;

                    break;
                case '__PROJECT':
                    newData = __CURRENT.__PROJECT;

                    break;
                case '__META':
                    newData = __CURRENT.__META;

                    break;
                case '__SETTINGS':
                    newData = __CURRENT.__SETTINGS;

                    break;
                case '__USER':
                    newData = __CURRENT.__USER;

                    break;
            }
        }
        return newData;
    }

    /**
     * Initiates BindedBox popup box in the user's browser
     * Renders the HTML
     * Starts BindedBox event listeners
     * @private
     */
    function __deactivate() {
        // Publish request
        // Check health state
        // Handle error || continue
        // Render & apply listeners
        // Publish response

        if( __CURRENT.__SETTINGS.panelOpen ){
            __CURRENT.__SETTINGS.panelOpen = false;
            $( '.binded-trigger-box-overlay' ).removeClass( 'show' );
            $( document ).off( 'click' , _handleBindBoxCloseClick );
            $( document ).off( 'click' , '.binded-trigger-box .item a' , _handleTriggerBoxNavClick );
            $( document ).off( 'click' , '.binded-trigger-box button.js-directional' , _handleDirectionalBtnClick );
            $( document ).off( 'click' , '.binded-trigger-box .action-btns .mark-complete' , _handleMarkCompleteClick );
            $( document ).off( 'keydown' , _handleBindedBoxKeydown );
            $( window ).off( 'load' , __handleBindedBoxResize );
            PubSub.unsubscribe( __pubsubRoot + 'state' , __auditChanges );
            PubSub.unsubscribe( 'bindedBox.resize' , _handleBindedBoxViewportResize );
            PubSub.unsubscribe( 'bindedBox.activeLockCollision' , _handleActiveLockCollision );
            PubSub.publish( 'bindedBox.closed' , null );
        }
        __auditChanges();

    }

    _init();

    return {
        TASK                        : __CURRENT.__TASK,
        TASKS                       : __CURRENT.__TASKS,
        activeLock                  : activeLock,
        keepOpen                    : keepOpen,
        userAcc                     : userAcc,
        actionBtns                  : actionBtns,
        selector                    : elementSelector,
        $el                         : $bindedBox,
        options                     : options,
        pubsubRoot                  : __pubsubRoot,
        task                        : __activeTask,
        stateChange                 : __stateChange,
        getScreenLogs               : __getScreenLogs,
        screenLog                   : __screenLog,
        checkForChanges             : __auditChanges,
        getCurrent                  : __getCurrent,
        setCurrent                  : __setCurrent,
        addRequest                  : __addRequest,
        addResponse                 : __addResponse,
        setNewActiveTask            : __setNewActiveTask,
        allowed                     : _accessAllowed,
        registerSlideListeners      : _registerSlideListener,
        unRegisterSlideListeners    : _unregisterSlideListener,
        // reload                      : _reloadBindedBox,
        getOption                   : _getOption,
        setOption                   : _setOption,
        getTaskById                 : _getTaskDataById,
        getTaskByNum                : _getTaskDataByNumber,
        getTaskIdByNum              : _getTaskIdByTaskNumber,
        setTaskById                 : _setTaskDataById,
        setTaskByNum                : _setTaskDataByNum,
        activateSlide               : _activateTriggerBoxSlide,
        triggerResize               : _triggerResize,
        setElementHTML              : _setBindedBoxElementHTML
    }
})();
