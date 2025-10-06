<div class="whx4-accounting">
	<p><strong>Transactions (Grouped by Category/Year):</strong>
	
	<div class="troubleshootingg">
        <h3>Info for Troubleshooting</h3>
        <p>[info] <?php echo $info; ?></p>
        <p>[atts] <pre><?php print_r($atts, true); ?></pre></p>
        <p>[years] <pre><?php print_r($years, true); ?></pre></p>
    </div>
    <hr />
    
	<?php if (!$rows): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <table>
        <tr>
            <th>Term</th>
            <?php foreach ($years as $year): ?>
            <th><?php echo $year; ?></th>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($rows as $row): ?>
            <tr>
				<td><?php echo $row['term']->name; ?></td>
				<?php foreach ($row['cols'] as $col): ?>
					<td><?php echo $col['sum']; ?> (<?php echo $col['count']; ?>)</td>
				<?php endforeach; ?>
				<?php //echo "<pre>".print_r($row['cols'], true)."</pre>"; ?>
				<?php //echo "=> [".count($result['posts'])."] posts"; ?>
			</tr>
		<?php endforeach; ?>
		</table>
    <?php endif; ?>
    
	<!--p><strong>Debug:</strong> <pre><?php //echo print_r($debug, true); ?></pre></p-->
</div>
