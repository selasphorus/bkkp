<?php
use atc\WHx4\Core\PostTypeHandler;

/** @var WP_Post $post */
$handler = PostTypeHandler::getHandlerForPost($post);

// Example: Person-specific data
$status = ($handler && method_exists($handler, 'getStatus'))
    ? $handler->getStatus($post)
    : '';
?>

<div>
Account Status: <?php echo $status; ?>
</div>
