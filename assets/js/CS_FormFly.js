/**
 * Created by benezerlancelot on 7/21/17.
 */

var CS_FormFly = (function(){

    var __FFFORMS = [],
        _listenersActive = false
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
                fields      : null
            };
            var encoded = ffform.$form.attr('data-formfly');
            ffform.json = JSON.parse(atob(encoded));
            console.log(ffform, encoded);

            __FFFORMS.push(ffform);
            _activateListeners();
        } else {
            console.error('Error occurred while attempting to register form.  Selector already exists.');
        }
    }

    function _getFormBySelector(selector){
        var index = null;
        for( var i in __FFFORMS ) if(__FFFORMS[i].selector == selector) index = i;
        if(index) return _getForm(index);
    }

    function _handleSubmitBtnClick(e){
        e.preventDefault();
        var $this = $(this),
            $form = $this.parents('form.form-fly'),
            id = $form.attr('id'),
            key = id.split('-')[1],
            FFF = _getFormBySelector('#' + id);
            ;

        console.log(FFF, id);

    }

    function _activateListeners(){
        if(!_listenersActive){
            // Activate Listeners
            _listenersActive = true;

            $(document).on('click', 'form.form-fly button[type=submit]', _handleSubmitBtnClick)

        }
    }

    function _getForm(index){

        var FormFlyForm = function(formData){

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

            // Analyze and render updates
            function _analyzeHTML(){

            }

            // Analyze the state of the form
            function _analyzeState(){

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
            }

            _init(formData);

            return {
                getKey          : _getKey,
                getData         : _getData,
                getFields       : _getFields,
                getElement      : _getElement,
                getAllData      : _getFFData,
                getState        : _getState,
                submit          : _submit,
                applyData       : _applyData,
                analyzeHTML     : _analyzeHTML,
                analyzeState    : _analyzeState,
                enableSubmit    : _enableSubmit,
                disableSubmit   : _disableSubmit
            }

        };

        // Check if form exists in __FFFORMS
        if(typeof __FFFORMS[index] == 'undefined') return null;

        // Get that form's data
        var formData = __FFFORMS[index];

        // return instance
        return new FormFlyForm(formData);
    }

    return {
        registerForm        : _registerForm,
        getFormBySelector   : _getFormBySelector,
        getForm             : _getForm
    }
})();