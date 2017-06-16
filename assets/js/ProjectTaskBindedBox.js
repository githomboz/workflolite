/**
 * Created by benezerlancelot on 5/17/17.
 */
var BindedBox = (function(){

    var elementSelector =  '.binded-trigger-box';
    var $element = $(elementSelector);
    var options = {
        showTaskCount : true,
        showTimer : false,
        elapsedTime : null,
        settingsDropdown : [],
        keyboardDirectionalBtnsActive : true
    };

    function _init(){

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
    
    _init();

    return {
        allowed : _accessAllowed,
        selector : elementSelector,
        $el : $element,
        options : options,
        getOption : _getOption,
        setOption : _setOption,
    }
})();
