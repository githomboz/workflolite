<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/css/reset.css"/>
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/styles/styles.css"/>
  <?php if(offline_mode()){ ?>
    <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/css/font-awesome.min.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/css/fontsOpenSans.css"/>
    <!--[if IE]>
    <script src="<?php echo base_url()?>assets/js/html5shiv.js"></script>
    <![endif]-->
  <?php } else {?>
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,700' rel='stylesheet' type='text/css'>
    <!--[if IE]>
    <script src="https://raw.githubusercontent.com/aFarkas/html5shiv/master/src/html5shiv.js"></script>
    <![endif]-->
  <?php } ?>
  <?php register_script(array('lodash','main')); ?>
  <?php if(isset($this->refresh_page) && is_numeric($this->refresh_page)){ ?><meta http-equiv="refresh" content="<?php echo $this->refresh_page?>" /><?php } // refresh ?>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="format-detection" content="telephone=no">
  <?php if(config_item('set_noindex_nofollow')){ ?>
    <meta name="robots" content="noindex, nofollow">
  <?php } ?>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/jquery-3.1.1.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/standardizr.js"></script>
  <link rel="shortcut icon" href="<?php echo base_url()?>favicon.png" type="image/x-icon">
  <link rel="icon" href="<?php echo base_url()?>favicon.png" type="image/x-icon">
  <title><?php echo config_item('site_name'); if(isset($this->page_header['header'])) echo '| '. $this->page_header['header'] ?></title>
</head>
<body>

<header class="main-header clearfix">
  <div class="main-header-inner">
    <div class="upper-head">
      <nav class="clearfix">
        <ul>
          <li><a href="#">Dashboard</a></li>
          <li><a href="#">Workflows</a></li>
          <li class="active"><a href="#">Jobs</a></li>
          <li><a href="#">Customers</a></li>
          <li><a href="#">Users</a></li>
          <li><a href="#">Search</a></li>
        </ul>
      </nav>
      <div class="upper-links">
        <a href="#"><i class="fa fa-cog"></i> Settings</a>
      </div>
    </div><!--/.upper-head-->
    <div class="main-head clearfix">
      <div class="logo-container">
        <a href="<?php echo _url("/")?>?rel=main-logo"><img src="<?php echo base_url()?>/assets/temp/main-logo.gif" /></a>
      </div><!--/logo-container-->
      <div class="page-title">
        <h1>Frasier 2nd Property Sale</h1>
        <h3>JNBPA Issuing Owners Policy File Processing Checklist</h3>
      </div>
      <div class="search">
        <i class="fa fa-search"></i>
        <input placeholder="Search ..." />
      </div>
    </div><!--/main-head-->
  </div><!--/main-header-inner-->
</header>

<div id="main-wrap" class="clearfix <?php if(isset($this->page_class)) { if(is_array($this->page_class)) echo join(' ', $this->page_class); else if(is_string($this->page_class)) echo $this->page_class; } ?>">

<section class="sidepanel js-sidepanel">
  <i class="js-toggle fa fa-chevron-left"></i>
  <div class="panel">
    <h1><i class="fa fa-list"></i> Job Details</h1>

    <div class="job-meta">
      <div class="meta-pair">
        <span class="title">File Number: </span>
        <span class="value">jnbpa-3921</span>
      </div><!--/.meta-pair-->
      <div class="meta-pair">
        <span class="title">File Received: </span>
        <span class="value">8/26/2016</span>
      </div><!--/.meta-pair-->
      <div class="meta-pair">
        <span class="title">Commitment Due: </span>
        <span class="value">9/10/2016</span>
      </div><!--/.meta-pair-->
      <div class="meta-pair">
        <span class="title">Closing Date: </span>
        <span class="value">10/24/2016</span>
      </div><!--/.meta-pair-->
      <div class="meta-pair">
        <span class="title">Property Address: </span>
        <span class="value block">5836 Candlewood Street, <br />West Palm Beach, FL 33407</span>
      </div><!--/.meta-pair-->
      <div class="meta-pair">
        <span class="title">Lender: </span>
        <span class="value">Wells Fargo</span>
      </div><!--/.meta-pair-->
    </div><!--/.job-meta-->
  </div><!--/.panel-->
  <div class="panel">
    <i class="js-send-message fa fa-envelope"></i>
    <h1><i class="fa fa-users"></i> Job Contacts</h1>
    <ul class="contact-list">
      <li class="contact-entry clearfix is-staff">
        <div class="image">
          <img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" />
        </div>
        <h3>Deanna Courtney</h3>
        <span class="role">Paralegal</span>
      </li>
      <li class="contact-entry clearfix is-staff">
        <div class="image">
          <img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" />
        </div>
        <h3>Ruti McCloy</h3>
        <span class="role">Legal Assistant</span>
      </li>
      <li class="contact-entry clearfix is-primary">
        <div class="image">
          <img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" />
        </div>
        <h3>David Ackoff</h3>
        <span class="role">Buyer / Party 1</span>
      </li>
      <li class="contact-entry clearfix is-primary">
        <div class="image">
          <img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" />
        </div>
        <h3>Linda Ackoff</h3>
        <span class="role">Buyer / Party 1</span>
      </li>
      <li class="contact-entry clearfix">
        <div class="image">
          <img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" />
        </div>
        <h3>Sheryl Duffley</h3>
        <span class="role">Loan Officer</span>
      </li>
      <li class="contact-entry clearfix">
        <div class="image">
          <img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" />
        </div>
        <h3>Melvin Costas</h3>
        <span class="role">Broker</span>
      </li>
      <li class="contact-entry clearfix">
        <div class="image">
          <img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" />
        </div>
        <h3>Regina Cade</h3>
        <span class="role">Buyer's Agent</span>
      </li>
    </ul>
  </div><!--/.panel-->
</section>
<section class="main-content">
  <div class="send-message">
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
  <ul class="inner-nav clearfix">
    <?php foreach(array('tasks','notes','people','time','client-view') as $i => $slug) { $this->page = isset($this->page) ? $this->page : 'tasks';?>
      <li class="<?php
      if($this->page == $slug) echo 'active ';
      if($slug == 'notes') echo ' notes-btn ';
      ?>"><a href="<?php echo site_url($slug) ?>"><?php echo ucwords(str_replace('-',' ', $slug)) ?></a></li>
    <?php } ?>
  </ul>
  <div class="notes-box">
    <div class="bubble">
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
      <a href="#" class="more">more messages <i class="fa fa-caret-down"></i></a>
    </div>
  </div>
