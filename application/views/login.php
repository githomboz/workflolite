<div id="main-mid-section" class="clearfix">
    <div class="main-mid-section-inner clearfix">
      <?php include 'widgets/message-box.php' ?>
        <div id="user-tabs" class="bjl-tabs">
            <div class="tabs-content clearfix">
                <form class="tab log-in clearfix" method="post" action="<?php if(isset($redirect_url)) echo '?redirect='.$redirect_url; ?>">
                    <p>Log in to your account.</p>
                    <p class="feedback"></p>
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
                    <label for="remember-cb" class="remember-cb"><input type="checkbox" id="remember-cb" name="remember" /> Remember Me</label>
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