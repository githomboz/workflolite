/**
 * @package         CS_MessageBox
 * @author          Benezer Jahdy Lancelot <jahdy@cosmicstrawberry.com>
 * @copyright       2016 Cosmic Strawberry, LLC
 * @version         1.0.0
 */

var CS_API = (function(){
    var defaultOptions = {
            baseUrl                 : (window.location.href.search(':8888') >= 0 ? '/source' : '') + '/api/v1/',
            method                  : 'GET',
            dataType                : 'jsonp',
            cache                   : true, // Whether to enable caching
            preferCache             : true // If caching enabled, check cache first before running
        },
        /**
         * Array of errors
         * @type {Array}
         */
        errors            = [],

        /**
         * Array of logs
         * @type {Array}
         */
        logs                      = [],

        /**
         * The API urls that have been called since page load
         * @type {Array}
         */
        history           = [],

        /**
         * API responses cached in key value pairs with API urls serving as keys
         * @type {{}}
         */
        cache             = {},

        /**
         * API Settings
         * @type {{}}
         */
        options           = {};

    _init();

    function _init(opts){
        $.extend(options, defaultOptions, opts);
    }

    /**
     * Connect to API
     * @param relUrl
     * @param beforeSend callable function
     * @param success callable function
     * @param error callable function
     * @private
     */
    function _connect(relUrl, beforeSend, success, error, POST, opts){
        $.extend(options, defaultOptions, opts);

        // Clean relUrl of leading slashes
        relUrl = relUrl.search('/') == 0 ? relUrl.substring(1) : relUrl;

        var apiUrl = options.baseUrl + relUrl,
            doAjaxRequest = true;

        if(options.preferCache){
            var response = options.method == 'GET' ? _getFromCache(apiUrl) : null;
            if(response) {
                if(_isCallable(success)) {
                    success(response, 'cacheSuccess');
                } else {
                    _logError('Invalid success callback');
                }
                doAjaxRequest = false;
            }
        }

        if(doAjaxRequest){
            $.ajax({
                url : apiUrl,
                //dataType: options.dataType,
                type: options.method,
                data: POST || {},

                beforeSend: function(jqXHR, settings){
                    _storeCallToHistory(apiUrl);
                    if(_isCallable(beforeSend)) beforeSend(jqXHR, settings);
                },

                success: function(data, textStatus, jqXHR){
                    _cacheRequest(apiUrl, data);
                    if(_isCallable(success)) {
                        success(data, textStatus, jqXHR);
                    } else {
                        _logError('Invalid success callback');
                    }
                },

                error: function(jqXHR, textStatus, errorThrown){
                    var eData = {
                        jqXHR : jqXHR,
                        textStatus : textStatus,
                        errorThrown : errorThrown
                    };
                    _logError('Error occurred while performing request. ' + JSON.stringify(eData), 'AJAX');
                    if(_isCallable(error)) error(jqXHR, textStatus, errorThrown);
                }
            });
        }
    }

    /**
     * The API url to retrieve from cache
     * @param apiUrl {string}
     */
    function _getFromCache(apiUrl){
        return typeof cache[apiUrl] != 'undefined' ? cache[apiUrl] : null;
    }

    function _getCache(){
        return cache;
    }

    function _getHistory(){
        return history;
    }

    /**
     * Store each call and optionally the data that was returned
     * @param requestUrl
     * @param response
     * @private
     */
    function _storeCallToHistory(requestUrl){
        history.push(requestUrl)
    }

    /**
     * Store each call and it's resulting data.
     * @param requestUrl
     * @param response
     * @private
     */
    function _cacheRequest(requestUrl, response){
        if(options.cache && response) cache[requestUrl] = {
            dateTime : new Date(),
            response : response
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
        context = context || 'error';
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
        errors          : _getErrors,
        call            : _connect,
        getFromCache    : _getFromCache,
        cache           : _getCache,
        history         : _getHistory
    }

})();
