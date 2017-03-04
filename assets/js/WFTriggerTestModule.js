var TriggerTestModule = (function () {

    var
        tabName = 'triggerTestTab',
        parsedHTML = null, // Most recent parsed html

        defaults = {
            title : 'Configure Trigger &amp; Test',
            titleIcon: 'fa-puzzle-piece',
            tabs : [
                {

                },
                {
                    type: 'dataInput',
                    panelTitle: null,
                    name: 'Configuration Options',
                    help : {},
                    saveCallback : function(){},
                    saved: false,
                    originalValues: {},
                    currentValues: {},
                    viewMode : 'json',
                    validated : false
                },
                {
                    type: 'dataInput',
                    panelTitle: null,
                    name: 'Task Completion Test',
                    help : {},
                    saveCallback : function(){},
                    saved: false,
                    originalValues: {},
                    currentValues: {},
                    viewMode : 'json',
                    validated : false
                },
                {
                    type: 'admin',
                    title: 'Admin Helper'
                }
            ]
        },
        currentState = {};

    function _drawTabs(){
        for(var i in currentState.tabs){
            switch(tabs[i].type){
                case 'dataInput':
                    break;
                case 'admin':
                    break;
            }

        }
    }

    function _registerEarlyListeners() {
        PubSub.subscribe('popoutChange.activeTab', _handleMainTabChange);
    }

    function _unRegisterEarlyListeners() {
        PubSub.unsubscribe('popoutChange.activeTab', _handleMainTabChange);
    }

    function _registerListeners() {
    }

    function _unRegisterListeners() {
    }

    function _handleMainTabChange(topic, mainTabOptions) {
        console.log(mainTabOptions, tabName);
        if (mainTabOptions.viewport == tabName) _activate();
    }

    function _cacheDom() {
    }

    function _activate(options) {
        if (options) _setOptions(options);
        // Register Listeners
        // Activate Modules
        // Render
        _registerListeners();
        _render();
        console.log(tabName + ' activated');
    }

    function _deactivate(options) {
        if (options) _setOptions(options);
        // UnRegister Listeners
        // DeActivate Modules
        // UnRender (unnecessary)
    }

    function _getHTML(options) {
        if (options) _setOptions(options);

        var
            source = TriggerTestContainer.dom('#' + tabName).$.html(),
            template = Handlebars.compile(source);

        parsedHTML = template(options);
        return parsedHTML;
    }

    function _render(options) {
        if (options) _setOptions(options);
        _getHTML(currentState);
        TriggerTestContainer.setTitle(currentState.title, currentState.titleIcon);
        TriggerTestContainer.dom("mainBody").$.html(parsedHTML);
    }

    function _setOptions(options) {
        $.extend(currentState, defaults, options);
    }

    _cacheDom();
    // Events to subscribe to as long as popout is open
    _registerEarlyListeners();

    return {
        tabName                     : tabName,
        registerEarlyListeners      : _registerEarlyListeners,
        unRegisterEarlyListeners    : _unRegisterEarlyListeners,
        registerListeners           : _registerListeners,
        unRegisterListeners         : _unRegisterListeners,
        activate                    : _activate,
        deactivate                  : _deactivate
    }
})();
TriggerTestContainer.registerModule(TriggerTestModule, 'triggerTestTab');
