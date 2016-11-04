/**
 * Allows for any div with the 'contenteditable' attribute set to true to be used as an input field or text area
 * @package         CS_EditableContentDivs
 * @author          Benezer Jahdy Lancelot <jahdy@cosmicstrawberry.com>
 * @copyright       2016 Cosmic Strawberry, LLC
 * @version         1.0.0
 */

var CS_EditableContentDivs = (function($){
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
            editableSelector        : 'div[contenteditable=true]',
            placeholderField        : 'placeholder',
            focusout                : function(){},
            focusin                 : function(){}
        },

        cache                       = [];

    _init();

    function _init(opts){
        $.extend(options, defaultOptions, opts);
        _registerEditableDivs();
        _setupListeners();
    }

    function _registerEditableDivs(){
        $(options.editableSelector).each(function(i){
            var info = _getInfo($(this));
            _setPlaceholder(info.$this);
        });
    }

    function _setupListeners(){
        $(document).on('click', options.editableSelector, function(e){
            var info = _getInfo($(this));
            _clearPlaceholder(info.$this);
        });

        $(document).on('focusout', options.editableSelector, function(){
            var info = _getInfo($(this));
            _setPlaceholder(info.$this);
            if(_isCallable(options.focusout)) options.focusout(info);
        });

        $(document).on('focusin', options.editableSelector, function(){
            var info = _getInfo($(this));
            if(_isCallable(options.focusin)) options.focusin(info);
        });

    }

    function _setPlaceholder($this){
        var info = _getInfo($this);

        if(info.currentContent.trim() == '' || info.currentContent == info.placeholder){
            info.$this.html(info.placeholder);
        }

    }

    function _clearPlaceholder($this){
        var info = _getInfo($this);

        if(info.currentContent.trim() == '' || info.currentContent == info.placeholder){
            info.$this.html('');
        }

    }

    function _getInfo($this){
        if($this) return {
            $this : $this,
            currentContent : $this.html().trim(),
            placeholder : $this.data(options.placeholderField).trim()
        }
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
