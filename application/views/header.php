<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/css/reset.css"/>
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/styles/styles.css"/>
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/css/jquery-ui.min.css"/>
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/css/alertify.min.css"/>
  <link type="text/css" rel="stylesheet" href="<?php echo base_url()?>assets/css/alertify.default.min.css"/>
<!--  <link type="text/css" rel="stylesheet" href="--><?php //echo base_url()?><!--assets/css/jquery.datetimepicker.min.css"/>-->
<!--  <link type="text/css" rel="stylesheet" href="--><?php //echo base_url()?><!--assets/css/jquery.periodpicker.min.css"/>-->
<!--  <link type="text/css" rel="stylesheet" href="--><?php //echo base_url()?><!--assets/css/jquery.simple-dtpicker.css"/>-->
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
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/jquery.maskedinput.min.js"></script>
<!--  <script type="text/javascript" src="--><?php //echo base_url('assets/js')?><!--/jquery.mousewheel.min.js"></script>-->
<!--  <script type="text/javascript" src="--><?php //echo base_url('assets/js')?><!--/moment.min.js"></script>-->
<!--  <script type="text/javascript" src="--><?php //echo base_url('assets/js')?><!--/jquery.datetimepickerx.min.js"></script>-->
<!--  <script type="text/javascript" src="--><?php //echo base_url('assets/js')?><!--/jquery.periodpicker.min.js"></script>-->
<!--  <script type="text/javascript" src="--><?php //echo base_url('assets/js')?><!--/jquery.simple-dtpicker.js"></script>-->
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/alertify.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/handlebars-v4.0.5.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/standardizr.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/wf.shortcodes.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_API.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_CSSOverride.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_MessageBox.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_EditableContentDivs.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_RenderBuddy.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_WFLogger.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_FormFly.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/setTimeout.polyfill.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/main.js"></script>
  <link rel="shortcut icon" href="<?php echo base_url()?>favicon.png" type="image/x-icon">
  <link rel="icon" href="<?php echo base_url()?>favicon.png" type="image/x-icon">
  <title><?php echo config_item('site_name'); if(isset($this->page_header['header'])) echo '| '. $this->page_header['header'] ?></title>
</head>
<body
  <?php if(UserSession::loggedIn()){ ?>
  data-organization="<?php echo UserSession::Get_Organization()->id()?>"
  data-workflow="<?php if(workflow()) echo workflow()->id()?>"
  data-template="<?php if(template()) echo template()->id()?>"
  data-version="<?php if(template()) echo template()->version()?>"
  data-entity_type="<?php echo entity() ? (entity() instanceof Project ? 'Project' : 'Job') : null?>"
  data-entity="<?php if(entity()) echo entity()->id()?>"
  data-job="<?php if(job()) echo job()->id()?>"
  data-user="<?php echo UserSession::Get_User()->id()?>"
  <?php } ?>
>
<style class="js-styles-override" type="text/css"></style> <!--/ Under penalty of death, do not remove -->
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
        <?php /**  <a href="<?php echo _url("/")?>?rel=main-logo"><img src="<?php echo base_url()?>/assets/temp/main-logo.gif" /></a><?php **/ ?>
      </div><!--/logo-container-->
      <div class="page-title">
          <?php if(project() || job()){ ?>
        <h1><?php echo entity()->getValue('name')?></h1>
        <h3><?php echo project() ? project()->getValue('template')->name('name') : $this->job->getValue('workflow')->getValue('name') ; ?></h3>
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
        <h1>WorkfloLite.com</h1>
        <h2>Job Management & Automation Framework</h2>
      </div>
      <?php } ?>
    </div><!--/main-head-->
  </div><!--/main-header-inner-->
</header>

<?php //var_dump(Template::Get('58379c3ebb222601208817fa')) ?>
<?php //var_dump(Project::Get('58385d60bb222601208817fc')) ?>

<?php if(load_before_content()) echo load_before_content(); ?>

<div id="main-wrap" class="clearfix <?php if(isset($this->page_class)) { if(is_array($this->page_class)) echo join(' ', $this->page_class); else if(is_string($this->page_class)) echo $this->page_class; } ?>">

  <?php if(show_sidebar()) { ?>

<section class="sidepanel js-sidepanel <?php $collapse = isset($this->preCollapseSidePanel) && $this->preCollapseSidePanel == true; if($collapse) echo 'collapse'; ?>">
  <i class="js-toggle js-toggle-sidebar fa fa-chevron-<?php if($collapse) echo 'right'; else echo 'left'; ?>"></i>
  <?php if(UserSession::loggedIn() && (project() || job())) :
    include_once 'widgets/_sidebar-meta-include.php';
    ?>
  <div class="panel">
    <i class="js-send-message fa fa-envelope"></i>
    <h1><i class="fa fa-users"></i> Job Parties</h1>
    <div class="contact-list">
      <?php foreach(entity()->getContacts() as $i => $contact){
        include 'widgets/_sidebar-contact-include.php';
      } ?>
    </div>
  </div><!--/.panel-->
  <?php endif; ?>
</section>
  <?php } // end show_sidebar() ?>
<section class="main-content">
  <?php
  $object = null;
  if(UserSession::loggedIn()) {
    $object = isset($this->navSelected) && in_array($this->navSelected, array('projects', 'projectsInner')) ? 'project' : null; // Check if project or not
    $object = isset($this->navSelected) && !$object && in_array($this->navSelected, array('jobs', 'jobsInner')) ? 'job' : $object; // If not project, check if job
    $object = $object ? $object : 'organization'; // If neither, make org
  }
  if(UserSession::loggedIn()) include_once 'widgets/inner-nav.php';
  if(UserSession::loggedIn() && (project() || job())) :
  include_once 'widgets/send-message.php';
  include_once 'widgets/notes-bubble.php';
  ?>

  <script type="application/javascript">
  </script>
<?php endif; ?>



<?php //if(isset($this->job)) var_dump($this->job->meta()->getAll());  ?>