var TriggerTestContainer = (function () {

    var $navContainer = null;
    var $mainActiveTab = null;
    var defaults = {
            /**
             * Options [triggerTestTab, formManageTab, marketplaceTab]
             */
            viewport: 'triggerTestTab',
            title : null,
            titleIcon : null
        },
        currentState = {},
        dom = [],
        modules = [];

    function _init() {
        _setOptions();
        _cacheDom();
        _setupListeners();
    }

    function _setTitle(title, icon){
        _dom("mainTitle").$.html((icon ? '<i class="fa ' + icon + '"></i>' : '') + ' ' + title);
    }

    function _registerModule(module, tabName) {
        modules.push({tab: tabName, module: module});
        var found = 0;
        for (var i in modules) {
            if (_getTabs().indexOf(modules[i].tab) >= 0) found++;
        }
        if (found >= _getTabs().length) _loadModule();
    }

    /**
     * Only run after all required modules have registered themselves;
     * @private
     */
    function _loadModule() {
        console.log('Loading Modules', currentState, modules);
        for(var i in modules){
            if(modules[i].tab == currentState.viewport) modules[i].module.activate();
        }
    }

    function _state() {
        console.log(currentState);
        return currentState;
    }

    function _getTabs() {
        return ['triggerTestTab', 'formManageTab', 'marketplaceTab'];
    }

    function _cacheDom() {
        var tabs = _getTabs;
        for (var i in tabs) {
            var _selector = '#' + tabs[i];
            _dom(_selector);
        }
        _dom('.container-nav', "navContainer");
        _dom('.trigger-test-popout', "mainContainer");
        _dom('.main-inner-body', "mainBody");
        _dom('.trigger-test-popout > header > h1', "mainTitle");
    }

    /**
     * Cache and retrieve dom elements easily
     * @param selector string CSS selector for the element to retrieve or cache and retrieve
     * @param slug string Nickname to make calling a wordy CSS selector easy
     */
    function _dom(selector, slug) {
        slug = slug || selector;

        var found = null;

        for (var i in dom) {
            if (dom[i].selector == selector) found = dom[i];
            if (!found) if (dom[i].slug == slug) found = dom[i];
        }

        if (!found) {
            // Create
            found = {
                slug: slug,
                selector: selector,
                $: $(selector)
            };
            found.length = found.$.length;
            dom.push(found);
        }

        return found;
    }

    function _setupListeners() {
        // Nav
        $(document).on('click', _dom("navContainer").selector + ' a', _handleNavClick);
    }

    function _handleNavClick(e) {
        e.preventDefault();
        var
            $el = $(this),
            viewport = $el.data('viewport');
        _setViewport(viewport);
    }

    function _updateActiveNavItem() {
        _dom("navContainer").$.find('li').removeClass('active');
        $mainActiveTab = _dom("navContainer").$.find('a[data-viewport=' + currentState.viewport + ']');
        $mainActiveTab.parents('li').addClass('active');
    }

    function _messageBox(type, message, options) {
        if (options) _setOptions(options);
        console.log(type, message, options);
    }

    function _setViewport(viewport) {
        if (viewport) _setOptions({viewport: viewport});
        _render();
    }

    function _render(options) {
        if (options) _setOptions(options);
        PubSub.publish('popoutChange.activeTab', currentState);
        _updateActiveNavItem();
    }

    function _setOptions(options) {
        $.extend(currentState, defaults, options);
        return this;
    }

    function _destroy() {
        // .remove()
        // unbind early listeners
        // unbind all listeners
    }

    _init();
    _setViewport();

    return {
        setOptions: _setOptions,
        setViewport: _setViewport,
        messageBox: _messageBox,
        destroy: _destroy,
        dom: _dom,
        /**
         * Get current state of the master module
         */
        state: _state,
        registerModule: _registerModule,
        setTitle: _setTitle
    }
})();