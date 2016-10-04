<div id="main-mid-section" class="clearfix">
    <div class="main-mid-section-inner clearfix">
      <?php include APPPATH . 'views/widgets/message-box.php' ?>

        <div id="user-tabs" class="bjl-tabs">
            <ul class="tab-links clearfix">
              <?php $fa = $this->input->post('form-action'); ?>
                <li><a href="#" class="tlink <?php if(!$fa || $fa == 'login-form-submitted') echo 'active'?>" rel="log-in">Log In</a></li>
            </ul>
            <div class="tabs-content clearfix">
                <form class="tab log-in clearfix" method="post">
                    <div class="form-row clearfix">
                        <label class="l">Email</label>
                        <div class="field profile x">
                            <div class="input">
                                <input type="email" id="lg-email" value="" name="email" />
                            </div>
                        </div>
                    </div>
                    <div class="form-row clearfix">
                        <label class="l">Password</label>
                        <div class="field profile x">
                            <div class="input">
                                <input type="password" id="lg-password" name="password" value="" />
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="form-action" value="login-form-submitted">Log In</button>
                </form><!--/.log-in-->
            </div>
        </div><!--/#user-tabs-->


    </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->
<script type="text/javascript" src="<?php echo base_url('assets/js')?>/tabs.js"></script>
<script type="text/javascript">
    $(document).on('click', '.field .input, .input-field', function(){
        $(this).find('input').focus();
    });
</script>