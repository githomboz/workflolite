<form class="type-form <?php $dataType = 'string'; echo $dataType; ?>">
  <div class="main-fields">
    <label for="">Max String Length</label>
    <input type="text" name="maxLengthDefault" value="<?php ?>" />
    <span class="helpful-tip">The maximum length allowable for this string</span>
  </div>
  <a href="#" class="set-default-link">Add / Update Default Values</a>
  <div class="set-default ">
    <label id="">Default Value: </label><input name="defaultValue" />
  </div>
</form>