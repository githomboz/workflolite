<?php
$data = isset($data) ? $data : null;
?>

<h1>Hey <?php echo $data['contact']['name'] ?>. We appreciate your business!</h1>
<p>Please take a moment to review your order.  This order is currently 'in progress' and will be completed by approximately midnight on <?php echo date('l, F j, Y', $data['meta']['orderDueDate']->sec);?>.</p>

<table>
  <tr>
    <td><strong>Twitter Handle: </strong></td>
    <td><?php echo $data['meta']['twitterHandle'] ?></td>
  </tr>
  <tr>
    <td><strong>Order Date </strong></td>
    <td><?php echo date('n-d-y g:ia', $data['meta']['orderDate']->sec) ?></td>
  </tr>
  <tr>
    <td><strong>Order Amount </strong></td>
    <td><?php echo number_format($data['meta']['orderCount']) ?></td>
  </tr>
  <tr>
    <td><strong>Current Follower Count </strong></td>
    <td><?php echo $data['meta']['accountMeta']['followers'] ?></td>
  </tr>
  <tr>
    <td><strong>Twitter Profile Valid </strong></td>
    <td><?php echo $data['meta']['accountMeta']['followers'] > 0 ? 'TRUE' : 'FALSE' ?></td>
  </tr>
</table>

<p><a href="http://workflolite.com/progress/<?php echo $data['projectId'] ?>">Track your order</a> or receive your updates via SMS by clicking <a href="#">here</a>.</p>

<p>If you are seeing this message by accident, please <a href="#">click here to cancel</a>.</p>





