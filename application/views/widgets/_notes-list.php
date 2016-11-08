<?php
/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 11/4/16
 * Time: 12:55 AM
 */ ?>

<div class="cs-notes-list">
  <?php if(isset($notes) && is_array($notes)) {

    $notes_limit = isset($notes_limit) ? $notes_limit : null;

    foreach($notes as $i => $note){ if(!$notes_limit || $i < $notes_limit){ ?>
      <div class="cs-note">
        <div class="avatar">
          <img src="#" />
        </div>
        <div class="top-bar">
          <span class="author-narrative">
            <span class="author"><?php echo $note['author']['shortName'] ?></span>
            <span class="verb"><?php echo $note['verb'] ?></span>
            <a href="#" class="noun"><?php echo $note['noun'] ?></a>
          </span>
          <span class="datetime">
            <span class="date"><?php echo date('m-d-Y', strtotime($note['datetime'])) ?></span>
            <span class="time-text">
              at <span class="time"><?php echo date('g:ia', strtotime($note['datetime'])) ?></span>
            </span>
          </span>
        </div>
        <div class="note-content">
          <?php

          $content = $note['content'];

          $content = explode("\n", $content);

          foreach($content as $paragraph){
            if(trim($paragraph) != ''){
              echo '<p>';
              echo $paragraph;
              echo '</p>';
            }
          }

          ?>
        </div>
        <div class="tags">
          <?php if(!empty($note['tags'])){
            echo '<i class="fa fa-tags"></i> Tags: &nbsp;';
            foreach( $note['tags'] as $i => $tag) {
              echo '<a href="?s='.$tag.'">';
              echo $tag;
              echo '</a>';
              if($i < (count($note['tags'])-1)) echo ', ';
            }
          } ?>
        </div>
      </div>
    <?php }}
  } ?>
</div>

