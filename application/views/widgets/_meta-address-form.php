<form class="type-form <?php $dataType = 'address'; echo $dataType; ?>">
  <div class="main-fields">
    <?php //var_dump($metaDataTypes[$dataType]); ?>
    <label for="">Country</label>
    <select name="country">
      <option value="us">United States</option>
    </select>
  </div>
  <a href="#" class="set-default-link">Add / Update Default Values</a>
  <div class="set-default ">
    <div class="data-type-address data-type-form no-labels">
      <div class="group">
        <label>Address</label><input type="input" name="address" placeholder="Address" />
      </div>
      <div class="group">
        <label>Address 2</label><input type="input" name="address2" placeholder="Address 2" />
      </div>
      <div class="group">
        <input type="input" name="city" placeholder="City" />
        <input type="input" name="state" placeholder="State" />
        <input type="input" name="zip" placeholder="Zip" />
      </div>
    </div>
  </div>
</form>
