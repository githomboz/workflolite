/**
 * Created by benezerlancelot on 5/17/17.
 */
var BindedBoxScreens = (function(){

    var _options = {
        screensActivated : false,
        activeScreen : 1,
        screenChangesMade : false,
        screenNavChangesMade : false,
        $taskInset : $(".task-inset")
    };
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
            contentCallback : _renderInsetTaskList, // Function to call to get content
            scrollX : false,
            scrollY : true,
        },
        {
            slug : 'logs',
            title : 'Logs',
            content : null,
            isLoaded : false, // Whether content has been loaded to dom
            isLoading : false, // If the content is in request mode
            contentCallback : null, // Function to call to get content
            scrollX : true,
            scrollY : true,
        },
        {
            slug : '_admin_task_dump',
            title : 'Task Dump (Admin)',
            content : null,
            isLoaded : false, // Whether content has been loaded to dom
            isLoading : false, // If the content is in request mode
            contentCallback : null, // Function to call to get content
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
        for(var i in _screens) _screens[i].index = parseInt(i);
    }

    function _renderInsetTaskList(){
        var html = '<ol class="inset-tasklist">';
        for(var i in _TASK_JSON){
            var isComplete = _TASK_JSON[i].data.status == 'completed';
            //console.log(_TASK_JSON[i].id);
            var activeTask = _BINDED_BOX.activeTaskId == _TASK_JSON[i].id;
            html += '<li data-status="' + _TASK_JSON[i].data.status + '" ';
            html += 'data-task_id="' + _TASK_JSON[i].id + '" ';
            html += 'class="' + (activeTask ? 'active':'') + '"';
            html += '>';
            if(isComplete){
                html += '<i class="fa fa-check-square"></i> &nbsp; ';
            } else {
                html += '<i class="fa fa-square"></i> &nbsp; ';
            }
            html += '<span class="task-sort-order">' + _TASK_JSON[i].data.sortOrder + '.</span> ';
            if(_hasDependencies(_TASK_JSON[i]) === true){
                html += '<i class="fa fa-' + (_isLocked(_TASK_JSON[i]) === true ? 'lock' : 'unlock') + '"></i> ';
            }
            html += '<a href="#" class="task-name">';
            if(isComplete) html += '<strike>';
            html += _TASK_JSON[i].data.taskName;
            if(isComplete) html += '</strike>';
            html += '</a> ';
            if(activeTask) html += ' <i class="fa fa-caret-left"></i>';
            html += '</li>';
        }
        return html;
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

    function _renderInsetTabs(){
        //console.log(_screens);
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
        $screensScreen.attr('has_content', 1);
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
        _initialize();
        $(document).on('click', '.inset-tab-link', _handleInsetBtnClick);
        $(document).on('click', '.inset-tasklist .task-name', _handleInsetTaskBtnClick);
        PubSub.subscribe('bindedBox.newTaskActivated', _handleRequestForReRender);
        PubSub.subscribe('taskData.updates.updatedTask', _handleTaskDataChanges);
        PubSub.subscribe('task.updated', _handleTaskDataChanges);
        _render();
    }

    function _deactivate(){
        $(document).off('click', '.inset-tab-link', _handleInsetBtnClick);
        $(document).off('click', '.inset-tasklist .task-name', _handleInsetTaskBtnClick);
        PubSub.unsubscribe('bindedBox.newTaskActivated', _handleRequestForReRender);
        PubSub.unsubscribe('taskData.updates.updatedTask', _handleTaskDataChanges);
        PubSub.unsubscribe('task.updated', _handleTaskDataChanges);
    }

    function _handleTaskDataChanges(topic, payload){
        console.log(payload);
        var redrawStatuses = ['completed','new','active','skipped','force_skipped'];
        // Check if tabbed-content.tasks is the active screen
        if(_BINDED_BOX.activeTabId == 'tasks'){
            console.log(redrawStatuses.indexOf(payload.updates.status));
            // Check if taskNames changed
            var taskNameChanged = typeof payload.updates.taskName != 'undefined';
            // Check if status changed
            var statusChanged = typeof payload.updates.status != 'undefined';
            var newStatusRequiresRender = statusChanged && (redrawStatuses.indexOf(payload.updates.status) >= 0);
            var dependenciesChanged = typeof payload.updates.dependenciesOKTimeStamp != 'undefined';
            // If necessary, redraw task list
            if(taskNameChanged || newStatusRequiresRender || dependenciesChanged){
                _handleRequestForReRender();
            }
        }

    }

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

        var isActiveTask = _BINDED_BOX.activeTaskId == taskId;
        var boxOpen = _PROJECT.triggerBoxOpen === true;

        if(!boxOpen || !isActiveTask) _triggerBoxOpen(taskId);
    }

    function _handleInsetBtnClick(e){
        e.preventDefault();
        var $this = $(this);
        _activateScreen(parseInt($this.attr('data-tab_id')));
    }

    function _updateScreenCount(topic, screenCount){
        screenCount = screenCount || _screens.length;
        _options.$taskInset.find('.screen-count').html((screenCount - 1));
    }

    function _render(){
        //console.log(_options);
        if(_options.screenNavChangesMade || !_options.screensActivated) _renderNav();

        _renderInsetTabs();

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
        }

        _options.screensActivated = true;
    }

    PubSub.subscribe('bindedBox.opened', _activate);
    PubSub.subscribe('bindedBox.closed', _deactivate);

    return {
        taskIsLocked : _isLocked, 
        render: _handleRequestForReRender
    }

})();