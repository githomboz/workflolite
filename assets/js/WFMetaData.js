/**
 * Created by benezerlancelot on 5/17/17.
 */
var MetaData = (function(){

    var pubSubRoot = 'metaData.';
    var runSaveUponSuccessfulValidation = false;
    var validationCache = {

    };

    function _autoRun(value){
        if([undefined,null,''].indexOf(value) >= 0) value = true;
        runSaveUponSuccessfulValidation = Boolean(value);
        return false;
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
        $.extend(_METADATA[field], {}, fieldData);

        // Notify interested parties in the new field value
        PubSub.publish(pubSubRoot + 'update.updated', {
            field : field,
            data : fieldData
        });
        return false;
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
        getValidationFromCache  : _getValidationFromCache
    }
})();