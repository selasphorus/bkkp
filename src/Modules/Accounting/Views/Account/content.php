<?php
use atc\WHx4\Core\PostTypeHandler;

/** @var \WP_Post $post */
$handler = PostTypeHandler::getHandlerForPost($post);

if ($handler) {
    $status = $handler->getStatus();
    $stats = $handler->getTransactionStats();
    
    $yearData = $stats['yearly'];
    $monthData = $stats['monthly'];
    $totalCount = $stats['total_count'];
    
    // Month names for display
    $monthNames = [
        '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
        '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug',
        '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'
    ];
    // TO be replaced with:
    //$monthNames = DateHelper::getMonthNames();
}
?>

<div>
    <p><strong>Account Status:</strong> <?php echo esc_html($status); ?></p>
    <p><strong>Total Transactions on Record:</strong> <?php echo $totalCount; ?></p>
    
    <?php if (!empty($yearData)): ?>
        <h3>Transactions by Year</h3>
        <table class="bkkp" style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left;">Year</th>
                    <th>Metric</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($yearData as $year => $data): ?>
                    <tr>
                        <td rowspan="3" style="font-weight: bold;"><?php echo esc_html($year); ?></td>
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
        
        <h3>Transactions by Month</h3>
        <?php foreach ($monthData as $year => $months): ?>
            <details style="margin-bottom: 20px;">
                <summary style="cursor: pointer; font-weight: bold; padding: 10px; background: #f9f9f9; border: 1px solid #ddd;">
                    <?php echo esc_html($year); ?> (<?php echo $yearData[$year]['total']; ?> transactions)
                </summary>
                <table style="border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Month</th>
                            <th>Total</th>
                            <th>Credits</th>
                            <th>Debits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($months as $month => $data): ?>
                            <tr>
                                <td><?php echo $monthNames[$month]; ?></td>
                                <td><?php echo $data['total']; ?></td>
                                <td><?php echo $data['credits']; ?></td>
                                <td><?php echo $data['debits']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </details>
        <?php endforeach; ?>
        
    <?php else: ?>
        <p><em>No transactions found for this account.</em></p>
    <?php endif; ?>
    
    <hr />
    <details>
        <summary>Post Meta (debug)</summary>
        <pre><?php print_r($handler->getPostMeta()); ?></pre>
    </details>
</div>