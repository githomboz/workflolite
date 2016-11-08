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
      <div class="cs-note
      <?php echo (isset($current_author_id) && $current_author_id == (string) $note['author']['id']) ? 'author-me' : '' ?>
      note-<?php echo $note['id']; ?>"
           data-id="<?php echo $note['id']; ?>"
           data-payload='<?php echo json_encode($note); ?>'
      >
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
              if(strpos($paragraph, '<p>') === false) echo '<p>';
              echo $paragraph;
              if(strpos($paragraph, '<p>') === false) echo '</p>';
            }
          }

          ?>
        </div>
        <div class="lower">
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
          <div class="author-links">
            <a href="#" class="js-delete-note" data-id="<?php echo $note['id'] ?>"><i class="fa fa-trash"></i> Delete Note</a>
          </div>
        </div>
      </div>
    <?php }}
  } ?>
</div>

