<div class="whx4-employment">
	<p><strong>Employers:</strong></p>
    
	<?php if(!$employers): ?>
        <p>No events found.</p>
    <?php else: ?>
        <table class="bkkp">
        <tr>
            <th>Employer</th>
            <th>Category</th>
            <?php foreach ($years as $year): ?>
                <th><?php echo $year; ?></th>
            <?php endforeach; ?>
        </tr>
        
        <?php foreach ($employers as $row): ?>
            <?php
            $employer = $row['post']; 
			$docs = $row['docs'];
			
			// Organize docs by tax_year
			$docs_by_year = [];
			foreach ($docs as $doc) {
			    $tax_year = get_post_meta($doc->ID, 'tax_year', true);
			    if ($tax_year) {
			        if (!isset($docs_by_year[$tax_year])) {
			            $docs_by_year[$tax_year] = [];
			        }
			        $docs_by_year[$tax_year][] = $doc;
			    }
			}
			?>
            <tr>
			    <td>
			        <a href="<?php echo esc_url(get_permalink($employer)); ?>">
				        <?php echo esc_html(get_the_title($employer)); ?>
				    </a>
				</td>
				<td>
				    <?php echo esc_html(get_post_meta($employer->ID, 'work_category_tmp', true)); ?>
				</td>
				
				<?php foreach ($years as $year): ?>
				    <td class="tax-year-cell">
				    <?php if (isset($docs_by_year[$year])): ?>
				        <?php foreach ($docs_by_year[$year] as $doc): ?>
				            <?php
				            $h = $handler($doc);
				            $total_comp = $h->getPostMeta('total_comp');
				            $total_withheld = $h->getPostMeta('total_withheld');
				            $comp_formatted = $total_comp ? '$' . number_format((float)$total_comp, 0) : '—';
				            ?>
				            <div class="tax-amount">
				                <a href="<?php echo esc_url(get_permalink($doc)); ?>" 
				                   title="<?php echo esc_attr(get_the_title($doc)); ?>" 
				                   class="comp-amount">
				                    <?php echo esc_html($comp_formatted); ?>
				                </a>
				                <?php if (!empty($total_withheld)): ?>
				                    <span class="withheld-amount">
				                        ($<?php echo esc_html(number_format((float)$total_withheld, 0)); ?> withheld)
				                    </span>
				                <?php endif; ?>
				            </div>
				        <?php endforeach; ?>
				    <?php else: ?>
				        <span class="no-data">—</span>
				    <?php endif; ?>
				    </td>
				<?php endforeach; ?>
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