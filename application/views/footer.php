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
        var $notesBox = $(".notes-box.show");
        if($notesBox.length){
          $notesBox.removeClass('show');
        } else {
          $(".notes-box").addClass('show');
        }
      }
      DC_Clicks[key].count = 0;
      clearTimeout(DC_Clicks[key].timeoutId);
    }

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
          $(".cs-notes-box").removeClass('show');
        }
      });
      $(document).on('click', function(event) {
        if (!$(event.target).closest('.cs-send-message').length) {
          $(".cs-send-message").hide();
        }
      });

      CS_EditableContentDivs.init();

  </script>
<?php echo get_registered_scripts_tags(false); ?>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/CS_JobsViewer.js"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/main.trailing.js"></script>
</html>