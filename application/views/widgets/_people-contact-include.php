<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/18/16
 * Time: 10:35 AM
 */
?>

<div class="people entry clearfix contact-<?php echo $contact->id() ?>">
  <div class="head-links">
    <a href="#editContact-<?php echo $contact->id() ?>" class="fa fa-pencil"></a> &nbsp;
    <a href="#deleteContact-<?php echo $contact->id() ?>" class="fa fa-times"></a>
  </div>
  <div class="role"><?php echo $contact->getValue('role') ?></div>
  <div class="contact-card">
    <div class="image"><img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" /></div>
    <div class="meta">
      <span class="data name"><?php echo $contact->getValue('name') ?></span>
      <span class="data email"><i class="fa fa-envelope"></i> <?php echo $contact->getValue('email') ?></span>
      <span class="data phone"><i class="fa fa-phone"></i> <?php echo phoneFormat($contact->getValue('phone')) ?></span>
      <span class="data mobile"><i class="fa fa-mobile"></i> <?php echo phoneFormat($contact->getValue('mobile')) ?></span>
    </div>
  </div><!--/.contact-card-->
  <div class="people-form clearfix">
    <input type="hidden" name="contactId" value="<?php echo $contact->id() ?>" />
    <div class="form-input"><input type="text" name="name" placeholder="Contact Name" value="<?php echo $contact->getValue('name') ?>" /></div>
    <div class="form-input">
      <label>Select Role: </label>
      <select name="role">
        <option value="">Select Role</option>
        <?php foreach($roles as $role){ ?>
          <option value="<?php echo $role; ?>"
                  <?php if($role == $contact->getValue('role')) echo 'selected="selected"'; ?>
          ><?php echo $role; ?></option>
        <?php } ?>
      </select>
    </div>
    <div class="form-input icon"><i class="fa fa-envelope"></i> <input type="text" name="email" placeholder="Email" value="<?php echo $contact->getValue('email') ?>" /></div>
    <div class="form-input icon"><i class="fa fa-phone"></i> <input type="text" name="phone" placeholder="Home / Work Phone" value="<?php echo $contact->getValue('phone') ?>" /></div>
    <div class="form-input icon"><i class="fa fa-mobile"></i> <input type="text" name="mobile" placeholder="Mobile" value="<?php echo $contact->getValue('mobile') ?>" /></div>
    <div class="contact-settings clearfix">
      <?php $settings = $contact->getValue('settings'); ?>
      <span class="setting"><input type="checkbox" <?php if(isset($settings['emailUpdates']) && $settings['emailUpdates']) echo 'checked="checked"'; ?> name="email_updates" /> Email Updates</span>
      <span class="setting"><input type="checkbox" <?php if(isset($settings['smsUpdates']) && $settings['smsUpdates']) echo 'checked="checked"'; ?> name="sms_updates" /> SMS Updates</span>
    </div>
    <button type="submit" class="btn submit"><i class="fa fa-save"></i> Update Contact</button>
  </div>

</div>

