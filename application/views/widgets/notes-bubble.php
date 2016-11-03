<div class="notes-box cs-notes-box">
  <div class="bubble">
    <div class="add-note-form">
      <div class="user">
        <?php //var_dump(user(), UserSession::EncodePassword('jahdy')) ?>
        <div class="thumb">
          <img src="" />
        </div>
      </div>
      <div class="form">
        <textarea class="cs-note-field"></textarea>
        <label>Tags: </label><input type="text" class="js-tags-input">
        <button class="btn submit"><i class="fa fa-plus"></i> Add Note</button>
      </div>
    </div>
    <ul class="notes-list">
      <li class="note clearfix">
        <span class="author">Efran Jacobs</span>
        <span class="date">8-28-2016 @ 4:44pm</span>
        <div class="image"><img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" /></div>
        <div class="message">Sure thing, boss!</div>
      </li>
      <li class="note clearfix">
        <span class="author">Jim Brown</span>
        <span class="date">8-28-2016 @ 3:34pm</span>
        <div class="image"><img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" /></div>
        <div class="message">Just got a response and I'm forwarding it to you now.</div>
      </li>
      <li class="note clearfix">
        <span class="author">Jim Brown</span>
        <span class="date">8-28-2016 @ 12:53pm</span>
        <div class="image"><img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" /></div>
        <div class="message">Emailed him; I should hear back soon</div>
      </li>
      <li class="note clearfix">
        <span class="author">Efran Jacobs</span>
        <span class="date">8-28-2016 @ 10:24pm</span>
        <div class="image"><img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" /></div>
        <div class="message">Customer doesn't seem to be responding. I'm going to need to escalate.</div>
      </li>
    </ul>
    <a href="<?php echo site_url('jobs/' . $this->job->id() . '/notes') ?>" class="more">more notes <i class="fa fa-caret-down"></i></a>
  </div>
</div>
