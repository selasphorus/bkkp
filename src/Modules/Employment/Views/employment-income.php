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

<div class="whx4 employment">
    <!-- Print-only header -->
    <div class="print-header">
        <h1><?php 
            if (!empty($print_header)) {
                echo esc_html($print_header);
            } else {
                echo 'Employment Income for ';
                echo count($years) === 1 ? $years[0] : min($years) . '–' . max($years);
            }
        ?></h1>
    </div>
    
    <?php if(!$employers): ?>
        <p>No employers found.</p>
    <?php else: ?>
        <table class="bkkp">
        <tr>
            <th>Employer</th>
            <th class="screen-only">Category</th>
            <th>W2/1099</th>
            <?php foreach ($years as $year): ?>
                <th class="year"><?php echo $year; ?></th>
            <?php endforeach; ?>
        </tr>
        
        <?php foreach ($employers as $bundle): ?>
            <tr>
                <td>
                    <a href="<?php echo esc_url(get_permalink($bundle['post'])); ?>">
                        <?php echo esc_html(get_the_title($bundle['post'])); ?>
                    </a>
                </td>
                <td><?php echo esc_html($bundle['work_category']); ?></td>
                <td><?php echo esc_html($bundle['employment_classification']); ?></td>
                
                <?php foreach ($years as $year): 
                    $yearData = $bundle['yearly_data'][$year];
                ?>
                    <td class="tax-year-cell">
                        <!-- Show docs if they exist -->
                        <?php if ($yearData['has_docs']): ?>
                            <?php foreach ($yearData['docs'] as $doc): ?>
                                <div class="tax-amount">
                                    <a href="<?php echo esc_url(get_edit_post_link($doc['post'])); ?>" 
                                       title="<?php echo esc_attr(get_the_title($doc['post'])); ?>" 
                                       class="comp-amount" target="_blank">
                                        <?php echo esc_html($doc['comp_formatted']); ?>
                                    </a>
                                    <?php if ($doc['withheld_formatted']): ?>
                                        <span class="withheld-amount screen-only">
                                            (<?php echo esc_html($doc['withheld_formatted']); ?> withheld)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Always show transaction total with link if there's activity -->
                        <?php if ($yearData['has_activity']): ?>
                            <div class="txn-total <?php echo $yearData['mismatch'] ? 'mismatch' : 'match'; ?> <?php echo $yearData['has_docs'] ? 'docs' : 'no_docs'; ?>">
                                <a href="<?php echo esc_url($yearData['txn_url']); ?>" target="_blank">
                                    <span class="txn-total">Txns:&nbsp;</span>$<?php echo number_format($yearData['txn_total'], 0); ?>
                                </a>
                                <?php if ($yearData['mismatch']): ?>
                                    <span class="difference <?php echo $yearData['difference'] > 0 ? 'over' : 'under'; ?>">
                                        <br />(<?php echo $yearData['difference'] > 0 ? 'tx-under' : 'tx-over'; ?>:<?php echo number_format($yearData['difference'], 0); ?>)
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="no-data">—</span>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        
        <!-- Totals row -->
        <?php if ($print_footer): ?>
        <tr class="totals-row">
            <td colspan="3"><strong>Totals</strong></td>
            <?php foreach ($years as $year): ?>
                <td class="tax-year-cell">
                    <?php if ($totals[$year]['employer_count'] > 0): ?>
                        <div class="total-employers">
                            <?php echo $totals[$year]['employer_count']; ?> employer<?php echo $totals[$year]['employer_count'] !== 1 ? 's' : ''; ?>
                        </div>
                        <div class="total-gross">
                            Gross: $<?php echo number_format($totals[$year]['gross'], 0); ?>
                        </div>
                        <div class="total-net">
                            Net: $<?php echo number_format($totals[$year]['net'], 0); ?>
                        </div>
                    <?php else: ?>
                        <span class="no-data">—</span>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <?php endif; ?>
        </table>
    <?php endif; ?>
</div>