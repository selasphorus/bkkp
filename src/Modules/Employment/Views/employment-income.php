<div class="whx4 employment">
    <!-- Print-only header -->
    <div class="print-header">
		<h1><?php 
			if (!empty($print_header)) {
				echo esc_html($print_header);
			} else {
				// Default header
				echo 'Employment Income for ';
				if (count($years) === 1) {
					echo $years[0];
				} else {
					echo min($years) . '–' . max($years);
				}
			}
		?></h1>
	</div>
	
	<?php if(!$employers): ?>
        <p>No employers found.</p>
    <?php else: ?>
        <table class="bkkp">
        <tr>
            <th>Employer</th>
            <th>Category</th>
            <th>W2/1099</th>
            <?php foreach ($years as $year): ?>
                <th class="year"><?php echo $year; ?></th>
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
				<td>
				    <?php echo esc_html(get_post_meta($employer->ID, 'employment_classification', true)); ?>
				</td>
				
				<?php foreach ($years as $year): ?>
				    <?php
					// Calculate totals and build transaction link (always needed)
					$doc_total = 0;
					if (isset($docs_by_year[$year])) {
						foreach ($docs_by_year[$year] as $doc) {
							$h = $handler($doc);
							$total_comp = (float)$h->getPostMeta('total_comp');
							$total_withheld = (float)$h->getPostMeta('total_withheld');
							$doc_total += ($total_comp - $total_withheld);
						}
						$docs = true;
					} else {
					    $docs = false;
					}
					
					$txn_total = $row['transaction_totals'][$year] ?? 0;
					$mismatch = (abs($doc_total - $txn_total) > 0.01);
					if (!isset($docs_by_year[$year])) { $mismatch = false; } // match N/A if no docs
					$difference = $doc_total - $txn_total;
					
					// These hash IDs are specific to a particular ACP setup 
					// ... so this url creation will need to change if the plugin is to work for other users on other sites
					// Find by manually filtering and copying from the URL
					// e.g. acp_filter[21fe4931b33334][0]=2024&acp_filter[21fe4931b33334][1]=2024 - This appears to be the tax_year filter (range: 2024 to 2024)
					$acp_tax_year_hash = '21fe4931b33334';  // Your tax_year column hash
					$acp_related_group_hash = '683bc82d0624dc';  // Your related_group column hash
					$acp_layout_id = '68b645905c8d6';  // Your saved layout ID (optional)
					
					$txn_url = add_query_arg([
						'post_type' => 'transaction',
						'layout' => $acp_layout_id,  // Optional: use a saved ACP layout
						"acp_filter[{$acp_related_group_hash}]" => $employer->ID,
						"acp_filter[{$acp_tax_year_hash}][0]" => $year,  // Range start
						"acp_filter[{$acp_tax_year_hash}][1]" => $year,  // Range end
						'filter_action' => 'Filter',
					], admin_url('edit.php'));
					/*$txn_url = add_query_arg([
						'scope' => $year,
						'transaction_category' => 'income',
						'related_group' => $employer->ID,
					], home_url('/accounts-overview/transactions/'));*/
					?>
					<td class="tax-year-cell">
					<!-- Show docs if they exist -->
					<?php if ($docs): ?>
						<?php foreach ($docs_by_year[$year] as $doc): ?>
							<?php
							$h = $handler($doc);
							$total_comp = $h->getPostMeta('total_comp');
							$total_withheld = $h->getPostMeta('total_withheld');
							$comp_formatted = $total_comp ? '$' . number_format((float)$total_comp, 0) : '—';
							?>
							<div class="tax-amount">
								<a href="<?php echo esc_url(get_edit_post_link($doc)); // get_permalink ?>" 
								   title="<?php echo esc_attr(get_the_title($doc)); ?>" 
								   class="comp-amount"
								   target="_blank">
									<?php echo esc_html($comp_formatted); ?>
								</a>
								<?php if (!empty($total_withheld)): ?>
									<span class="withheld-amount screen-only">
										($<?php echo esc_html(number_format((float)$total_withheld, 0)); ?> withheld)
									</span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
					<!-- Always show transaction total with link -->
					<?php if ($txn_total > 0 || $docs): ?>
						<div class="txn-total <?php echo $mismatch ? 'mismatch' : 'match'; ?> <?php echo $docs ? 'docs' : 'no_docs'; ?>">
							<a href="<?php echo esc_url($txn_url); ?>" target="_blank">
								<span class="txn-total">Txns:&nbsp;</span>$<?php echo number_format($txn_total, 0); ?>
							</a>
							<?php if ($mismatch): ?>
								<span class="difference <?php echo $difference > 0 ? 'over' : 'under'; ?>"><br />(<?php echo $difference > 0 ? 'tx-under' : 'tx-over'; ?>:<?php echo number_format($difference, 0); ?>)</span>
							<?php endif; ?>
						</div>
					<?php else: ?>
						<span class="no-data">—</span>
					<?php endif; ?>
					</td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
		<!-- Totals row -->
		<?php
		// Calculate totals per year
		$totals_by_year = [];
		foreach ($years as $year) {
			$employer_count = 0;
			$gross_total = 0;
			$net_total = 0;
			
			foreach ($employers as $row) {
				$docs = $row['docs'];
				$txn_total = $row['transaction_totals'][$year] ?? 0;
				
				// Organize docs by tax_year for this employer
				$has_activity = false;
				$year_gross = 0;
				$year_net = 0;
				
				foreach ($docs as $doc) {
					$tax_year = get_post_meta($doc->ID, 'tax_year', true);
					if ((int)$tax_year === (int)$year) {
						$has_activity = true;
						$h = $handler($doc);
						$total_comp = (float)$h->getPostMeta('total_comp');
						$total_withheld = (float)$h->getPostMeta('total_withheld');
						$year_gross += $total_comp;
						$year_net += ($total_comp - $total_withheld);
					}
				}
				
				// If no docs but transactions exist, use transaction total for gross/net
				if (!$has_activity && $txn_total > 0) {
					$year_gross = $txn_total;
					$year_net = $txn_total; // No withholding info available
					$has_activity = true;
				}
				
				// Add to totals and count employer if they have activity this year
				if ($has_activity) {
					$employer_count++;
					$gross_total += $year_gross;
					$net_total += $year_net;
				}
			}
			
			$totals_by_year[$year] = [
				'employers' => $employer_count,
				'gross' => $gross_total,
				'net' => $net_total,
			];
		}
		?>
		<tr class="totals-row">
			<td colspan="3"><strong>Totals</strong></td>
			<?php foreach ($years as $year): ?>
				<td class="tax-year-cell">
					<?php if ($totals_by_year[$year]['employers'] > 0): ?>
						<div class="total-employers">
							<?php echo $totals_by_year[$year]['employers']; ?> employer<?php echo $totals_by_year[$year]['employers'] !== 1 ? 's' : ''; ?>
						</div>
						<div class="total-gross">
							Gross: $<?php echo number_format($totals_by_year[$year]['gross'], 0); ?>
						</div>
						<div class="total-net">
							Net: $<?php echo number_format($totals_by_year[$year]['net'], 0); ?>
						</div>
					<?php else: ?>
						<span class="no-data">—</span>
					<?php endif; ?>
				</td>
			<?php endforeach; ?>
		</tr>
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