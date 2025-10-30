<?php
/**
 * Account Content View
 * 
 * Displays account status and transaction statistics
 * Pure presentation layer - all data preparation done in Account handler
 * 
 * @var string $status Account status
 * @var array $viewData Prepared transaction statistics
 * @var array $postMeta Post meta for debug display
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="account-view">
    <div class="account-summary">
        <p><strong>Account Status:</strong> <?php echo esc_html($status); ?></p>
        <p><strong>Total Transactions on Record:</strong> <?php echo $viewData['total_count']; ?></p>
    </div>
    
    <?php if ($viewData['has_data']): ?>
        <h3>Transaction History</h3>
        
        <?php foreach ($viewData['years'] as $yearData): ?>
            <details class="transaction-year">
                <summary class="year-summary">
                    <span class="year-label"><?php echo esc_html($yearData['year']); ?></span>
                    <span class="year-count">(<?php echo $yearData['stats']['total']; ?> transactions)</span>
                </summary>
                
                <div class="year-details">
                    <div class="year-totals">
                        <div class="stat-row">
                            <span class="stat-label">Total Transactions:</span>
                            <span class="stat-value"><?php echo $yearData['stats']['total']; ?></span>
                            <span class="stat-label">Credits:</span>
                            <span class="stat-value"><?php echo $yearData['stats']['credits']; ?></span>
                            <span class="stat-label">Debits:</span>
                            <span class="stat-value"><?php echo $yearData['stats']['debits']; ?></span>
                        </div>
                    </div>
                    
                    <?php if ($yearData['has_months']): ?>
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
                                <?php foreach ($yearData['months'] as $monthData): ?>
                                    <tr>
                                        <td class="col-month">
                                            <a href="<?php echo esc_url($monthData['url']); ?>" target="_blank">
                                                <?php echo esc_html($monthData['month_name']); ?>
                                            </a>
                                        </td>
                                        <td class="col-total"><?php echo $monthData['total']; ?></td>
                                        <td class="col-credits"><?php echo $monthData['credits']; ?></td>
                                        <td class="col-debits"><?php echo $monthData['debits']; ?></td>
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
        <pre><?php print_r($postMeta); ?></pre>
    </details>
</div>