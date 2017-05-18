/**
 * Created by benezerlancelot on 5/17/17.
 */
var SlideMetadata = (function(){

    var $form = $(".tabbed-content.metadata form.set-meta-value");
    var $btn = $form.find('button');
    var $details = $('.column-details');

    var _METADATA_DATA = {
        unsavedChanges : false,
        showForm : false,
        listSelected : false,
        selectedEntry : null,
        selectedData : null,
        listChanged : true, //(typeof _METADATA != 'undefined')
        actionMode : null, // update, add, remove
        formEnabled : false,
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
        $(".js-us-phone-mask").mask("(999) 999-9999", {autoclear: false});
        $(".js-twitter-handle-mask").mask("@***************", {autoclear: false});

    }

    function _setSelectedField(fieldData){
        _METADATA_DATA.selectedData = fieldData;
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
        // Mark list as selected
        _METADATA_DATA.listSelected = true;
        // Mark current entry as selected
        _METADATA_DATA.selectedEntry = slug;
        _METADATA_DATA.selectedData = _METADATA[slug];
        // Run Render
        _render();
    }

    function _onMetaDataAdding(topic, payload){
        // Mark list as not selected
        _METADATA_DATA.listSelected = false;
        // Mark current entry as not selected
        _METADATA_DATA.selectedEntry = null;
        // Run Render
        _setSelectedField(payload);
        _render();
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
    }

    function _renderDetails(){
        console.log(_METADATA_DATA);
        if(_METADATA_DATA.selectedData){
            $details.find('h2').html(_METADATA_DATA.selectedData.field);
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
    }

    function _drawFormField(data){
        data = data || _METADATA_DATA.selectedData;
        var html = '', val = data.value;
        switch(data.type.toLowerCase()){
            case 'boolean':
                val = Boolean(val);
                html += '<input type="checkbox" name="' + data.slug + '"';
                html += val ? ' checked="checked"' : '';
                html += ' />';
                break;
            case 'string':
                html += '<input type="text" name="' + data.slug + '"';
                html += ' placeholder="' + data.field + '"';
                html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                html += ' />';
                break;
            case 'date':
            case 'datetime':
                html += '<input type="text" name="' + data.slug + '_date"';
                html += ' class="js-date-mask"';
                html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                html += ' />';
                if(data.type == 'datetime'){
                    html += '<input type="text" name="' + data.slug + '_time"';
                    html += ' class="js-time-mask"';
                    html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                    html += ' />';
                    html += '<select name="amOrPm"><option value="am">AM</option><option value="pm">PM</option></select>';
                }
                break;
            case 'number':
                html += '<input type="text" name="' + data.slug + '"';
                html += ' placeholder="' + data.field + '"';
                html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                html += ' />';
                html += '<span class="help-details">Value must be numeric.</span>'
                break;
            case 'phone':
                html += '<input type="text" name="' + data.slug + '"';
                html += ' class="js-us-phone-mask"';
                html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                html += ' />';
                break;
            case 'twitterhandle':
                html += '<input type="text" name="' + data.slug + '"';
                html += ' class="js-twitter-handle-mask"';
                html += val ? ' value="' + ( val === null ? '' : val ) + '"' : '';
                html += ' />';
                break;
            case 'text':
                html += '<textarea name="' + data.slug + '"';
                html += ' placeholder="' + data.field + '">' + ( val === null ? '' : val ) + '</textarea>';
                break;
            case 'url':
                html += '<input type="text" name="' + data.slug + '"';
                html += ' placeholder="Enter a valid URL" value="' + ( val === null ? '' : val ) + '" />';
                html += '<span class="help-details">URL must begin with "http://" or "https://" in order to validate.</span>'
                break;
            case 'address':
                var addressFields = ['street','city','state','zip'];

                for(var f in addressFields){
                    switch(addressFields[f]){
                        case 'state':
                            var states = get50States();
                            html += '<select name="' + data.slug + '__' + addressFields[f] + '">';
                            html += '<option>Select State</option>';
                            for(var i in states){
                                html += '<option value="' + i + '"';
                                if(val && val[addressFields[f]] == i) html += ' selected="selected"';
                                html += '>' + states[i] + '</option>';
                            }
                            html += '</select>';
                            break;
                        default:
                            html += '<input type="text" name="' + data.slug + '__' + addressFields[f] + '"';
                            html += ' placeholder="' + addressFields[f].capitalize() + '" value="' + (val && typeof val[addressFields[f]] != 'undefined' ? val[addressFields[f]] : '') + '" />';
                            break;
                    }
                }
                break;
        }

        html += _drawIcons(data);
        $form.find('.inner-form').html(html);

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
                break;
            case 'cancel':
                _hideForm();
                break;
        }
        return false;
    }

    function _handleFormSubmitClick(e){
        e.preventDefault();
    }

    function _showForm(){
        if(_METADATA_DATA.selectedData) _drawFormField();
        $form.show();
        // Hide Value
        $details.find('.meta-entry.value').hide();
        $(document).on('click', BindedBox.selector + ' .icon-set a', _handleFormIconClick);
        $(document).on('click', BindedBox.selector + ' .set-meta-value button[type=submit]', _handleFormSubmitClick);
        //_enableFormSubmit();
    }

    function _hideForm(){
        $form.hide();
        // Show Value

        $details.find('.meta-entry.value').show();
        $(document).off('click', BindedBox.selector + ' .icon-set a', _handleFormIconClick);
        $(document).off('click', BindedBox.selector + ' .set-meta-value button[type=submit]', _handleFormSubmitClick);
        _disableFormSubmit();
    }

    function _enableFormSubmit(){
        _METADATA_DATA.formEnabled = true;
        $form.find('.icon-set').addClass('enabled');
        $btn.prop('disabled',null);
    }

    function _disableFormSubmit(){
        _METADATA_DATA.formEnabled = false;
        $form.find('.icon-set').removeClass('enabled');
        $btn.attr('disabled','disabled');
    }

    function _drawIcons(data){
        var html = '<div class="icon-set ' + (_METADATA_DATA.formEnabled ? 'enabled':'') + '">';
        // Validation icon
        html += '<a href="#" class="enabled-only"><i class="fa fa-check"></i> Validate <span class="extra"></span></a>';
        // Save icon (change indicator)
        html += '<a href="#" class="enabled-only"><i class="fa fa-save"></i> Save <span class="extra"></span></a>';
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
        } else {
            _handleError(validationResponse.logs.errors[0]);
        }
        //console.log(meta);
    }

    function _handleError(error){
        console.error(error);
    }

    function _handleClickEditMetaValue(e){
        e.preventDefault();
        _showForm();
    }

    function _activate(){
        _render();
        $(document).on('click', '.tabbed-content.metadata .meta-fields .entry', _metadataEntrySelected);
        $(document).on('click', '.tabbed-content.metadata .tab-form button[type=submit]', _handleClickSubmitNewMetaKeyForm);
        $(document).on('click', '.tabbed-content.metadata .meta-entry.value .fa-pencil', _handleClickEditMetaValue);
        PubSub.subscribe('bindedBox.tabs.metadata.slugSelected', _onMetaDataSelected);
        PubSub.subscribe('bindedBox.tabs.metadata.addNewTriggered', _onMetaDataAdding);
    }

    function _deactivate(){
        $(document).off('click', '.tabbed-content.metadata .meta-fields .entry', _metadataEntrySelected);
        $(document).off('click', '.tabbed-content.metadata .tab-form button[type=submit]', _handleClickSubmitNewMetaKeyForm);
        $(document).off('click', '.tabbed-content.metadata .meta-entry.value .fa-pencil', _handleClickEditMetaValue);
        PubSub.unsubscribe('bindedBox.tabs.metadata.slugSelected', _onMetaDataSelected);
        PubSub.unsubscribe('bindedBox.tabs.metadata.addNewTriggered', _onMetaDataAdding);
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