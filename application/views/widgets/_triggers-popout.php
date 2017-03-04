<div class="trigger-test-popout boxed">
  <header>
    <h1><i class="fa fa-puzzle-piece"></i> Configure Trigger & Test</h1>
    <ul class="container-nav clearfix">
      <li>
        <a href="#" data-viewport="triggerTestTab"><i class="fa fa-puzzle-piece"></i> Trigger & Test</a>
      </li>
      <li>
        <a href="#" data-viewport="formManageTab"><i class="fa fa-wpforms"></i> Manage Forms</a>
      </li>
      <li>
        <a href="#" data-viewport="marketplaceTab"><i class="fa fa-shopping-cart"></i> Marketplace</a>
      </li>
    </ul>
  </header>
  <div class="main-inner-body">
  </div><!--/.main-inner-body-->
</div><!--/.triggers-popout-->

<?php
if(!isset($popout) || isset($popout) && !in_array($popout, ['main','forms','shop'])) $popout = 'main';
$fileName = APPPATH.'views/widgets/_triggers-popout-'.$popout.'.php';
?>

<script id="triggerTestTab" type="text/x-handlebars-template">
  <?php echo get_include($fileName); ?>
</script>

<script id="formManageTab" type="text/x-handlebars-template">
  Form Management
</script>

<script id="marketplaceTab" type="text/x-handlebars-template">
  Marketplace
</script>

<script type="text/javascript" src="<?php echo base_url('assets/js')?>/WFTriggerPopoutContainer.js"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/WFTriggerTestModule.js"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/WFFormManageModule.js"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/WFMarketplaceModule.js"></script>
