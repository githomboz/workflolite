<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/css/reset.css"/>
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/styles/styles.css"/>
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/css/jquery-ui.min.css"/>
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/css/alertify.min.css"/>
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/css/alertify.default.min.css"/>
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
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/pubsub.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/jquery-3.1.1.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/jquery-migrate-3.0.0.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/jquery-ui.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/alertify.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_API.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_MessageBox.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/standardizr.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/setTimeout.polyfill.js"></script>
  <link rel="shortcut icon" href="<?php echo base_url()?>favicon.png" type="image/x-icon">
  <link rel="icon" href="<?php echo base_url()?>favicon.png" type="image/x-icon">
  <title><?php echo config_item('site_name'); if(isset($this->page_header['header'])) echo '| '. $this->page_header['header'] ?></title>
</head>
<body
  <?php if(UserSession::loggedIn()){ ?>
  data-organization="<?php echo UserSession::Get_Organization()->id()?>"
  data-workflow="<?php if(isset($this->workflow)) echo $this->workflow->id()?>"
  data-job="<?php if(isset($this->job)) echo $this->job->id()?>"
  data-user="<?php echo UserSession::Get_User()->id()?>"
  <?php } ?>
>

<header class="main-header clearfix">
  <div class="main-header-inner">
    <div class="upper-head">
      <?php if(UserSession::loggedIn()) include 'widgets/_main-nav.php'?>
      <div class="upper-links">
        <?php if(UserSession::loggedIn()) { ?>
          <a href="#"><i class="fa fa-cog"></i> Settings</a>
          <a href="<?php echo site_url('/logout')?>"> Logout</a>
        <?php } else { ?>
<!--          <a href="--><?php //echo site_url('/login')?><!--"> Log In</a>-->
        <?php } ?>
      </div>
    </div><!--/.upper-head-->
    <div class="main-head clearfix">
      <?php if(UserSession::loggedIn()){ ?>
      <div class="logo-container">
        <a href="<?php echo _url("/")?>?rel=main-logo"><img src="<?php echo base_url()?>/assets/temp/main-logo.gif" /></a>
      </div><!--/logo-container-->
      <div class="page-title">
          <?php if(isset($this->job)){ ?>
        <h1><?php echo $this->job->getValue('name')?></h1>
        <h3><?php echo $this->job->getValue('workflow')->getValue('name')?></h3>
        <?php } else {
            if(isset($this->pageTitle)){ ?>
        <h1 class="<?php if(!isset($this->pageDescription)) echo 'solo '?>"><?php echo $this->pageTitle; ?></h1>
              <?php if(isset($this->pageDescription)) {?>
                <h3><?php echo $this->pageDescription; ?></h3>
              <?php } ?>
          <?php
            }
          } ?>
      </div>
      <div class="search">
        <i class="fa fa-search"></i>
        <input placeholder="Search ..." />
      </div>
      <?php } else { // What to do if user is not logged in?>
      <div class="page-title">
        <h1>Workflo Lite</h1>
        <h3>Workflow Management Software for the Modern Business</h3>
      </div>
      <?php } ?>
    </div><!--/main-head-->
  </div><!--/main-header-inner-->
</header>

<div id="main-wrap" class="clearfix <?php if(isset($this->page_class)) { if(is_array($this->page_class)) echo join(' ', $this->page_class); else if(is_string($this->page_class)) echo $this->page_class; } ?>">

<section class="sidepanel js-sidepanel <?php $collapse = isset($this->preCollapseSidePanel) && $this->preCollapseSidePanel == true; if($collapse) echo 'collapse'; ?>">
  <i class="js-toggle fa fa-chevron-<?php if($collapse) echo 'right'; else echo 'left'; ?>"></i>
  <?php if(UserSession::loggedIn() && isset($this->job)) : ?>
  <div class="panel">
    <h1><i class="fa fa-list"></i> Job Details</h1>
    <div class="job-meta">
      <?php foreach($this->job->getMeta() as $key => $value){
        $metaSettings = $this->workflow->getMetaSettings($key);
        ?>
      <div class="meta-pair">
        <span class="title"><?php echo $metaSettings['field'] ?>: </span>
        <span class="value <?php if((strlen($key) + strlen($value) + 2) > 33) echo 'multi-line'?>"><?php echo $value ?></span>
      </div><!--/.meta-pair-->
      <?php } ?>
    </div><!--/.job-meta-->
  </div><!--/.panel-->
  <div class="panel">
    <i class="js-send-message fa fa-envelope"></i>
    <h1><i class="fa fa-users"></i> Job Contacts</h1>
    <div class="contact-list">
      <?php foreach($this->job->getContacts() as $i => $contact){
        include 'widgets/_sidebar-contact-include.php';
      } ?>
    </div>
  </div><!--/.panel-->
  <?php endif; ?>
</section>
<section class="main-content">
  <?php
  if(UserSession::loggedIn()) $object = in_array($this->navSelected, array('jobs', 'jobsInner')) ? 'job' : 'organization';
  if(UserSession::loggedIn()) include_once 'widgets/inner-nav.php';
  if(UserSession::loggedIn() && isset($this->job)) :
  include_once 'widgets/send-message.php';
  include_once 'widgets/notes-bubble.php';
  ?>

  <script type="application/javascript">
    var DC_Clicks = {};
    $(document).on('click', ".double-click", function(e){
      var $this = $(this), key = md5(this.innerHTML);
      if(typeof DC_Clicks[key] == 'undefined') {DC_Clicks[key] = {}; DC_Clicks[key].count = 1;} else DC_Clicks[key].count++;
      console.log($this, $this.attr('href'));
      DC_Clicks[key].href = $this.attr('href');
      DC_Clicks[key].timeoutId = window.setTimeout(clickAction, 400, key);
      return false;
    });

    function clickAction(key){
      if(!key) return;
      if(DC_Clicks[key].count > 1){ // doubleclick
        window.location = DC_Clicks[key].href;
      } else { // singleclick
        $(".notes-box").toggle();
      }
      DC_Clicks[key].count = 0;
      clearTimeout(DC_Clicks[key].timeoutId);
    }
  </script>
<?php endif; ?>