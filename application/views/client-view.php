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
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/pubsub.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/jquery-3.1.1.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_API.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/standardizr.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js')?>/setTimeout.polyfill.js"></script>
  <link rel="shortcut icon" href="<?php echo base_url()?>favicon.png" type="image/x-icon">
  <link rel="icon" href="<?php echo base_url()?>favicon.png" type="image/x-icon">
  <title><?php echo config_item('site_name'); if(isset($this->page_header['header'])) echo '| '. $this->page_header['header'] ?></title>
</head>
<body>
<div class="client-view-container">
  <?php include 'widgets/client-view-include.php'; ?>
</div>

</body>
</html>
