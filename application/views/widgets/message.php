<div class="message-box <?php if(isset($message['classes'])) echo (is_array($message['classes']) ? join(' ', $message['classes']) : $message['classes']);?>">
  <a class="close-btn fa fa-close"></a>
  <div class="content">
    <?php if(isset($message['content'])) echo $message['content'];?>
  </div>
</div>
<style>
  .message-box {
    padding: 14px 16px;
    border: 1px solid #777;
    background: #bababa;
    color: #333;
    margin-bottom: 16px;
    -moz-border-radius: 6px;
    -webkit-border-radius: 6px;
    border-radius: 6px;
    line-height: 1.4em;
    position: relative;
  }

  .message-box .close-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    cursor: pointer;
  }

  .message-box.success {
    border-color: #006e14;
    background: #cdfbd3;
    color: #003a0e;
  }

  .message-box.error {
    border-color: #6e0004;
    background: #dab9c2;
    color: #2f0005;
  }

  .message-box.warning {
    color: #fbf000;
  }

</style>
<?php register_script('message-box.js'); ?>

<script type="text/javascript">
  notifier = (function(){
    var $messageBox = $(".message-box");

    if(!$messageBox.is('.show')) $messageBox.hide();

    function handleUserNotification(messageType, message){
      var type = 'general';
      if(messageType.indexOf('_error') >= 0) type = 'error';
      $messageBox.find('.content').html(message);
      if(['general','success','error'].indexOf(messageType) >= 0) type = messageType;
      $messageBox.addClass(type).slideDown();
    }

    function closeUserNotification(){
      $messageBox.slideUp(function(){
        $messageBox.removeClass('error success show').find('.content').html('');
      });
    }

    $(document).on('click', '.message-box .close-btn', function(e){
      e.preventDefault();

      closeUserNotification();
      return false;
    });

    return {
      handleUserNotification: handleUserNotification,
      closeUserNotification: closeUserNotification
    }
  })();

</script>