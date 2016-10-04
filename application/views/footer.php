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
        $(".sidepanel .js-send-message").click(function(){
          $(".send-message").toggle();
        });

        $(".js-toggle").click(function(){
          var $this = $(this);
          if($this.is(".fa-chevron-left")){
            $this.removeClass("fa-chevron-left").addClass("fa-chevron-right");
            $this.parents('.sidepanel').addClass('collapse');
          } else {
            $this.removeClass("fa-chevron-right").addClass("fa-chevron-left");
            $this.parents('.sidepanel').removeClass('collapse');
          }

        });

      });
  </script>
<?php echo get_registered_scripts_tags(false); ?>
</html>