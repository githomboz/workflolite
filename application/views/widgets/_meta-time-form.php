<?php if(isset($meta)) { if($meta['value'] instanceof MetaObject) : ?>
  <div class="meta-type-form <?php echo $meta['type'] ?> meta-object-<?php echo $meta['slug'] ?>" data-type="<?php echo $meta['type'] ?>" data-record="<?php $jobId = (string) job()->id(); echo di_encrypt_s($jobId, salt()); ?>" data-collection="<?php echo di_encrypt_s(Job::CollectionName(), salt()) ?>" data-slug="<?php echo $meta['slug'] ?>" data-interface="<?php echo $meta['value']::className() ?>">
    <input name="meta_<?php echo $meta['slug'] ?>" value="<?php echo $meta['value']->get() != '' ? date('g:i a', strtotime($meta['value']->get())) : '' ?>" />
    <button type="submit" class="btn-style submit"><i class="fa fa-save"></i> </button>
    <button class="btn-style js-edit-mode"><i class="fa fa-times"></i> </button>
  </div>
<?php endif; } ?>
