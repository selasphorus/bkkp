<?php
/**
 * Employment Income View
 * 
 * Displays employer income data across multiple tax years
 * Pure presentation layer - all data preparation done in shortcode
 * 
 * @var array $employer_data Prepared employer data
 * @var array $years Array of years to display
 * @var array $totals Aggregated totals by year
 * @var bool $print_header Whether to print header
 * @var bool $print_footer Whether to print footer
 * @var array $pagination Pagination info
 * @var string $troubleshooting Debug/troubleshooting info
 * @var array $debug Debug data
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="employment-income-view">
    
    <?php if ($print_header): ?>
        <h2>Employment Income Summary</h2>
    <?php endif; ?>
    
    <?php if (!empty($employer_data)): ?>
        
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Employer</th>
                    <?php foreach ($years as $year): ?>
                        <th colspan="4" class="year-header"><?php echo esc_html($year); ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th></th>
                    <?php foreach ($years as $year): ?>
                        <th class="column-gross">Gross</th>
                        <th class="column-net">Net</th>
                        <th class="column-total">Total Comp</th>
                        <th class="column-txn">Txns</th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employer_data as $data): 
                    $employer = $data['employer'];
                    $yearlyData = $data['yearly_data'];
                ?>
                    <tr class="employer-row">
                        <td class="employer-name">
                            <strong><?php echo esc_html($employer->post_title); ?></strong>
                        </td>
                        
                        <?php foreach ($years as $year): 
                            $year_data = $yearlyData[$year];
                            $has_activity = $year_data['has_activity'];
                            $has_docs = $year_data['has_docs'];
                            $mismatch = $year_data['mismatch'];
                        ?>
                            
                            <?php if ($has_docs): ?>
                                <!-- Has tax documents -->
                                <td class="column-gross">
                                    $<?php echo number_format($year_data['gross_total'], 0); ?>
                                </td>
                                <td class="column-net">
                                    $<?php echo number_format($year_data['net_total'], 0); ?>
                                </td>
                                <td class="column-total">
                                    $<?php echo number_format($year_data['doc_total'], 0); ?>
                                </td>
                                <td class="column-txn <?php echo $mismatch ? 'mismatch' : ''; ?>">
                                    <?php if ($year_data['txn_total'] > 0): ?>
                                        <a href="<?php echo esc_url($year_data['txn_url']); ?>">
                                            $<?php echo number_format($year_data['txn_total'], 0); ?>
                                        </a>
                                        <?php if ($mismatch): ?>
                                            <span class="mismatch-indicator" title="Difference: $<?php echo number_format(abs($year_data['difference']), 0); ?>">
                                                ⚠
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url($year_data['txn_url']); ?>">—</a>
                                    <?php endif; ?>
                                </td>
                                
                            <?php elseif ($has_activity): ?>
                                <!-- No docs but has transactions -->
                                <td class="column-gross no-docs">—</td>
                                <td class="column-net no-docs">—</td>
                                <td class="column-total no-docs">—</td>
                                <td class="column-txn">
                                    <a href="<?php echo esc_url($year_data['txn_url']); ?>">
                                        $<?php echo number_format($year_data['txn_total'], 0); ?>
                                    </a>
                                </td>
                                
                            <?php else: ?>
                                <!-- No activity -->
                                <td class="column-gross">—</td>
                                <td class="column-net">—</td>
                                <td class="column-total">—</td>
                                <td class="column-txn">—</td>
                            <?php endif; ?>
                            
                        <?php endforeach; ?>
                    </tr>
                    
                    <?php // Document detail rows
                    foreach ($years as $year):
                        $year_data = $yearlyData[$year];
                        if ($year_data['has_docs']):
                            foreach ($year_data['docs'] as $doc):
                    ?>
                        <tr class="doc-detail-row">
                            <td class="doc-title">
                                → <a href="<?php echo esc_url(get_permalink($doc['post']->ID)); ?>">
                                    <?php echo esc_html($doc['post']->post_title); ?>
                                </a>
                            </td>
                            <?php foreach ($years as $display_year): ?>
                                <?php if ($display_year === $year): ?>
                                    <td class="column-gross"><?php echo esc_html($doc['gross']); ?></td>
                                    <td class="column-net"><?php echo esc_html($doc['net']); ?></td>
                                    <td class="column-total"><?php echo esc_html($doc['total']); ?></td>
                                    <td class="column-txn">—</td>
                                <?php else: ?>
                                    <td colspan="4"></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php 
                            endforeach;
                        endif;
                    endforeach; 
                    ?>
                    
                <?php endforeach; ?>
            </tbody>
            
            <?php if ($print_footer): ?>
                <tfoot>
                    <tr class="totals-row">
                        <th>Totals</th>
                        <?php foreach ($years as $year): 
                            $year_totals = $totals[$year];
                        ?>
                            <th class="column-gross">
                                $<?php echo number_format($year_totals['gross_total'], 0); ?>
                            </th>
                            <th class="column-net">
                                $<?php echo number_format($year_totals['net_total'], 0); ?>
                            </th>
                            <th class="column-total">
                                (<?php echo $year_totals['employer_count']; ?> employer<?php echo $year_totals['employer_count'] !== 1 ? 's' : ''; ?>)
                            </th>
                            <th class="column-txn">—</th>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
        
    <?php else: ?>
        <p>No employment income data found for the selected period.</p>
    <?php endif; ?>
    
    <?php if (!empty($troubleshooting)): ?>
        <div class="troubleshooting">
            <?php echo $troubleshooting; // Already contains HTML ?>
        </div>
    <?php endif; ?>
    
</div>