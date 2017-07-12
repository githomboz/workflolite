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
        __RENDERED                  = {},
        __REQUEST_COUNTER           = 0,
        __pubsubRoot                = 'APP.BB.',
        __ENABLE_LOGGING            = false,
        dataCategories = ['__TASK','__TASKS','__PROJECT','__SETTINGS','__USER','__CACHE'],
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
            logThreshold : 100
        },
        /**
         * This is an array of screen logs. This is to capture logging that occurs before the screens module is loaded.
         * @type {Object}
         */
        __screenLogRepo = {},

        __screenLogCount = 1,
        __listenersActive = false
        ;

    function _init(){
        PubSub.subscribe( __pubsubRoot.substr(0, (__pubsubRoot.length - 1)) , __captureLoggableTraffic );
        var reqId = __addRequest( 'initiateMainModule' , 'Initializing `BindedBox` module' );
        __CURRENT.__PROJECT = _PROJECT;
        __CURRENT.__TASKS = _TASK_JSON;

        PubSub.subscribe(__pubsubRoot + 'state', _handleStateChange);

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
        __CURRENT.__PROJECT.dimensions = typeof __CURRENT.__PROJECT.dimensions == 'undefined' ? null : __CURRENT.__PROJECT.dimensions;

        if(JSON.stringify(payload) != JSON.stringify(__CURRENT.__PROJECT.dimensions)){
            __CURRENT.__PROJECT.dimensions = payload;
            payload.windowChanges.width = null;
            if(payload.windowWidth != __CURRENT.__PROJECT.dimensions.windowWidth){
                payload.windowChanges.width = (payload.windowWidth > __CURRENT.__PROJECT.dimensions.windowWidth) ? 'grow' : 'shrink';
            }
            payload.windowChanges.height = null;
            if(payload.windowHeight != __CURRENT.__PROJECT.dimensions.windowHeight){
                payload.windowChanges.height = (payload.windowHeight > __CURRENT.__PROJECT.dimensions.windowWidth) ? 'grow' : 'shrink';
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
    }

    function _setTaskDataByNum(num, data){
        var id = _getTaskIdByTaskNumber(num);
        if(id){
            return _setTaskDataById(id, data);
        }
    }

    function _handleBindBoxCloseClick(e){
        if (!$(e.target).closest(BindedBox.selector).length) {
            __stateChange('settings', {panelOpen: false});
        }
    }



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

    function _handleTriggerBoxNavClick(e){
        e.preventDefault();
        var $this = $(this);
        var $activeSlide = $(".tabbed-content.show");
        var activeSlideName = $activeSlide.data('slide');
        var clickedSlide = $this.attr('rel');
        if(activeSlideName != clickedSlide){
            __stateChange('settings', {slide: clickedSlide});
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
                console.error('Invalid $element', preKey + key);
            }
        }
    }

    function _renderSettingsData(){
        //console.log(__CURRENT.__SETTINGS);
        var reqId = __addRequest( 'renderSettingsData' , 'Preparing to render settings data' );

        if(__CURRENT.__SETTINGS.panelOpen){
            $( '.binded-trigger-box-overlay' ).addClass( 'show' );
        } else {
            $( '.binded-trigger-box-overlay' ).removeClass( 'show' );
        }


        _activateTriggerBoxSlide(__CURRENT.__SETTINGS.slide);

        _triggerResize();

        __addResponse( reqId , 'Settings data rendered' );
    }

    function _renderProjectData(){
        //console.log(__CURRENT.__PROJECT);
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
        __RENDERED.__PROJECT = __CURRENT.__PROJECT;
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
            _found = typeof __CURRENT[ _type ] != 'undefined' && __CURRENT[ _type ] && typeof __CURRENT[ _type ][ field ] != 'undefined' ;
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
        if(__ENABLE_LOGGING){
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
    }

    function __addResponse ( requestId , data ) {
        if(__ENABLE_LOGGING) {
            // Cancel timeout
            // Get slug from __REQUESTS data
            var type = 'res';
            var slug = typeof __REQUESTS[requestId] != 'undefined' ? __REQUESTS[requestId].slug : null;
            var topic = __pubsubRoot + type + '.' + slug + '._' + requestId;
            if (typeof data == 'string') data = {message: data};
            if (!slug) {
                console.error('Unable to find the slug for this request');
            } else {
                PubSub.publish(topic, data);
            }
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
        //__activate();

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
//            var newTask = _getTaskDataById( taskId );

            __stateChange('project', {});
            __stateChange('settings', {panelOpen: true, slide : 'tasks'});
            __stateChange('task', { id : taskId });

            __addResponse( reqId , 'New task set' );
        } else {
            __addResponse( reqId , 'Task requested already active' );
        }

        if(typeof BindedBoxScreens != 'undefined') BindedBoxScreens.renderTaskList();

        //__auditChanges();
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
        //console.log(__entity, scData, __CURRENT);

        // Publish state change
        PubSub.publish(__pubsubRoot + 'state.' + scData.entity, __entity);

        __addResponse(reqId, 'The `' + scData.entity + '` state has been changed');
    }

    function __applyStateChange(scData){
        var newData = null;
        if(typeof scData.__ENTITY != 'undefined'){
            switch (scData.__ENTITY){
                case '__TASK':
                    // Check to see if id is set
                    var idSet = typeof scData.values['id'] != 'undefined';

                    // If id set, get task by id, and store in newData
                    if(idSet){
                        newData = _getTaskDataById(scData.values.id.newVal);
                    } else {
                        newData = __CURRENT[scData.__ENTITY];
                    }

                    // Only run if valid task set
                    if(newData.id){

                        // Merge current data with scData.values
                        for(var key in scData.values){
                            newData.data[key] = scData.values[key].newVal;
                        }
                        // update current with new data
                        __CURRENT[scData.__ENTITY] = newData;
                    }

                    break;
                case '__TASKS':
                    newData = __CURRENT[scData.__ENTITY];
                    //console.log(scData);
                    switch(scData.values.id.newVal){
                        case 'all': // Replace  __CURRENT.__TASKS with processed newData
                            // validate newData before overwrite
                            break;
                        default: // Handle single replacement
                            // confirm that id is set and valid
                            var t = _getTaskDataById(scData.values.id.newVal);
                            if(t.id) {
                                // create var updates = {};
                                var updates = {};
                                for(var key in scData.values){
                                    // add values to updates
                                    updates[key] = scData.values[key].newVal
                                }
                                if(Object.keys(updates).length > 0){
                                    // _setTaskDataById(id, updates);
                                    _setTaskDataById(t.id, updates);
                                    newData = __CURRENT[scData.__ENTITY];
                                }
                            }
                            break;
                    }
                    break;
                case '__PROJECT':
                case '__META':
                case '__SETTINGS':
                case '__USER':
                    newData = __CURRENT[scData.__ENTITY];

                    // Merge current data with scData.values
                    for(var key in scData.values){
                        newData[key] = scData.values[key].newVal;
                    }
                    // update current with new data
                    __CURRENT[scData.__ENTITY] = newData;
                    break;
            }
        }
        return newData;
    }

    function __parseAppTopic(topic){
        var errors = [],
            hasValidAction = false,
            hasValidEntity = false,
            validActions = ['state','req','res','log'],
            validEntities = ['task','tasks','meta','settings','user','project'],
            topicGroups = topic.split('.'),
            map = {
                mainApp : typeof topicGroups[0] != 'undefined' ? topicGroups[0] : null,
                appContext : typeof topicGroups[1] != 'undefined' ? topicGroups[1] : null,
                action  : typeof topicGroups[2] != 'undefined' ? topicGroups[2] : null,
                entity : typeof topicGroups[3] != 'undefined' ? topicGroups[3] : null,
                key : typeof topicGroups[4] != 'undefined' ? topicGroups[4] : null
            };

        if(map.action && validActions.indexOf(map.action) >= 0){
            hasValidAction = true;
        } else {
            errors.push('Invalid action `' + map.action + '` provided');
        }

        if(map.entity && validEntities.indexOf(map.entity) >= 0){
            hasValidEntity = true;
        } else {
            errors.push('Invalid entity `' + map.entity + '` provided');
        }

        return {
            isValid : (hasValidAction && hasValidEntity && map.mainApp != null && map.appContext != null),
            map : map,
            errors : errors.length > 0
        }
    }

    function _handleStateChange(topic, payload){
        var parsedTopic = BindedBox.parseAppTopic(topic);
        if(parsedTopic.isValid) {
            switch (parsedTopic.map.entity){
                case 'settings':
                        _renderSettingsData();
                        if(__CURRENT.__SETTINGS.panelOpen) __activateListeners(); else __deactivateListeners();
                    break;
                case 'project':
                    if(__CURRENT.__SETTINGS.panelOpen){
                        if(typeof __RENDERED.__PROJECT == 'undefined') _renderProjectData();
                    }
                    break;
                case 'meta':
                    if(__CURRENT.__SETTINGS.panelOpen) {
                    }
                    break;
                case 'task':

                case 'tasks':
                    if(__CURRENT.__SETTINGS.panelOpen) {

                    }
                    break;
            }
        }

    }

    /**
     * Initiates BindedBox popup box in the user's browser
     *
     * Starts BindedBox event listeners
     * @private
     */
    function __activateListeners() {
        if(!__listenersActive){
            __listenersActive = true;
            $( document ).on( 'click' , _handleBindBoxCloseClick );
            $( document ).on( 'click' , '.binded-trigger-box .item a' , _handleTriggerBoxNavClick );
            $( document ).on( 'click' , '.binded-trigger-box button.js-directional' , _handleDirectionalBtnClick );
            $( document ).on( 'click' , '.binded-trigger-box .action-btns .mark-complete' , _handleMarkCompleteClick );
            $( document ).on( 'keydown' , _handleBindedBoxKeydown );
            $( window ).on( 'load' , __handleBindedBoxResize );
        }
    }


    /**
     * Initiates BindedBox popup box in the user's browser
     *
     * Starts BindedBox event listeners
     * @private
     */
    function __deactivateListeners() {
        if(__listenersActive){
            __listenersActive = false;
            $( document ).off( 'click' , _handleBindBoxCloseClick );
            $( document ).off( 'click' , '.binded-trigger-box .item a' , _handleTriggerBoxNavClick );
            $( document ).off( 'click' , '.binded-trigger-box button.js-directional' , _handleDirectionalBtnClick );
            $( document ).off( 'click' , '.binded-trigger-box .action-btns .mark-complete' , _handleMarkCompleteClick );
            $( document ).off( 'keydown' , _handleBindedBoxKeydown );
            $( window ).off( 'load' , __handleBindedBoxResize );
        }
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
        parseAppTopic               : __parseAppTopic,
        task                        : __activeTask,
        stateChange                 : __stateChange,
        getScreenLogs               : __getScreenLogs,
        screenLog                   : __screenLog,
        getCurrent                  : __getCurrent,
        setCurrent                  : __setCurrent,
        addRequest                  : __addRequest,
        addResponse                 : __addResponse,
        setNewActiveTask            : __setNewActiveTask,
        allowed                     : _accessAllowed,
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
