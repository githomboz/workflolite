<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 11/4/16
 * Time: 12:40 AM
 */
?>

<div class="cs-add-note-form <?php $show_tags = !(!(isset($show_tags) && $show_tags) || !isset($show_tags)); echo $show_tags ? 'show-tags' : 'no-tags' ;  echo isset($additional_classes) ? $additional_classes : '';  ?>">
  <div class="inner-note">
    <div class="avatar">
      <img src="#" />
    </div>
    <div class="note-field" contenteditable="true" data-placeholder="Click here to <strong><em>leave a note</em></strong>" >
    </div>
    <div class="cs-note-tags tags-field clearfix">
    </div>
    <div class="tags-submit">
      <label class="tags-field title"><i class="fa fa-tags"></i> Tags: </label>
      <input class="tags-field field" type="text" placeholder='Press "return" to add' />
      <button class="js-add-note-btn btn-style submit"><i class="fa fa-paper-plane"></i> Post</button>
    </div>
    <a class="toggle-no-tags"><i class="fa fa-tags"></i> Add Tags</a>
  </div>
</div><!--/.cs-add-note-form-->

