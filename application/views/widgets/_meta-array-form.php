<?php if(isset($meta)) { if($meta['value'] instanceof MetaObject) : ?>
  <div class="meta-type-form <?php echo $meta['type'] ?> meta-object-<?php echo $meta['slug'] ?>" data-type="<?php echo $meta['type'] ?>" data-record="<?php $jobId = (string) $entity->id(); echo di_encrypt_s($jobId, salt()); ?>" data-collection="<?php echo di_encrypt_s(Project::CollectionName(), salt()) ?>" data-slug="<?php echo $meta['slug'] ?>" data-interface="<?php echo $meta['value']::className() ?>">
    <div class="array-list">
      <?php //var_dump($meta['value']->get());
      foreach($meta['value']->get() as $key => $value){
        ?>
        <div class="array-group">
          <span class="array-key">
            <input type="text" placeholder="Key" value="<?php echo $key ?>" />
          </span>
          <span class="array-value">
            <input type="text" placeholder="Value" value="<?php echo is_array($value) ? json_encode($value) : $value ?>" />
          </span>
          <a href="#" class="fa fa-minus-circle link-blue"></a>
        </div>
      <?php } ?>
    </div>
    <div class="main array-group">
      <span class="array-key">
        <input type="text" placeholder="Key" value="" />
      </span>
      <span class="array-value">
        <input type="text" placeholder="Value" value="" />
      </span>
      <a href="#" class="fa fa-plus-circle link-blue"></a>
    </div>
    <button type="submit" class="btn-style submit"><i class="fa fa-save"></i> </button>
    <button class="btn-style js-edit-mode"><i class="fa fa-times"></i> </button>
  </div>
<?php endif; } ?>
