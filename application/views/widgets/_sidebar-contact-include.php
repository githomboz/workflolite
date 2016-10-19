<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/18/16
 * Time: 11:10 AM
 */
?>
<div class="contact-entry clearfix contact-<?php echo $contact->id(); ?>">
  <div class="image">
    <img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" />
  </div>
  <h3><?php echo $contact->getValue('name') ?></h3>
  <span class="role"><?php echo $contact->getValue('role') ?></span>
</div>

