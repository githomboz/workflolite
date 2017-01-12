<div class="main-mid-section clearfix">
    <div class="main-mid-section-inner clearfix">


        <?php

        Bytion_SC();

        if(isset($errors) && isset($noActionMessage) && in_array($noActionMessage, $errors)) {
            $index = array_search($noActionMessage, $errors);
            unset($errors[$index]);
        }
        if(!empty($errors)){ ?>
            <h1><i class="fa fa-exclamation-circle"></i> Oops! Something happened.</h1>

            <p><?php echo $errors[0]; ?></p>

        <?php }

        if(isset($isProcessed)) {
            if($isProcessed) {?>
                <h1><i class="fa fa-exclamation-circle"></i> Oops! You're too late.</h1>

                <p>This confirmation has already been processed.</p>
            <?php }
        } elseif(empty($errors) && isset($action) && !$action){ ?>
            <h1><i class="fa fa-check-circle"></i> Review & Approve Information</h1>

            <p>Please review the data below and click on either the "Approve" or "Deny" button.</p>

        <div class="message-content">
            <h2>Under Review: </h2>
            <?php
            if(isset($payload)) {
                $outputted = false;
                if(isset($payloadCallback) && is_callable($payloadCallback)){
                    $val = call_user_func_array($payloadCallback, $payload);
                    if(is_string($val)){
                        $outputted = true;
                        echo $val;
                    } else {
                        $outputted = true;
                        var_dump($val);
                    }
                }
                if(!$outputted){
                    if(is_string($payload)){
                        $outputted = true;
                        echo $payload;
                    } else {
                        var_dump($payload);
                    }
                }
            } ?>
        </div>

        <a href="?action=approve" class="btn btn-style submit"><i class="fa fa-check-square"></i> Approve</a>
        <br />
        <br />
        <a href="?action=deny" class="btn btn-style"><i class="fa fa-times"></i> Deny</a>
        <?php } ?>

    </div>
</div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->