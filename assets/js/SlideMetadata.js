/**
 * Created by benezerlancelot on 5/17/17.
 */
var SlideMetadata = (function(){

    var formSelector = ".tabbed-content.metadata form.set-meta-value";
    var $form = $(formSelector);
    var $btn = $form.find('button');
    var $details = $('.column-details');
    var currFormData = null;
    var formVal = null;
    var formIcons = {};

    var options = {
        loadingIcon : '<i class="fa fa-spin fa-spinner"></i>',
        btnAddTxt : '<i class="fa fa-plus"></i> Add',
        btnUpdateTxt : '<i class="fa fa-save"></i> Update'
    };

    var _METADATA_DATA = {
        unsavedChanges : false,
        showForm : false,
        listSelected : false,
        selectedEntry : null,
        selectedData : null,
        listChanged : true, //(typeof _METADATA != 'undefined')
        actionMode : null, // update, add, remove
        formEnabled : false,
        selectedDataNew : false, // Whether this is data for update, or add
    };

    function _updateMeta(key, value){

        _METADATA_DATA.listChanged = true;
        _METADATA_DATA.unsavedChanges = false;
    }

    /**
     * Runs once upon startup
     * @private
     */
    function _initialize(){
        $(".tabbed-nav .database-nav .num-flag").text(_getMetaCount());
        // $(".js-us-phone-mask").mask("(999) 999-9999", {autoclear: false});
        // $(".js-twitter-handle-mask").mask("@***************", {autoclear: false});
        return false;
    }

    function _setSelectedField(fieldData){
        _METADATA_DATA.selectedData = fieldData;
        return false;
    }

    function _getMetaCount(){
        return Object.keys(_METADATA).length;
    }

    function _metadataEntrySelected(e){
        e.preventDefault();
        var
            $entry = $(this),
            slug = $entry.data('slug');
        PubSub.publish('bindedBox.tabs.metadata.slugSelected', slug);

        return false;
    }

    function _onMetaDataSelected(topic, slug){
        $btn.html(options.btnUpdateTxt);
        // Mark list as selected
        _METADATA_DATA.listSelected = true;
        // Mark current entry as selected
        _METADATA_DATA.selectedEntry = slug;
        _METADATA_DATA.selectedData = _METADATA[slug];
        _METADATA_DATA.selectedDataNew = false;
        _METADATA_DATA.showForm = false;
        // Run Render
        _render();
        return false;
    }

    function _onMetaDataAdding(topic, payload){
        $btn.html(options.btnAddTxt);
        // Mark list as not selected
        _METADATA_DATA.listSelected = false;
        // Mark current entry as not selected
        _METADATA_DATA.selectedEntry = null;
        _METADATA_DATA.selectedDataNew = true;
        _METADATA_DATA.showForm = true;
        // Run Render
        _setSelectedField(payload);
        _render();
        //_enableFormSubmit();
        return false;
    }

    function _render(){
        var $entries = $('.binded-trigger-box .tabbed-content.metadata .entries');
        // Render list
        if(_METADATA_DATA.listChanged){
            var html = '';
            var metaDataCount = 0;
            for(var _slug in _METADATA){
                html += '<div class="entry clearfix" data-slug="' + _METADATA[_slug].slug + '">' + "\n";
                html += "\t" + '<span class="key truncate">' + _METADATA[_slug].field + '</span>' + "\n";
                html += "\t" + '<span class="value truncate">';
                var value = _METADATA[_slug].formatted || _METADATA[_slug].value;
                if(value != null){
                    switch(_METADATA[_slug].type){
                        case 'address':
                            html += value;
                            break;
                        case 'array':
                            if(value.length <= 0) {
                                html += '[ value not set ]';
                            } else {
                                html += JSON.stringify(value);
                            }
                            break;
                        default:
                            html += value;
                            break;
                    }
                } else {
                    html += '[ value not set ]';
                }
                html += '</span>' + "\n";
                html += "\t" + '<i class="fa fa-chevron-right"></i>' + "\n";
                html += '</div>' + "\n";
                metaDataCount ++;
            }

            $entries.html(html);
            $(".tabbed-nav .database-nav .num-flag").text(metaDataCount);

            switch(_METADATA_DATA.actionMode){
                case 'update':

                    break;
            }

            // Reset listChanged
            _METADATA_DATA.listChanged = false;
        }

        //console.log($entries.html());

        // Set list selected class
        if(_METADATA_DATA.listSelected) {
            $entries.addClass('selected');
        } else {
            $entries.removeClass('selected');
        }

        // Set entry selected
        if(_METADATA_DATA.selectedEntry){
            $entries.find('.entry').removeClass('selected');
            var $entry = $entries.find('[data-slug='+_METADATA_DATA.selectedEntry+']');
            $entry.addClass('selected');
        } else {
            $entries.find('.entry').removeClass('selected');
        }

        // Render Details
        _renderDetails();
        return false;
    }

    function _renderDetails(){
        //console.log(_METADATA_DATA);
        if(_METADATA_DATA.selectedData){
            $details.find('h2').html(_METADATA_DATA.selectedData.field + (_METADATA_DATA.selectedDataNew ? ' <span class="pending">(pending)</span>' : ''));
            $details.find('.meta-entry.slug .val').html('job.' + _METADATA_DATA.selectedData.slug);
            $details.find('.meta-entry.type .val').html(_METADATA_DATA.selectedData.type.capitalize());
            switch(_METADATA_DATA.selectedData.type){
                case 'array':
                    var html = '';
                    if(_METADATA_DATA.selectedData.value && Object.keys(_METADATA_DATA.selectedData.value).length > 0){
                        html += '<pre>' + JSON.stringify(_METADATA_DATA.selectedData.value, undefined, 2) + '</pre>';
                    }
                    $details.find('.meta-entry.value .val').html(html);
                    break;
                case 'address':
                    var html = '';
                    if(_METADATA_DATA.selectedData.value && Object.keys(_METADATA_DATA.selectedData.value).length > 0){
                        html += '<pre>' + JSON.stringify(_METADATA_DATA.selectedData.value, undefined, 2) + '</pre>';
                    }
                    $details.find('.meta-entry.value .val').html(html);
                    break;
                default:
                    $details.find('.meta-entry.value .val').html(_METADATA_DATA.selectedData.value);
                    break;
            }
            if(_METADATA_DATA.selectedData.formatted){
                $details.find('.meta-entry.formatted').addClass('show');
                $details.find('.meta-entry.formatted .val').html(_METADATA_DATA.selectedData.formatted);
            } else {
                $details.find('.meta-entry.formatted').removeClass('show');
                $details.find('.meta-entry.formatted .val').html('');
            }
            if(_METADATA_DATA.selectedData.format){
                $details.find('.meta-entry.format').addClass('show');
                $details.find('.meta-entry.format .val').html(_METADATA_DATA.selectedData.format);
            } else {
                $details.find('.meta-entry.format').removeClass('show');
                $details.find('.meta-entry.format .val').html('');
            }

            $details.find('.inner-details').addClass('show');
        } else {
            $details.find('.inner-details').removeClass('show');
        }

        if(_METADATA_DATA.showForm){
            _showForm();
        } else {
            _hideForm();
        }
        return false;
    }

    function _drawFormField(data){
        data = data || _METADATA_DATA.selectedData;
        var html = '', val = data.value;

        switch(data.type.toLowerCase()){
            case 'boolean':
                val = Boolean(val);
                html += 'Mark True <input type="checkbox" class="metaField" name="' + data.slug + '"';
                html += val ? ' checked="checked"' : '';
                html += ' />';
                html += '<span class="help-details">Check the box for "true". Leave unchecked for "false".</span>';
                break;
            case 'string':
                html += '<input type="text" class="metaField" name="' + data.slug + '"';
                html += ' placeholder="' + data.field + '"';
                html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                html += ' />';
                break;
            case 'date':
            case 'datetime':
                html += '<input type="text" name="' + data.slug + '_date"';
                html += ' class="js-date-mask metaField"';
                html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                html += ' />';
                if(data.type == 'datetime'){
                    html += '<input type="text" name="' + data.slug + '_time"';
                    html += ' class="js-time-mask metaField"';
                    html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                    html += ' />';
                    html += '<select class="metaField" name="amOrPm"><option value="am">AM</option><option value="pm">PM</option></select>';
                }
                break;
            case 'number':
                html += '<input class="metaField" type="text" name="' + data.slug + '"';
                html += ' placeholder="' + data.field + '"';
                html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                html += ' />';
                html += '<span class="help-details">Value must be numeric.</span>';
                break;
            case 'phone':
                html += '<input type="text" name="' + data.slug + '"';
                html += ' class="js-us-phone-mask metaField"';
                html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                html += ' />';
                break;
            case 'twitterhandle':
                html += '<input type="text" name="' + data.slug + '"';
                html += ' class="js-twitter-handle-mask metaField"';
                html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                html += ' />';
                break;
            case 'text':
                html += '<textarea class="metaField" name="' + data.slug + '"';
                html += ' placeholder="' + data.field + '">' + ( val === null ? '' : val ) + '</textarea>';
                break;
            case 'url':
                html += '<input class="metaField" type="text" name="' + data.slug + '"';
                html += ' placeholder="Enter a valid URL" value="' + ( val === null ? '' : val ) + '" />';
                html += '<span class="help-details">URL must begin with "http://" or "https://" in order to validate.</span>'
                break;
            case 'address':
                var addressFields = ['street','city','state','zip'];

                for(var f in addressFields){
                    switch(addressFields[f]){
                        case 'state':
                            var states = get50States();
                            html += '<select class="metaField" name="' + data.slug + '__' + addressFields[f] + '">';
                            html += '<option value="">Select State</option>';
                            for(var i in states){
                                html += '<option value="' + i + '"';
                                if(val && val[addressFields[f]] == i) html += ' selected="selected"';
                                html += '>' + states[i] + '</option>';
                            }
                            html += '</select>';
                            break;
                        default:
                            html += '<input class="metaField" type="text" name="' + data.slug + '__' + addressFields[f] + '"';
                            html += ' placeholder="' + addressFields[f].capitalize() + '" value="' + (val && typeof val[addressFields[f]] != 'undefined' ? val[addressFields[f]] : '') + '" />';
                            break;
                    }
                }
                break;
        }

        if(_METADATA_DATA.selectedDataNew && BindedBox.allowed(5)){
            html += '<div class="add-to-template"><input type="checkbox" id="addMetaKey" name="addMetaKey" /> <label for="addMetaKey">Add this meta key to the template</label>  </div>';
        }

        html += _drawIcons(data);
        $form.find('.inner-form').html(html);

        $form.find('.icon-set a').each(function(i, item){
            var $item = $(item),
                btnText = $item.text().trim();

            formIcons[btnText.toLowerCase()] = {
                $       : $item,
                text    : btnText,
                html    : $item.html()
            };
        });

        // Routines and listeners to set up once html has been added to DOM
        switch(data.type.toLowerCase()){
            case 'phone':
                $(".js-us-phone-mask").mask("(999) 999-9999", {autoclear: false});
                break;
            case 'twitterhandle':
                $(".js-twitter-handle-mask").mask("@***************", {autoclear: false});
                break;
            case 'datetime':
                $(".js-time-mask").mask("99:99", {autoclear: false});
            case 'date':
                $(".js-date-mask").mask("99/99/9999", {autoclear: false, alias: "dd/mm/yyyy"});
                break;
        }
        return false;
    }

    /**
     * Handles event where any of the form icons are clicked
     * @param e
     * @private
     */
    function _handleFormIconClick(e){
        e.preventDefault();
        var $this = $(this),
            btnName = $this.text().toLowerCase().trim();
        console.log(btnName);
        switch(btnName){
            case 'validate':
                break;
            case 'save':
                break;
            case 'clear':
                if(_METADATA_DATA.selectedData){
                    if(confirm('Are you sure you want to clear `' + _METADATA_DATA.selectedData.slug + '`?')){
                        var data = _METADATA_DATA.selectedData;
                        data.value = null;
                        _drawFormField(data);
                    }
                }
                break;
            case 'remove':
                if(_METADATA_DATA.selectedData){
                    if(confirm('Are you sure you want to remove `' + _METADATA_DATA.selectedData.slug + '`?')){
                        // Attempt meta data removal and subsequent re-renderings
                    }
                }
                break;
            case 'cancel':
                _hideForm();
                break;
        }
        return false;
    }


    function _showForm(){
        if(_METADATA_DATA.selectedData) _drawFormField();

        $form.show();
        // Hide Value
        $details.find('.meta-entry.value').hide();
        $(document).on('click', BindedBox.selector + ' .icon-set a', _handleFormIconClick);
        PubSub.subscribe(MetaData.pubSubRoot + 'form.checkForDataChange', _handleFormInteraction);
        PubSub.subscribe(MetaData.pubSubRoot + 'form.changeTriggered', _handleFormChangesFound);
        PubSub.subscribe(MetaData.pubSubRoot + 'form.changeCancelled', _handleFormChangesCancelled);
        PubSub.subscribe(MetaData.pubSubRoot + 'update.validate.before', _handleValidationBefore);
        PubSub.subscribe(MetaData.pubSubRoot + 'update.validate.error', _handleValidationError);
        // Register form change listeners
        _setFormChangeListeners(true);
        //_enableFormSubmit();
        return false;
    }

    function _hideForm(){
        $form.hide();
        // Show Value

        $details.find('.meta-entry.value').show();
        $(document).off('click', BindedBox.selector + ' .icon-set a', _handleFormIconClick);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'form.checkForDataChange', _handleFormInteraction);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'form.changeTriggered', _handleFormChangesFound);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'form.changeCancelled', _handleFormChangesCancelled);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'update.validate.before', _handleValidationBefore);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'update.validate.error', _handleValidationError);
        _setFormChangeListeners(false);
        _disableFormSubmit();
        return false;
    }

    function _setFormChangeListeners(enable){
        if(typeof enable == 'undefined' || enable === null) enable = true;
        enable = Boolean(enable);
        if(enable){
            // Enable
            $(document).on('keyup', formSelector + ' input[type=text].metaField', _handleFormChange);
            $(document).on('change', formSelector + ' input[type=checkbox].metaField', _handleFormChange);
            $(document).on('change', formSelector + ' select.metaField', _handleFormChange);
        } else {
            // Disable
            $(document).off('keyup', formSelector + ' input[type=text].metaField', _handleFormChange);
            $(document).off('change', formSelector + ' input[type=checkbox].metaField', _handleFormChange);
            $(document).off('change', formSelector + ' select.metaField', _handleFormChange);
        }
        return false;
    }

    function _handleValidationBefore(topic, payload){
        // Set validation icon to spinner, change color,
        if(typeof formIcons.validate != 'undefined'){
            formIcons.validate.$.find('.fa').removeClass('fa-check').addClass('fa-spin fa-spinner');
            formIcons.validate.$.addClass('active disable').removeClass('error');
        }
    }

    function _handleValidationError(topic, payload){
        console.log(payload);
        // Set validation icon to spinner, change color,
        if(typeof formIcons.validate != 'undefined'){
            formIcons.validate.$.find('.fa').addClass('fa-exclamation-triangle').removeClass('fa-check fa-spin fa-spinner');
            formIcons.validate.$.removeClass('active').addClass('disable error');
        }
    }

    function _handleFormChange(e){

        var payload = {
            formData : $form.serializeArray(),
            field : _METADATA_DATA.selectedData.slug,
            metaType : _METADATA_DATA.selectedData.type
        };

        PubSub.publish(MetaData.pubSubRoot + 'form.checkForDataChange', payload);
        return false;
    }

    function _enableFormSubmit(){
        if(_METADATA_DATA.formEnabled !== true){ // to avoid creating tons of listeners
            _METADATA_DATA.formEnabled = true;
            _resetValidation();
            $form.find('.icon-set').addClass('enabled');
            $btn.prop('disabled',null);
            $(document).on('click', formSelector + ' button[type=submit]', _handleClickFormSubmit);
        }
        return false;
    }

    function _disableFormSubmit(){
        if(_METADATA_DATA.formEnabled !== false){
            _METADATA_DATA.formEnabled = false;
            $form.find('.icon-set').removeClass('enabled');
            $btn.attr('disabled','disabled');
            $(document).off('click', formSelector + ' button[type=submit]', _handleClickFormSubmit);
        }
        return false;
    }

    function _resetValidation(){
        if(typeof formIcons.validate != 'undefined'){
            formIcons.validate.$.removeClass('error active');
            formIcons.validate.$.find('.fa').removeClass('fa-exclamation-triangle').addClass('fa-check');
        }
    }

    function _handleClickFormSubmit(e){
        e.preventDefault();

        MetaData.trySave(currFormData.field, currFormData);

        if(_METADATA_DATA.selectedDataNew){
            // Handle Add
        } else {
            // Handle Update
        }
        return false;
    }

    function _handleFormInteraction(topic, payload){
        console.log(topic);
        if(typeof payload.formData != 'undefined'){
            if(typeof payload.field != 'undefined'){
                if(typeof payload.metaType != 'undefined'){

                    // Parse value from input form
                    var formData = _parseFormInputData(payload);

                    if(formData){
                        // Grab original meta data
                        var meta = typeof _METADATA[payload.field] != 'undefined' ? _METADATA[payload.field] : null;

                        var activateForm = false;

                        //console.log(meta, formData, _METADATA);

                        if(meta){
                            activateForm = JSON.stringify(formData.value) != JSON.stringify(meta.value);
                            //console.log(activateForm, formData.value, meta.value);
                        } else {
                            activateForm = formData.value !== null;
                            //console.log(activateForm, formData.value);
                        }

                        // Check to see if current input value is the same as original meta data
                        if(activateForm){
                            // If not the same, publish MetaData.pubSubRoot + 'form.changeTriggered' with formData
                            //console.log('Publishing to ' + MetaData.pubSubRoot + 'form.changeTriggered');
                            PubSub.publish(MetaData.pubSubRoot + 'form.changeTriggered', {
                                field : payload.field,
                                formData : formData
                            });
                        } else {
                            //console.log('Publishing to ' + MetaData.pubSubRoot + 'form.changeCancelled');
                            PubSub.publish(MetaData.pubSubRoot + 'form.changeCancelled', {
                                field : payload.field,
                                formData : formData
                            });
                        }
                    } else {
                        console.error('Form data provided is invalid');
                    }
                } else {
                    console.error('Invalid meta type provided');
                }
            } else {
                console.error('Invalid meta field provided');
            }
        } else {
            console.error('Invalid form data provided');
        }
        return false;
    }

    function _handleFormChangesFound(topic, payload){
        // Store formData to the object to avoid having to re-parse the form data

        console.log(topic, payload, MetaData.getValue(payload.field));
        var meta = MetaData.getValue(payload.field);
        
        if((meta && meta.slug == payload.field) || meta === null){
            currFormData = {};
            currFormData.value = payload.formData.value;
            console.log(currFormData.value);
            formVal = currFormData.value;

            // @todo; If fields set, add them to currFormData [field, slug, sort, type]

            _enableFormSubmit();
        }

        //
        // //if(_METADATA_DATA.selectedData && typeof payload.formData != 'undefined' && typeof payload.formData.value != 'undefined'){
        //     currFormData = payload,//_METADATA_DATA.selectedData;
        //     //
        // //}
        return false;
    }

    function _handleFormChangesCancelled(topic, payload){

        currFormData = null;
        formVal = null;

        _disableFormSubmit();
        return false;
    }

    /**
     *
     * @param formData
     * @private
     */
    function _parseFormInputData(payload){
        var rtn = {
            field : payload.field,
            value : null
        };
        // Check if formData is set for given field
        var validDataChange = false;
        for(var i in payload.formData){
            if(!validDataChange && payload.formData[i].name.indexOf(payload.field) === 0) validDataChange = true;
        }
        if(validDataChange || payload.metaType == 'boolean'){ // Checkboxes will not return formData at all if unchecked
            switch(payload.metaType){
                case 'datetime':
                    break;
                case 'date':
                    break;
                case 'address':
                    rtn.value = {};
                    for(var i in payload.formData){
                        var key = payload.formData[i].name.replace(payload.field + '__', '');
                        rtn.value[key] = payload.formData[i].value;
                    }
                    var fieldCount = Object.keys(rtn.value).length;
                    //console.log(fieldCount);
                    for(var field in rtn.value){
                        var fieldSet = [undefined, null, ''].indexOf(rtn.value[field]) < 0;
                        //console.log('yup', fieldSet, field, rtn.value[field]);
                        if(!fieldSet) fieldCount --;
                    }
                    //console.log(fieldCount);
                    if(fieldCount <= 0) rtn.value = null;
                    console.log(payload, rtn);
                    break;
                case 'number':
                    break;
                case 'boolean':
                    //console.log(payload.formData);
                    var formFieldValid = typeof payload.formData[0] != 'undefined'
                        && typeof payload.formData[0].name != 'undefined'
                        && payload.formData[i].name.indexOf(payload.field) === 0;
                    rtn.value = Boolean(formFieldValid);
                    break;
                case 'text':
                    break;
                case 'array':
                    break;
                case 'phone':
                    rtn.value = payload.formData[i].value.replace([' ','-','(',')'],'').trim().match(/[0-9]+/g).join('');
                    console.log(rtn.value);
                    if(rtn.value == '') rtn.value = null;
                    break;
                case 'twitterhandle':
                case 'url':
                case 'string':
                    rtn.value = payload.formData[i].value.trim();
                    if(rtn.value == '') rtn.value = null;
                    break;
            }
        } else return false;
        return rtn;
    }

    function _drawIcons(data){
        var html = '<div class="icon-set ' + (_METADATA_DATA.formEnabled ? 'enabled':'') + '">';
        // Validation icon
        html += '<a href="#" class="enabled-only"><i class="fa fa-check"></i> Validate <span class="extra"></span></a>';
        // Save icon (change indicator)
        //html += '<a href="#" class="enabled-only"><i class="fa fa-save"></i> Save <span class="extra"></span></a>';
        if(data.value != null){
            // clear icon
            html += '<a href="#"><i class="fa fa-eraser"></i> Clear <span class="extra"></span></a>';
        }
        if(BindedBox.allowed(5)){
            // delete icon
            html += '<a href="#"><i class="fa fa-trash"></i> Remove <span class="extra"></span></a>';
        }
        // Cancel form icon
        html += '<a href="#"><i class="fa fa-times-circle"></i> Cancel <span class="extra"></span></a>';
        html += '</div>';
        return html;
    }

    function _validateNewField(meta){
        var errorFields = [], logs = {debug:[], errors:[]};
        // Must not be empty
        if(meta.field === '') {
            errorFields.push('input[type=text]');
            logs.errors.push('Must enter a valid meta key');
        }
        // Must be at least 3 characters long

        // Must be less than 32 characters long
        // Type must be valid
        // Must be unique
        // Slug must be valid & unique
        // Must start with letter
        return {
            response : {
                errorFields : errorFields,
                success : true
            },
            logs : logs,
            errors : logs.errors.length > 0
        };
    }

    function _handleClickSubmitNewMetaKeyForm(e){
        e.preventDefault();
        var $this = $(this),
            $form = $this.parents('form.tab-form'),
            $input = $form.find('input'),
            $select = $form.find('select'),
            meta = {
                field : $input.val().trim().capitalize(),
                type : $select.val(),
                value : null,
                formatted : null,
                format: null
            };

        meta.slug = removeSpecialChars(meta.field);
        meta.slug = meta.slug.toCamelCase();

        // Validate
        var validationResponse = _validateNewField(meta);
        if(validationResponse.errors === false){
            PubSub.publish('bindedBox.tabs.metadata.addNewTriggered', meta);
            $input.val('');
            $form.find('select option').removeAttr('selected');
        } else {
            _handleError(validationResponse.logs.errors[0]);
        }
        //console.log(meta);
        return false;
    }

    function _handleError(error){
        console.error(error);
        return false;
    }

    function _handleClickEditMetaValue(e){
        e.preventDefault();
        _showForm();
        return false;
    }

    function _activate(){
        _render();
        $(document).on('click', '.tabbed-content.metadata .meta-fields .entry', _metadataEntrySelected);
        $(document).on('click', '.tabbed-content.metadata .tab-form button[type=submit]', _handleClickSubmitNewMetaKeyForm);
        $(document).on('click', '.tabbed-content.metadata .meta-entry.value .fa-pencil', _handleClickEditMetaValue);
        PubSub.subscribe('bindedBox.tabs.metadata.slugSelected', _onMetaDataSelected);
        PubSub.subscribe('bindedBox.tabs.metadata.addNewTriggered', _onMetaDataAdding);
        return false;
    }

    function _deactivate(){
        $(document).off('click', '.tabbed-content.metadata .meta-fields .entry', _metadataEntrySelected);
        $(document).off('click', '.tabbed-content.metadata .tab-form button[type=submit]', _handleClickSubmitNewMetaKeyForm);
        $(document).off('click', '.tabbed-content.metadata .meta-entry.value .fa-pencil', _handleClickEditMetaValue);
        PubSub.unsubscribe('bindedBox.tabs.metadata.slugSelected', _onMetaDataSelected);
        PubSub.unsubscribe('bindedBox.tabs.metadata.addNewTriggered', _onMetaDataAdding);
        return false;
    }

    PubSub.subscribe('bindedBox.tabs.metadata.openTriggered', _activate);
    PubSub.subscribe('bindedBox.tabs.metadata.closeTriggered', _deactivate);
    PubSub.subscribe('bindedBox.closed', _deactivate);

    _initialize();

    return {
        updateMeta : _updateMeta,
        showForm : _showForm,
        hideForm : _hideForm,
        enableFormSubmit : _enableFormSubmit,
        disableFormSubmit : _disableFormSubmit,
    };
})();