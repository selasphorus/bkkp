<div class="whx4-employment">
	<p><strong>Transactions:</strong>
	
	<p class="troubleshooting">
        <h3>Info for Troubleshooting</h3>
        <p><?php echo $info; ?></p>
        <p>atts: <pre><?php print_r($atts, true); ?></pre></p>
        <!--debug: <pre><?php //print_r($debug, true); ?></pre>-->
        <hr />
    </p>
    
	<?php if(!$posts): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <?php 
        echo "[".count($posts)."] transactions found<br />";
        //echo " in the {$term} category, for a total of ${$total}<br />"; ?>
        <?php foreach ($posts as $post): ?>
			    <a href="<?php echo esc_url(get_permalink($post)); ?>">
				<?php echo esc_html(get_the_title($post)); ?>
				</a-->
				<?php
				echo "<br />";
				?>
			
			<!-- render $docs for that employer -->
		<?php endforeach; ?>

        <ul class="whx4-events__items items_list">
            <?php /*foreach($posts as $post): ?>
                <?php $h = $handler($post); ?>
                <?php
                //$start = $h && method_exists($h, 'getPostMeta') ? (string)($h->getPostMeta('start_date') ?? '') : (string)get_post_meta($post->ID, 'start_date', true);
                //$end   = $h && method_exists($h, 'getPostMeta') ? (string)($h->getPostMeta('end_date') ?? '')   : (string)get_post_meta($post->ID, 'end_date', true);
                ?>
                <li class="whx4-employers__item list_item">
                    <a href="<?php echo esc_url(get_permalink($post)); ?>">
                        <?php echo esc_html(get_the_title($post)); ?>
                    </a>
                </li>
            <?php endforeach;*/ ?>
        </ul>

        <?php /*if($pagination['max_pages'] > 1): ?>
            <nav class="whx4-pagination" aria-label="pagination">
                <span>Page <?php echo (int)$pagination['paged']; ?> of <?php echo (int)$pagination['max_pages']; ?></span>
            </nav>
        <?php endif;*/ ?>
    <?php endif; ?>
    
	<p><strong>Debug:</strong> <pre><?php //echo print_r($debug, true); ?></pre></p>
</div>
