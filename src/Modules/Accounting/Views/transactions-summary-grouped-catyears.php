<?php
/**
 * @var array $rows Pivot table rows with URLs
 * @var array $years Array of years
 * @var array $yearTotals Year totals
 * @var array $yearUrls URLs for year totals
 * @var string $print_header Optional custom print header
 */
?>

<div class="whx4 accounting">
	<!-- Print-only header -->
    <div class="print-header">
		<h1><?php 
			if (!empty($print_header)) {
				echo esc_html($print_header);
			} else {
				// Default header
				echo 'Transactions for ';
				if (count($years) === 1) {
					echo $years[0];
				} else {
					echo min($years) . '–' . max($years);
				}
				//echo " &mdash; Categories:"; // wip
			}
		?></h1>
	</div>
    
	<?php if (!$rows): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <table class="bkkp">
        <thead>
        <tr>
            <th>Category</th><!-- previously: Term -->
            <?php foreach ($years as $year): ?>
            <th><?php echo $year; ?></th>
            <?php endforeach; ?>
            <!--th>TS</th-->
        </tr>
        </thead>
        <tbody>
        <tr class="screen-only">
			<th>Total</th>
			<?php foreach ($years as $year): ?>
			<th>
			    <a href="<?php echo esc_url($yearUrls[$year]); ?>" target="_blank">
				<?php 
				$yearTotal = $yearTotals[$year] ?? ['sum' => 0.0, 'count' => 0];
				echo number_format($yearTotal['sum'], 2);
				if ($yearTotal['count'] > 0) {
					echo '&nbsp;<span class="subtle">(' . $yearTotal['count'] . ')</span>';
				}
				?>
				</a>
			</th>
			<?php endforeach; ?>
		</tr>
        <?php foreach ($rows as $row): ?>
            <tr>
				<td><?php echo $row['term']->name; ?></td>
				<?php foreach ($row['cols'] as $col): ?>
					<td>
					<?php // TODO: style according to whether sum is <> previous year ?>
					<a href="<?php echo esc_url($col['url']); ?>" class="txn-total" target="_blank">
					<?php if ($col['sum'] == 0.0): ?>
						<span class="zero-amount">—</span>
					<?php else: ?>
						<span class="numeric">$<?php echo number_format($col['sum'], 2); ?></span>
					<?php endif; ?>
					<?php
					// Show count if non-zero, even if sum is zero
					if ( $col['count'] > 0 ) { echo '<span class="subtle txn-count">&nbsp;' . '(' . $col['count'] . ')' . '</span>'; }
					?>
					</a>
					</td>
				<?php endforeach; ?>
				<?php 
				//echo "<pre>".print_r($row['cols'], true)."</pre>";
				//echo "=> [".count($result['posts'])."] posts";
				?>
				<!--td><pre><?php //echo print_r($row['result']['query_request'], true); ?></pre></td-->
			</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
    <?php endif; ?>
    
    <?php if (!empty($debug) && WP_DEBUG): ?>
	<div class="troubleshooting">
		<details>
			<summary><strong>Debug Information</strong></summary>
			<pre><?php echo esc_html(print_r($debug, true)); ?></pre>
		</details>
	</div>
	<?php endif; ?>
</div>
