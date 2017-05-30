/**
 * Created by benezerlancelot on 5/17/17.
 */
var MetaData = (function(){

    var pubSubRoot = 'metaData.';
    var runSaveUponSuccessfulValidation = false;
    var validationCache = {};
    var addMetaKeyFieldsToTemplate = [];


    function _autoRun(value){
        if([undefined,null,''].indexOf(value) >= 0) value = true;
        runSaveUponSuccessfulValidation = Boolean(value);
        return false;
    }

    function _isAutoRun(){
        return runSaveUponSuccessfulValidation;
    }

    function _attemptUpdate(field, fieldData){
        // PubSub.publish('metadata.update.pending', field);
        _autoRun(true);
        console.log(PubSub);
        _apiValidateMeta(fieldData);
        return false;
    }

    function _handleValidationAPIResponse(topic, payload){
        var successful = payload.data.response.success == true;
        console.log(payload);
        if(typeof payload.fieldData != 'undefined'){
            if(successful && runSaveUponSuccessfulValidation) {
                console.log('getting here 1?');
                _apiSaveMeta(payload.fieldData);
            } else {
                console.log('getting here 2?');
                // should we handle error again here? it is already handled elsewhere but maybe backup?
            }

        } else {
            console.error('No fieldData passed along with api payload');
        }
        return false;
    }

    function _handleSaveAPIResponse(topic, payload){
        // Upon success, run _setNewValue with new data

        // Set back to default value after run.
        runSaveUponSuccessfulValidation = false;
        return false;
    }

    function _apiValidateMetaResponseHandler(data, fieldData){
        if(data.errors === false){
            PubSub.publish(pubSubRoot + 'update.validate.response', {
                data : data,
                fieldData : fieldData
            });
        } else {
            PubSub.publish(pubSubRoot + 'update.validate.error', {
                context: 'api_validation_error',
                errors: data.errors
            });
            console.error(data.errors[0]);
        }
        return false;
    }

    function _apiValidateMeta(fieldData){
        console.log(fieldData);
        var cachedData = _getValidationFromCache(fieldData.slug, fieldData.value);
        //console.log(cachedData);
        PubSub.publish(pubSubRoot + 'update.validate.before', {
            fieldData : fieldData
        });
        if(cachedData){
            _apiValidateMetaResponseHandler(cachedData.results, fieldData);
        } else {
            CS_API.call('ajax/validate_meta_field',
                function(){
                    // Before.
                },
                function(data){
                    _addValidationToCache(fieldData.slug, fieldData.value, data);
                    _apiValidateMetaResponseHandler(data, fieldData);
                },
                function(){
                    PubSub.publish(pubSubRoot + 'update.validate.error', {
                        context: 'api_request_error',
                        errors: ['API Request Error has occurred']
                    });
                    console.error('An error has occurred');
                },
                fieldData,
                {
                    method: 'POST',
                    preferCache : false
                }
            );
        }
        return false;
    }

    function _apiSaveMeta(fieldData){
        console.log(fieldData);
        CS_API.call('ajax/save_meta_field',
            function(){
                PubSub.publish(pubSubRoot + 'update.save.before', {
                    fieldData : fieldData
                });
            },
            function(data){
                PubSub.publish(pubSubRoot + 'update.save.response', {
                    data : data,
                    fieldData : fieldData
                });
            },
            function(){
                PubSub.publish(pubSubRoot + 'update.save.error', {
                    context: 'api_request_error'
                });
                console.error('An error has occurred');
            },
            fieldData,
            {
                method: 'POST',
                preferCache : false
            }
        );
        return false;
    }

    function _setNewValue(field, fieldData){
        fieldData = _performDataTransformationsForReturn(fieldData);
        //$.extend(_METADATA[field], {}, fieldData);
        // @todo: Set clientView, (format, formatDefault & formatted if applicable), and sort
        // @todo: Unset metaObject and projectId

        $.extend(fieldData, _getFieldTypeDefaults(fieldData.type));

        if(typeof fieldData.projectId != 'undefined') delete fieldData.projectId;
        if(typeof fieldData.metaObject != 'undefined') delete fieldData.metaObject;

        if(typeof fieldData.clientView == 'undefined') fieldData.clientView = true;
        if(typeof fieldData.sort == 'undefined') fieldData.sort = _getNextSortOrder();
        
        
        // Add to _METADATA
        _METADATA[field] = fieldData;
        console.log(_METADATA, field, fieldData);

        // Notify interested parties in the new field value
        PubSub.publish(pubSubRoot + 'update.updated', {
            field : field,
            data : fieldData
        });
        return false;
    }

    function _getFieldTypeDefaults(type){
        var defaults = {};
        switch(type){
            case 'string':
                defaults._ = 32;
                break;
            case 'date':
                defaults.format = 'n/J/Y';
                defaults.formatDefault = 'n/j/Y';
                //@todo; Add this
                break;
        }
        return defaults;
    }

    function _getNextSortOrder(){
        var highestSortOrder = 0;
        for(var slug in _METADATA){
            if(_METADATA[slug].sort >= highestSortOrder) highestSortOrder = _METADATA[slug].sort;
        }
        return highestSortOrder + 1;
    }

    function _performDataTransformationsForReturn(fieldData){
        switch(fieldData.type){
            // for address, rewrite the `formatted` string.
            case 'address':
                fieldData.formatted = fieldData.value.street + ', ';
                fieldData.formatted += fieldData.value.city + ', ';
                fieldData.formatted += fieldData.value.state + ' ';
                fieldData.formatted += fieldData.value.zip;
                break;
        }
        return fieldData;
    }

    function _getValue(field){
        return typeof _METADATA[field] != 'undefined' ? _METADATA[field] : null;
    }

    function _addValidationToCache(slug, value, validationResults){
        var key = md5(slug) + md5(JSON.stringify(value));
        validationCache[key] = {
            slug : slug,
            value : value,
            results : validationResults
        };
        return false;
    }

    function _getValidationFromCache(slug, value){
        var key = md5(slug) + md5(JSON.stringify(value));
        for(var k in validationCache){
            if(k == key){
                return validationCache[k];
            }
        }
        return false;
    }

    function _activate(){
        _deactivate();
        PubSub.subscribe(pubSubRoot + 'update.validate.response', _handleValidationAPIResponse);
        PubSub.subscribe(pubSubRoot + 'update.save.response', _handleSaveAPIResponse);
        return false;
    }

    function _deactivate(){
        PubSub.unsubscribe(pubSubRoot + 'update.validate.response', _handleValidationAPIResponse);
        PubSub.unsubscribe(pubSubRoot + 'update.save.response', _handleSaveAPIResponse);
        return false;
    }

    _activate();

    return {
        pubSubRoot              : pubSubRoot,
        setValue                : _setNewValue,
        getValue                : _getValue,
        validate                : _apiValidateMeta,
        trySave                 : _attemptUpdate,
        validationResponse      : _handleValidationAPIResponse,
        activate                : _activate,
        deactivate              : _deactivate,
        autoRun                 : _autoRun,
        isAutoRun                 : _isAutoRun,
        getValidationFromCache  : _getValidationFromCache
    }
})();