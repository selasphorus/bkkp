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
						<?php 
						// Calculate net comp (total - withheld) from docs for this year
						$doc_total = 0;
						foreach ($docs_by_year[$year] as $doc) {
							$h = $handler($doc);
							$total_comp = (float)$h->getPostMeta('total_comp');
							#$doc_total += $total_comp;
							$total_withheld = (float)$h->getPostMeta('total_withheld');
							$doc_total += ($total_comp - $total_withheld);
						}
						
						// Get transaction total for this year
						$txn_total = $row['transaction_totals'][$year] ?? 0;
						
						// Check if they match
						$mismatch = (abs($doc_total - $txn_total) > 0.01); // Allow for floating point rounding
						$difference = $doc_total - $txn_total;
						?>
						
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
						
						<!-- Transaction total comparison -->
						<div class="txn-total <?php echo $mismatch ? 'mismatch' : 'match'; ?>">
							Txns: $<?php echo number_format($txn_total, 0); ?>
							<?php if ($mismatch): ?>
							    <span class="difference">(<?php echo $difference > 0 ? '+' : ''; ?><?php echo number_format($difference, 0); ?>)</span>
							<?php endif; ?>
						</div>
						
					<?php else: ?>
						<?php 
						// No docs, but check if there are transactions
						$txn_total = $row['transaction_totals'][$year] ?? 0;
						if ($txn_total > 0): 
						?>
							<div class="txn-total mismatch">
								Txns: $<?php echo number_format($txn_total, 0); ?>
							</div>
						<?php else: ?>
							<span class="no-data">—</span>
						<?php endif; ?>
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