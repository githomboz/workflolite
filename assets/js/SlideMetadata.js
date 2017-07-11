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
    var $metaList = $(".meta-fields .entries");
    var
        $tabForm = $('form.tab-form'),
        $tabFormInput = $tabForm.find('input[type=text]'),
        $tabFormSelect = $tabForm.find('select');
    var messageContainerSelector = '.message-container';
    var $messageContainer = $(messageContainerSelector);
    var messageBoxOpen = false;
    var _listenersActive = false;

    var options = {
            slideName : 'metadata',
            loadingIcon : '<i class="fa fa-spin fa-spinner"></i>',
            btnAddTxt : '<i class="fa fa-plus"></i> Add',
            btnUpdateTxt : '<i class="fa fa-save"></i> Update'
        }
        ;

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
        var reqId = BindedBox.addRequest('initializeModule', 'Initializing `SlideMetadata` module');
        _updateMetaCount();
        PubSub.subscribe(BindedBox.pubsubRoot + 'state', _handleStateChange);
        // $(".js-us-phone-mask").mask("(999) 999-9999", {autoclear: false});
        // $(".js-twitter-handle-mask").mask("@***************", {autoclear: false});
        BindedBox.addResponse(reqId, '`SlideMetadata` module initialized' );
        return false;
    }

    function _updateMetaCount(){
        $(".tabbed-nav .database-nav .num-flag").text(_getMetaCount());
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

    function _renderMetaListRow(fieldData){
        var html = '';
        html += '<div class="entry clearfix" data-slug="' + fieldData.slug + '">' + "\n";
        html += "\t" + '<span class="key truncate">' + fieldData.field + '</span>' + "\n";
        html += "\t" + '<span class="value truncate">';
        var value = fieldData.formatted || fieldData.value;
        if(value != null){
            switch(fieldData.type){
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
        return html;
    }

    function _render(){
        _closeMessage();
        var $entries = $('.binded-trigger-box .tabbed-content.metadata .entries');
        // Render list
        if(_METADATA_DATA.listChanged){
            var html = '';
            var metaDataCount = 0;
            for(var _slug in _METADATA){
                html += _renderMetaListRow(_METADATA[_slug]);
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
        switch(btnName){
            case 'validate':
                MetaData.autoRun(false);
                MetaData.validate(currFormData);
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
        $details.find('.meta-entry.value').hide();
        _formListenersStop();
        _formListenersStart();
        _disableFormSubmit();
        return false;
    }

    function _hideForm(){
        $form.hide();
        $details.find('.meta-entry.value').show();
        _formListenersStop();
        _disableFormSubmit();
        return false;
    }

    function _formListenersStart(){
        $(document).on('click', BindedBox.selector + ' .icon-set a', _handleFormIconClick);
        PubSub.subscribe(MetaData.pubSubRoot + 'form.checkForDataChange', _handleFormInteraction);
        PubSub.subscribe(MetaData.pubSubRoot + 'form.changeTriggered', _handleFormChangesFound);
        PubSub.subscribe(MetaData.pubSubRoot + 'form.changeCancelled', _handleFormChangesCancelled);
        PubSub.subscribe(MetaData.pubSubRoot + 'update.validate.before', _handleValidationBefore);
        PubSub.subscribe(MetaData.pubSubRoot + 'update.validate.response', _handleValidationResponse);
        PubSub.subscribe(MetaData.pubSubRoot + 'update.validate.response', MetaData.validationResponse);
        PubSub.subscribe(MetaData.pubSubRoot + 'update.validate.error', _handleValidationError);
        PubSub.subscribe(MetaData.pubSubRoot + 'update.save.before', _handleSaveBefore);
        PubSub.subscribe(MetaData.pubSubRoot + 'update.save.response', _handleSaveResponse);
        PubSub.subscribe(MetaData.pubSubRoot + 'update.save.error', _handleSaveError);
        _setFormChangeListeners(true);
    }

    function _formListenersStop(){
        $(document).off('click', BindedBox.selector + ' .icon-set a', _handleFormIconClick);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'form.checkForDataChange', _handleFormInteraction);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'form.changeTriggered', _handleFormChangesFound);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'form.changeCancelled', _handleFormChangesCancelled);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'update.validate.before', _handleValidationBefore);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'update.validate.response', _handleValidationResponse);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'update.validate.response', MetaData.validationResponse);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'update.validate.error', _handleValidationError);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'update.save.before', _handleSaveBefore);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'update.save.response', _handleSaveResponse);
        PubSub.unsubscribe(MetaData.pubSubRoot + 'update.save.error', _handleSaveError);
        _setFormChangeListeners(false);
    }

    function _setFormChangeListeners(enable){
        if(typeof enable == 'undefined' || enable === null) enable = true;
        enable = Boolean(enable);
        if(enable){
            // Enable
            $(document).on('keyup', formSelector + ' textarea.metaField', _handleFormChange);
            $(document).on('keyup', formSelector + ' input[type=text].metaField', _handleFormChange);
            $(document).on('change', formSelector + ' input[type=checkbox].metaField', _handleFormChange);
            $(document).on('change', formSelector + ' select.metaField', _handleFormChange);
        } else {
            // Disable
            $(document).off('keyup', formSelector + ' textarea.metaField', _handleFormChange);
            $(document).off('keyup', formSelector + ' input[type=text].metaField', _handleFormChange);
            $(document).off('change', formSelector + ' input[type=checkbox].metaField', _handleFormChange);
            $(document).off('change', formSelector + ' select.metaField', _handleFormChange);
        }
        return false;
    }

    function _handleValidationResponse(topic, payload) {
        console.log(topic, payload);
        if(typeof payload.errors != 'undefined' && payload.errors.length > 0){
            _setMessage('error', payload.errors[0], 'meta_validation');
            if(typeof formIcons.validate != 'undefined'){
                formIcons.validate.$.find('.fa').addClass('fa-exclamation-triangle').removeClass('fa-check fa-spin fa-spinner');
                formIcons.validate.$.removeClass('active disable success').addClass('error');
            }
        } else {
            if(payload.data.response.success){
                // If this is a validate->save routine, do not send a validation success message since a message will be sent for the save event
                if(!MetaData.isAutoRun()) _setMessage('success', payload.fieldData.field + ' is valid','meta_validation');
                formIcons.validate.$.find('.fa').addClass('fa-check').removeClass('fa-spin fa-spinner fa-exclamation-triangle');
                formIcons.validate.$.addClass('success disable').removeClass('active error');
            } else {
                _setMessage('error', 'An error has occurred. Please try again.', 'meta_validation');
                if(typeof formIcons.validate != 'undefined'){
                    formIcons.validate.$.find('.fa').addClass('fa-exclamation-triangle').removeClass('fa-check fa-spin fa-spinner');
                    formIcons.validate.$.removeClass('active disable success').addClass('error');
                }
            }
        }
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
        if(typeof payload.errors != 'undefined' && payload.errors.length > 0){
            _setMessage('error', payload.errors[0], 'meta_validation');
        }
        // Set validation icon to spinner, change color,
        if(typeof formIcons.validate != 'undefined'){
            formIcons.validate.$.find('.fa').addClass('fa-exclamation-triangle').removeClass('fa-check fa-spin fa-spinner');
            formIcons.validate.$.removeClass('active disable success').addClass('error');
        }
    }

    function _handleSaveResponse(topic, payload) {
        console.log(topic, payload);
        if(payload.data.errors === false) {
            if(payload.data.response.success){
                $btn.html(_METADATA_DATA.selectedDataNew ? options.btnAddTxt : options.btnUpdateTxt);
                _setMessage('success', payload.fieldData.field + ' updated successfully', 'meta_save');
                MetaData.setValue(payload.fieldData.slug, payload.fieldData);
                _disableFormSubmit();
            } else {
                $btn.html((_METADATA_DATA.selectedDataNew ? options.btnAddTxt : options.btnUpdateTxt) + ' &nbsp; <i class="fa fa-exclamation-triangle"></i>');
                var error = (typeof payload.data.errors != 'undefined' && payload.data.errors.length > 0) ? payload.data.errors[0] + '.' : 'Please try again.';
                _setMessage('error', payload.fieldData.field + ' update failed. ' + error, 'meta_save');
            }
        } else {
            $btn.html((_METADATA_DATA.selectedDataNew ? options.btnAddTxt : options.btnUpdateTxt) + ' &nbsp; <i class="fa fa-exclamation-triangle"></i>');
            _setMessage('error', payload.data.errors[0], 'meta_save');
        }
    }

    function _handleSaveBefore(topic, payload){
        // Set validation icon to spinner, change color,
        $btn.html((_METADATA_DATA.selectedDataNew ? options.btnAddTxt : options.btnUpdateTxt) + ' ' + options.loadingIcon);
        return false;
    }

    function _handleSaveError(topic, payload){
        console.log(payload);
        if(typeof payload.data.errors != 'undefined' && payload.data.errors.length > 0){
            $btn.html((_METADATA_DATA.selectedDataNew ? options.btnAddTxt : options.btnUpdateTxt) + ' &nbsp; <i class="fa fa-exclamation-triangle"></i>');
            _setMessage('error', payload.data.errors[0], 'meta_save');
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
            $btn.removeClass('error success');
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
        _closeMessage('meta_validation'); // close any validation related error messages
        _closeMessage('meta_save'); // close any validation related error messages
        $btn.html((_METADATA_DATA.selectedDataNew ? options.btnAddTxt : options.btnUpdateTxt));
        MetaData.trySave(currFormData.field, currFormData);
        return false;
    }

    function _getMetaClass(type){
        var className = null;
        switch(type){
            case 'address': className = 'MetaAddress'; break;
            case 'array': className = 'MetaArray'; break;
            case 'boolean': className = 'MetaBoolean'; break;
            case 'datetime': className = 'MetaDateTime'; break;
            case 'date': className = 'MetaDateTime'; break;
            case 'number': className = 'MetaNumber'; break;
            case 'string': className = 'MetaString'; break;
            case 'phone': className = 'MetaPhone'; break;
            case 'text': className = 'MetaText'; break;
            case 'twitterhandle': className = 'MetaTwitterHandle'; break;
            case 'url': className = 'MetaUrl'; break;
        }
        return className;
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

        var meta = MetaData.getValue(payload.field);
        console.log(topic, payload, meta, _METADATA_DATA.selectedData);

        if((meta && meta.slug == payload.field) || meta === null){
            currFormData = {};
            currFormData.value = payload.formData.value;
            console.log(currFormData.value);
            formVal = currFormData.value;

            currFormData.projectId = _CS_Get_Project_ID();
            //@todo: get addMetaKey working
            // currFormData.addMetaKey = $('.add-to-template #addMetaKey').is(":checked");
            // console.log(currFormData.addMetaKey);

            // @todo; If fields set, add them to currFormData [field, slug, sort, type]
            if(meta){
                currFormData.type = meta.type;
                currFormData.field = meta.field;
                currFormData.slug = meta.slug;
                currFormData.sort = meta.sort;
            }

            if(typeof currFormData.type == 'undefined' && typeof _METADATA_DATA.selectedData != 'undefined'){
                currFormData.type = _METADATA_DATA.selectedData.type;
                currFormData.field = _METADATA_DATA.selectedData.field;
                currFormData.slug = _METADATA_DATA.selectedData.slug;
                // @todo: Create a function that calculates the current sort index
            }

            currFormData.metaObject = _getMetaClass(currFormData.type);

            // Display what a user sees when a change has been made to the form
            var cachedValidation = MetaData.getValidationFromCache(currFormData.slug, currFormData.value);
            console.log(cachedValidation);
            if(cachedValidation && typeof formIcons.validate != 'undefined'){
                if(cachedValidation.results.response.success === true){
                    // Set validate link to success
                    formIcons.validate.$.find('.fa').addClass('fa-check').removeClass('fa-spin fa-spinner fa-exclamation-triangle');
                    formIcons.validate.$.addClass('success disable').removeClass('active error');
                } else {
                    // Make sure validate link is default
                    formIcons.validate.$.find('.fa').addClass('fa-exclamation-triangle').removeClass('fa-spin fa-spinner fa-check');
                    formIcons.validate.$.addClass('error').removeClass('success disable active');
                }
            } else {
                formIcons.validate.$.find('.fa').addClass('fa-check').removeClass('fa-spin fa-spinner fa-exclamation-triangle');
                formIcons.validate.$.removeClass('success disable active error');
            }
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
        var inputField = 'input[type=text]';
        var selectField = 'select';

        var metaTypes = [];
        $(BindedBox.selector + " .tab-form select option").each(function(i, item){
            var $item = $(item);
            var val = $item.val().trim();
            if(val != '') metaTypes.push(val);
        });

        // Must not be empty
        if(meta.field === '') {
            if(errorFields.indexOf(inputField) === -1) errorFields.push(inputField);
            logs.errors.push('Meta key can\'t be an empty value');
        }

        // Must be at least 3 characters long
        if(meta.field.length < 3){
            if(errorFields.indexOf(inputField) === -1) errorFields.push(inputField);
            logs.errors.push('Meta key must be 3 characters or more');
        }

        // Must be less than 32 characters long
        if(meta.field.length > 32){
            if(errorFields.indexOf(inputField) === -1) errorFields.push(inputField);
            logs.errors.push('Meta keys have a 32 character limit');
        }

        // Must be unique
        if(typeof _METADATA[meta.slug] !== 'undefined'){
            if(errorFields.indexOf(inputField) === -1) errorFields.push(inputField);
            logs.errors.push('Meta key slugs must be unique');
        }

        // Slug must be valid & unique
        var found = false;
        for(var i in _METADATA){
            if(meta.field.toLowerCase() == _METADATA[i].field) found = true;
        }
        if(found){
            if(errorFields.indexOf(inputField) === -1) errorFields.push(inputField);
            logs.errors.push('Meta keys must be unique');
        }

        // Must start with letter
        if(typeof meta.slug[0] != 'undefined' && meta.slug[0].match(/[a-z]/i) === null){
            if(errorFields.indexOf(inputField) === -1) errorFields.push(inputField);
            logs.errors.push('Meta key name must begin with a letter');
        }

        // Must not be empty
        if(meta.type === '') {
            if(errorFields.indexOf(selectField) === -1) errorFields.push(selectField);
            logs.errors.push('A valid "type" must be selected');
        }

        // Type must be valid
        if(metaTypes.indexOf(meta.type) === -1){
            if(errorFields.indexOf(selectField) === -1) errorFields.push(selectField);
            logs.errors.push('Invalid meta type provided');
        }

        return {
            response : {
                errorFields : errorFields,
                success : errorFields.length > 0
            },
            logs : logs,
            errors : logs.errors.length > 0
        };
    }

    function _handleClickSubmitNewMetaKeyForm(e){
        e.preventDefault();
        var meta = {
                field : $tabFormInput.val().trim().capitalize(),
                type : $tabFormSelect.val(),
                value : null,
                formatted : null,
                format: null
            };

        meta.slug = removeSpecialChars(meta.field);
        meta.slug = meta.slug.toCamelCase();

        // Validate
        var validationResponse = _validateNewField(meta);

        var messageBoxContext = 'add_meta_validation';
        _closeMessage(messageBoxContext);

        if(validationResponse.errors === false){
            PubSub.publish('bindedBox.tabs.metadata.addNewTriggered', meta);
            _resetTabForm(true);
        } else {
            _resetTabForm(false);
            $tabForm.find(validationResponse.response.errorFields[0]).addClass('error');
            _setMessage('error', validationResponse.logs.errors[0], messageBoxContext);
            console.error(validationResponse.logs.errors[0]);

        }
        //console.log(meta);
        return false;
    }

    function _resetTabForm(clearVals){
        // var $tabForm = $('form.tab-form'),
        //     $input = $tabForm.find('input'),
        //     $select = $tabForm.find('select');
        if(typeof clearVals == 'undefined') clearVals = true;
        clearVals = Boolean(clearVals);
        $tabForm.find('select,  input').removeClass('error');
        if(clearVals){
            $tabFormInput.val('');
            $tabFormSelect.find('option').removeAttr('selected');
        }
    }

    function _handleClickEditMetaValue(e){
        e.preventDefault();
        _showForm();
        return false;
    }

    function _handleMetaDataUpdated(topic, payload){
        //console.log(payload);
        // update the html for the meta entry
        // Generate HTML
        var HTML = _renderMetaListRow(payload.data);
        var innerHTML = $(HTML).html();

        //
        var $entry = $metaList.find('.entry[data-slug=' + payload.data.slug + ']');
        if($entry.length > 0){
            $entry.html(innerHTML);
        } else {
            $metaList.append(HTML);
        }
        _updateMetaCount();
        _renderDetails();
    }


    function _setMessage(type, message, context){
        if(message && message.trim() != ''){
            messageBoxOpen = true;
            var classes = ['error','success'].indexOf(type) === -1 ? 'general' : type;

            $messageContainer.removeClass('error success');
            $messageContainer.attr('data-context',context);
            $messageContainer.addClass(classes + ' show');
            $messageContainer.find('.message').html(message);
            $(document).on('click', BindedBox.selector + ' ' + messageContainerSelector + ' .fa-close', _handleClickCloseMessage);
        }
        return false;
    }

    /**
     * Close message box
     * @param context Context in which to close message. Will not close message if data-context doesn't match
     * @returns {boolean}
     * @private
     */
    function _closeMessage(context){
        if(messageBoxOpen){
            var currentContext = $messageContainer.attr('data-context');
            var runClose = true;
            if(context){
                runClose = currentContext == context;
            }

            console.log(context);
            console.log(currentContext);
            console.log(runClose);

            if(runClose){
                messageBoxOpen = false;
                $messageContainer.attr('data-context','');
                $messageContainer.removeClass('show error success general');
                $messageContainer.find('.message').html('');
                $(document).off('click', BindedBox.selector + ' ' + messageContainerSelector + ' .fa-close', _handleClickCloseMessage);
            }
        }
        return false;
    }

    function _setOption(option, value){
        options[option] = value;
        return true;
    }

    function _getOption(option){
        return typeof options[option] == 'undefined' ? undefined : options[option];
    }

    function _handleClickCloseMessage(e){
        e.preventDefault();
        _closeMessage();
    }

    function _handleStateChange(topic, payload){
        var parsedTopic = BindedBox.parseAppTopic(topic);
        if(parsedTopic.isValid) {
            switch (parsedTopic.map.entity){
                case 'settings':
                    if(_isActiveSlide()) {
                        _render();
                        _activateListeners();
                    } else _deactivateListeners();
                    break;
                case 'meta':
                    if(_isActiveSlide()){

                    }
                    break;
            }
        }

    }

    function _isActiveSlide(){
        return _getOption('slideName') == BindedBox.getCurrent('settings','slide');
    }

    function _activateListeners(){
        if(!_listenersActive) {
            _listenersActive = true;
            $(document).on('click', '.tabbed-content.metadata .meta-fields .entry', _metadataEntrySelected);
            $(document).on('click', '.tabbed-content.metadata .tab-form button[type=submit]', _handleClickSubmitNewMetaKeyForm);
            $(document).on('click', '.tabbed-content.metadata .meta-entry.value .fa-pencil', _handleClickEditMetaValue);
            PubSub.subscribe('bindedBox.tabs.metadata.slugSelected', _onMetaDataSelected);
            PubSub.subscribe('bindedBox.tabs.metadata.addNewTriggered', _onMetaDataAdding);
            PubSub.subscribe(MetaData.pubSubRoot + 'update.updated', _handleMetaDataUpdated);
        }
        return false;
    }

    function _deactivateListeners(){
        if(_listenersActive) {
            _listenersActive = false;
            $(document).off('click', '.tabbed-content.metadata .meta-fields .entry', _metadataEntrySelected);
            $(document).off('click', '.tabbed-content.metadata .tab-form button[type=submit]', _handleClickSubmitNewMetaKeyForm);
            $(document).off('click', '.tabbed-content.metadata .meta-entry.value .fa-pencil', _handleClickEditMetaValue);
            PubSub.unsubscribe('bindedBox.tabs.metadata.slugSelected', _onMetaDataSelected);
            PubSub.unsubscribe('bindedBox.tabs.metadata.addNewTriggered', _onMetaDataAdding);
            PubSub.unsubscribe(MetaData.pubSubRoot + 'update.updated', _handleMetaDataUpdated);
            _closeMessage();
            _hideForm();
        }
        return false;
    }

    _initialize();

    return {
        activate : _activateListeners,
        deactivate : _deactivateListeners,
        updateMeta : _updateMeta,
        showForm : _showForm,
        hideForm : _hideForm,
        getOption : _getOption,
        setOption : _setOption,
        isActiveSlide : _isActiveSlide,
        enableFormSubmit : _enableFormSubmit,
        disableFormSubmit : _disableFormSubmit,
        setMessage : _setMessage,
        closeMessage : _closeMessage
    };
})();
