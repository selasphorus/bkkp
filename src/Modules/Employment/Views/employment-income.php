<div class="whx4-employment">
	<p><strong>Employers:</strong> <?php //echo (int)$stats['monsters']; ?></p>
	
	<p class="troubleshooting">
        <h3>Info for Troubleshooting</h3>
        <p><?php echo $info; ?></p>
        <!--debug: <pre><?php print_r($debug, true); ?></pre>-->
        <hr />
    </p>
    
	<?php if(!$posts): ?>
        <p>No events found.</p>
    <?php else: ?>
        <ul class="whx4-events__items items_list">
            <?php foreach($posts as $post): ?>
                <?php $h = $handler($post); ?>
                <?php
                //$start = $h && method_exists($h, 'getPostMeta') ? (string)($h->getPostMeta('start_date') ?? '') : (string)get_post_meta($post->ID, 'start_date', true);
                //$end   = $h && method_exists($h, 'getPostMeta') ? (string)($h->getPostMeta('end_date') ?? '')   : (string)get_post_meta($post->ID, 'end_date', true);
                ?>
                <li class="whx4-employers__item list_item">
                    <a href="<?php echo esc_url(get_permalink($post)); ?>">
                        <?php echo esc_html(get_the_title($post)); ?>
                    </a>
                    <?php /*if($start || $end): ?>
                        <div class="whx4-events__dates">
                            <?php echo esc_html(trim($start.($end && $end !== $start ? ' – '.$end : ''))); ?>
                        </div>
                    <?php endif;*/ ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if($pagination['max_pages'] > 1): ?>
            <nav class="whx4-pagination" aria-label="Employers pagination">
                <span>Page <?php echo (int)$pagination['paged']; ?> of <?php echo (int)$pagination['max_pages']; ?></span>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
    
	<!--p><strong>Debug:</strong> <pre><?php echo print_r($debug, true); ?></pre></p-->
</div>
