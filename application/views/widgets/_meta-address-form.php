<?php if(isset($meta)) { if($meta['value'] instanceof MetaObject) : ?>
  <div class="meta-type-form <?php echo $meta['type'] ?> meta-object-<?php echo $meta['slug'] ?>" data-type="<?php echo $meta['type'] ?>" data-record="<?php $jobId = (string) job()->id(); echo di_encrypt_s($jobId, salt()); ?>" data-collection="<?php echo di_encrypt_s(Job::CollectionName(), salt()) ?>" data-slug="<?php echo $meta['slug'] ?>" data-interface="<?php echo $meta['value']::className() ?>">
    <div class="data-type-address data-type-form no-labels">
      <div class="group">
        <?php $address = $meta['value']->get() ?>
        <input name="meta_<?php echo $meta['slug'] ?>_street" value="<?php echo $address['street'];?>" />
      </div>
      <div class="group">
        <input name="meta_<?php echo $meta['slug'] ?>_city" value="<?php echo $address['city'];?>" />
        <select name="meta_<?php echo $meta['slug'] ?>_state">
          <option value="">Select</option>
          <?php foreach(MetaAddress::get_states() as $abbr => $state) { ?>
            <option value="<?php echo $abbr ?>" <?php if($address['state'] == $abbr) echo 'selected="selected"'; ?>><?php echo $state ?></option>
          <?php } ?>
        </select>
        <input name="meta_<?php echo $meta['slug'] ?>_zip" value="<?php echo $address['zip'];?>" />
        <button type="submit" class="btn-style submit"><i class="fa fa-save"></i> </button>
        <button class="btn-style js-edit-mode"><i class="fa fa-times"></i> </button>
      </div>
    </div>
</div>
<?php endif; } ?>
