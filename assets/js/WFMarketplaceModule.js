var MarketplaceModule = (function(){

    var
        activeTab                   = false,
        tabName                     = 'marketplaceTab',
        parsedHTML                  = null, // Most recent parsed html

        defaults                    = {
            title : 'Marketplace',
            titleIcon: 'fa-shopping-cart'
        },
        currentState                = {};

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

    function _handleMainTabChange(topic, mainTabOptions){
        console.log(mainTabOptions, tabName);
        if (mainTabOptions.viewport == tabName) {
            if(!activeTab){
                _activate();
            }
        } else {
            if(activeTab) _deactivate();
        }
    }

    function _cacheDom(){
    }

    function _activate(options){
        if(options) _setOptions(options);
        activeTab = true;
        // Register Listeners
        // Activate Modules
        // Render
        _registerListeners();
        _render();
        console.log(tabName + ' activated');
    }

    function _deactivate(options){
        if(options) _setOptions(options);
        activeTab = false;
        // UnRegister Listeners
        _unRegisterEarlyListeners();
        // DeActivate Modules
        // UnRender (unnecessary)
        console.log(tabName + ' de-activated');
    }

    function _getHTML(options){
        if(options) _setOptions(options);

        var
            source = TriggerTestContainer.dom('#' + tabName).$.html(),
            template = Handlebars.compile(source);

        parsedHTML = template(options);
        return parsedHTML;
    }

    function _render(options){
        if(options) _setOptions(options);
        _getHTML(currentState);
        TriggerTestContainer.setTitle(currentState.title, currentState.titleIcon);
        TriggerTestContainer.dom("mainBody").$.html(parsedHTML);
    }

    function _setOptions(options){
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
TriggerTestContainer.registerModule(MarketplaceModule, 'marketplaceTab');
