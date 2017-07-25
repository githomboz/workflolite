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
        _repeaterIndex = 0;
        ;

    function _selectorExists(formIdSelector){
        for( var i in __FFFORMS ) if(__FFFORMS[i].id == formIdSelector) return true;
        return false;
    }

    function _registerForm(formIdSelector, options){
        var optionsSet = typeof options != 'undefined';
        if(!_selectorExists(formIdSelector)){
            var ffform = {
                key         : optionsSet && typeof options.key != 'undefined' ? options.key : formIdSelector.split('-')[1],
                id          : formIdSelector,
                $form       : $(formIdSelector),
                data        : null,
                state       : null,
                json        : null,
                parsed      : null,
                fields      : []
            };
            var encoded = ffform.$form.attr('data-formfly'), json;
            var jsonString = atob(encoded);
            json = JSON.parse(jsonString);
            ffform.json = jsonString;
            if(_isNode(json)) ffform.parsed = _standardizeNodes(_parseJSONNode(json));

            if(ffform.parsed) ffform.fields = _sweepForFormFields(ffform.parsed);

            ffform.data = _createDataObject(ffform.fields);

            ffform.json = JSON.parse(ffform.json);

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

    function _checkForDataValues(fields){

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

    function _parseJSONNode(node, groupPath){
        // group, element, repeater
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
                                    node.properties[groupName].items = _parseJSONNode(node.properties[groupName].items, groupName + '[' + node.properties[groupName].repeaterIndex + ']', groupName);
                                }
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
                                    node.properties[groupName].properties[fieldName] = _parseJSONNode(node.properties[groupName].properties[fieldName], groupName);
                                }
                                break;
                            default:
                                node.properties[groupName].fType = 'element';
                                node.properties[groupName].name = groupName; // value may be overwritten later
                                node.properties[groupName].fieldName = groupName;
                                node.properties[groupName].nativeName = true;
                                if(groupPath) node.properties[groupName].groupName = groupPath;
                                break;
                        }

                        // Identify fields

                    }
                }
                if(typeof node.name == 'undefined') {
                    node.name = _generateAnonymousElement(node.type);
                    node.nativeName = false;
                }
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
                    node.items = _parseJSONNode(node.items, node.groupName + '[' + node.repeaterIndex + ']', node.groupName);
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
                if(groupPath) node.groupName = groupPath;
                break;
        }

        if(typeof node.nativeName == 'undefined') node.nativeName = true;

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

    function _handleSubmitBtnClick(e){
        e.preventDefault();
        var $this = $(this),
            $form = $this.parents('form.form-fly'),
            id = $form.attr('id'),
            key = id.split('-')[1],
            FFF = _getFormByIdAttribute('#' + id);
            ;

        console.log(FFF, id, 'SUBMIT');

    }

    function _handleRepeaterAddBtnClick(e){
        e.preventDefault();
        var $this = $(this),
            $form = $this.parents('form.form-fly'),
            id = $form.attr('id'),
            key = id.split('-')[1],
            FFF = _getFormByIdAttribute('#' + id);
        ;

        console.log(FFF, id, 'ADD');
    }

    function _activateListeners(){
        if(!_listenersActive){
            // Activate Listeners
            _listenersActive = true;

            $(document).on('click', 'form.form-fly button[type=submit].submit-formfly-btn', _handleSubmitBtnClick)
            $(document).on('click', 'form.form-fly button[type=submit].repeater-add-btn', _handleRepeaterAddBtnClick)

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

                // Analyze and render updates
                function _analyzeHTML(){

                }

                // Analyze the state of the form
                function _analyzeState(){

                }

                function _generateLabelName(node, force){
                    force = Boolean(force) === true;
                    var run = false;
                    if(node.nativeName) run = true;
                    if(run || force){
                        var fieldNames = ['fieldName','name'];
                        for ( var i in fieldNames ){
                            if(typeof node[fieldNames[i]] != 'undefined') {
                                return node[fieldNames[i]].capitalize();
                            }
                        }
                    }
                    return null;
                }

                function _generateFormHTML(){
                    var html = '';
                    html += '<form method="post" data-formfly="' + btoa(JSON.stringify(_current.formData.json)) + '" id="' + _current.formData.id + '" class="form-fly">';

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
                        html = '';

                    switch (node.type){
                        case 'string':
                            var isEnum = typeof node.enum != 'undefined' && node.enum.length > 0,
                                isRequired = node.required;

                            console.log(node);

                            if(isEnum){
                                html += '<select>';
                                html += '<option>Select ' + label;
                                if(!isRequired) html += ' (optional)';
                                html += '</option>';
                                for ( var i in node.enum ) {
                                    html += '<option value="' + node.enum[i] + '">' + node.enum[i] + '</option>';
                                }
                                html += '</select>';
                            } else {
                                html += '<input type="text" class="' + '' + '" ';
                                if(label){
                                    html += 'placeholder="' + label;
                                    if(!isRequired) html += ' (optional)';
                                }
                                html += '" ';
                                html += ' />';
                            }
                            break;
                        case 'number':
                            html += '<input type="text" class="' + '' + '" ';
                            if(label){
                                html += 'placeholder="' + label;
                                if(!isRequired) html += ' (optional)';
                            }
                            html += '" ';
                            html += ' />';
                            break;
                        case 'boolean':
                            break;
                    }

                    return html;
                }

                function _generateFFFGroupHTML(node) {
                    var suppressLegend = (typeof node.suppressLegend != 'undefined' && node.suppressLegend);
                    var html = '<fieldset>';
                        //console.log(node, suppressLegend);
                    if(node.nativeName && !suppressLegend) {
                        html += '<legend>' + node.groupName + '</legend>';
                    }
                    if( typeof node.elements != 'undefined' ) {
                        for ( var i in node.elements ) {
                            html += _generateFFFHTML(node.elements[i]);
                        }
                    }

                    html += '</fieldset>';
                    return html;
                }

                function _generateFFFRepeaterHTML(node) {
                    var suppressLegend = (typeof node.suppressLegend != 'undefined' && node.suppressLegend);
                    var html = '<fieldset class="repeater">';
                    //console.log(node, suppressLegend);
                    if(node.nativeName && !suppressLegend) {
                        html += '<legend>' + node.groupName + '</legend>';
                    }

                    html += '<div class="form-repeater source" data-repeater_index="' + node.repeaterIndex + '">';
                    if( typeof node.elements != 'undefined' ) {
                        for ( var i in node.elements ) {
                            html += _generateFFFHTML(node.elements[i]);
                        }
                    }
                    html += '</div><!--/.form-repeater.source-->';
                    html += '<button type="submit" class="repeater-add-btn">+ Add</button>';
                    html += '</fieldset>';
                    return html;
                }

                function _renderFormHTML(targetSelector){
                    $(targetSelector).html(_generateFormHTML());
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
                    render          : _renderFormHTML,
                    getFormHTML     : _generateFormHTML,
                    submit          : _submit,
                    applyData       : _applyData,
                    analyzeHTML     : _analyzeHTML,
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

    return {
        registerForm        : _registerForm,
        getFormById         : _getFormByIdAttribute,
        getForm             : _getForm
    }
})();