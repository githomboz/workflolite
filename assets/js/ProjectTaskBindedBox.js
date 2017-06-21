/**
 * Created by benezerlancelot on 5/17/17.
 */
var BindedBox = (function(){

    var
        elementSelector =  '.binded-trigger-box',
        $bindedBox = $(elementSelector),
        $bindedBoxInnerHead = $bindedBox.find('.inner-head'),
        dimensions  = {
            padding : 10,
            actionBtnHeight : 46,
            slideNavWidth : $(".tabbed-nav .item").outerWidth()
        },
        actionBtns = null;

    var options = {
        showTaskCount : true,
        showTimer : false,
        elapsedTime : null,
        settingsDropdown : [],
        keyboardDirectionalBtnsActive : true,
        issetH2 : false,
        issetH3 : false
    };

    function _init(){
        PubSub.subscribe('task.updated', _handleTaskUpdates);
        PubSub.subscribe('meta.updated', _handleMetaUpdates);
        PubSub.subscribe('project.updated', _handleProjectUpdates);

        $(document).on('click', '.col-title .task-name', _handleTaskBindedTrigger); // Project list title js click event
        $(window).load(function(){
            $(window).resize(function(){
                _triggerResize();
            });
        });
    }

    function _triggerResize(){
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
        return _BINDED_BOX.userAcc.acc >= level;
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


    function _handleBindBoxCloseClick(e){
        if (!$(e.target).closest(BindedBox.selector).length) {
            _triggerBoxClose();
        }
    }

    function _reloadBindedBox(reloadProject){
        if(_BINDED_BOX.activeTaskId){
            var task = BindedBox.getTaskById(_BINDED_BOX.activeTaskId);
            if(reloadProject) _renderProjectData(task);
            PubSub.publish('bindedBox.newTaskActivated', {
                activeTaskId : task.id
            });
        }
    }

    function _triggerBoxOpen(taskId){
        var task = BindedBox.getTaskById(taskId);
        if(task){
            _BINDED_BOX.activeTaskId = taskId;
            if(!_PROJECT.triggerBoxOpen){
                //console.log('trigger box opened');
                $(".binded-trigger-box-overlay").addClass('show');
                $(document).on('click', _handleBindBoxCloseClick);
                $(document).on('click', '.binded-trigger-box .item a', _handleTriggerBoxNavClick);
                $(document).on('click', '.binded-trigger-box button.js-directional', _handleDirectionalBtnClick);
                $(document).on('click', '.binded-trigger-box .action-btns .mark-complete', _handleMarkCompleteClick);
                $(document).on('keydown', _handleBindedBoxKeydown);
                PubSub.subscribe('bindedBox.resize', _handleBindedBoxViewportResize);
                PubSub.subscribe('bindedBox.activeLockCollision', _handleActiveLockCollision);
                _PROJECT.triggerBoxOpen = true;
                PubSub.publish('bindedBox.opened', null);
            }
            _reloadBindedBox(true);
            _activateTriggerBoxSlide('tasks'); // Default back to tasks slide
            _triggerResize();
        }
    }

    function _triggerBoxClose(){
        //console.log(_BINDED_BOX);
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
            $(document).off('click', _handleBindBoxCloseClick);
            $(document).off('click', '.binded-trigger-box .item a', _handleTriggerBoxNavClick);
            $(document).off('click', '.binded-trigger-box button.js-directional', _handleDirectionalBtnClick);
            $(document).off('click', '.binded-trigger-box .action-btns .mark-complete', _handleMarkCompleteClick);
            $(document).off('keydown', _handleBindedBoxKeydown);
            PubSub.unsubscribe('bindedBox.resize', _handleBindedBoxViewportResize);
            PubSub.unsubscribe('bindedBox.activeLockCollision', _handleActiveLockCollision);
            _PROJECT.triggerBoxOpen = false;
            _BINDED_BOX.activeTaskId = null;
            PubSub.publish('bindedBox.closed', null);

        }
    }

    function _handleBindedBoxKeydown(e){
        switch(e.which){
            case 37: // left
                //case 38: // up
                if(BindedBox.actionBtns.prev && BindedBox.getOption('keyboardDirectionalBtnsActive')) _triggerBoxOpen(BindedBox.actionBtns.prev.id);
                break;
            case 39: // right
                //case 40: // down
                if(BindedBox.actionBtns.next && BindedBox.getOption('keyboardDirectionalBtnsActive')) _triggerBoxOpen(BindedBox.actionBtns.next.id);
                break;
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
        // console.log(topic, payload)
    });

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
    
    
    _init();

    return {
        actionBtns : actionBtns,
        allowed : _accessAllowed,
        selector : elementSelector,
        $el : $bindedBox,
        options : options,
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
