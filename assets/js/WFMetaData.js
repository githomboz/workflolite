/**
 * Created by benezerlancelot on 5/17/17.
 */
var MetaData = (function(){

    var pubSubRoot = 'metaData.';
    var runSaveUponSuccessfulValidation = false;

    function _attemptUpdate(field, fieldData){
        // PubSub.publish('metadata.update.pending', field);
        runSaveUponSuccessfulValidation = true;
        _apiValidateMeta(fieldData);
        return true;
    }

    function _handleValidationAPIResponse(topic, payload){
        var successful = payload.data.response.success == true;
        if(typeof payload.fieldData != 'undefined'){
            if(successful && runSaveUponSuccessfulValidation) {
                _apiSaveMeta(payload.fieldData);
            }

        } else {
            console.error('No fieldData passed along with api payload');
        }
    }

    function _handleSaveAPIResponse(topic, payload){
        // Upon success, run _setNewValue with new data

        // Set back to default value after run.
        runSaveUponSuccessfulValidation = false;
    }

    function _apiValidateMeta(fieldData){
        CS_API.call('ajax/validate_meta_field',
            function(){
                PubSub.publish(pubSubRoot + 'update.validate.before', {
                    fieldData : fieldData
                });
            },
            function(data){
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

    function _apiSaveMeta(fieldData){
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
    }

    function _setNewValue(field, fieldData){
        $.extend(_METADATA[field], {}, fieldData);

        // Notify interested parties in the new field value
        PubSub.publish(pubSubRoot + 'update.updated', {
            field : field,
            data : fieldData
        });
    }

    function _getValue(field){
        return typeof _METADATA[field] != 'undefined' ? _METADATA[field] : null;
    }

    function init(){
        PubSub.subscribe(pubSubRoot + 'update.validate.response', _handleValidationAPIResponse);
        PubSub.subscribe(pubSubRoot + 'update.save.response', _handleSaveAPIResponse);
    }

    init();

    return {
        pubSubRoot      : pubSubRoot,
        setValue        : _setNewValue,
        getValue        : _getValue,
        validate        : _apiValidateMeta,
        trySave         : _attemptUpdate
    }
})();