<?php if(!isset($metaDataTypes)) $metaDataTypes = Workflow::MetaDataTypes(); ?>
<form class="type-form <?php $dataType = 'dateTime'; echo $dataType; ?>">
  <div class="main-fields">
    <?php //var_dump($metaDataTypes[$dataType]); ?>
    <label for="">Format</label>
    <select name="format">
      <?php foreach($metaDataTypes[$dataType]['options']['formats'] as $format){ ?>
        <option value="<?php echo $format ?>"><?php echo date($format, strtotime('4/9/2016 3:02 pm')) ?></option>
      <?php } ?>
    </select>
  </div>
</form>
