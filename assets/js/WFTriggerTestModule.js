var TriggerTestModule = (function () {

    var
        $panel1 = null,
        $panel2 = null,
        resultList = null,
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
        $(document).on('click', '.results-list .option', _handleResultsListClick);
        $(document).on('click', '.register-trigger-btn', _handleRegisterBtnClick);
        $(document).on('click', '.btn.btn-activate', _handleActivateTriggerBtnClick);
    }

    function _unRegisterListeners() {
        $(document).off('click', TriggerTestContainer.dom('tabsNavItem').selector, _handleTabClick);
        $(document).off('click', '.toggle-payload', _handlePayloadClick);
        $(document).off('click', '.help-btn a', _handleToggleHelp);
        $(document).off('click', '.results-list .option', _handleResultsListClick);
        $(document).off('click', '.register-trigger-btn', _handleRegisterBtnClick);
        $(document).off('click', '.btn.btn-activate', _handleActivateTriggerBtnClick);
    }

    function _handleActivateTriggerBtnClick(e){
        e.preventDefault(); 
        var triggerId = $(".viewport.tabs").attr('data-trigger_id');
        console.log(triggerId);
        // ajax request to add trigger to task
        // 
    }

    function _handleRegisterBtnClick(e){
        e.preventDefault();
        alertify.alert('Invalid Action', 'This action is temporarily unavailable');
    }

    function _handleResultsListClick(e){
        e.preventDefault();
        var $el = $(this),
            id = $el.data('id'),
            data = null;
        $('.results-list .option').removeClass('active');
        $el.addClass('active');
        if(!resultList) resultList = JSON.parse($("#listEntries").html());
        for(var i in resultList){
            if(resultList[i].id == id) data = resultList[i];
        }
        _loadTrigger(data);
        var panel2Data = {
            triggerActive: false,
            blankPanel: false,
            name : data.name,
            triggerId : data.id
        };
        console.log(panel2Data);
        _loadPanel2Data(panel2Data);
    }

    function _loadTrigger(data){
        var html = _drawPanel1InfoBox({
            classes : 'trigger-description',
            template : '#infoBox-triggerStyle',
            data : data
        });
        $('.panel-1 .info-box').html(html);

        data.triggerId = data.id;
        $(".viewport.tabs").attr('data-trigger_id', data.triggerId);
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
                blankPanel : true,
                triggerId : null,
                triggerActive : true,
                type : '{{triggerType}}',
                name : '{{triggerName}}',
                payload : null,
                payloadActive : false,
                activeTab : 'config',
                config : {
                    help : null,
                    helpActive : false,
                    saveEndpoint : null, // api url to post changes to
                    data : {}, // input data
                    current : {}, // updated input data
                    isSaved : false, // data == current
                    isValidated : false, // Data is valid
                    isComplete : false // Data form is complete
                },
                test : {
                    help: null,
                    helpActive : false,
                    saveEndpoint : null, // api url to post changes to
                    data : {}, // input data
                    current : {}, // updated input data
                    isSaved : true, // data == current
                    isValidated : true, // Data is valid
                    isComplete : true // Data form is complete
                }

            },
            data = {},
            $panel2 = $(".panel-2");
        $.extend(data, defaults, _data);

        var $togglePayloadBTN = $(".toggle-payload");
        var $activateViewport = $panel2.find(".viewport.activate");
        var $tabsViewport = $panel2.find(".viewport.tabs");
        if(data.blankPanel){
            $panel2.hide();
        } else {
            $panel2.show();
            $panel2.find(".viewport").removeClass('active');
            if(data.triggerActive){
                if(!data.triggerId || data.triggerId == null) {
                    console.error('Trigger Id not set. Can not be activated');
                } else {
                    console.log(data.triggerId);
                    $panel2.find(".viewport.tabs").attr('data-trigger_id', data.triggerId);
                    $panel2.find(".viewport").removeClass('active');
                    $tabsViewport.addClass('active');
                    //_setActiveTab(data.activeTab);
                    // Set trigger name
                    $panel2.find("h2 > .name").html(data.name);
                    // Set trigger type
                    $panel2.find("h2 > .type").html(data.type);
                    // Set payload
                    if(data.payload) {
                        $panel2.find(".viewport.payload .code").html(data.payload);
                    }

                    if(data.payload){
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
                        tabContent[thisTab]['$saveBtn'] = tabContent[thisTab].$tab.find('.save-btn');
                        tabContent[thisTab]['$validated'] = tabContent[thisTab].$tab.find('.validated');
                        tabContent[thisTab]['$navItem'] = $(".nav-item[data-tab=" + tabs[i] +"]");

                        if(data[thisTab].helpActive) {
                            tabContent[thisTab]['$helpContent'].show();
                        } else {
                            tabContent[thisTab]['$helpContent'].hide();
                        }

                        if(data[thisTab].help) {
                            var helpHTML = data[thisTab].help;
                            tabContent[thisTab]['$helpContent'].html(helpHTML);
                            tabContent[thisTab]['$helpBtnWrap'].show();
                        } else {
                            tabContent[thisTab]['$helpBtnWrap'].hide();
                        }

                        // Set Nav
                        if(data[thisTab].isComplete) {
                            tabContent[thisTab]['$navItem'].addClass('isComplete');
                        }

                        // Set Saved
                        var
                            addClass = data[thisTab].isSaved ? 'saved' : '',
                            saveHTML = '<i class="fa fa-save"></i> ' + (data[thisTab].isSaved ? 'Saved' : 'Save');
                        tabContent[thisTab]['$saveBtn'].addClass(addClass).html(saveHTML);

                        // Set validated
                        addClass = data[thisTab].isValidated ? 'saved' : '';
                        saveHTML = '<i class="fa fa-check"></i> ' + (data[thisTab].isValidated ? 'Validated' : 'Validate');
                        tabContent[thisTab]['$validated'].addClass(addClass).html(saveHTML);

                    }
                }

            } else {
                $panel2.find("h2 > .name").html(data.name);
                $togglePayloadBTN.hide();
                $panel2.find(".viewport").removeClass('active');
                $activateViewport.addClass('active');
            }

        }

        console.log('_loadPanel2Data finised running', data);

        // Set completion test
        // Set is valid
        // Set view mode
    }

    function _loadPanel1Data(_data){
        var defaults = {
                triggers : [
                    {
                        id : 'nslkdfn',
                        type : 'lambda',
                        callback : 'Bernies::RequestPickUp',
                        name : 'Bernie\'s Deliveries / Request Pickup',
                        description : 'Bernie\'s RESTful API trigger',
                        category : 'Mail & Deliveries',
                        tags : ['mail','shipping','deliveries'],
                        author : 'Bernies DevShop',
                        authorUrl : 'http://www.bernies-deliveries.com/support',
                        averageExecution : 1500,
                        registered : true, // whether this trigger is available to this organization / user
                        active : false,
                        registeredUsers : 154138,
                        developerRating : {
                            support : 9.5,
                            development : 8.3
                        },
                        config: {
                            help: ''
                        },
                        test: {
                        },
                        payload : {}
                    },
                    {
                        id : 'fgd4dfs',
                        type : 'lambda',
                        callback : 'USPS::RequestPickUp',
                        name : 'United States Postal Service / Request Pickup',
                        description : '<p>Make an API request to the United States Postal Service to pick up a package on a specific day.</p> <p>Contains a robust completion test.</p>',
                        category : 'Mail & Deliveries',
                        tags : ['deliveries','mail  ','shipping','USPS'],
                        author : 'Team Workflo',
                        averageExecution : 6000,
                        registered : true, // whether this trigger is available to this organization / user
                        active : false,
                        registeredUsers : 154138,
                        developerRating : {
                            support : 9.5,
                            development : 8.3
                        },
                        config: {
                            help: ''
                        },
                        test: {
                        },
                        payload : {}
                    }
                ],
                infoBox : {
                    classes : 'trigger-description',
                    template : '#infoBox-triggerStyle',
                    data : {},
                    content : null
                }
            },
            data = {},
            $panel1 = $(".panel-1");
        $.extend(data, defaults, _data);

        //var output = _drawPanel1List();
        //output += _drawPanel1InfoBox(data.infoBox);
        $panel1.find('.results-list').html(_drawPanel1List(data.triggers));
        $panel1.find('.info-box').html(_drawPanel1InfoBox(data.infoBox));
        //return output;
    }

    function _drawPanel1List(_data){
        var defaults = {

            },
            data = {},
            $panel1 = $(".panel-1");
        _data = _data || JSON.parse($("#listEntries").html());
        $.extend(data, defaults, _data);
        var output = '<ul>',
            category = null;
        for(var i in data){
            if(data[i].category != category){
                category = data[i].category;
                output += '<li class="category">' + category + '</li>';
            }
            output += '<li class="option" data-id="' + data[i].id + '">' + data[i].name + '</li>';
        }
        output += '</ul>';
        return output;
    }

    function _drawPanel1InfoBox(_data){
        var defaults = {
            },
            data = {},
            $panel1 = $(".panel-1");
        $.extend(data, defaults, _data);

        var source = $(data.template).html(),
            template = Handlebars.compile(source), html;

        Handlebars.registerHelper('tryLink', function(options){
            var text = typeof options.hash.text != 'undefined' ? options.hash.text : '';
            var url = typeof options.hash.url != 'undefined' ? options.hash.url : '';
            text = Handlebars.Utils.escapeExpression(text);
            url = Handlebars.Utils.escapeExpression(url);

            var theLink;
            if(url.trim() != ''){
                theLink = '<a href="' + url + '" target="_blank">' + text + '</a>';
            } else {
                theLink = '<span>' + text + '</span>';
            }

            return new Handlebars.SafeString(theLink);
        });

        Handlebars.registerHelper('avgExec', function(options){
            var milliseconds = Handlebars.Utils.escapeExpression(options.hash.milliseconds),
                output = '',
                seconds = 0;

            seconds = (Math.round(milliseconds) / 1000).toFixed(1);
            console.log(seconds);
            output += seconds + ' seconds';
            return new Handlebars.SafeString(output);
        });

        Handlebars.registerHelper('registerBtn', function(options){
            var active = Handlebars.Utils.escapeExpression(options.hash.active),
                output = '';

            if(active) output += '<a href="#" class="register-trigger-btn ban"><i class="fa fa-ban"></i> Un-register Lambda</a>';
            else output += '<a href="#" class="register-trigger-btn"><i class="fa fa-ban"></i> Register Lambda</a>';
            return new Handlebars.SafeString(output);
        });

        if(typeof data.data.test != 'undefined') data.data.hasTest = 'true'; else data.data.hasTest = 'n/a';

        html = template(data.data);

        //console.log('source: ', source, 'html: ',  html, 'data: ', data);
        return html;
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
    }

    function _cacheDom() {
        TriggerTestContainer.dom('.viewport.tabs', 'tabsViewport');
        TriggerTestContainer.dom('.viewport.tabs .nav-item a', 'tabsNavItem');
        $panel1 = $(".panel-1");
        $panel2 = $(".panel-2");
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
        _loadPanel1Data({blankPanel: true});
        _loadPanel2Data({blankPanel: true});
    }

    function _setOptions(options) {
        $.extend(currentState, defaults, options);
    }

    function _tmp(){
        Handlebars.registerHelper('tryLink', function(options){
            var text = typeof options.hash.text != 'undefined' ? text : '';
            text = Handlebars.Utils.escapeExpression(text);
            var url = typeof options.hash.url != 'undefined' ? url : '';
            url = Handlebars.Utils.escapeExpression(url);

            var theLink;
            if(url.trim() != ''){
                theLink = '<a href="' + url + '">' + text + '</a>';
            } else {
                theLink = '<span>' + text + '</span>';
            }

            return Handlebars.SafeString(theLink);
        });
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
        loadPanel1Data             : _loadPanel1Data,
        loadPanel2Data             : _loadPanel2Data
    }
})();
TriggerTestContainer.registerModule(TriggerTestModule, 'triggerTestTab');
