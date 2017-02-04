<h1>Confirmation Report</h1>
<p>Please review the following data. Then approve or deny this request.</p>

<?php
$data = isset($data) ? $data : null;
?>

<pre>
<?php var_dump(json_decode(json_encode($data),true)); ?>
</pre>

<p>Please select <a href="http://workflolite.com/confirmations/<?php echo $data['confirmationId'] ?>?action=approve">APPROVE</a>
  or
  <a href="http://workflolite.com/confirmations/<?php echo $data['confirmationId'] ?>?action=deny">DENY</a> to continue.
</p>




