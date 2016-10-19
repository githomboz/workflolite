<div class="main-mid-section clearfix">
    <div class="main-mid-section-inner clearfix">

        <h1><i class="fa fa-users"></i>Interested Parties</h1>
        <h4>Manage this job's contacts and communication options.</h4>
        <div class="inner-nav-btns">
            <a href="#" class="btn-sync-contacts btn"><i class="fa fa-refresh"></i> Sync Contacts</a>
        </div>

        <span class="last-sync">Last sync with <a href="#">Google Contacts</a>: <span class="datetime">8-24-2016 @ 12:02am</span> </span>
        <input type="text" class="search-contacts" placeholder="Search contacts by name" />
        <?php $currentContacts = $this->job->getContacts();

        $currentContactIds = array();
        foreach($currentContacts as $contact) $currentContactIds[] = (string) $contact->id();

        ?>
        <div class="people-list" data-current_contacts='<?php echo json_encode($currentContactIds) ?>'>

            <?php //var_dump($this->organization->searchContactsByName('J')); ?>

            <?php $roles = (array) $this->job->getValue('workflow')->getValue('roles'); $job = $this->job;?>

            <div class="people-form entry main-form clearfix">
                <input type="hidden" name="contactId" value="" />
                <div class="form-input"><input type="text" name="name" placeholder="Contact Name" /></div>
                <div class="form-input">
                    <label>Select Role: </label>
                    <select name="role">
                        <option value="">Select Role</option>
                        <?php foreach($roles as $role){ ?>
                          <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-input icon"><i class="fa fa-envelope"></i> <input type="text" name="email" placeholder="Email" /></div>
                <div class="form-input icon"><i class="fa fa-phone"></i> <input type="text" name="phone" placeholder="Home / Work Phone" /></div>
                <div class="form-input icon"><i class="fa fa-mobile"></i> <input type="text" name="mobile" placeholder="Mobile" /></div>
                <div class="contact-settings clearfix">
                    <span class="setting"><input type="checkbox" name="email_updates" /> Email Updates</span>
                    <span class="setting"><input type="checkbox" name="sms_updates" /> SMS Updates</span>
                </div>
                <button type="submit" class="btn submit"><i class="fa fa-plus"></i> Add Contact to Job</button>
            </div>
            <?php foreach($currentContacts as $i => $contact){
                include 'widgets/_people-contact-include.php';
            } ?>
        </div>
    </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->
<script type="text/javascript">

    CS_ERRORS = [];

    function _CS_getCurrentContacts(){
        return $(".people-list").data('current_contacts');
    }

    $(document).on('click', '.people-form button.submit', function(){
        var $button = $(this),
          $form = $button.parents('.people-form'),
          contact = {
              organizationId : _CS_Get_Organization_ID(),
              jobId : _CS_Get_Job_ID(),
              contactId : $form.find('[name=contactId]').val(),
              name : $form.find('[name=name]').val(),
              role : $form.find('[name=role]').val(),
              email : $form.find('[name=email]').val(),
              phone : $form.find('[name=phone]').val(),
              mobile : $form.find('[name=mobile]').val(),
              emailUpdates : $form.find('[name=email_updates]').is(":checked"),
              smsUpdates : $form.find('[name=sms_updates]').is(":checked")
          }, isValid = false, action = null;

        action = $form.is('.main-form') ? 'add' : 'update';

        isValid = _validateAddContactData(contact, action);

        if(isValid){
            if(action == 'add'){
                // Add
                _ajaxAddContact(contact);
            } else {
                // Update
                _ajaxUpdateContact(contact);
            }
        } else {
            var error = CS_ERRORS[CS_ERRORS.length - 1];
            alert(error);
            CS_MessageBox.add(error);
        }

    });

    $(document).on("click", ".people.entry .fa-pencil", function(e) {
        var $this = $(this), $contactContainer = $this.parents('.people.entry');
        $contactContainer.toggleClass('edit-mode');
    });

    $(document).on("click", ".people.entry .fa-times", function(e){
        var $this = $(this), post = {
            contactId: $this.attr('href').split('-')[1],
            jobId: _CS_Get_Job_ID()
        };
        if(confirm('Are you sure you want to remove this contact the job?')){
            CS_API.call(
                '/ajax/remove_contact',
                function(){
                  // before
                },
              function(data){
                  if(data.errors == false) {
                      PubSub.publish('contactsChange.contactRemoved', post);
                  }
              },
              function(){
                  // error
              },
              post
              ,
              {
                  method: 'POST',
                  preferCache : false
              }
            );
        }
    });

    function _validateAddContactData(data, action){
        var fieldsNotSet = [],
          action = action || 'add';
        for(var field in data){
            if(typeof data[field] == 'string' && data[field].trim() == '' && ['phone','mobile'].indexOf(field) == -1) fieldsNotSet.push(field);
        }

        if(fieldsNotSet.length > 0) CS_ERRORS.push('Required fields not set');

        if(action == 'add'){
            var duplicateFound = false;
            if(_CS_getCurrentContacts().indexOf(data.contactId) >= 0){
                duplicateFound = true;
                CS_ERRORS.push('Duplicate contact found');
            }
        }

        if(fieldsNotSet.length > 0 || duplicateFound) return false;
        return true;
    }

    function _ajaxAddContact(post){
        CS_API.call(
          '/ajax/add_contact',
          function(){
              // Before send
          },
          function(data){
              if(data.errors == false){
                  if(data.response.success){
                      _resetFormPeopleAddContact();
                      PubSub.publish('contactsChange.contactAdded', data.response);
                  } else {
                      console.log('An error has occurred while trying to create your contact');
                  }
              } else {

              }
          },
          function(){
              // Errors
          },
          post,
          {
              method: 'POST',
              preferCache : false
          }
        );
    }

    function _ajaxUpdateContact(post){
        CS_API.call(
          '/ajax/update_contact',
          function(){
              // Before send
          },
          function(data){
              if(data.errors == false){
                  if(data.response.success){
                      PubSub.publish('contactsChange.contactUpdated', data.response);
                  } else {
                      console.log('An error has occurred while trying to update your contact');
                  }
              } else {

              }
          },
          function(){
              // Errors
          },
          post,
          {
              method: 'POST',
              preferCache : false
          }
        );
    }

    function _updateHtmlSidebarContactAdded(topic, data){
        if(typeof data.sidebar_html != 'undefined'){
            $(".sidepanel .contact-list").append(data.sidebar_html);
        }
    }

    function _updateHtmlPeopleContactAdded(topic, data){
        if(typeof data.people_html != 'undefined'){
            $(".people-list").append(data.people_html);
        }
    }

    function _resetFormPeopleAddContact() {
        var $form = $(".people-list .people-form.main-form");

        $form.find('[name=contactId]').val('');
        $form.find('[name=name]').val('');
        $form.find('[name=role]').val('');
        $form.find('[name=email]').val('');
        $form.find('[name=phone]').val('');
        $form.find('[name=mobile]').val('');
        $form.find('[name=email_updates]').prop('checked', false);
        $form.find('[name=sms_updates]').prop('checked', false);
        $(".search-contacts").val('');
    }


    var CONTACTS_CACHE = [];

    function _people_get_contact(id){
        for( var i in CONTACTS_CACHE){
            if(CONTACTS_CACHE[i].contactId == id) return CONTACTS_CACHE[i];
        }
    }

    $(".search-contacts").autocomplete({
        minLength : 2,
        source : function(request, response){
            CS_API.call(
                '/ajax/search_contacts',
                function(){
                  // before
                },
                function(data){
                    CONTACTS_CACHE = data.response;
                    var matches = [];
                    for(var i in data.response) matches.push({
                      label : data.response[i].name,
                      value : data.response[i].contactId
                    });
                    response(matches);
                },
                function() {
                    // error
                },
                {
                    organizationId : _CS_Get_Organization_ID(),
                    term : request.term
                },
                {
                    method: 'POST',
                    preferCache : false
                }
            )
        },
        select : function(event, ui){
            $(this).val(ui.item.label);
            PubSub.publish('searchForm.selectedContact', _people_get_contact(ui.item.value));
            return false;
        }
    });

    function _peopleSearchContactsItemSelected(topic, data){
        //console.log(CONTACTS_CACHE, data);
            var $form = $(".people-list .people-form.main-form");

            $form.find('[name=contactId]').val(data.contactId);
            $form.find('[name=name]').val(data.name);
            $form.find('[name=role]').val('');
            $form.find('[name=email]').val(data.email);
            $form.find('[name=phone]').val(data.phone);
            $form.find('[name=mobile]').val(data.mobile);
            $form.find('[name=email_updates]').prop('checked', false);
            $form.find('[name=sms_updates]').prop('checked', false);
    }

    function _updateHTMLPeopleRemoveContact(topic, data){
        $(".people.contact-" + data.contactId).fadeOut();
    }

    function _updateHTMLSidebarRemoveContact(topic, data){
        $(".sidepanel .contact-" + data.contactId).fadeOut();
    }

    function _updateHTMLUpdateCurrentContactsList(topic, data){
        var $peopleList = $(".people-list"), currentContacts = $peopleList.data('current_contacts');
        console.log(currentContacts, data);
        currentContacts.push(data.contactId);
        $peopleList.attr('data-current_contacts', JSON.stringify(currentContacts));
    }

    PubSub.subscribe('searchForm.selectedContact', _peopleSearchContactsItemSelected);

    PubSub.subscribe('contactsChange.contactAdded', _updateHtmlSidebarContactAdded);
    PubSub.subscribe('contactsChange.contactAdded', _updateHtmlPeopleContactAdded);
    PubSub.subscribe('contactsChange.contactAdded', _updateHTMLUpdateCurrentContactsList);
    PubSub.subscribe('contactsChange.contactRemoved', _updateHTMLPeopleRemoveContact);
    PubSub.subscribe('contactsChange.contactRemoved', _updateHTMLSidebarRemoveContact);

</script>