var TriggerTestModule = (function () {

    var
        activeTab = false,
        tabName = 'triggerTestTab',
        parsedHTML = null, // Most recent parsed html

        defaults = {
            title : 'Configure Trigger &amp; Test',
            titleIcon: 'fa-puzzle-piece',
        },
        currentState = {};

    function _registerEarlyListeners() {
        PubSub.subscribe('popoutChange.activeTab', _handleMainTabChange);
    }

    function _unRegisterEarlyListeners() {
        PubSub.unsubscribe('popoutChange.activeTab', _handleMainTabChange);
    }

    function _registerListeners() {
        $(document).on('click', TriggerTestContainer.dom('tabsNavItem').selector, _handleTabClick);
        $(document).on('click', '.toggle-payload', _handlePayloadClick);
        $(document).on('click', '.help-btn a', _handleToggleHelp);
    }

    function _unRegisterListeners() {
        $(document).off('click', TriggerTestContainer.dom('tabsNavItem').selector, _handleTabClick);
        $(document).off('click', '.toggle-payload', _handlePayloadClick);
        $(document).off('click', '.help-btn a', _handleToggleHelp);
    }

    function _handleToggleHelp(e){
        e.preventDefault();
        var $el = $(this),
            $helpContent = $el.parents('.tab-content').find('.help'),
            $helpVisible = $helpContent.is(':visible');
        if($helpVisible){
            $helpContent.hide();
        } else {
            $helpContent.show();
        }
    }

    function _handlePayloadClick(e){
        e.preventDefault();
        var $panel2 = $(".panel-2"),
            $payloadViewport = $panel2.find(".viewport.payload"),
            $tabsViewport = $panel2.find(".viewport.tabs");

        if(!$payloadViewport.is('.active')){
            $panel2.find(".viewport").removeClass('active');
            $payloadViewport.addClass('active');
        } else {
            $panel2.find(".viewport").removeClass('active');
            $tabsViewport.addClass('active');
        }
    }


    function _handleTabClick(e){
        e.preventDefault(e);
        var $el = $(this), activeTab = $el.parents('li').attr('data-tab');
        _setActiveTab(activeTab);
    }

    function _setActiveTab(tab){
        currentState.activeTab = tab;
        var $tabsContainer = $(".tabs-container");
        $tabsContainer.find(".tab-content").removeClass('active');
        var activeTab = $tabsContainer.find(".tab-content[data-tab=" + tab + "]");
        activeTab.addClass('active');
    }

    function _loadPanel2Data(_data){
        var
            defaults = {
                triggerActive : true,
                type : '{{triggerType}}',
                name : '{{triggerName}}',
                payload : null,
                payloadActive : false,
                activeTab : 'config',
                config : {
                    help : null,
                    helpActive : false,
                    data : {},
                    current : {},
                    isSaved : false,
                    // Data is valid
                    isValidated : false,
                    // Data has b
                    isVerified : false,
                    // Data is complete
                    isComplete : true
                },
                test : {
                    help: null,
                    helpActive : false,
                    data : {}
                }

            },
            data = {},
            $panel2 = $(".panel-2");
        $.extend(data, defaults, _data);

        if(data.triggerActive){
            _setActiveTab(data.activeTab);
            // Set trigger name
            $panel2.find("h2 > .name").html(data.name);
            // Set trigger type
            $panel2.find("h2 > .type").html(data.type);
            // Set payload
            if(data.payload) {
                $panel2.find(".viewport.payload .code").html(data.payload);
            }

            var $togglePayloadBTN = $(".toggle-payload");
            if(data.payload){
                console.log(data.payload);
                $togglePayloadBTN.show();
                if(data.payloadActive){
                    $panel2.find(".viewport.payload .code").html(data.payload);
                    $panel2.find(".viewport").removeClass('active');
                    $panel2.find(".viewport.payload").addClass('active');
                } else {
                    $panel2.find(".viewport").removeClass('active');
                    $panel2.find(".viewport.tabs").addClass('active');
                }
            } else {
                $togglePayloadBTN.hide();
            }

            // Set help
            var tabs = ['config','test'], tabContent = {};

            for(var i in tabs){
                var thisTab = tabs[i];
                tabContent[thisTab] = {
                    $tab : $(".tab-content[data-tab=" + thisTab + "]")
                };

                tabContent[thisTab]['$helpBtnWrap'] = tabContent[thisTab].$tab.find('.help-btn');
                tabContent[thisTab]['$helpBtn'] = tabContent[thisTab].$helpBtnWrap.find('a');
                tabContent[thisTab]['$helpContent'] = tabContent[thisTab].$tab.find('.help');

                if(data[thisTab].helpActive){
                    tabContent[thisTab]['$helpContent'].show();
                } else {
                    tabContent[thisTab]['$helpContent'].hide();
                }

                if(data[thisTab].help){
                    var helpHTML = data[thisTab].help;
                    tabContent[thisTab]['$helpContent'].html(helpHTML);
                    tabContent[thisTab]['$helpBtnWrap'].show();
                } else {
                    tabContent[thisTab]['$helpBtnWrap'].hide();
                }
            }

        } else {
            var $activateViewport = $panel2.find(".viewport.activate");
            $panel2.find(".viewport").removeClass('active');
            $activateViewport.addClass('active');
        }

        // Set config
        // Set completion test
        // Set is valid
        // Set view mode
    }

    function _handleMainTabChange(topic, mainTabOptions) {
        console.log(mainTabOptions, tabName);
        if (mainTabOptions.viewport == tabName) {
            if(!activeTab){
                _activate();
            }
        } else {
            if(activeTab) _deactivate();
        }
        // Works if you comment out activeTab in _active and _deactivate
        // if (mainTabOptions.viewport == tabName) {
        //     if(!activeTab){
        //         _activate();
        //     }
        // } else {
        //     if(activeTab) _deactivate();
        // }
    }

    function _cacheDom() {
        TriggerTestContainer.dom('.viewport.tabs', 'tabsViewport');
        TriggerTestContainer.dom('.viewport.tabs .nav-item a', 'tabsNavItem');
    }

    function _activate(options) {
        if (options) _setOptions(options);
        activeTab = true;
        // Register Listeners
        // Activate Modules
        // Render
        _registerListeners();
        _render();
        console.log(tabName + ' activated');
    }

    function _deactivate(options) {
        if (options) _setOptions(options);
        activeTab = false;
        // UnRegister Listeners
        _unRegisterListeners();
        // DeActivate Modules
        // UnRender (unnecessary)
        console.log(tabName + ' de-activated');
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
        deactivate                  : _deactivate,
        loadPanel2Data             : _loadPanel2Data
    }
})();
TriggerTestContainer.registerModule(TriggerTestModule, 'triggerTestTab');
