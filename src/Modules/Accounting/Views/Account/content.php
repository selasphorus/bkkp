<?php
use atc\WHx4\Core\PostTypeHandler;

/** @var \WP_Post $post */
$handler = PostTypeHandler::getHandlerForPost($post);

if ($handler) {
    $accountId = $handler->getPostId();
    $meta = $handler->getPostMeta();
    //$status = $handler->getStatus();
    $status = (string)$handler->getPostMeta('account_status', 'Unknown');
   
    // Get all transactions for this account (respects URL params automatically)
    $transactions = $handler->getTransactions();
     /*
    // Group transactions by year and count credits/debits
    $yearData = [];
    foreach ($transactions as $transaction) {
        $dateValue = get_post_meta($transaction->ID, 'transaction_date', true);
        $type = get_post_meta($transaction->ID, 'transaction_type', true);
        
        // Extract year from yyyymmdd format
        $year = substr($dateValue, 0, 4);
        
        if (!isset($yearData[$year])) {
            $yearData[$year] = [
                'total' => 0,
                'credits' => 0,
                'debits' => 0
            ];
        }
        
        $yearData[$year]['total']++;
        if ($type === 'credit') {
            $yearData[$year]['credits']++;
        } elseif ($type === 'debit') {
            $yearData[$year]['debits']++;
        }
    }
    
    // Sort by year descending
    krsort($yearData);
    */
}
?>

<div>
    <p><strong>Account Status:</strong> <?php echo esc_html($status); ?></p>
    <p><strong>Total Transactions on Record:</strong> <?php //echo count($transactions); ?></p>
    
    <?php /*if (!empty($yearData)): ?>
        <h3>Transactions by Year</h3>
        <table class="bkkp">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Metric</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($yearData as $year => $data): ?>
                    <tr>
                        <td rowspan="3"><?php echo esc_html($year); ?></td>
                        <td>Total Transactions</td>
                        <td><?php echo $data['total']; ?></td>
                    </tr>
                    <tr>
                        <td>Credits</td>
                        <td><?php echo $data['credits']; ?></td>
                    </tr>
                    <tr>
                        <td>Debits</td>
                        <td><?php echo $data['debits']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p><em>No transactions found for this account.</em></p>
    <?php endif;*/ ?>
    
    <hr />
    <details>
        <summary>Post Meta (debug)</summary>
        <pre><?php print_r($meta); ?></pre>
    </details>
</div>