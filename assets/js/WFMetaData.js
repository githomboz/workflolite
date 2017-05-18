/**
 * Created by benezerlancelot on 5/17/17.
 */
var MetaData = (function(){

    var pubsubRoot = 'metaData.';
    var runSaveUponSuccessfulValidation = false;

    function _attemptUpdate(field, fieldData){
        // PubSub.publish('metadata.update.pending', field);
        runSaveUponSuccessfulValidation = true;
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
                PubSub.publish(pubsubRoot + 'update.validate.before', {
                    fieldData : fieldData
                });
            },
            function(data){
                PubSub.publish(pubsubRoot + 'update.validate.response', {
                    data : data,
                    fieldData : fieldData
                });
            },
            function(){
                PubSub.publish(pubsubRoot + 'update.error', {
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

    function _apiSaveMeta(fieldData){
        CS_API.call('ajax/save_meta_field',
            function(){
                PubSub.publish(pubsubRoot + 'update.save.before', {
                    fieldData : fieldData
                });
            },
            function(data){
                PubSub.publish(pubsubRoot + 'update.save.response', {
                    data : data,
                    fieldData : fieldData
                });
            },
            function(){
                PubSub.publish(pubsubRoot + 'update.error', {
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
        PubSub.publish(pubsubRoot + 'update.updated', {
            field : field,
            data : fieldData
        });
    }

    function _getValue(field){
        return typeof _METADATA[field] != 'undefined' ? _METADATA[field] : null;
    }

    function init(){
        PubSub.subscribe(pubsubRoot + 'update.validate.response', _handleValidationAPIResponse);
        PubSub.subscribe(pubsubRoot + 'update.save.response', _handleSaveAPIResponse);
    }

    init();

    return {
        pubSubRoot      : pubsubRoot,
        setValue        : _setNewValue,
        getValue        : _getValue,
        validate        : _apiValidateMeta,
        trySave         : _attemptUpdate
    }
})();