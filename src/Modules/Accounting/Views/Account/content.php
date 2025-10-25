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

<div class="account-view">
    <div class="account-summary">
        <p><strong>Account Status:</strong> <?php echo esc_html($status); ?></p>
        <p><strong>Total Transactions on Record:</strong> <?php echo $totalCount; ?></p>
    </div>
    
    <?php if (!empty($yearData)): ?>
        <h3>Transaction History</h3>
        
        <?php foreach ($yearData as $year => $yearStats): ?>
            <details class="transaction-year">
                <summary class="year-summary">
                    <span class="year-label"><?php echo esc_html($year); ?></span>
                    <span class="year-count">(<?php echo $yearStats['total']; ?> transactions)</span>
                </summary>
                
                <div class="year-details">
                    <div class="year-totals">
                        <div class="stat-row">
                            <span class="stat-label">Total Transactions:</span>
                            <span class="stat-value"><?php echo $yearStats['total']; ?></span>
                            <span class="stat-label">Credits:</span>
                            <span class="stat-value"><?php echo $yearStats['credits']; ?></span>
                            <span class="stat-label">Debits:</span>
                            <span class="stat-value"><?php echo $yearStats['debits']; ?></span>
                        </div>
                    </div>
                    
                    <?php if (isset($monthData[$year])): ?>
                        <h4 class="monthly-heading">Monthly Breakdown</h4>
                        <table class="monthly-table">
                            <thead>
                                <tr>
                                    <th class="col-month">Month</th>
                                    <th class="col-total">Total</th>
                                    <th class="col-credits">Credits</th>
                                    <th class="col-debits">Debits</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthData[$year] as $month => $data): ?>
                                    <tr>
                                        <td class="col-month"><?php echo $monthNames[$month]; ?></td>
                                        <td class="col-total"><?php echo $data['total']; ?></td>
                                        <td class="col-credits"><?php echo $data['credits']; ?></td>
                                        <td class="col-debits"><?php echo $data['debits']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </details>
        <?php endforeach; ?>
        
    <?php else: ?>
        <p class="no-transactions"><em>No transactions found for this account.</em></p>
    <?php endif; ?>
    
    <hr class="debug-divider" />
    <details class="debug-info">
        <summary>Post Meta (debug)</summary>
        <pre><?php print_r($handler->getPostMeta()); ?></pre>
    </details>
</div>