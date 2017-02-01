/**
 * Dynamically add embedded <style> styles to the page
 * @package         CS_WFLogger
 * @author          Benezer Jahdy Lancelot <jahdy@cosmicstrawberry.com>
 * @copyright       2017 Cosmic Strawberry, LLC
 * @version         1.0.0
 */

var CS_WFLogger = (function($){
    var defaultOptions = {
        },

        /**
         * Errors
         * @type {Array}
         */
        errors                        = [],

        /**
         * Settings
         * @type {{}}
         */
        options                     = {
        },

        /**
         * CSS styles to be applied to page
         * @type {{}}
         * @private
         */
        _logEntries                     = {},
        _logsHTML                       = '',
        _polling                        = false,
        _currentQuery                   = null,
        _queryHistory                   = [];

    _init();

    function _init(opts){
        $.extend(options, defaultOptions, opts);
    }

    function _logMessageExists(msgData){
        if(typeof msgData == 'undefined') return false;
        if(typeof msgData._id == 'undefined') return false;
        return typeof _logEntries[msgData._id] !== 'undefined';
    }

    function _logMessageRendered(msgData){
        if(_logMessageExists(msgData)){
            return _logEntries[msgData._id]['rendered'];
        }
        return false;
    }

    function _processQuery(){
        // Parse query
            // Perform ajax request
            CS_API.call(
                '/ajax/wf_logger',
                function(){
                    console.log('Preparing to request new logs for query: ' + JSON.stringify(_currentQuery));
                },
                function(data){
                    if(!data.errors){
                        console.log('Publishing new topic and payload');
                        // Publish new logs topic and payload
                        PubSub.publish('WFLogger.new-logs', data.response);
                        _queryHistory.push({
                            query: _currentQuery,
                            response: data.response
                        });
                        if(_polling) _processQuery();
                    } else {
                        console.error('API: ' + data.errors[0]);
                    }
                },
                function(){
                    console.error('An error has occurred while trying to grab new log data');
                },
                _currentQuery,
                {
                    method: 'GET',
                    preferCache : false
                }
            );
    }

    function _setQuery(query){
        _currentQuery = query;
        console.log(_currentQuery);
        _processQuery();
        return this;
    }

    function _validLogMessageInput(msgData){
        var fields = ['hash','dateAdded','type','message','data','context'];
        for(var field in fields){
            if(typeof msgData[field] != 'undefined') return true;
        }
        return true;
    }

    function _addLogMessage(msgData){
        if(_validLogMessageInput(msgData) && typeof _logEntries[msgData._id] == 'undefined'){
            if(typeof msgData['rendered'] != 'undefined') msgData['rendered'] = false;
            _logEntries[msgData._id] = msgData;
            return true;
        }
        return false;
    }

    function _setPolling(val){
        _polling = val;
    }

    function _render(){
        for(var logId in _logEntries){
            if(!_logMessageRendered(_logEntries[logId])){
                _renderLogMessage(_logEntries[logId]);
            }
        }
    }

    function _renderLogMessage(msgData){
        // Set rendered to true
        msgData['rendered'] = true;
        // Update _logEntries
        _logEntries[msgData['_id']] = msgData;
        // Update _logHTML
        _logsHTML += _generateLogMessageHTML(msgData);
        $(".wflogger-entries").html(_logsHTML);
        return true;
    }

    function _generateLogMessageHTML(msgData){
        var output = '<div class="entry entry-' + msgData._id + '">';
        output += '<span class="dateAdded">' + msgData.dateAdded + '</span>';
        output += '<span class="type">' + msgData.type + '</span>';
        output += '<span class="message">' + msgData.message + '</span>';
        output += '<span class="data">' + JSON.stringify(msgData.data) + '</span>';
        output += '<span class="context">' + JSON.stringify(msgData.context) + '</span>';
        output += '</div>';
        return output;
    }

    // Publish function
    PubSub.subscribe('WFLogger.new-logs', _handleNewLogsIncoming);

    // Subscribe function
    function _handleNewLogsIncoming(topic, payload){
        console.log(topic, payload);
        if(typeof payload != 'undefined' && typeof payload.entries != 'undefined'){
            console.log('test');
            var added;
            for(var i in payload.entries){
                added = _addLogMessage(payload.entries[i]);
                if(!added){
                    console.log('Entry not added to page', [payload.entries[i]._id, payload.entries[i].message])
                }
            }
            _render();
        } else {
            console.log('Invalid payload provided', payload);
        }
    }



    return {
        init            : _init,
        render          : _render,
        addLogMessage   : _addLogMessage,
        sendQuery       : _processQuery,
        setPolling      : _setPolling,
        setQuery        : _setQuery
    }

})(jQuery);
