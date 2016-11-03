<div class="send-message cs-send-message">
  <header>
    <h1>Send a Message</h1>
    <div class="select-template">
      <label for="template">Message Templates: </label>
      <select id="template">
        <option>Standard Updates #1</option>
        <option>Standard Updates #2</option>
      </select>
    </div>
  </header>
  <section class="message-body">
    <div class="message-forms">
      <div class="seg-email">
        <h2><i class="fa fa-envelope"></i> Email Message <span class="disclaimer">(This will be sent to all contacts)</span></h2>
        <input id="email-subject" placeholder="Email Subject" value="We've recieved your order (#{job.orderNumber})" />
          <textarea id="email-copy">Dear {contact.name},

  The order for {job.name} is in progress. The closing date is set to {job.closingDate}.

Very Best,
Jim N Brown
          </textarea>
      </div>
      <div class="seg-sms">
        <h2><i class="fa fa-mobile"></i> SMS Text Message</h2>
        <span class="character-count"><span class="count">140</span> Characters</span>
        <textarea id="sms-copy">We've reached a milestone.  The closing date is {job.closingDate}.  Visit http://wfl.com/n42nsq5</textarea>
      </div>
    </div><!--/.message-forms-->
    <div class="recipients-fields">
      <h2><i class="fa fa-user-plus"></i> Recipients</h2>
      <input class="recipient-name" placeholder="Recipient's Name" />
      <div class="recipient-list">
        <div class="recipient">
          <span class="name">Rick Mayfield</span>
          <a href="#" class="fa fa-times"></a>
        </div>
        <div class="recipient">
          <span class="name">Jim Brown</span>
          <a href="#" class="fa fa-times"></a>
        </div>
        <div class="recipient">
          <span class="name">Laura Edgerton</span>
          <a href="#" class="fa fa-times"></a>
        </div>
        <div class="recipient">
          <span class="name">Phyllis Potes</span>
          <a href="#" class="fa fa-times"></a>
        </div>
        <div class="recipient">
          <span class="name">Don Ward</span>
          <a href="#" class="fa fa-times"></a>
        </div>
        <div class="recipient">
          <span class="name">Billy Chambers</span>
          <a href="#" class="fa fa-times"></a>
        </div>
        <div class="recipient">
          <span class="name">Bob Chambers</span>
          <a href="#" class="fa fa-times"></a>
        </div>
      </div><!--recipient-list-->
    </div>
    <button class="js-send-message"><i class="fa fa-send"></i> Send Message(s)</button>
  </section>
</div><!--/.send-message-->
