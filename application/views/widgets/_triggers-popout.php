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

<script id="listEntries" type="text/x-handlebars-template">
  {"0":{"id":"nslkdfn","type":"lambda","callback":"Bernies::RequestPickUp","name":"Bernie's Deliveries / Request Pickup","description":"Bernie's RESTful API trigger","category":"Mail & Deliveries","tags":["mail","shipping","deliveries"],"author":"Bernies DevShop","authorUrl":"http://www.bernies-deliveries.com/support","averageExecution":1500,"registered":true,"active":false,"registeredUsers":154138,"developerRating":{"support":9.5,"development":8.3},"config":{"help":""},"test":{},"payload":{}},"1":{"id":"fgd4dfs","type":"lambda","callback":"USPS::RequestPickUp","name":"United States Postal Service / Request Pickup","description":"<p>Make an API request to the United States Postal Service to pick up a package on a specific day.</p> <p>Contains a robust completion test.</p>","category":"Mail & Deliveries","tags":["deliveries","mail  ","shipping","USPS"],"author":"Team Workflo","averageExecution":6500,"registered":true,"active":false,"registeredUsers":154138,"developerRating":{"support":9.5,"development":8.3},"config":{"help":""},"test":{},"payload":{}}}
</script>

<script id="infoBox-triggerStyle" type="text/x-handlebars-template">
  <span class="type">{{type}}</span>
    <span class="category">{{category}}</span>
    <h3>{{name}}</h3>
  <div class="content">
    {{{description}}}
  </div>
  <div class="meta">
    <ul>
    <li><em>Tags: </em>
      {{#each tags}}
      <a href="#">{{this}}</a>,
      {{/each}}
    </li>
      <li><em>Developer: </em>{{tryLink url=authorUrl text=author}}</li>
  <li><em>Completion Test: </em>{{hasTest}}</li>
  <li><em>Average Usage: </em>{{avgExec milliseconds=averageExecution}}</li>
  </ul>
  </div><!--/.meta-->
  <div class="fixed-pane">
    {{registerBtn active=active}}
  </div>
</script>

<script type="text/javascript" src="<?php echo base_url('assets/js')?>/WFTriggerPopoutContainer.js"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/WFTriggerTestModule.js"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/WFFormManageModule.js"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/WFMarketplaceModule.js"></script>
