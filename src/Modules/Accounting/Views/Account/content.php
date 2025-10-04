<?php
use atc\WHx4\Core\PostTypeHandler;

/** @var \WP_Post $post */
$handler = PostTypeHandler::getHandlerForPost($post);
$pID = $handler->getPostID();
$meta = $handler->getPostMeta();

$handler = PostTypeHandler::getHandlerForPost($post);
if ($handler) {
    $postId = $handler->getPostId();
    $meta = $handler->getPostMeta();
    // Account-specific data
    $status = (string)$handler->getPostMeta('account_status', 'purple');
    $transactions = (method_exists($handler, 'getTransactions')) ? $handler->getTransactions() : [];
}
?>

<div>
Account Status: <?php echo $status; ?><br />
Transactions on record: <?php echo count($transactions); ?>
<hr />
Post Meta: <pre><?php print_r($meta,true); ?></pre>
</div>
