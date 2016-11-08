/**
 *
 * @package         CS_RenderBuddy
 * @author          Benezer Jahdy Lancelot <jahdy@cosmicstrawberry.com>
 * @copyright       2016 Cosmic Strawberry, LLC
 * @version         1.0.0
 */
var
    CS_RenderBuddyInstances = (function(){

        var CS_RenderBuddy = function(opts){
            var defaultOptions = {},

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
                    renderItemCallback              : null,
                    onKeypressCallback              : null,
                    addItemCallback                 : null,
                    pressEnterToAdd                 : true,
                    overallWidgetContainer          : '',
                    instanceDataFieldSelector       : '',
                    itemClass                       : '',
                    itemsContainerSelector          : '',
                    itemSelector                    : '',
                    inputSelector                   : '',
                    closeSelector                   : '',
                    delimiter                       : ';',
                    itemType                        : 'item',
                    itemTypePlural                  : 'items',
                    uniqueFields                    : ['value']
                },

                _html                       = '',

                items                       = [{value:'Item 1'},{value:'Item 2'}],

                _identity                   = null,

                debugMode                   = true;

            _init(opts);

            _clearItems();

            function _init(opts){
                $.extend(options, defaultOptions, opts);
                _defaultIdentity();
                _setupListeners();
                $(options.itemsContainerSelector).attr('data-items', '[]');
                // console.log($(options.itemsContainerSelector).closest(options.instanceDataFieldSelector));
                // $(options.itemsContainerSelector).parents(options.overallWidgetContainer).find(options.instanceDataFieldSelector).val(_identity);
                // console.log('Instance ID:', _identity, options.instanceDataFieldSelector, $(options.itemsContainerSelector).parents(options.overallWidgetContainer).find(options.instanceDataFieldSelector).length, $(options.instanceDataFieldSelector).val(), CS_RenderBuddyInstances.allInstances());
                return this;
            }

            function _defaultIdentity() {
                if(!_identity) _identity = (md5(options.itemsContainerSelector));
                return this;
            }

            function _getId(){
                return _identity;
            }

            function _isId(id){
                return id == _identity;
            }

            function _listenerKeypressInput(e){
                var val = _getInputValue();
                if(val){
                    //console.log(e.which, String.fromCharCode(e.which));
                    // @todo: Add delimiter functionality || String.fromCharCode(e.which) == options.delimiter
                    if((options.pressEnterToAdd && e.which == 13)) {
                        if(_isCallable(options.onKeypressCallback)) {
                            options.onKeypressCallback(e, {value: val});
                        } else {
                            _addItem({value: val});
                            // Clear input field
                        }
                    }
                }
            }

            function _listenerClickClose(e){
                var $btn = $(this),
                    $item = $btn.parents('.' + options.itemClass),
                    value = $item.text();

                _removeItem(value.trim());
                return false;
            }

            function _setupListeners(){
                // Listen for keypress
                $(document).on('keypress', options.inputSelector, _listenerKeypressInput);

                $(document).on('click', options.closeSelector, _listenerClickClose);
            }

            function _addItem(data){
                errors = [];
                if(typeof data == 'string') data = {value: data};
                if(options.uniqueFields.length > 0){
                    // Check if unique
                    for (var i in items){
                        for (var u in options.uniqueFields) {
                            var
                                item = items[i],
                                field = options.uniqueFields[u];

                            if(item[field] == data[field]){
                                _addError('Duplicate data found', 'duplicate_found', {field: field, value: data[field]});
                            }
                        }
                    }
                }

                var hasErrors = _hasErrors('duplicate_found');

                if(!hasErrors){
                    // Add to array
                    items.push(data);
                    _normalizeItems();
                    PubSub.publish('CS_RenderBuddy.addItem', data);
                    if(debugMode) console.log(options.itemType.capitalize() + ' "' + data.value + '" added');
                    _render();
                } else {
                    alertify.notify('Oops, that ' + options.itemType + ' already exists', 'error', 2);
                }
                return this;
            }

            function _addItems(dataArray){
                for(var i in dataArray) _addItem(dataArray[i]);
                return this;
            }

            function _removeItem(data){
                if(typeof data == 'string') data = {value: data};
                for (var i in items) {
                    if(typeof items[i] != 'undefined'){
                        //console.log(items[i], data);
                        if(items[i]['value'] == data['value']){
                            delete items[i];
                            _normalizeItems();
                            PubSub.publish('CS_RenderBuddy.deleteItem', data);
                            if(debugMode) console.log(options.itemType.capitalize() + ' "' + data.value + '" removed');
                            _render();
                        }
                    }
                }
                return this;
            }

            function _getInputValue(){
                var val = $(options.inputSelector).val();
                if(typeof val == 'string') val = val.trim();
                if(val != '') {
                    //val = val.replaceAll(options.delimiter,'');
                    return val;
                }
                return null;
            }

            function _getItems(){
                _normalizeItems();
                return items;
            }

            function _normalizeItems(){
                var data = [];
                for (var i in items){
                    if(!(typeof items[i] == 'undefined' || items[i] === null)) data.push(items[i]);
                }
                items = data;
            }

            function _render(){
                _html = '';
                for(var i in items){
                    _renderItem(items[i]);
                }
                $(options.itemsContainerSelector).html(_html);
                $(options.inputSelector).val('');
                $(options.itemsContainerSelector).attr('data-instance', _identity);

                var currentItems = _getItems();
                $(options.itemsContainerSelector).attr('data-items', JSON.stringify(currentItems));
            }

            function _renderItem(data){
                if(_isCallable(options.renderItemCallback)) {
                    _html += options.renderItemCallback(data);
                } else {
                    _html += '<span class="' + options.itemClass + '">';
                    _html += data['value'];
                    _html += ' <a href="#removeTag" class="fa fa-times-circle"></a>';
                    _html += '</span>';
                }
            }

            function _is(identity){

            }

            function _clearItems(){
                items = [];
                return this;
            }

            function _isCallable(funcName){
                return typeof funcName == 'function';
            }

            function _addError(message, topic, data){
                var error = {
                    message     : message,
                    topic       : topic || 'general',
                    data       : data || null
                };
                errors.push(error);
                PubSub.publish('CS_RenderBuddy.errorAdded', error);
                if(debugMode) console.error('An error has occurred', error);
                return this;
            }

            function _hasErrors(topic){
                if(!topic) return errors.length > 0;

                for( var i in errors ) if(errors[i].topic == topic) return true;

                return false;
            }

            function _getErrors(){
                return errors;
            }

            return {
                init            : _init,
                errors          : _getErrors,
                addItem         : _addItem,
                addItems        : _addItems,
                removeItem      : _removeItem,
                clearItems      : _clearItems,
                getItems        : _getItems,
                render          : _render,
                isId            : _isId,
                id              : _getId
            }

        };

        var instances = [];

        function _setInstance(id, instance, replaceId) {
            if(id){
                if (replaceId) { // Update an instance id
                    for (var i in instances) {
                        if (instances[i].id == replaceId) {
                            instances[i].id = id;
                        }
                    }
                } else { // Add new instance id
                    instances.push({
                        id: id,
                        instance: instance
                    });
                }
            } else {
                console.error('Invalid id provided for setInstance', id);
            }
            return this;
        }

        function _getInstance(id){
            for(var i in instances){
                if(instances[i].id == id) return instances[i];
            }
        }

        function _allInstances(){
            return instances;
        }

        function _newInstance(opts){
            var thisInstance = CS_RenderBuddy(opts);
            _setInstance(thisInstance.id(), thisInstance);
            return thisInstance;
        }

        return {
            allInstances    : _allInstances,
            setInstance     : _setInstance,
            getInstance     : _getInstance,
            newInstance     : _newInstance
        }
    })();
