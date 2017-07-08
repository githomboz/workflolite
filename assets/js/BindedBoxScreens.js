/**
 * Created by benezerlancelot on 5/17/17.
 */
var BindedBoxScreens = (function(){

    var _isActive = false;
    var _options = {
        screenCount : 0,
        screensActivated : false,
        activeScreen : 1,
        screenChangesMade : false,
        screenNavChangesMade : false,
        $taskInset : $(".task-inset")
    };

    /**
     * Whether or not the screen logs that were fired before the screen log module loaded have been brought into the
     * array _screenLog.
     * @type {boolean}
     * @private
     */
    var _preLoadScreenLogsMerged = false;
    var _screenLog = [];
    var _screens = [
        {
            slug : 'screens',
            title : 'All Screens',
            content : null,
            isLoaded : false, // Whether content has been loaded to dom
            isLoading : false, // If the content is in request mode
            contentCallback : null, // Function to call to get content
            scrollX : false,
            scrollY : true,
        },
        {
            slug : 'task_list',
            title : 'Task List',
            content : null,
            isLoaded : false, // Whether content has been loaded to dom
            isLoading : false, // If the content is in request mode
            contentCallback : _generateInsetTaskList, // Function to call to get content
            scrollX : false,
            scrollY : true,
        },
        {
            slug : 'logs',
            title : 'Logs',
            content : null,
            isLoaded : false, // Whether content has been loaded to dom
            isLoading : false, // If the content is in request mode
            contentCallback : _generateLogs, // Function to call to get content
            scrollX : true,
            scrollY : true,
        },
        {
            slug : '_admin_task_dump',
            title : 'Task Dump (Admin)',
            content : null,
            isLoaded : false, // Whether content has been loaded to dom
            isLoading : false, // If the content is in request mode
            contentCallback : _renderTaskDump, // Function to call to get content
            scrollX : true,
            scrollY : true,
        },
        {
            slug : '_admin_meta_dump',
            title : 'Meta Dump (Admin)',
            content : null,
            isLoaded : false, // Whether content has been loaded to dom
            isLoading : false, // If the content is in request mode
            contentCallback : null, // Function to call to get content
            scrollX : true,
            scrollY : true,
        }
    ];

    function _initialize(){
        var reqId = BindedBox.addRequest('initializingBBScreens', 'Initializing the BindedBox screens');
        for(var i in _screens) _screens[i].index = parseInt(i);
        PubSub.subscribe(BindedBox.pubsubRoot + 'state', _handleStateChange);
        BindedBox.addResponse(reqId, 'BindedBox screens initialized');
    }

    function _renderTaskDump(){
        var html = '';
        for(var i in BindedBox.TASKS) {
            if(BindedBox.getCurrent('task','id') == BindedBox.TASKS[i].id){
                html += JSON.stringify(BindedBox.TASKS[i], undefined, 2);
                _options.$taskInset.find('pre.task-data').html(html);
            }
        }
        //return html;
    }

    function _generateLogs(){
        var html = '',
            logs = BindedBox.getScreenLogs(),
            isRequest,
            isResponse,
            isInfo,
            reqId;

        //console.log(logs);
        html += '<ul class="screen-logs">';
        for ( var i in logs ){
            isRequest = logs[i].context.topic.indexOf('.req.') >= 0;
            isResponse = logs[i].context.topic.indexOf('.res.') >= 0;
            reqId = logs[i].context.topic.split('.')[4];
            isInfo = !isRequest && !isResponse;
            html += '<li class="clearfix">';
            html += '<span class="date">' + logs[i].date.toISOString() + '</span> ';
            if(!isInfo) html += '(' + reqId.replace('_','') + ') ';
            html += '<i class="fa fa-' + (isInfo ? 'info' : (isRequest ? 'arrow-right' : 'arrow-left')) + '"></i> ';
            html += '<span class="message">' + logs[i].message + '</span>';
            html += '</li>';
        }
        html += '</ul>';
        return html;
    }

    function _renderLogs(){
        $( ".inset-tab[data-tab_id=2]" ).html( _generateLogs() );
    }

    function _generateInsetTaskList(){
        var reqId = BindedBox.addRequest('generateInsetTaskList', 'Generate tasks slide inset task list html');
        var html = '<ol class="inset-tasklist">';
        for(var i in BindedBox.TASKS){
            var isComplete = BindedBox.TASKS[i].data.status == 'completed';
            //console.log(BindedBox.TASKS[i].id);
            var activeTask = BindedBox.getCurrent('task', 'id') == BindedBox.TASKS[i].id;
            html += '<li data-status="' + BindedBox.TASKS[i].data.status + '" ';
            html += 'data-task_id="' + BindedBox.TASKS[i].id + '" ';
            html += 'class="' + (activeTask ? 'active':'') + '"';
            html += '>';
            if(isComplete){
                html += '<i class="fa fa-check-square"></i> &nbsp; ';
            } else {
                html += '<i class="fa fa-square"></i> &nbsp; ';
            }
            html += '<span class="task-sort-order">' + BindedBox.TASKS[i].data.sortOrder + '.</span> ';
            if(_hasDependencies(BindedBox.TASKS[i]) === true){
                html += '<i class="fa fa-' + (_isLocked(BindedBox.TASKS[i]) === true ? 'lock' : 'unlock') + '"></i> ';
            }
            html += '<a href="#" class="task-name">';
            if(isComplete) html += '<strike>';
            html += BindedBox.TASKS[i].data.taskName;
            if(isComplete) html += '</strike>';
            html += '</a> ';
            if(activeTask) html += ' <i class="fa fa-caret-left"></i>';
            html += '</li>';
        }
        BindedBox.addResponse(reqId, {
            message : 'Tasks slide inset task list html generated',
            //html : html
        });
        return html;
    }

    function _renderInsetTaskList(){
        $( ".inset-tab[data-tab_id=1]" ).html( _generateInsetTaskList() );
    }

    function _hasDependencies(task){
        if(typeof task.data != 'undefined'){
            var hasDependencies = task.data.dependencies && task.data.dependencies.length >= 1;
            return hasDependencies;
        }
        console.error('Invalid task provided');
    }

    function _isLocked(task){
        if(typeof task.data != 'undefined'){
            var hasDependencies = task.data.dependencies && task.data.dependencies.length >= 1;
            var locked = hasDependencies && !task.data.dependenciesOKTimeStamp;
            return locked;
        }
        console.error('Invalid task provided');
    }

    function _activateScreen(index){
        if(index != +_options.activeScreen) {
            _options.activeScreen = index;
            _options.screenChangesMade = true;
        }
        _render();
    }

    function _setScreenData(index, data){
        data.isLoaded = false;
        for(var field in data){
            _options.screenChangesMade = true;
            if(field == 'title') _options.screenNavChangesMade = true;
            _screens[index][field] = data[field];
        }
        //console.log(data);
    }

    function _setScreenDataBySlug(slug, data){
        data.isLoaded = false;
        for(var field in data){
            _options.screenChangesMade = true;
            if(field == 'title') _options.screenNavChangesMade = true;
            var screen = _getScreenBySlug(slug);
            _screens[screen.index][field] = data[field];
        }
        console.log(data);
    }

    function _getScreen(index){
        for(var i in _screens){
            if(i == index) return _screens[i];
        }
    }

    function _getScreenBySlug(slug){
        for(var i in _screens){
            if(_screens[i].slug == slug) return _screens[i];
        }
    }

    function _applyScreenContent(index, content){
        data = {
            content : content
        };
        _setScreenData(index, data);
    }

    function _loadContent(index, flush){
        flush = flush || false;
        // activate loading overlay
        _setLoadingScreen(index);
        // attempt to get returned content
        var content = null;
        var screen = _getScreen(index);
        if(screen.content && !flush){
            content = screen.content;
        } else {
            if(screen.contentCallback){
                content = screen.contentCallback();
            }
            if(!content && screen.content) content = screen.content;
        }
        if(content){
            _applyScreenContent(index, content);
            _unsetLoadingScreen(index);
            $(".inset-tab[data-tab_id=" + index + "]").attr('has_content', 1)
        }
        return content;

    }

    function _renderNav(){
        var html = '<ul>';

        for(var i in _screens){
            if(i > 0){
                html += '<li data-slug="' + _screens[i].slug + '"';

                html += '>';
                html += '<a class="inset-tab-link';
                if(_options.activeScreen == i) html += ' active';
                html += '" ';
                html += 'data-tab_id="' + i + '" ';
                html += 'href="#">';
                html += _screens[i].title;
                html += '</a>';
                html += '</li>';
            }
        }

        html += '</ul>';
        var $screensScreen = $(".inset-tab[data-tab_id=0]");
        $screensScreen.html(html);
        _options.screenNavChangesMade = false;
    }

    function _setLoadingScreen(index){
        // Apply the loading ui to the given screen
    }

    function _unsetLoadingScreen(index){
        // Remove the loading ui from the given screen
    }

    function _activate(){
        if(!_isActive){
            $(document).on('click', '.inset-tab-link', _handleInsetBtnClick);
            $(document).on('click', '.inset-tasklist .task-name', _handleInsetTaskBtnClick);
            //PubSub.subscribe(BindedBox.pubsubRoot + 'state.task', _handleRequestForReRender);
            // PubSub.subscribe('taskData.updates.updatedTask', _handleTaskDataChanges);
            // PubSub.subscribe('task.updated', _handleTaskDataChanges);
            _render();
            _isActive = true;
        }
    }

    function _deactivate(){
        if(_isActive){
            $(document).off('click', '.inset-tab-link', _handleInsetBtnClick);
            $(document).off('click', '.inset-tasklist .task-name', _handleInsetTaskBtnClick);
            //PubSub.unsubscribe(BindedBox.pubsubRoot + 'state.task', _handleRequestForReRender);
            // PubSub.unsubscribe('taskData.updates.updatedTask', _handleTaskDataChanges);
            // PubSub.unsubscribe('task.updated', _handleTaskDataChanges);
            _isActive = false;
        }
    }

    // function _handleTaskDataChanges(topic, payload){
    //     //console.log(payload);
    //     var redrawStatuses = ['completed','new','active','skipped','force_skipped'];
    //     // Check if tabbed-content.tasks is the active screen
    //     if(BindedBox.getCurrent('settings','slide') == 'tasks'){
    //         // Check if taskNames changed
    //         var taskNameChanged = typeof payload.updates.taskName != 'undefined';
    //         // Check if status changed
    //         var statusChanged = typeof payload.updates.status != 'undefined';
    //         var newStatusRequiresRender = statusChanged && (redrawStatuses.indexOf(payload.updates.status) >= 0);
    //         var dependenciesChanged = typeof payload.updates.dependenciesOKTimeStamp != 'undefined';
    //         //console.log(taskNameChanged, newStatusRequiresRender, dependenciesChanged);
    //         // If necessary, redraw task list
    //         if(taskNameChanged || newStatusRequiresRender || dependenciesChanged){
    //             _handleRequestForReRender();
    //         }
    //     }
    //
    // }

    function _handleRequestForReRender(topic, payload){
        var taskListScreen = 1;
        var content = _loadContent(taskListScreen, true);
        if(content) _options.$taskInset.find('.inset-tab[data-tab_id=' + taskListScreen + ']').html(content);
    }

    function _handleInsetTaskBtnClick(e){
        e.preventDefault();
        var $this = $(this),
            $li = $this.parents('li'),
            taskId = $li.data('task_id');

        var isActiveTask = BindedBox.getCurrent('task','id') == taskId;
        var boxOpen = BindedBox.getCurrent('settings','panelOpen') === true;

        if(!boxOpen || !isActiveTask) BindedBox.setNewActiveTask(taskId);
        //console.log('this is happening');
        return false;
    }

    function _handleInsetBtnClick(e){
        e.preventDefault();
        var $this = $(this),
            tabId = parseInt($this.attr('data-tab_id'));
        _activateScreen(tabId);
    }

    function _updateScreenCount(topic, screenCount){
        screenCount = screenCount || _screens.length;
        if(screenCount != _options.screenCount){
            _options.screenCount = screenCount;
            _options.$taskInset.find('.screen-count').html((screenCount - 1));
        }
    }

    function _render(){
        var reqId = BindedBox.addRequest('renderingBBScreens', 'Preparing to render BindedBox task slide screens');
        if(_options.screenNavChangesMade || !_options.screensActivated) _renderNav();

        // Set screen count
        _updateScreenCount();

        if(_options.screenChangesMade || !_options.screensActivated){

            var $screen = _options.$taskInset.find('.inset-tab[data-tab_id=' + _options.activeScreen + ']');

            // Get Rendered content
            _loadContent(_options.activeScreen);

            var screen = _getScreen(_options.activeScreen);


            // Activate Screen name
            if(_options.activeScreen > 0){
                _options.$taskInset.find('.tab-name').html(screen.title);
            } else {
                _options.$taskInset.find('.tab-name').html('');
            }
            if(screen.isLoading){
                _setLoadingScreen(_options.activeScreen);
            }

            if(screen.content && !screen.loaded){
                // Load screen
                $screen.html(screen.content);

                // Mark screen loaded
                _screens[_options.activeScreen].isLoaded = true;

                if(_options.activeScreen === 1) _calculateActiveScrollPositions();
            }

            // Activate active tab
            _options.$taskInset.find('.inset-tab').removeClass('active');
            $screen.addClass('active');

            if(screen.scrollX){
                $screen.css('overflow-x', 'scroll');
            }

            if(screen.scrollY){
                $screen.css('overflow-y', 'scroll');
            }

            _options.screenChangesMade = false;

            _options.screensActivated = true;
        }

        BindedBox.addResponse(reqId, 'BindedBox tasks slide screens rendered');

    }


    function _handleStateChange(topic, payload){
        var parsedTopic = BindedBox.parseAppTopic(topic);
        if(parsedTopic.isValid) {
            switch (parsedTopic.map.entity){
                case 'task':
                    if(_isActive){
                        _renderTaskDump();
                        _renderInsetTaskList();
                    }
                    break;
                case 'tasks':
                    if(_isActive) {
                        _renderInsetTaskList();
                    }
                    break;
            }
        }

    }

    function _calculateActiveScrollPositions(){
        // console.log('this is a task list screen', actionBtns);
        // var $screen = $('.inset-tab[data-tab_id=' + _options.activeScreen + ']');
        // var $taskLI = $('.inset-tasklist li[data-task_id=' + actionBtns.curr.id + ']');
        // console.log('taskLi', $taskLI.offset( ), $taskLI.position(), $taskLI.scrollTop());
        // console.log('screen', $screen.offset(), $screen.position(), $screen.scrollTop());
    }

    _initialize();

    PubSub.subscribe('bindedBox.tabs.tasks.openTriggered', _activate);
    PubSub.subscribe('bindedBox.tabs.tasks.closeTriggered', _deactivate);

    return {
        renderTaskList              : _handleRequestForReRender,//_renderInsetTaskList,
        renderLogs                  : _renderLogs,
        taskIsLocked                : _isLocked,
        render                      : _render,
        activate                    : _activate,
        deactivate                  : _deactivate
    }

})();