<?php
/**
 * Accounts Pivot Table - Monthly View
 * 
 * Displays account transaction statistics in a pivot table format
 * with accounts on rows and months on columns
 * 
 * @var array $pivot_data Array of account rows with period data
 * @var array $periods Array of period identifiers (YYYY-MM)
 * @var array $period_labels Formatted labels for display
 * @var array $period_totals Column totals for each period
 * @var array $grand_total Overall totals
 * @var bool $has_categories Whether accounts span multiple categories
 * @var string $print_header Optional header for print view
 * @var array $atts Shortcode attributes
 * @var string $info Debug/troubleshooting info
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="whx4 accounting accounts-pivot">
    <!-- Print-only header -->
    <?php if (!empty($print_header)): ?>
    <div class="print-header">
        <h1><?php echo esc_html($print_header); ?></h1>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($info)): ?>
    <div class="info screen-only">
        <?php echo $info; ?>
    </div>
    <?php endif; ?>
    
    <?php if (empty($pivot_data)): ?>
        <p>No account data available for the selected period.</p>
    <?php else: ?>
        <table class="bkkp accounts-pivot-table">
            <thead>
                <tr>
                    <th class="col-account">Account</th>
                    <?php /*if ($has_categories): ?>
                        <th class="col-category screen-only">Category</th>
                    <?php endif;*/ ?>
                    <!--th class="col-status screen-only">Status</th-->
                    <?php foreach ($periods as $period): ?>
                        <th class="col-period"><?php echo esc_html($period_labels[$period]); ?></th>
                    <?php endforeach; ?>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $currentCategory = null;
                foreach ($pivot_data as $row): 
                    // Get account name
                    if ( $abbr = get_post_meta($row['account']->ID, 'abbr', true)) {
                        $acct_name = $abbr;
                    } else {
                        $acct_name = esc_html($row['account']->post_title);
                    }                    
                    
                    // Category separator row
                    if ($has_categories && $row['category'] !== $currentCategory):
                        $currentCategory = $row['category'];
                ?>
                <tr class="category-separator">
                    <td colspan="<?php echo 2 + count($periods); ?>" class="category-header">
                        <strong><?php echo esc_html($currentCategory); ?></strong>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr class="account-row" data-account-id="<?php echo $row['account']->ID; ?>">
                    <td class="col-account">
                        <a href="<?php echo esc_url(get_permalink($row['account'])); ?>">
                            <?php echo $acct_name; ?>
                        </a>
                    </td>
                    <?php /*if ($has_categories): ?>
                        <td class="col-category screen-only"><?php echo esc_html($row['category']); ?></td>
                    <?php endif;*/ ?>
                    <!--td class="col-status screen-only">
                        <span class="status-badge status-<?php echo esc_attr($row['status']); ?>">
                            <?php echo esc_html(ucfirst($row['status'])); ?>
                        </span>
                    </td-->
                    
                    <?php foreach ($periods as $period): 
                        $cell = $row['periods'][$period];
                        $hasData = $cell['has_data'];
                        $hasGap = $cell['has_gap'];
                        
                        // Determine activity level for color coding
                        $activityClass = '';
                        if ($hasData) {
                            if ($cell['total'] >= 20) {
                                $activityClass = 'activity-high';
                            } elseif ($cell['total'] >= 10) {
                                $activityClass = 'activity-medium';
                            } else {
                                $activityClass = 'activity-low';
                            }
                        }
                    ?>
                        <td class="col-period <?php echo $activityClass; ?> <?php echo $hasGap ? 'has-gap' : ''; ?>">
                            <?php if ($hasData): ?>
                                <a href="<?php echo esc_url($cell['url']); ?>" 
                                   target="_blank"
                                   title="Total: <?php echo $cell['total']; ?> | Credits: <?php echo $cell['credits']; ?> | Debits: <?php echo $cell['debits']; ?>">
                                    <span class="cell-data">
                                        <?php echo $cell['total']; ?>/<span class="credit"><?php echo $cell['credits']; ?></span>/<span class="debit"><?php echo $cell['debits']; ?></span>
                                    </span>
                                </a>
                            <?php else: ?>
                                <span class="no-data">—</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                    
                    <td class="col-total">
                        <a href="<?php echo esc_url($row['totals']['url']); ?>" 
                           target="_blank"
                           title="Total: <?php echo $row['totals']['total']; ?> | Credits: <?php echo $row['totals']['credits']; ?> | Debits: <?php echo $row['totals']['debits']; ?>">
                            <strong class="cell-data">
                                <?php echo $row['totals']['total']; ?>/<span class="credit"><?php echo $row['totals']['credits']; ?></span>/<span class="debit"><?php echo $row['totals']['debits']; ?></span>
                            </strong>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td class="col-account"><strong>Totals</strong></td>
                    <?php /*if ($has_categories): ?>
                        <td class="col-category screen-only"></td>
                    <?php endif;*/ ?>
                    <!--td class="col-status screen-only"></td-->
                    
                    <?php foreach ($periods as $period): 
                        $total = $period_totals[$period];
                    ?>
                        <td class="col-period">
                            <strong class="cell-data">
                                <?php echo $total['total']; ?>/<span class="credit"><?php echo $total['credits']; ?></span>/<span class="debit"><?php echo $total['debits']; ?></span>
                            </strong>
                        </td>
                    <?php endforeach; ?>
                    
                    <td class="col-total">
                        <strong class="cell-data grand-total">
                            <?php echo $grand_total['total']; ?>/<span class="credit"><?php echo $grand_total['credits']; ?></span>/<span class="debit"><?php echo $grand_total['debits']; ?></span>
                        </strong>
                    </td>
                </tr>
            </tfoot>
        </table>
        
        <div class="legend screen-only">
            <h4>Legend</h4>
            <p><strong>Format:</strong> Total/Credits/Debits</p>
            <p><strong>Colors:</strong> 
                <span class="activity-sample activity-high">High activity (20+)</span> | 
                <span class="activity-sample activity-medium">Medium activity (10-19)</span> | 
                <span class="activity-sample activity-low">Low activity (1-9)</span>
            </p>
            <p><strong>⚠ Gap indicator:</strong> No transactions in previous period</p>
        </div>
    <?php endif; ?>
</div>