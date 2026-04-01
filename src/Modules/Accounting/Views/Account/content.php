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
                            <span class="stat-value credit"><?php echo $yearData['stats']['credits']; ?></span>
                            <span class="stat-label">Debits:</span>
                            <span class="stat-value debit"><?php echo $yearData['stats']['debits']; ?></span>
                        </div>
                        <div class="stat-row">
							<span class="stat-label">Total Credits:</span>
							<span class="stat-value">$<?php echo number_format($yearData['stats']['credit_amount'], 2); ?></span>
						</div>
						<div class="stat-row">
							<span class="stat-label">Total Debits:</span>
							<span class="stat-value">$<?php echo number_format($yearData['stats']['debit_amount'], 2); ?></span>
						</div>
						<div class="stat-row stat-net">
							<span class="stat-label">Net:</span>
							<span class="stat-value">$<?php echo number_format($yearData['stats']['net'], 2); ?></span>
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
                                    <th class="col-amount">Credit $</th>
                                    <th class="col-amount">Debit $</th>
                                    <th class="col-amount">Net $</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($yearData['months'] as $monthData): ?>
                                    <tr>
                                        <td class="col-month">
                                            <a href="<?php echo esc_url($monthData['url']); ?>" target="_blank"<?php if ($monthData['has_gap']){ echo ' class="data-gap"'; } ?>>
                                                <?php echo esc_html($monthData['month_name']); ?>
                                            </a>
                                        </td>
                                        <td class="col-total"><?php echo $monthData['total']; ?></td>
                                        <td class="col-credits credit"><?php echo $monthData['credits']; ?></td>
                                        <td class="col-debits debit"><?php echo $monthData['debits']; ?></td>
                                        <td class="col-amount credit">$<?php echo number_format($monthData['credit_amount'], 2); ?></td>
                                        <td class="col-amount debit">$<?php echo number_format($monthData['debit_amount'], 2); ?></td>
                                        <td class="col-amount net">$<?php echo number_format($monthData['net'], 2); ?></td>
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