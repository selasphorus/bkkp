<div class="whx4 accounting">
	
	<!-- Print-only header -->
    <div class="print-header">
    <h1>Transactions for <?php
    if (count($years) === 1) {
        echo $years[0];
    } else {
        echo min($years) . '–' . max($years);
    }
    //echo " &mdash; Categories:"; // wip
    ?></h1>
    </div>
    
	<?php if (!$rows): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <table class="bkkp">
        <tr>
            <th>Category</th><!-- previously: Term -->
            <?php foreach ($years as $year): ?>
            <th><?php echo $year; ?></th>
            <?php endforeach; ?>
            <!--th>TS</th-->
        </tr>
        <tr class="screen-only">
			<th>Total</th>
			<?php foreach ($years as $year): ?>
			<th>
				<?php 
				$yearTotal = $yearTotals[$year] ?? ['sum' => 0.0, 'count' => 0];
				echo number_format($yearTotal['sum'], 2);
				if ($yearTotal['count'] > 0) {
					echo '&nbsp;<span class="subtle">(' . $yearTotal['count'] . ')</span>';
				}
				?>
			</th>
			<?php endforeach; ?>
		</tr>

        <?php
        // TODO: add a row for annual totals (sum of all categories and num transactions)
        ?>
        <?php foreach ($rows as $row): ?>
            <tr>
				<td><?php echo $row['term']->name; ?></td>
				<?php foreach ($row['cols'] as $col): ?>
					<td><?php
					// TODO: style according to whether sum is <> previous year
					if ( $col['sum'] == "0.0" ) { echo "--"; } else { echo "$".$col['sum']; }
					// Show count if non-zero, even if sum is zero
					if ( $col['count'] > 0 ) { echo '<span class="subtle txn-count">&nbsp;' . '(' . $col['count'] . ')' . '</span>'; }
					?></td>
				<?php endforeach; ?>
				<?php 
				//echo "<pre>".print_r($row['cols'], true)."</pre>";
				//echo "=> [".count($result['posts'])."] posts";
				?>
				<!--td><pre><?php //echo print_r($row['result']['query_request'], true); ?></pre></td-->
			</tr>
		<?php endforeach; ?>
		</table>
    <?php endif; ?>
    
    <div class="troubleshooting">
	    <p><strong>Debug:</strong><pre><?php echo print_r($debug, true); ?></pre></p>
	</div>
</div>
