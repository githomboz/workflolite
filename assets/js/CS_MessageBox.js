/**
 * @package         CS_MessageBox
 * @author          Benezer Jahdy Lancelot <jahdy@cosmicstrawberry.com>
 * @copyright       2016 Cosmic Strawberry, LLC
 * @version         1.0.0
 */

var CS_MessageBox = (function(){
    var defaultOptions  = {
            selector                : '.c-messagebox',
            parentSelector          : '.c-mb__container',
            defaultType             : 'info',
            cssClassPrefix          : 'c-alert--',
            closeBtnClass           : '.c-alert__close'
        },

        /**
         * Array of logs
         * @type {Array}
         */
        logs                      = [],

        /**
         * Available types for messages
         */
        types = {
            danger : ['danger','failure','error'],
            success : ['success'],
            warning : ['warning','caution'],
            info : ['info']
        },

        /**
         * Whether messageBox is currently visible
         */
        isVisible                 = false,

        /**
         * The class to be added to message box to denote type
         */
        cssClass                  = '',

        /**
         * API Settings
         * @type {{}}
         */
        options                   = {},
        $messageBox               = null,
        $messageBoxContainer      = null,
        typeSelected              = null;

    _init();

    function _init(selector, opts){
        $.extend(options, defaultOptions, opts);
        if(selector) options.selector = selector;
        $messageBoxContainer = $(options.parentSelector);
        if(!_mbExists()){
            $messageBoxContainer.html(_draw());
        }
        $messageBox = $(options.selector);
        _closeBtnListener();
        _hide();
    }

    function _closeBtnListener(){
        $(document).on('click', options.closeBtnClass, function(e){
            e.preventDefault();
            _hide();
        });
    }

    function _setType(type){
        for(var typeGroup in types){
            if(types[typeGroup].indexOf(type) >= 0){
                typeSelected = typeGroup;
                cssClass = options.cssClassPrefix + typeGroup;
            }
        }
    }

    function _show(message, type, opts){
        _setType(type);
        $messageBox.removeClass('c-alert--danger c-alert--success c-alert--warning c-alert--info');
        $messageBox.addClass(cssClass).find('.c-alert__text').html(message);
        if(typeof opts == 'undefined' ||
            (typeof opts != 'undefined' && typeof opts.closeBtn == 'undefined') ||
            (typeof opts != 'undefined' && typeof opts.closeBtn != 'undefined' && !opts.closeBtn)) {
            $messageBox.find('.c-alert__close').show();
        }
        isVisible = true;
        $messageBox.fadeIn();
    }

    function _hide(){
        $messageBox.fadeOut();
        isVisible = false;
    }

    function _draw(){
        var output = '<div class="c-messagebox c-alert">';
        output+= '<p class="c-alert__text"></p>';
        output+= '<i class="fa fa-times-circle c-alert__close"></i>';
        output+= '</div><!-- /c-alert -->';
        return output;
    }

    function _mbExists(){
        return $(options.selector).length >= 1;
    }

    function _clear(){
        $messageBox.find('.c-alert__text').html('');
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
        add             : _show,
        remove          : _hide,
        clear           : _clear
    }

})();