/**
 * @package         CS_JobsViewer
 * @author          Benezer Jahdy Lancelot <jahdy@cosmicstrawberry.com>
 * @copyright       2016 Cosmic Strawberry, LLC
 * @version         1.0.0
 */

var CS_JobsViewer = (function($){
    var defaultOptions = {
            baseUrl                 : (window.location.href.search(':8888') >= 0 ? '/source' : '') + '/api/v1/',
            method                  : 'GET',
            dataType                : 'jsonp',
            cache                   : true, // Whether to enable caching
            preferCache             : true, // If caching enabled, check cache first before running
            workflowClass           : '.cs-workflow',
            jobClass                : '.cs-job',
            taskClass               : '.cs-task'
        },

        /**
         * Logs
         * @type {Array}
         */
        logs                        = [],

        /**
         * Settings
         * @type {{}}
         */
        options                     = {
        },

        _dimensions                  = {},

        /**
         * Dom cache
         * @type {{}}
         * @private
         */
        _cache                      = {},

        /**
         * CSS styles to be applied to page
         * @type {{}}
         * @private
         */
        _styles                     = {};

    _init();

    _listeners();

    PubSub.subscribe('viewport.change', _calculateSizes);

    CS_CSSOverride.addStyle('.jobs-list .job-entry .cs-job-tasks .cs-task:hover', 'cursor', 'pointer');
    CS_CSSOverride.addStyle('.jobs-list .job-entry .cs-job-tasks .cs-task.selected:hover', 'cursor', 'auto');
    CS_CSSOverride.addStyle('.jobs-list .job-entry.view-glance .cs-job-tasks .cs-task:hover', 'cursor', 'auto');

    function _init(opts){
        $.extend(options, defaultOptions, opts);
        _calculateSizes();
    }

    function activateSelectedTask(e){
        var $el = $(this),
            $job = $el.parents('.cs-job'),
            $workflow = $job.parents('.cs-workflow'),
            jobId = $job.data('job');
        e.preventDefault();

        $(".job-" + jobId).removeClass('task-selected');
        $(".job-" + jobId + " .cs-task.selected").removeClass('selected');
        $el.addClass('selected');
        $job.addClass('task-selected');

    }

    function _listeners(){

        $( window ).resize(function() {
            _calculateSizes();
        });

        $(document).on('click', options.taskClass, activateSelectedTask);

        $(document).on('click', '.cs-task .status .content .fa-times', function(e){
            var $el = $(this),
                jobId = $el.data('job'),
                taskId = $el.data('task'),
                $job = $(".job-" + jobId);

            $job.find(".cs-task.selected").removeClass('selected');
            $job.removeClass('task-selected');
            //console.log('test is here', jobId, taskId);
            return false;
        });

        $(document).on('click', '.js-change-views i', function(e){
           var $el = $(this), viewStyle = $el.data('view'), $jobs = $(".cs-job");
            $jobs.removeClass('view-glance view-normal view-full');
            $jobs.addClass('view-' + viewStyle);
            switch (viewStyle){
                case 'glance':
                    _activateGlanceFeatures();
                    break;
                default:
                    _deactivateGlanceFeatures();
                    break;
            }
        });

    }

    function _activateGlanceFeatures(){
        $('.cs-task').removeClass('selected');
        $('.cs-job.job-entry').removeClass('task-selected');
        $(document).off('click', options.taskClass, activateSelectedTask);
        for(id in _dimensions.workflows){
            CS_CSSOverride.addStyle('.cs-workflow.workflow-' + id + ' .job-entry.cs-job.view-glance .cs-job-tasks .job-inner', 'height', _dimensions.workflows[id].tasksAllClosedWidth + 'px');
        }
        _calculateSizes();
    }

    function _deactivateGlanceFeatures(){
        $(document).on('click', options.taskClass, activateSelectedTask);
        $(".cs-task.next-step").addClass('selected');
        $(".cs-job").addClass('task-selected');
        for(id in _dimensions.workflows){
            CS_CSSOverride.removeStyle('.cs-workflow.workflow-' + id + ' .job-entry.cs-job.view-glance .cs-job-tasks .job-inner', 'height');
        }
    }

    /**
     * Calculate the widths and heights of jobs and tasks based on current page dimensions
     * @private
     */
    function _calculateSizes(){
        _dimensions.viewportWidth = $(".cs-job-tasks").width();
        _dimensions.defaultActiveTaskWidth = 240;
        _dimensions.workflows = {};

        // Break out calculations by workflow id
        $('.cs-workflow').each(function(i){
            var id = $(this).data('workflow'),
                displayDetails = $(this).data('display_details');
            _dimensions.workflows[id] = displayDetails;
            _dimensions.workflows[id].tasksAllClosedWidth = _dimensions.viewportWidth / _dimensions.workflows[id].taskCount;
            _dimensions.workflows[id].activeTaskWidth = _dimensions.defaultActiveTaskWidth;
            _dimensions.workflows[id].tasksOneOpenWidth = (_dimensions.viewportWidth - _dimensions.workflows[id].activeTaskWidth) / (_dimensions.workflows[id].taskCount - 1);
            CS_CSSOverride.addStyle('.cs-workflow.workflow-' + id + ' .cs-task', 'width', _dimensions.workflows[id].tasksAllClosedWidth + 'px');
            CS_CSSOverride.addStyle('.cs-workflow.workflow-' + id + ' .cs-job.task-selected .cs-task', 'width', _dimensions.workflows[id].tasksOneOpenWidth + 'px');
            CS_CSSOverride.addStyle('.cs-workflow.workflow-' + id + ' .cs-job.task-selected .cs-task.selected', 'width', _dimensions.workflows[id].activeTaskWidth + 'px');
            CS_CSSOverride.addStyle('.cs-workflow.workflow-' + id + ' .job-entry.cs-job.view-glance .cs-job-tasks .job-inner', 'height', _dimensions.workflows[id].tasksAllClosedWidth + 'px');
        });
    }

    function _workflowById(id, fromCache){
        if(id){
            var className = '.cs-workflow.workflow-' + id;
            fromCache = fromCache || true;
            if(fromCache){
                if(typeof _cache[className] != 'undefined') return _cache[className];
            }

            var $el = $(className);
            _cache[className] = $el;
            return $el;
        }
    }

    function _jobById(id, fromCache){
        if(id){
            var className = '.cs-job.job-' + id;
            fromCache = fromCache || true;
            if(fromCache){
                if(typeof _cache[className] != 'undefined') return _cache[className];
            }

            var $el = $(className);
            _cache[className] = $el;
            return $el;
        }
    }

    function _taskById(id, fromCache){
        if(id){
            var className = '.cs-task.task-' + id;
            fromCache = fromCache || true;
            if(fromCache){
                if(typeof _cache[className] != 'undefined') return _cache[className];
            }

            var $el = $(className);
            _cache[className] = $el;
            return $el;
        }
    }

    function _log(variable, context){
        // Set context if not set
        context = (typeof context == 'string' ? context : null) || 'general';
        // Check if context exists, if not, create
        if(typeof logs[context] == 'undefined') logs.context = [];
        // Add error
        logs.context.push(variable);
        // Log to screen
        if(typeof variable == 'string'){
            console.log(context.toUpperCase() + ': ' + variable);
        } else {
            console.log(context.toUpperCase() + ': ', variable);
        }
    }

    function _logError(error, context){
        context = context || 'general';
        _log(error, context);
    }

    function _isCallable(funcName){
        return typeof funcName == 'function';
    }

    function _getErrors(){
        return errors;
    }

    return {
        init            : _init,
        errors          : _getErrors
    }

})(jQuery);
