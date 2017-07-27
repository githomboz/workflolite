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

    function _selectorExists(formIdSelector){
        for( var i in __FFFORMS ) if(__FFFORMS[i].id == formIdSelector) return true;
        return false;
    }

    function _getDataSource(options, formIdSelector){
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
        var formIdSelector = '#formfly-' + key;
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

            console.log(ffform);

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
            data[fields[i].name] = null;
        }
        return data;
    }

    function _generateFieldName(node){
        var name = node.name;
        if(typeof node.groupName != 'undefined') {
            name = node.groupName + '[' +  node.name + ']';
        }
        return name;
    }

    function _sweepForFormFields(node, parentNode, fields){
        fields = fields || [];
        parentNode = parentNode || null;
        if( node.fType == 'element' ){
            var required = parentNode && typeof parentNode.required != 'undefined' ? parentNode.required : false ;
            node.required = (required && required.indexOf(node.name) >= 0);
            node.name = _generateFieldName(node);
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
            return '_af' + type.capitalize() + _anonymousElementCounts[type];
        }
    }

    function _regenerateGroupName(node){
        var newFieldName = node.fieldName, newFieldName = null;
        console.log(node);
        if(node.groupRepeaterIndex && node.repeaterIndex){
            if( node.fieldName.indexOf('[' + node.groupRepeaterIndex + ']') >= 0 ){
                newFieldName = node.fieldName.split('[' + node.groupRepeaterIndex)[0];
                newFieldName += '[' + node.repeaterIndex + ']';
            }
        }
        return newFieldName;
    }


    function _parseJSONNode(node, state){
        // group, element, repeater
        if(!state) state = {};
         switch(node.type){
            case 'object':
                node.fType = 'group';
                if(typeof node.properties != 'undefined'){

                    for(var groupName in node.properties){
                        switch (node.properties[groupName].type){
                            case 'array':
                                node.properties[groupName].fType = 'repeater';
                                node.properties[groupName].name = groupName;
                                node.properties[groupName].groupName = groupName;
                                node.properties[groupName].repeaterIndex = _repeaterIndex += 20;
                                node.properties[groupName].nativeName = true;
                                if(typeof node.properties[groupName].items != 'undefined') {
                                    node.properties[groupName].items = _parseJSONNode(node.properties[groupName].items, {
                                        groupPath : groupName,
                                        groupRepeaterIndex : node.properties[groupName].repeaterIndex
                                    });
                                }
                                _repeaterCache.push(node.properties[groupName]);
                                break;
                            case 'object':
                                node.properties[groupName].fType = 'group';
                                node.properties[groupName].groupName = groupName;
                                node.properties[groupName].nativeName = true;
                                for(var fieldName in node.properties[groupName].properties){
                                    node.properties[groupName].properties[fieldName].name = fieldName;
                                    var _type = node.properties[groupName].properties[fieldName].type;
                                    node.properties[groupName].properties[fieldName].fType = (['array','object'].indexOf(_type) >= 0 ? (_type == 'array' ? 'repeater' : 'group') : 'element');
                                    node.properties[groupName].properties[fieldName].groupName = groupName;
                                    node.properties[groupName].properties[fieldName] = _parseJSONNode(node.properties[groupName].properties[fieldName], {
                                        groupPath : groupName,
                                        groupRepeaterIndex : ( typeof state.groupRepeaterIndex != 'undefined' ? state.groupRepeaterIndex : undefined )
                                    });
                                }
                                if(typeof state.groupRepeaterIndex != 'undefined') node.properties[groupName].groupRepeaterIndex = state.groupRepeaterIndex;
                                break;
                            default:
                                node.properties[groupName].fType = 'element';
                                node.properties[groupName].name = groupName; // value may be overwritten later
                                node.properties[groupName].fieldName = groupName;
                                node.properties[groupName].nativeName = true;
                                //if(groupPath) node.properties[groupName].groupName = groupPath;
                                node.properties[groupName].groupRepeaterIndex;
                                if(typeof state.groupRepeaterIndex != 'undefined') node.properties[groupName].groupRepeaterIndex = state.groupRepeaterIndex;
                                if(typeof state.groupPath != 'undefined') {
                                    node.properties[groupName].groupName = state.groupPath;
                                }

                                if(node.properties[groupName].repeaterIndex) {
                                    node.properties[groupName].groupName = node.properties[groupName].groupName + '[' + node.properties[groupName].repeaterIndex + ']';
                                } else {
                                    if(node.properties[groupName].groupRepeaterIndex) {
                                        node.properties[groupName].groupName += '[' + node.properties[groupName].groupRepeaterIndex + ']';
                                    }
                                }



                                //console.log(node.properties[groupName], node.properties[groupName].groupName);

                                break;
                        }

                        // Identify fields

                    }
                }
                if(typeof node.name == 'undefined') {
                    node.name = _generateAnonymousElement(node.type);
                    node.nativeName = false;
                }
                if(typeof node.groupName == 'undefined') node.groupName = node.name;
                if(typeof state.groupRepeaterIndex != 'undefined') node.groupRepeaterIndex = state.groupRepeaterIndex;
                break;
            case 'array':
                node.fType = 'repeater';
                node.repeaterIndex = _repeaterIndex += 20;
                if(typeof node.name == 'undefined') {
                    node.name = _generateAnonymousElement(node.type);
                    node.nativeName = false;
                }
                node.groupName = node.name;

                if(typeof node.items != 'undefined') {
                    node.items = _parseJSONNode(node.items, {
                        groupPath : node.groupName,
                        groupRepeaterIndex : node.repeaterIndex
                    });
                }
                break;
            case 'boolean':
            case 'string':
            case 'number':
                node.fType = 'element';
                if(typeof node.name == 'undefined') {
                    node.name = _generateAnonymousElement(node.type);
                    node.nativeName = false;
                }
                node.fieldName = node.name;
                //if(groupPath) node.groupName = groupPath;
                if(typeof state.groupRepeaterIndex != 'undefined') node.groupRepeaterIndex = state.groupRepeaterIndex;
                if(typeof state.groupPath != 'undefined') node.groupName = state.groupPath;
                if(node.repeaterIndex) {
                    node.groupName = node.groupName + '[' + node.repeaterIndex + ']';
                } else {
                    if(node.groupRepeaterIndex) {
                        node.groupName += '[' + node.groupRepeaterIndex + ']';
                    }
                }

                console.log(node, node.groupName);
                break;
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
            FFF = _getFormByIdAttribute(id);

        console.log(FFF, id, 'SUBMIT');

    }

    function _handleRepeaterAddBtnClick(e){
        e.preventDefault();
        var $this = $(this),
            $form = $this.parents('form.formfly'),
            $fieldset = $this.parents('fieldset'),
            $containerSource = $fieldset.find('.ftype-repeater-container.source'),
            id = $form.attr('id'),
            FFF = _getFormByIdAttribute(id),
            repeaterIndex = $containerSource.attr('data-group_repeater_index'),
            ffform;

        // Transform source html based on FFF data

        // PubSub.publish('APP.FormFly.repeater.add', {
        //     repeaterIndex : repeaterIndex,
        //     key : id.split('-')[1],
        //     id : id
        // });
        if(FFF) FFF.addRepeater(repeaterIndex);

        console.log(FFF, id, 'ADD', repeaterIndex);
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

                }

                function _parseNameToLabel(name){
                    return name.replace('_',' ').replace(/([A-Z]+)/g, " $1").replace(/([A-Z][a-z])/g, " $1");
                }

                function _generateLabelName(node, force){
                    force = Boolean(force) === true;
                    var run = false;
                    if(node.nativeName) run = true;
                    if(run || force){
                        var fieldNames = ['fieldName','name'];
                        for ( var i in fieldNames ){
                            if(typeof node[fieldNames[i]] != 'undefined') {
                                return _parseNameToLabel(node[fieldNames[i]]).capitalize();
                            }
                        }
                    }
                    return null;
                }

                function _generateFormHTML(){
                    var html = '';
                    html += '<form method="post" data-formfly="' + btoa(JSON.stringify(_current.formData.json)) + '" id="' + _current.formData.id + '" class="formfly">';

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
                        dataValue = _current.formData.data[node.name];

                    html += '<span class="ftype-element field-type-' + node.type + (__(node, 'enum') ? 'enum':'') + ' field-name-' + node.name.hyphenateString() + '">';
                    switch (node.type){
                        case 'string':
                            var isEnum = typeof node.enum != 'undefined' && node.enum.length > 0;

                            if(isEnum){
                                html += '<select class="ftype-element__field type-' + node.type + ' enum name-' + node.name.hyphenateString() + '" ';
                                html += 'name="' + node.name + '" ';
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
                                html += 'class="ftype-element__field type-' + node.type + ' text name-' + node.name.hyphenateString() + '" ';
                                html += 'name="' + node.name + '" ';
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
                            html += '<input type="text" class="ftype-element__field type-' + node.type + ' name-' + node.name.hyphenateString() + '" ';
                            html += 'name="' + node.name + '" ';
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
                            html += '<input type="checkbox" class="ftype-element__field type-' + node.type + ' name-' + node.name.hyphenateString() + '" ';
                            html += 'name="' + node.name + '" ';
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
                    var suppressLegend = ( __(node, 'suppressLegend') && node.suppressLegend );
                    var html = '<fieldset class="ftype-group type-' + node.type + ' name-' + node.groupName.hyphenateString() + '" ';
                    if( __(node, 'repeaterIndex') ) html += 'data-repeater_index="' + node.repeaterIndex + '" ';
                    //if( __(node, 'groupRepeaterIndex') ) html += 'data-repeater_index="' + node.groupRepeaterIndex + '" ';
                    html += '>';
                        //console.log(node, suppressLegend);
                    if(node.nativeName && !suppressLegend) {
                        html += '<legend>' + node.groupName.capitalize() + '</legend>';
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
                    var html = '<fieldset class="ftype-repeater type-' + node.type + ' name-' + node.name.hyphenateString() + '">';
                    //console.log(node);
                    if(node.nativeName && !suppressLegend) {
                        html += '<legend>' + node.groupName.capitalize() + '</legend>';
                    }

                    html += '<div class="ftype-repeater-container source" ';
                    if( __(node, 'repeaterIndex') ) html += 'data-group_repeater_index="' + node.repeaterIndex + '"';
                    html += 'data-cur_index="' + node.repeaterIndex + '"';
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
                    console.log(_current.formData.data, node);
                    return '';
                }

                function _renderFormHTML(targetSelector){
                    targetSelector = targetSelector || _current.formData.id;
                    // Render Form
                    _current.formData.$form = $(targetSelector);
                    _current.formData.$form.html(_generateFormHTML());
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
                        $source = $('.ftype-repeater-container.source[data-group_repeater_index=' + repeaterIndex + ']'),
                        node;

                    if(typeof _repeaterStates[repeaterIndex] == 'undefined') {
                        _repeaterStates[repeaterIndex] = { currentIndex : ( repeaterIndex - 0 ) } ;
                    }

                    _repeaterStates[repeaterIndex].currentIndex ++;


                    for( var i in _repeaterCache){
                        if(repeaterIndex == _repeaterCache[i].repeaterIndex) {
                            node = _parseJSONNode(_repeaterCache[i].elements[0]);
                            console.log(repeaterIndex, _repeaterStates[repeaterIndex].currentIndex, node);

                            node.repeaterIndex = _repeaterStates[repeaterIndex].currentIndex;
                            node = _regenerateGroupName(node, (_repeaterStates[repeaterIndex].currentIndex - 1));
                            if(typeof node.elements != 'undefined') {
                                for( var a in node.elements) {
                                    node.elements[a].repeaterIndex = _repeaterStates[repeaterIndex].currentIndex;
                                    node.elements[a] = _regenerateGroupName(node.elements[a], (_repeaterStates[repeaterIndex].currentIndex - 1));
                                    if(typeof node.elements[a].elements != 'undefined'){
                                        for ( var b in node.elements[a].elements) {
                                            node.elements[a].elements[b].repeaterIndex = _repeaterStates[repeaterIndex].currentIndex;
                                            node.elements[a].elements[b] = _regenerateGroupName(node.elements[a].elements[b], (_repeaterStates[repeaterIndex].currentIndex - 1));
                                            if(typeof node.elements[a].elements[b].elements != 'undefined'){
                                                for ( var c in node.elements[a].elements[b].elements) {
                                                    node.elements[a].elements[b].elements[c].repeaterIndex = _repeaterStates[repeaterIndex].currentIndex;
                                                    node.elements[a].elements[b].elements[c] = _regenerateGroupName(node.elements[a].elements[b].elements[c], (_repeaterStates[repeaterIndex].currentIndex - 1));
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            repeaterHTML += _generateFFFHTML(node);
                            console.log(repeaterHTML);
                        }
                    }

                    if(repeaterHTML.trim() != ''){
                        _current.formData.$form.find('.ftype-repeater-container.target').append(repeaterHTML);
                        $source.attr( 'data-curr_index', _repeaterStates[repeaterIndex].currentIndex );
                    }

                }

                function _regenerateGroupName(node, oldRepeaterIndex){
                    var groupRepeaterIndexSet = typeof node.groupRepeaterIndex != 'undefined',
                        repeaterIndexSet = typeof node.repeaterIndex != 'undefined';
                    if(groupRepeaterIndexSet && repeaterIndexSet){
                            node.groupName = node.groupName.split( '[' + oldRepeaterIndex + ']' )[0];
                            node.groupName += '[' + node.repeaterIndex + ']';
                            if(typeof node.fieldName != 'undefined') node.name = node.groupName + '[' + node.fieldName + ']';
                    }
                    console.log(node, groupRepeaterIndexSet, repeaterIndexSet, node.groupName);
                    return node;
                }

                // Submit the form to process script
                function _submit(){

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
                    disableSubmit   : _disableSubmit
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