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
      <?php if(UserSession::loggedIn()){ ?>
      <div class="logo-container">
        <a href="<?php echo _url("/")?>?rel=main-logo"><img src="<?php echo base_url()?>/assets/temp/main-logo.gif" /></a>
      </div><!--/logo-container-->
      <div class="page-title">
        <h1><?php echo $this->job->getValue('name')?></h1>
        <h3><?php echo $this->job->getValue('workflow')->getValue('name')?></h3>
      </div>
      <div class="search">
        <i class="fa fa-search"></i>
        <input placeholder="Search ..." />
      </div>
      <?php } ?>
    </div><!--/main-head-->
  </div><!--/main-header-inner-->
</header>

<div id="main-wrap" class="clearfix <?php if(isset($this->page_class)) { if(is_array($this->page_class)) echo join(' ', $this->page_class); else if(is_string($this->page_class)) echo $this->page_class; } ?>">

<section class="sidepanel js-sidepanel">
  <i class="js-toggle fa fa-chevron-left"></i>
  <div class="panel">
    <h1><i class="fa fa-list"></i> Job Details</h1>
    <div class="job-meta">
      <?php foreach($this->job->getMeta() as $key => $value){ ?>
      <div class="meta-pair">
        <span class="title"><?php echo $key ?>: </span>
        <span class="value <?php if((strlen($key) + strlen($value) + 2) > 33) echo 'multi-line'?>"><?php echo $value ?></span>
      </div><!--/.meta-pair-->
      <?php } ?>
    </div><!--/.job-meta-->
  </div><!--/.panel-->
  <?php if(UserSession::loggedIn()) { ?>
  <div class="panel">
    <i class="js-send-message fa fa-envelope"></i>
    <h1><i class="fa fa-users"></i> Job Contacts</h1>
    <ul class="contact-list">
      <?php foreach($this->job->getContacts() as $i => $contact){ ?>
      <li class="contact-entry clearfix is-staff">
        <div class="image">
          <img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" />
        </div>
        <h3><?php echo $contact->getValue('name') ?></h3>
        <span class="role"><?php echo $contact->getValue('role') ?></span>
      </li>
      <?php } ?>
    </ul>
  </div><!--/.panel-->
  <?php } ?>
</section>
<section class="main-content">
  <?php include_once 'widgets/send-message.php'?>
  <ul class="inner-nav clearfix">
    <?php foreach(array('tasks','notes','people','time','client-view') as $i => $slug) { $this->page = isset($this->page) ? $this->page : 'tasks';?>
      <li class="<?php
      if($this->page == $slug) echo 'active ';
      if($slug == 'notes') echo ' notes-btn ';
      ?>"><a href="<?php echo site_url('jobs/' . $this->job->id(). '/' . $slug) ?>"><?php echo ucwords(str_replace('-',' ', $slug)) ?></a></li>
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
