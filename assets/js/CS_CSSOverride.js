/**
 * Dynamically add embedded <style> styles to the page
 * @package         CS_CSSOverride
 * @author          Benezer Jahdy Lancelot <jahdy@cosmicstrawberry.com>
 * @copyright       2016 Cosmic Strawberry, LLC
 * @version         1.0.0
 */

var CS_CSSOverride = (function($){
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
        _styles                     = {};

    _init();

    function _init(opts){
        $.extend(options, defaultOptions, opts);

        PubSub.subscribe('stylesChanged', _applyStyles);
    }

    function _addStyle(selector, cssProperty, value){
        if(typeof _styles[selector] == 'undefined') _styles[selector] = {};
        _styles[selector][cssProperty] = value;
        PubSub.publish('stylesChanged.addStyle', _styles);
    }

    function _removeStyle(selector, cssProperty){
        if(typeof _styles[selector] != 'undefined') {
            if(typeof _styles[selector][cssProperty] != 'undefined') {
                delete _styles[selector][cssProperty];
            }
        }
        PubSub.publish('stylesChanged.removeStyle', _styles);
    }

    function _getStyle(selector, cssProperty) {
        if(typeof _styles[selector] == 'undefined') return null;
        if(cssProperty){
            if(typeof _styles[selector][cssProperty] == 'undefined') return null;
            return _styles[selector][cssProperty];
        } else {
            return _styles[selector];
        }
    }

    function _applyStyles(){
        var css = '';
        for(var selector in _styles){
            css += selector + '{';
            for(var cssProperty in _styles[selector]){
                css += cssProperty + ':' + _styles[selector][cssProperty] + '; !important'
            }
            css += '}';
        }
        $(".js-styles-override").html(css);
        return css;
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
        'apply'         : _applyStyles,
        addStyle        : _addStyle,
        removeStyle     : _removeStyle
    }

})(jQuery);
