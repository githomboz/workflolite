<?php if(!isset($metaDataTypes)) $metaDataTypes = Workflow::MetaDataTypes(); ?>
<form class="type-form <?php $dataType = 'time'; echo $dataType; ?>">
  <div class="main-fields">
    <?php //var_dump($metaDataTypes[$dataType]); ?>
    <label for="">Format</label>
    <select name="format">
      <?php foreach($metaDataTypes[$dataType]['options']['formats'] as $format){ ?>
        <option value="<?php echo $format ?>"><?php echo date($format, strtotime('4/9/2013 3:02 pm')) . ' | ' . date($format, strtotime('8/18/2010 5:35 am')) ?></option>
      <?php } ?>
    </select>
  </div>
</form>
