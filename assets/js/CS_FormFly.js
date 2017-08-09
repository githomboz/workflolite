/**
 * Created by benezerlancelot on 7/21/17.
 */

var CS_FormFly = (function(){

    var FormFlyForm = null,
        __FFFORMS = [],
        _listenersActive = false,
        _anonymousElementCounts = {
            string : 0,
            number : 0,
            boolean : 0,
            object : 0,
            array : 0
        },
        _repeaterIndex = 0,

        /**
         * Store each repeater so it can be easily retrieved and rendered
         * @type {Array}
         * @private
         */
        _repeaterCache = [],
        _repeaterStates = {}
        ;

    function __(obj, property){
        return obj && typeof obj[property] != 'undefined' ? obj[property] : null ;
    }

    function _cleanSelector(selector){
        return selector.replace('#','');
    }

    function _selectorExists(formIdSelector){
        formIdSelector = _cleanSelector(formIdSelector);
        for( var i in __FFFORMS ) if(__FFFORMS[i].id == formIdSelector) return true;
        return false;
    }

    function _getDataSource(options, formIdSelector){
        formIdSelector = _cleanSelector(formIdSelector);
        var found = {
            json : false,
            parsed : false,
            $form : false,
            jsonString : null,
            fields : null
        };

        // Check options.json,
        found.json = options && typeof options.json != 'undefined' ? options.json : false ;

        // Check options.node / options.parsed
        found.parsed = options && typeof options.node != 'undefined' ? options.node : false ;

        if( !found.parsed ) found.parsed = options && typeof options.parsed != 'undefined' ? options.parsed : false ;

        var $form = $(formIdSelector);
        found.$form = $form.length >= 1 ? $form : null;


        // Check if json is on $(formIdSelector).attr('data-formfly');
        if( !found.json && found.$form) {
            var encoded = found.$form.attr('data-formfly');
            if(encoded){
                found.jsonString = atob(encoded);
                found.json = JSON.parse(found.jsonString);
            }
        }

        if( !found.parsed && found.json && _isNode( found.json ) ) {
            found.parsed = _standardizeNodes( _parseJSONNode( found.json ) );
        }

        if(found.parsed) found.fields = _sweepForFormFields(found.parsed);

        found.data = _createDataObject(found.fields);

        if(found.jsonString) found.json = JSON.parse(found.jsonString);

        return found;
    }

    function _registerForm(key, options){
        var formIdSelector = 'formfly-' + key;
        formIdSelector = _cleanSelector(formIdSelector);
        if(!_selectorExists(formIdSelector)){
            var sourceData = _getDataSource(options, formIdSelector),
                ffform = {
                key         : key,
                id          : formIdSelector,
                $form       : sourceData.$form,
                data        : sourceData.data,
                state       : null,
                json        : sourceData.json,
                parsed      : sourceData.parsed,
                fields      : sourceData.fields
            };

            //console.log(ffform);

            __FFFORMS.push(ffform);
            _activateListeners();
            _createFormFlyFormObject();
        } else {
            console.error('Error occurred while attempting to register form.  Selector already exists.');
        }
    }

    function _createDataObject(fields){
        var data = {};
        for ( var i in fields ) {
            data[fields[i].nameAttr] = null;
        }
        return data;
    }

    function _generateFieldName(node){
        var name = node.nameAttr;
        if(typeof node.rootName != 'undefined') {
            name = node.rootName + '[' +  node.nameAttr + ']';
        }
        return name;
    }

    function _sweepForFormFields(node, parentNode, fields){
        fields = fields || [];
        parentNode = parentNode || null;
        if( node.fType == 'element' ){
            var required = parentNode && typeof parentNode.required != 'undefined' ? parentNode.required : false ;
            node.required = (required && required.indexOf(node.nameAttr) >= 0);
            node.nameAttr = _generateFieldName(node);
            fields.push(node);
        } else {
            if( typeof node.elements != 'undefined' ){
                for ( var i in node.elements ) {
                    _sweepForFormFields(node.elements[i], node, fields);
                }
            }
        }
        return fields
    }

    function _standardizeNodes(node){
        switch (node.type){
            case 'string':
            case 'number':
            case 'boolean':
                break;
            case 'object':
                if(typeof node.elements == 'undefined') {
                    node.elements = [];
                    var standardizedNode = null;
                    for( var fieldNames in node.properties ) {
                        standardizedNode = _standardizeNodes(node.properties[fieldNames]);
                        node.elements.push(standardizedNode);
                    }
                    delete node.properties;
                }
                break;
            case 'array':
                if(typeof node.elements == 'undefined'){
                    standardizedNode = _standardizeNodes(node.items);
                    node.elements = [ standardizedNode ];
                    delete node.items;
                }
                break;
        }

        return node;
    }

    function _generateAnonymousElement(type){
        if(['string','number','boolean','object','array'].indexOf(type) >= 0){
            _anonymousElementCounts[type]++;
            return 'ff' + type.capitalize() + _anonymousElementCounts[type];
        }
    }

    function _analyzeAndCreateFFFProperties(node, mergeData){
        //console.log(node);

        var backup = JSON.stringify(node);

        if(preMergeData) {
            for( var field in preMergeData ) {
                node[field] = preMergeData[field];
            }
        }


        //node.fieldName;
        //node.nameAttr;

        switch(node.type){
            case 'array':
                break;
            case 'group':
                if(node.rootRepeaterIndex){
                    // node.repeaterIndex;
                    // node.rootName;
                } else {

                }

                break;
            case 'element':
                if(node.rootRepeaterIndex){
                    // node.repeaterIndex;
                    // node.rootName;
                } else {

                }

                break;
        }

        if(typeof node.nameAttr == 'undefined') {
            node.nameAttr = _generateAnonymousElement(node.type);
            node.nativeName = false;
        }

        // Verify Field Name
        if(!node.fieldName && node.nameAttr) {
            node.fieldName = node.nameAttr.split('[')[0];
        }


        if(typeof node.nativeName == 'undefined') node.nativeName = true;

        //console.log(node);
        node = JSON.parse(backup);
        //console.log(node);
        return node;
    }

    function _parseJSONNode(node, state){
        // group, element, repeater
        if(!state) state = {};
         switch(node.type){
            case 'object':
                node.fType = 'group';
                if(typeof node.properties != 'undefined'){

                    for(var rootName in node.properties){
                        switch (node.properties[rootName].type){
                            case 'array':
                                node.properties[rootName].fType = 'repeater';
                                node.properties[rootName].nameAttr = rootName;
                                node.properties[rootName].rootName = rootName;
                                node.properties[rootName].repeaterIndex = _repeaterIndex += 20;
                                node.properties[rootName].nativeName = true;
                                if(typeof node.properties[rootName].items != 'undefined') {
                                    node.properties[rootName].items = _parseJSONNode(node.properties[rootName].items, {
                                        rootName : rootName,
                                        rootRepeaterIndex : node.properties[rootName].repeaterIndex
                                    });
                                }
                                _repeaterCache.push(node.properties[rootName]);
                                // node.properties[rootName] = _analyzeAndCreateFFFProperties(node.properties[rootName], {
                                //      nameAttr : rootName,
                                //      rootName : rootName,
                                //      repeaterIndex : _repeaterIndex += 20,
                                //      nativeName = true
                                // });
                                if(node.properties[rootName].rootName) node.properties[rootName].root = node.properties[rootName].rootName.split('[')[0];
                                break;
                            case 'object':
                                node.properties[rootName].fType = 'group';
                                node.properties[rootName].rootName = rootName;
                                node.properties[rootName].nativeName = true;
                                for(var fieldName in node.properties[rootName].properties){
                                    node.properties[rootName].properties[fieldName].nameAttr = fieldName;
                                    var _type = node.properties[rootName].properties[fieldName].type;
                                    node.properties[rootName].properties[fieldName].fType = (['array','object'].indexOf(_type) >= 0 ? (_type == 'array' ? 'repeater' : 'group') : 'element');
                                    node.properties[rootName].properties[fieldName].rootName = rootName;
                                    node.properties[rootName].properties[fieldName] = _parseJSONNode(node.properties[rootName].properties[fieldName], {
                                        rootName : rootName,
                                        rootRepeaterIndex : ( typeof state.rootRepeaterIndex != 'undefined' ? state.rootRepeaterIndex : undefined )
                                    });
                                }
                                if(typeof state.rootRepeaterIndex != 'undefined') node.properties[rootName].rootRepeaterIndex = state.rootRepeaterIndex;
                                if(typeof state.rootName != 'undefined' && node.properties[rootName].nameAttr) node.properties[rootName].nameAttr = state.rootName;
                                //_analyzeAndCreateFFFProperties(node.properties[rootName]);
                                if(node.properties[rootName].rootName) node.properties[rootName].root = node.properties[rootName].rootName.split('[')[0];
                                break;
                            default:
                                //console.log(rootName);
                                node.properties[rootName].fType = 'element';
                                node.properties[rootName].nameAttr = rootName; // value may be overwritten later
                                node.properties[rootName].fieldName = (node.properties[rootName].fieldName) ? rootName.split('[')[0] : undefined;
                                node.properties[rootName].nativeName = true;
                                //if(rootName) node.properties[rootName].rootName = rootName;
                                node.properties[rootName].rootRepeaterIndex;
                                if(typeof state.rootRepeaterIndex != 'undefined') node.properties[rootName].rootRepeaterIndex = state.rootRepeaterIndex;
                                if(typeof state.rootName != 'undefined') node.properties[rootName].rootName = state.rootName;

                                if(node.properties[rootName].repeaterIndex) {
                                    node.properties[rootName].rootName = node.properties[rootName].rootName + '[' + node.properties[rootName].repeaterIndex + ']';
                                } else {
                                    if(node.properties[rootName].rootRepeaterIndex) {
                                        node.properties[rootName].rootName += '[' + node.properties[rootName].rootRepeaterIndex + ']';
                                    }
                                }

                                if(node.properties[rootName].rootName) node.properties[rootName].root = node.properties[rootName].rootName.split('[')[0];

                                //_analyzeAndCreateFFFProperties(node.properties[rootName]);

                                //console.log(node.properties[rootName], node.properties[rootName].rootName);

                                break;
                        }

                        if(typeof node.properties[rootName].nameAttr == 'undefined' && node.properties[rootName].root) {
                            node.properties[rootName].nameAttr = node.properties[rootName].root;
                        }

                        if(typeof node.properties[rootName].nameAttr == 'undefined') {
                            node.properties[rootName].nameAttr = _generateAnonymousElement(node.properties[rootName].type);
                            node.properties[rootName].nativeName = false;
                        }

                        // Verify Field Name
                        if(!node.properties[rootName].fieldName && node.properties[rootName].nameAttr) {
                            node.properties[rootName].fieldName = node.properties[rootName].nameAttr.split('[')[0];
                        }

                        if(typeof node.properties[rootName].nativeName == 'undefined') node.properties[rootName].nativeName = true;
                        //console.log('------FOCUS HERE-(named)-----', node.properties[rootName], node.properties[rootName].rootName);

                    }
                }
                if(!node.rootName && state.rootName) node.rootName = state.rootName;
                if(!node.rootName && node.nameAttr) node.rootName = node.nameAttr;
                if(node.rootName) node.root = node.rootName.split('[')[0];

                if(!node.nameAttr) {
                    node.nameAttr = _generateAnonymousElement(node.type);
                    node.nativeName = false;
                }
                if(state.rootRepeaterIndex) node.rootRepeaterIndex = state.rootRepeaterIndex;
                //_analyzeAndCreateFFFProperties(node);
                break;
            case 'array':
                node.fType = 'repeater';
                node.repeaterIndex = _repeaterIndex += 20;
                if(!node.nameAttr) {
                    node.nameAttr = _generateAnonymousElement(node.type);
                    node.nativeName = false;
                }
                node.rootName = node.nameAttr;
                node.root = node.nameAttr.split('[')[0];

                if(node.items) {
                    node.items = _parseJSONNode(node.items, {
                        rootName : node.rootName,
                        rootRepeaterIndex : node.repeaterIndex
                    });
                }
                //console.log('------FOCUS HERE-(root)------', node, node.rootName);
                //_analyzeAndCreateFFFProperties(node);
                break;
            case 'boolean':
            case 'string':
            case 'number':
                node.fType = 'element';
                if(!node.nameAttr) {
                    node.nameAttr = _generateAnonymousElement(node.type);
                    node.nativeName = false;
                }

                if(node.nameAttr && !node.root) node.fieldName = node.nameAttr.split('[')[0];
                //if(rootName) node.rootName = rootName;
                if(state.rootRepeaterIndex) node.rootRepeaterIndex = state.rootRepeaterIndex;
                if(state.rootName) node.rootName = state.rootName;
                if(node.repeaterIndex) {
                    node.rootName = node.rootName + '[' + node.repeaterIndex + ']';
                } else {
                    var indexAlreadyAdded = typeof node.rootName != 'undefined' && node.rootName.indexOf('[' + node.rootRepeaterIndex + ']') >= 1;
                    if( node.rootRepeaterIndex && !indexAlreadyAdded ) {
                        node.rootName += '[' + node.rootRepeaterIndex + ']';
                    }
                    if(node.rootRepeaterIndex) node.repeaterIndex = node.rootRepeaterIndex;
                }

                if(node.rootName) node.root = node.rootName.split('[')[0];
                if(node.root && node.repeaterIndex) node.rootName = node.root + '[' + node.repeaterIndex + ']';

                //console.log('------FOCUS HERE-(root)------', node, node.rootName);
                //_analyzeAndCreateFFFProperties(node);
                break;
        }

        if(typeof node.nameAttr == 'undefined') {
            node.nameAttr = _generateAnonymousElement(node.type);
            node.nativeName = false;
        }

        // Verify Field Name
        if(!node.fieldName && node.nameAttr) {
            node.fieldName = node.nameAttr.split('[')[0];
        }

        if(typeof node.nativeName == 'undefined') node.nativeName = true;

        if(node.fType == 'repeater') _repeaterCache.push(node);

        // Reformat data so that it replaces properties and items with just 'members'
        // Attempt to architect output friendly format

        return node;
    }

    function _isNode(node){
        return typeof node.type != 'undefined' && ['object','array','boolean','string','number'].indexOf(node.type) >= 0;
    }

    function _getFormByIdAttribute(formIdSelector){
        formIdSelector = _cleanSelector(formIdSelector);
        var index = null;
        for( var i in __FFFORMS ) if(__FFFORMS[i].id == formIdSelector) index = i;
        if(index) return _getForm(index);
    }

    function _getFormByKey(key){
        var index = null;
        for( var i in __FFFORMS ) if(__FFFORMS[i].key == key) index = i;
        if(index) return _getForm(index);
    }

    function _handleSubmitBtnClick(e){
        e.preventDefault();
        var $this = $(this),
            $form = $this.parents('form.formfly'),
            id = $form.attr('id'),
            key = id.split('-')[1],
            FFF = _getFormByIdAttribute(id),
            fields = FFF.getFields();

        var results = FFF.analyzeState();

        if(results && results.success){
            for ( var i in fields ) {
                var node = fields[i];
                className = FFF.generateClass(node);
                $(".ftype-element__field." + className).removeClass('error');
            }
            FFF.submit();
        } else {
            // Handle Message Errors
            if( results.errors.messages.length > 0 ) {
                for( var i in results.errors.messages ) {
                    console.error(results.errors.messages[i]);
                }
            }

            // Handle Required Field Errors
            var className = null, className2, field, $field, $field2;
            if( results.errors.requiredFields.length > 0 ) {
                for( var i in results.errors.requiredFields ) {
                    //console.log(results.errors.requiredFields);
                    for ( var f in fields ){
                        className = FFF.generateClass(fields[f]);
                        $field = $(".ftype-element__field." + className);
                        //console.log(className, $field, fields[f].nameAttr);

                        if(!(results.errors.requiredFields.indexOf(fields[f].nameAttr) >= 0) && $field.length) $field.removeClass('error');
                        if(results.errors.requiredFields[i] == fields[f].nameAttr){
                            field = fields[f];
                        }
                    }

                    if(field){
                        className2 = FFF.generateClass(field);
                        //console.log(field, className2);
                        $field2 = $(".ftype-element__field." + className2);
                        $field2.addClass('error');
                    }
                }
            }


        }

        //console.log(FFF.getData(), id, 'SUBMIT');

    }

    function _handleRepeaterAddBtnClick(e){
        e.preventDefault();
        var $this = $(this),
            $form = $this.parents('form.formfly'),
            $fieldset = $this.parents('fieldset'),
            $containerSource = $fieldset.find('.ftype-repeater-container.source'),
            id = $form.attr('id'),
            FFF = _getFormByIdAttribute(id),
            repeaterIndex = $containerSource.attr('data-root_repeater_index');

        if(FFF) FFF.addRepeater(repeaterIndex);

        //console.log(FFF, id, 'ADD', repeaterIndex);
    }

    function _activateListeners(){
        if(!_listenersActive){
            // Activate Listeners
            _listenersActive = true;

            $(document).on('click', 'form.formfly button[type=submit].submit-formfly-btn', _handleSubmitBtnClick)
            $(document).on('click', 'form.formfly button[type=submit].repeater-add-btn', _handleRepeaterAddBtnClick)

        }
    }

    function _createFormFlyFormObject(){
        if(!FormFlyForm) {
            FormFlyForm = function(formData){

                var _current = {};

                // Unique id for given form
                function _getKey(){
                    return _current.formData.key;
                }

                // Return all FFF data object
                function _getFFData(){
                    return _current.formData;
                }

                // Return parsed data object
                function _getData(){
                    return _current.formData.data;
                }

                // Return parsed data object
                function _getContext(){
                    if(typeof _current.formData.state.context != 'undefined') return _current.formData.state.context;
                    return {};
                }

                function _setContext(context){
                    _current.formData.state.context = context;
                }

                // Return form jquery element
                function _getElement(){
                    return _current.formData.$form;
                }

                // Return array of field names
                function _getFields(){
                    return _current.formData.fields;
                }

                // Apply field data values to the html fields
                function _applyData(data){
                    for( var fieldName in data ){
                        _current.formData.data[fieldName] = data[fieldName];
                    }
                    _updateFormHTML();
                }

                // Return current state data for given form
                function _getState(){

                }

                function _resetState(type){
                    var defaults = {
                        submitted   : false,
                        validated   : false,
                        validTS     : null,
                        rendered    : false,
                        context     : {}
                    };

                    if(type){
                        if(typeof defaults[type] != 'undefined'){
                            _current.formData.state[type] = defaults[type];
                        } else {
                            return false;
                        }
                    } else {
                        _current.formData.state = defaults;
                    }
                    return true;
                }

                // Analyze the state of the form
                function _analyzeState(){
                    var errors = {
                        messages: [],
                        requiredFields : []
                    };
                    // Loop through the fields
                    for( var i in _current.formData.fields ){
                        var val = null,
                            className = _generateFFFClassName(_current.formData.fields[i]),
                            $element = $('.ftype-element__field.' + className);

                        switch(_current.formData.fields[i].type){
                            case 'string':
                            case 'number':
                                val = $element.val();
                                _current.formData.data[_current.formData.fields[i].nameAttr] = (val == '' ? null : val);

                                if(_current.formData.fields[i].type == 'number') {
                                    if(isNaN(parseInt(val))) errors.messages.push(_current.formData.fields[i].fieldName + ' must be numeric');
                                }
                                break;
                            case 'boolean':
                                val = $element.prop('checked');
                                _current.formData.data[_current.formData.fields[i].nameAttr] = val;
                                break;
                        }

                        if(_current.formData.fields[i].required && _current.formData.data[_current.formData.fields[i].nameAttr] == null){
                            errors.requiredFields.push(_current.formData.fields[i].nameAttr);
                        }
                    }

                    return {
                        success : (errors.messages.length == 0 && errors.requiredFields.length == 0),
                        errors : errors
                    };
                }

                function _parseNameToLabel(name){
                    return name.replace('_',' ').replace(/([A-Z]+)/g, " $1").replace(/([A-Z][a-z])/g, " $1");
                }

                function _generateLabelName(node, force){
                    force = Boolean(force) === true;
                    var run = false;
                    if(node.nativeName) run = true;
                    if(run || force){
                        var fieldNames = ['fieldName','nameAttr'];
                        for ( var i in fieldNames ){
                            if(typeof node[fieldNames[i]] != 'undefined') {
                                return _parseNameToLabel(node[fieldNames[i]]).capitalize();
                            }
                        }
                    }
                    return null;
                }

                function _generateFFFClassName(node, hyphenate){
                    var output = '';

                    hyphenate = typeof hyphenate == 'undefined' || Boolean(hyphenate);
                    //console.log(node);

                    switch(node.fType){
                        case 'group':
                            if(node.rootName) output += node.rootName;
                            var riText = '[' + node.repeaterIndex + ']',
                                riTextPrev = '[' + (node.repeaterIndex-1) + ']',
                                riTextExistsInRootName = node.rootName && node.rootName.indexOf(riText) >= 0,
                                riTextPrevExistsInRootName = node.rootName && node.rootName.indexOf(riTextPrev) >= 0;

                            if(node.repeaterIndex && !(riTextExistsInRootName || riTextPrevExistsInRootName)) output += riText;
                            if(node.fieldName) output += node.fieldName;
                            break;
                        case 'repeater':
                            if(node.nameAttr) output += node.nameAttr;
                            break;
                        case 'element':
                            if(node.nameAttr) output += node.nameAttr;
                            break;
                    }

                    if(hyphenate) return output.hyphenateString();
                    return output;
                }

                function _regenerateRootName(node, oldRepeaterIndex){

                    node = JSON.parse(JSON.stringify(node));
                    var rootRepeaterIndexSet = typeof node.rootRepeaterIndex != 'undefined',
                        repeaterIndexSet = typeof node.repeaterIndex != 'undefined',
                        rootName = typeof node.rootName != 'undefined' ? node.rootName : false;
                    if(rootRepeaterIndexSet && repeaterIndexSet && rootName){

                        var riText = '[' + node.repeaterIndex + ']';

                        // console.log(node, rootName, riText, riTextPrev, riTextExistsInRootName);
                        node.rootName = rootName.split( '[')[0];
                        node.root = rootName.split( '[')[0];
                        if(node.rootName && !(node.rootName.indexOf(riText) >= 0)) node.rootName += riText;
                        if(typeof node.fieldName != 'undefined') node.nameAttr = node.rootName + '[' + node.fieldName + ']';
                    }
                    //console.log(node, rootRepeaterIndexSet, repeaterIndexSet, node.rootName, node.fieldName, node.nameAttr);
                    return node;
                }

                function _generateFormHTML(){
                    var html = '';
                    html += '<form method="post" data-formfly="' + btoa(JSON.stringify(_current.formData.json)) + '" id="' + _current.formData.id.replace('#','') + '" class="formfly">';

                    html += _generateFFFHTML(_current.formData.parsed);

                    html += '<button type="submit" class="submit-formfly-btn">Submit</button>';

                    html += '</form>';
                    return html;
                }

                function _generateFFFHTML(node){
                    var html = '';
                    switch (node.fType) {
                        case 'element':
                            html += _generateFFFElementHTML(node);
                            break;
                        case 'group':
                            html += _generateFFFGroupHTML(node);
                            break;
                        case 'repeater':
                            html += _generateFFFRepeaterHTML(node);
                            break;
                    }
                    return html;
                }

                function _generateFFFElementHTML(node) {
                    var label = _generateLabelName(node),
                        isRequired = node.required,
                        html = '',
                        dataValue = _current.formData.data[node.nameAttr];

                    html += '<span class="ftype-element field-type-' + node.type + (__(node, 'enum') ? ' enum ':' ') + _generateFFFClassName(node) + '">';
                    switch (node.type){
                        case 'string':
                            var isEnum = typeof node.enum != 'undefined' && node.enum.length > 0;

                            if(isEnum){
                                html += '<select class="ftype-element__field type-' + node.type + ' enum ' + _generateFFFClassName(node) + '" ';
                                html += 'name="' + node.nameAttr + '" ';
                                html += '>';
                                html += '<option>Select ' + label;
                                if(!isRequired) html += ' (optional)';
                                html += '</option>';
                                for ( var i in node.enum ) {
                                    html += '<option value="' + node.enum[i] + '" ';
                                    if(node.enum[i] == dataValue) html += 'selected="selected" ';
                                    html += '>' + node.enum[i] + '</option>';
                                }
                                html += '</select>';
                            } else {

                                var typeAttr = __(node, 'attributes') && __(node.attributes, 'type') ? __(node.attributes, 'type') : 'text';
                                html += '<input ';
                                html += 'type="' + typeAttr  + '" ';
                                if(typeAttr == 'password') html += 'autocomplete="off" ';
                                //console.log(node.nameAttr);
                                html += 'class="ftype-element__field type-' + node.type + ' text ' + _generateFFFClassName(node) + '" ';
                                html += 'name="' + node.nameAttr + '" ';
                                if(label){
                                    html += 'placeholder="' + label;
                                    if(!isRequired) html += ' (optional)';
                                }
                                html += '" ';
                                if(dataValue) {
                                    html += 'value="' + dataValue + '" ';
                                }
                                html += ' />';
                            }
                            break;
                        case 'number':
                            html += '<input type="text" class="ftype-element__field type-' + node.type + ' ' + _generateFFFClassName(node) + '" ';
                            html += 'name="' + node.nameAttr + '" ';
                            if(label){
                                html += 'placeholder="' + label;
                                if(!isRequired) html += ' (optional)';
                            }
                            html += '" ';
                            if(dataValue) {
                                html += 'value="' + dataValue + '" ';
                            }
                            html += ' />';
                            break;
                        case 'boolean':
                            html += '<input type="checkbox" class="ftype-element__field type-' + node.type + ' ' + _generateFFFClassName(node) + '" ';
                            html += 'name="' + node.nameAttr + '" ';
                            if(dataValue) {
                                html += 'value="' + dataValue + '" ';
                            }
                            html += ' />';
                            html += '<label>' + label + '</label>';
                            break;
                    }
                    html += '</span>';

                    return html;
                }

                function _generateFFFGroupHTML(node) {
                    //console.log(node);
                    var suppressLegend = ( __(node, 'suppressLegend') && node.suppressLegend );
                    var html = '<fieldset class="ftype-group type-' + node.type + ' ' + _generateFFFClassName(node) + '" ';
                    if( __(node, 'repeaterIndex') ) html += 'data-repeater_index="' + node.repeaterIndex + '" ';
                    //if( __(node, 'rootRepeaterIndex') ) html += 'data-repeater_index="' + node.rootRepeaterIndex + '" ';
                    html += '>';
                        //console.log(node, suppressLegend);
                    if(node.nativeName && !suppressLegend && node.root) {
                        html += '<legend>' + node.root.capitalize() + '</legend>';
                    }

                    if( __(node, 'elements') ) {
                        for ( var i in node.elements ) {
                            html += _generateFFFHTML(node.elements[i]);
                        }
                    }

                    html += '</fieldset>';
                    return html;
                }

                function _generateFFFRepeaterHTML(node) {
                    var suppressLegend = ( __(node, 'suppressLegend') && node.suppressLegend);
                    var html = '<fieldset class="ftype-repeater type-' + node.type + ' ' + _generateFFFClassName(node) + '">';
                    //console.log(node);
                    if(node.nativeName && !suppressLegend && node.root) {
                        html += '<legend>' + node.root.capitalize() + '</legend>';
                    }

                    html += '<div class="ftype-repeater-container source" ';
                    if( __(node, 'repeaterIndex') ) html += 'data-root_repeater_index="' + node.repeaterIndex + '"';
                    html += 'data-repeater_index="' + node.repeaterIndex + '"';
                    html += '>';
                    if( __(node, 'elements') ) {
                        for ( var i in node.elements ) {
                            html += _generateFFFHTML(node.elements[i]);
                        }
                    }
                    html += '</div><!--/.ftype-repeater-container.source-->';
                    html += '<div class="ftype-repeater-container target">' + _generateRepeaterTargetsFromData(node) + '</div>';
                    html += '<button type="submit" class="repeater-add-btn">+ Add</button>';
                    html += '</fieldset>';
                    return html;
                }

                function _generateRepeaterTargetsFromData(node){
                    //console.log(_current.formData.data, node);
                    return '';
                }

                function _renderFormHTML(targetSelector){
                    targetSelector = targetSelector || '#' + _current.formData.id;
                    // Render Form
                    _current.formData.$form = $(targetSelector);
                    var html = _generateFormHTML();
                    //console.log(targetSelector, html);
                    _current.formData.$form.html(html);
                    //console.log(_current);
                }

                function _updateFormHTML(){
                    var formHTML = _generateFormHTML(), $form = $(formHTML), inner = $form.html();
                    // Render Form
                    _current.formData.$form.html(inner);
                }

                function _addRepeater(repeaterIndex){
                    // Have this run by pulling from ffform.data.  Only show repeater options that have been created and set first



                    //Generate field repeater html
                    var repeaterHTML = '',
                        $source = $('.ftype-repeater-container.source[data-root_repeater_index=' + repeaterIndex + ']'),
                        node;

                    if(typeof _repeaterStates[repeaterIndex] == 'undefined') {
                        _repeaterStates[repeaterIndex] = { currentIndex : ( parseInt(repeaterIndex) + 1 ) } ;
                    }

                    for( var i in _repeaterCache){
                        if(repeaterIndex == _repeaterCache[i].repeaterIndex) {
                            node = _repeaterCache[i].elements[0];
                            node = _standardizeNodes(_parseJSONNode(_repeaterCache[i].elements[0], {
                                rootName : node.rootName,
                                rootRepeaterIndex : node.repeaterIndex
                            }));
                            node.repeaterIndex = _repeaterStates[repeaterIndex].currentIndex;
                            node = _regenerateRootName(node, (_repeaterStates[repeaterIndex].currentIndex));
                            //console.log(repeaterIndex, _repeaterStates[repeaterIndex].currentIndex, node);
                            if(typeof node.elements != 'undefined') {
                                for( var a in node.elements) {
                                    node.elements[a].repeaterIndex = _repeaterStates[repeaterIndex].currentIndex;
                                    node.elements[a] = _regenerateRootName(node.elements[a], (_repeaterStates[repeaterIndex].currentIndex - 1));
                                    if(typeof node.elements[a].elements != 'undefined'){
                                        for ( var b in node.elements[a].elements) {
                                            node.elements[a].elements[b].repeaterIndex = _repeaterStates[repeaterIndex].currentIndex;
                                            node.elements[a].elements[b] = _regenerateRootName(node.elements[a].elements[b], (_repeaterStates[repeaterIndex].currentIndex - 1));
                                            if(typeof node.elements[a].elements[b].elements != 'undefined'){
                                                for ( var c in node.elements[a].elements[b].elements) {
                                                    node.elements[a].elements[b].elements[c].repeaterIndex = _repeaterStates[repeaterIndex].currentIndex;
                                                    node.elements[a].elements[b].elements[c] = _regenerateRootName(node.elements[a].elements[b].elements[c], (_repeaterStates[repeaterIndex].currentIndex - 1));
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            repeaterHTML += _generateFFFHTML(node);
                            //console.log(repeaterHTML);
                        }
                    }

                    if( !_current.formData.$form || ( _current.formData.$form && !_current.formData.$form.length ) ){
                        _current.formData.$form = $( '#' + _current.formData.id );
                    }

                    if( repeaterHTML.trim() != '' && _current.formData.$form ){
                        //console.log('test');
                        _current.formData.$form.find('.ftype-repeater-container.target').append(repeaterHTML);
                        _repeaterStates[repeaterIndex].currentIndex ++;
                        _searchNodeAddRepeaterElementsToFieldsArrayAndDataObj(node);
                        $source.attr( 'data-repeater_index', _repeaterStates[repeaterIndex].currentIndex );
                    }

                }

                function _searchNodeAddRepeaterElementsToFieldsArrayAndDataObj( node ) {
                    if(node.elements){
                        for ( var i in node.elements){
                            if(node.elements[i].fType == 'element'){
                                _current.formData.fields.push(node.elements[i]);
                                _current.formData.data[node.elements[i].nameAttr] = null;
                            } else {
                                _searchNodeAddRepeaterElementsToFieldsArrayAndDataObj( node.elements[i] );
                            }
                        }
                    } else {
                        if(node.fType == 'element'){
                            _current.formData.fields.push(node);
                            _current.formData.data[node.nameAttr] = null;
                        }
                    }
                }

                // Submit the form to process script
                function _submit(){
                    //@todo
                    console.log(_current.formData.data);
                    console.log(_current);
                    CS_API.call('ajax/save_form',
                        function(){
                            // beforeSend
                            BindedBox.disableTraffic();
                        },
                        function(data){
                            // success
                            if(data.errors == false && data.response.success){
                                SlideTasks.validateAndApplyUpdates(data, true);
                                BindedBox.enableTraffic();
                            } else {
                                BindedBox.enableTraffic();
                                if(data.errors && typeof data.errors[0] != 'undefined') alertify.error(data.errors[0]);
                            }
                        },
                        function(){
                            // error
                            BindedBox.enableTraffic();
                            alertify.error('Error', 'An error has occurred.');
                        },
                        {
                            projectId: _CS_Get_Entity_ID(),
                            taskId : BindedBox.task().id,
                            sortOrder : BindedBox.task().data.sortOrder,
                            dataJSON : JSON.stringify(_current.formData.data)
                        },
                        {
                            method: 'POST',
                            preferCache : false
                        }
                    );

                }

                function _enableSubmit(){

                }

                function _disableSubmit(){

                }

                function _init(formData){
                    _current = {
                        formData : formData
                    };
                    _resetState();
                }

                _init(formData);

                return {
                    getKey          : _getKey,
                    getData         : _getData,
                    getFields       : _getFields,
                    getElement      : _getElement,
                    getAllData      : _getFFData,
                    getState        : _getState,
                    getContext      : _getContext,
                    setContext      : _setContext,
                    addRepeater     : _addRepeater,
                    render          : _renderFormHTML,
                    getFormHTML     : _generateFormHTML,
                    submit          : _submit,
                    applyData       : _applyData,
                    analyzeState    : _analyzeState,
                    enableSubmit    : _enableSubmit,
                    disableSubmit   : _disableSubmit,
                    generateClass   : _generateFFFClassName
                }

            };
        }
    }

    function _getForm(index){

        _createFormFlyFormObject();

        // Check if form exists in __FFFORMS
        if(typeof __FFFORMS[index] == 'undefined') return null;

        // Get that form's data
        var formData = __FFFORMS[index];

        // return instance
        return new FormFlyForm(formData);
    }

    function _init(){

    }

    _init();

    return {
        registerForm        : _registerForm,
        getFormById         : _getFormByIdAttribute,
        getFormByKey        : _getFormByKey,
        getForm             : _getForm
    }
})();