<div class="whx4-employment">
	<p><strong>Employers:</strong></p>
    
	<?php if(!$employers): ?>
        <p>No events found.</p>
    <?php else: ?>
        <table class="bkkp">
        <tr><th>Employer</th><th>Category</th><th>Tax Docs</th></tr>
        <?php foreach ($employers as $row): ?>
            <?php
            $employer = $row['post']; 
			$docs = $row['docs'];
			?>
            <tr>
			    <td>
			    <a href="<?php echo esc_url(get_permalink($employer)); ?>">
				<?php echo esc_html(get_the_title($employer)); ?>
				</a>
				</td>
				<td>
				<?php echo get_post_meta($employer->ID,'work_category_tmp'); ?>
				</td>
				<td>
				<?php echo "[".count($docs)."] "; ?>
				<?php
				foreach ( $docs as $doc ) {
				    $h = $handler($doc);
				    $total_comp = $h->getPostMeta('total_comp');
				    $total_withheld = $h->getPostMeta('total_withheld');
				    ?>
				    <a href="<?php echo esc_url(get_permalink($doc)); ?>">
				    <?php echo esc_html(get_the_title($doc)); ?>
				    </a>
				    <?php
				    echo " [".$total_comp."/".$total_withheld."]";
				}
				?>
				</td>
			</tr>
		<?php endforeach; ?>
		</table>
    <?php endif; ?>
    
    <hr class="debug-divider" />
    <details class="debug-info">
        <summary>Post Meta</summary>
        <pre><?php //print_r($handler->getPostMeta()); ?></pre>
        <summary>Debug</summary>
        <pre><?php //print_r($debug); ?></pre>
    </details>
    
</div>
