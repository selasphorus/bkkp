<?php
use atc\WHx4\Core\PostTypeHandler;

/** @var WP_Post $post */
$handler = PostTypeHandler::getHandlerForPost($post);
$pID = $handler->getPostID();
$meta = $handler->getPostMeta();

// Account-specific data
$status = ($handler && method_exists($handler, 'getStatus')) ? $handler->getStatus($post) : '';
$transactions = ($handler && method_exists($handler, 'getTransactions')) ? $handler->getTransactions($post) : [];
?>

<div>
Account Status: <?php echo $status; ?><br />
Transactions on record: <?php echo count($transactions); ?>
<hr />
Post Meta: <pre><?php print_r($meta,true); ?></pre>
</div>
