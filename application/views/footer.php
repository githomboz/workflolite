<div class="clear"></div>
</section><!--/.main-content-->
</div><!--/#main-wrap-->

<footer class="main-footer clearfix">
    <div class="main-footer-inner">
        <p class="copyright"><?php echo config_item('footer_message') ?></p>
    </div><!--/.main-footer-inner-->
  </footer>
</body>
  <script type="text/javascript">
      $(document).ready(function(){
        $(".sidepanel .js-send-message").click(function(e){
          $(".send-message").toggle();
          return false;
        });

        $(".js-toggle-sidebar").click(function(e){
          e.preventDefault();
          var $this = $(this);
          if($this.is(".fa-chevron-left")){
            $this.removeClass("fa-chevron-left").addClass("fa-chevron-right");
            $this.parents('.sidepanel').addClass('collapse');
            PubSub.publish('viewport.change.minimize', {});
          } else {
            $this.removeClass("fa-chevron-right").addClass("fa-chevron-left");
            $this.parents('.sidepanel').removeClass('collapse');
            PubSub.publish('viewport.change.maximize', {});
          }
        });
        
      });

      $(document).on('click', function(event) {
        if (!$(event.target).closest('.cs-notes-box').length) {
          $(".cs-notes-box").hide();
        }
      });
      $(document).on('click', function(event) {
        if (!$(event.target).closest('.cs-send-message').length) {
          $(".cs-send-message").hide();
        }
      });
  </script>
<?php echo get_registered_scripts_tags(false); ?>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_JobsViewer.js"></script>
</html>