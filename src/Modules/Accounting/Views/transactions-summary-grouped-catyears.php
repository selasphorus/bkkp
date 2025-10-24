<div class="whx4-accounting">
	<p><strong>Transactions (Grouped by Category/Year):</strong>
	
	<div class="troubleshootingg">
        <!--h3>Info for Troubleshooting</h3-->
        <p>[info] <?php echo $info; ?></p>
        <!--p>[atts] <pre><?php echo print_r($atts, true); ?></pre></p>
        <p>[years] <pre><?php echo print_r($years, true); ?></pre></p-->
    </div>
    <hr />
    
	<?php if (!$rows): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <table class="bkkp">
        <tr>
            <th>Term</th>
            <?php foreach ($years as $year): ?>
            <th><?php echo $year; ?></th>
            <?php endforeach; ?>
            <!--th>TS</th-->
        </tr>
        <tr>
            <th>Total</th>
            <?php foreach ($years as $year): ?>
            <th><?php //echo $transactions_total; ?></th>
            <?php endforeach; ?>
            <!--th>TS</th-->
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
					if ( $col['sum'] == "0.0" ) { echo "--"; } else { echo $col['sum']; }
					// Show count if non-zero, even if sum is zero
					if ( $col['count'] > 0 ) { echo '&nbsp;<span class="subtle">' . '(' . $col['count'] . ')' . '</span>'; }
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
    
	<p><strong>Debug:</strong><pre><?php //echo print_r($debug, true); ?></pre></p>
</div>
