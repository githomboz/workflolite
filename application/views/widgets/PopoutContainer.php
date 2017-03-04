<div class="trigger-test-popout boxed">
  <header>
    <h1><i class="fa fa-puzzle-piece"></i> Configure Trigger & Test</h1>
    <ul class="container-nav clearfix">
      <li>
        <a href="#" data-viewport="triggerTestTab"><i class="fa fa-puzzle-piece"></i> Trigger & Test</a>
      </li>
      <li>
        <a href="#" data-viewport="formManageTab"><i class="fa fa-wpforms"></i> Manage Forms</a>
      </li>
      <li>
        <a href="#" data-viewport="marketplaceTab"><i class="fa fa-shopping-cart"></i> Marketplace</a>
      </li>
    </ul>
  </header>
  <div class="main-inner-body">
  </div><!--/.main-inner-body-->
</div><!--/.triggers-popout-->

<?php
if(!isset($popout) || isset($popout) && !in_array($popout, ['main','forms','shop'])) $popout = 'main';
$fileName = APPPATH.'views/widgets/_triggers-popout-'.$popout.'.php';
?>

<script id="triggerTestTab" type="text/x-handlebars-template">
  <?php echo get_include($fileName); ?>
</script>

<script id="formManageTab" type="text/x-handlebars-template">
  Form Management
</script>

<script id="marketplaceTab" type="text/x-handlebars-template">
  Marketplace
</script>

<script type="text/javascript">
  var TriggerTestContainer = (function(){

    var $navContainer = null;
    var $mainActiveTab = null;
    var defaults = {
        /**
         * Options [triggerTestTab, formManageTab, marketplaceTab]
         */
        viewport                : 'triggerTestTab'
      },
      currentState              = {},
      dom                      = [];

    function _init(){
      _setOptions();
      _cacheDom();
      _setupListeners();
    }

    function _state(){
      return currentState;
    }

    function _getTabs(){
      return [
        {
          slug : 'triggerTestTab',
          obj : TriggerTestTab
        }
      ];
    }

    function _cacheDom(){
      _dom('.container-nav', "navContainer");
      _dom('.trigger-test-popout', "mainContainer");
      _dom('.main-inner-body', "mainBody");
    }

    /**
     * Cache and retrieve dom elements easily
     * @param selector string CSS selector for the element to retrieve or cache and retrieve
     * @param slug string Nickname to make calling a wordy CSS selector easy
     */
    function _dom(selector, slug){
      slug = slug || selector;

      var found = null;

      for(var i in dom){
        if(dom[i].selector == selector) found = dom[i];
        if(!found) if(dom[i].slug == slug) found = dom[i];
      }

      if(!found){
        // Create
        found = {
          slug      : slug,
          selector : selector,
          $ : $(selector)
        };
        found.length = found.$.length;
        dom.push(found);
      }

      return found;
    }

    function _setupListeners(){
      // Nav
      $(document).on('click', _dom("navContainer").selector + ' a', _handleNavClick);
    }

    function _handleNavClick(e){
      e.preventDefault();
      var
        $el = $(this),
        viewport = $el.data('viewport');
      _setViewport(viewport);
    }

    function _updateActiveNavItem(){
      _dom("navContainer").$.find('li').removeClass('active');
      $mainActiveTab = _dom("navContainer").$.find('a[data-viewport='+currentState.viewport+']');
      $mainActiveTab.parents('li').addClass('active');
    }

    function _messageBox(type, message, options){
      if(options) _setOptions(options);
      console.log(type, message, options);
    }

    function _setViewport(viewport){
      if(viewport) _setOptions({viewport: viewport});
      _render();
    }

    function _render(options){
      if(options) _setOptions(options);
      _updateActiveNavItem();
      PubSub.publish('popoutChange.activeTab', currentState);
    }

    function _setOptions(options){
      $.extend(currentState, defaults, options);
      return this;
    }

    function _destroy(){
      // .remove()
      // unbind listeners
    }

    function _renderTab(tab){
      switch (tab){
        case '':
          break;

      }
    }

    _init();
    _setViewport();

    return  {
      setOptions              : _setOptions,
      setViewport             : _setViewport,
      messageBox              : _messageBox,
      destroy                 : _destroy,
      dom                     : _dom,
      /**
       * Get current state of the master module
       */
      state                   : _state
    }
  })();



  var TriggerTestTab = (function(){

    var tabName = 'triggerTestTab';
    var parsedHTML = null; // Most recent parsed html

    var defaults = {

      },
      currentState              = {},
      dom                       = [];

    _cacheDom();

    function _registerListeners(){
      PubSub.subscribe('popoutChange.activeTab', _handleMainTabChange);
    }

    function _handleMainTabChange(topic, mainTabOptions){
      if(mainTabOptions.viewport == tabName) _activate();
    }

    function _cacheDom(){
      TriggerTestContainer.dom(".trigger-test-popout .main-inner-body", "mainContainer");
      var tabs = ['triggerTestTab','formManageTab','marketplaceTab'];
      for(var i in tabs){
        var _selector = '#' + tabs[i];
        TriggerTestContainer.dom(_selector);
      }
    }

    function _activate(options){
      if(options) _setOptions(options);
      // Register Listeners
      // Activate Modules
      // Render
      _registerListeners();
      _render();
      console.log(tabName + ' activated');
    }

    function _deactivate(options){
      if(options) _setOptions(options);
      // UnRegister Listeners
      // DeActivate Modules
      // UnRender (unnecessary)
    }

    function _getHTML(options){
      if(options) _setOptions(options);

      var source = null;
      switch(currentState.viewport){
        case 'formManageTab':
              break;
        case 'marketplaceTab':
              break;
        case 'triggerTestTab':
        default:
          source = TriggerTestContainer.dom("#triggerTestTab").$.html();
              break;
      }
      var
        template = Handlebars.compile(source);

      parsedHTML = template(options);
      return parsedHTML;
    }

    function _render(options){
      if(options) _setOptions(options);
      _getHTML(currentState);
      TriggerTestContainer.dom("mainBody").$.html(parsedHTML);
    }

    function _setOptions(options){
      $.extend(currentState, defaults, options);
    }

    if(TriggerTestContainer.state().viewport == 'triggerTestTab') {
      _activate();
    }

    return {
      activate : _activate,
      deactivate : null
    }
  })();


</script>