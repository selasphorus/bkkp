<?php

declare(strict_types=1);

namespace atc\Bkkp\Modules\Accounting\Shortcodes;

use atc\WXC\App;
use atc\WXC\Utils\ClassInfo;
use atc\WXC\PostTypes\PostTypeHandler;
use atc\WXC\Templates\ViewLoader;
use atc\WXC\Contracts\ShortcodeInterface;
use atc\WXC\Query\ScopedDateResolver;
use atc\WXC\Utils\DateHelper;
//
use atc\Bkkp\Modules\Accounting\PostTypes\Account;
use atc\Bkkp\Modules\Accounting\PostTypes\Transaction;

final class AccountsShortcode implements ShortcodeInterface
{
    public static function tag(): string
    {
        return 'accounts';
    }
    
    /**
     * [accounts] – supports atts like:
     * scope="2024" | "this_year" | "2022-2025"
     * account_category="credit-cards,banking"
     * accounts="517,518" (specific account IDs or slugs)
     * account_status="active" | "closed" | "all"
     * group_by="none|account|account_months|account_years"
     * include_empty="0|1"
     * print_header="Credit Card Usage 2024"
     */
    public function render(array $atts, ?string $content = null, string $tag = ''): string
    {
        $info = "";
        
        // Defaults
        $defaults = [
            'scope' => 'this_year',
            'account_category' => '',
            'accounts' => '',
            'account_status' => 'active',
            'group_by' => 'account_months',
            'include_empty' => '1',
            'print_header' => '',
            'order' => 'ASC',
            'orderby' => 'title',
        ];
        
        $rawAtts = (array)$atts;
        $atts = shortcode_atts($defaults, $rawAtts, self::tag());
    
		// Return something immediately to test
		#return '<div style="background: yellow; padding: 20px;">AccountsShortcode merged atts: <pre>' . print_r($atts, true) . '</pre></div>';
        
        // Resolve scope with query-var override
        $scope = PostTypeHandler::getScopeFromRequest($atts, $atts['scope']);
        $atts['scope'] = $scope;
        
        // Normalize group mode
        $groupMode = strtolower(trim((string)($atts['group_by'])));
        if (!in_array($groupMode, ['none', 'account', 'account_months', 'account_years'], true)) {
            $groupMode = 'account_months';
        }
        $atts['group_by'] = $groupMode;
        
        $info .= "groupMode: {$groupMode}<br />";
        $info .= "scope: {$scope}<br />";

        // Get filtered accounts
        $accounts = $this->resolveAccounts($atts);
        
        #error_log('Accounts found: ' . count($accounts));
        
        if (empty($accounts)) {
            return '<p>No accounts found matching the specified criteria.</p>';
        }
        
        $info .= "accounts found: " . count($accounts) . "<br />";
        
        // Prepare view variables
        $viewVars = [
            'atts' => $atts,
            'grouped_by' => $groupMode,
            'print_header' => $atts['print_header'],
            'info' => $info,
        ];
        
        $viewSpecs = ['kind' => 'partial', 'module' => 'accounting', 'post_type' => 'account'];
        
        #error_log('About to render with group_by: ' . $groupMode);
        
        // Branch based on grouping mode
        switch ($groupMode) {
            case 'account_months':
                return $this->renderAccountMonths($accounts, $atts, $viewVars, $viewSpecs);
            
            case 'account_years':
                return $this->renderAccountYears($accounts, $atts, $viewVars, $viewSpecs);
            
            case 'account':
                return $this->renderAccountGrouped($accounts, $atts, $viewVars, $viewSpecs);
            
            case 'none':
            default:
                return $this->renderAccountSimple($accounts, $atts, $viewVars, $viewSpecs);
        }
    }
    
    /**
     * Resolve accounts based on filters
     */
    private function resolveAccounts(array $atts): array
    {
        $filters = [
            'post_type' => 'account',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        ];
        
        // Filter by account_category taxonomy
        if (!empty($atts['account_category'])) {
            $categories = is_array($atts['account_category']) 
                ? $atts['account_category'] 
                : array_map('trim', explode(',', $atts['account_category']));
            
            $filters['tax_query'] = [
                [
                    'taxonomy' => 'account_category',
                    'field' => 'slug',
                    'terms' => $categories,
                ],
            ];
        }
        
        // Filter by specific account IDs or slugs
        if (!empty($atts['accounts'])) {
            $accountIds = is_array($atts['accounts']) 
                ? $atts['accounts'] 
                : array_map('trim', explode(',', $atts['accounts']));
            
            // Check if they're numeric IDs or slugs
            $numericIds = array_filter($accountIds, 'is_numeric');
            if (count($numericIds) === count($accountIds)) {
                $filters['post__in'] = array_map('intval', $accountIds);
            } else {
                $filters['post_name__in'] = $accountIds;
            }
        }
        
        // Filter by account_status meta
        if (!empty($atts['account_status']) && $atts['account_status'] !== 'all') {
            $filters['meta_query'] = [
                [
                    'key' => 'account_status',
                    'value' => $atts['account_status'],
                    'compare' => '=',
                ],
            ];
        }
        #error_log('Filters: ' . print_r($filters, true));
        
        $query = new \WP_Query($filters);
        
        #error_log('Query found: ' . $query->found_posts . ' posts');
        
        return $query->posts;
    }
    
    /**
     * Render account × months pivot table
     */
    /*private function renderAccountMonths(array $accounts, array $atts, array $viewVars, array $viewSpecs): string
	{
		error_log('Starting with ' . count($accounts) . ' accounts');
		error_log('Scope: ' . $atts['scope']);
		
		$bounds = ScopedDateResolver::resolve($atts['scope'], ['mode' => 'DATE']);
		error_log('Bounds: ' . print_r($bounds, true));
		
		$periods = DateHelper::generateMonthPeriods($bounds['start'], $bounds['end']);
		error_log('Periods generated: ' . count($periods));
		error_log('Periods: ' . print_r($periods, true));
		
		$periodLabels = $this->formatPeriodLabels($periods, 'month');
		
		$pivotData = $this->buildPivotData($accounts, $atts, $periods, 'month');
		error_log('Pivot rows: ' . count($pivotData['rows']));
		
		$result = $this->renderPivotView('accounts-pivot-months', $pivotData, $periods, $periodLabels, $viewVars, $viewSpecs);
		error_log('View rendered, length: ' . strlen($result));
		
		return $result;
	}*/
	private function renderAccountMonths(array $accounts, array $atts, array $viewVars, array $viewSpecs): string
    {
        error_log('Scope: ' . $atts['scope']);
		
		$bounds = ScopedDateResolver::resolve($atts['scope'], ['mode' => 'DATE']);
		error_log('Bounds: ' . print_r($bounds, true));
		
        $periods = DateHelper::generateMonthPeriods($bounds['start'], $bounds['end']);
        $periodLabels = $this->formatPeriodLabels($periods, 'month');
        
        $pivotData = $this->buildPivotData($accounts, $atts, $periods, 'month');
        
        return $this->renderPivotView('accounts-pivot-months', $pivotData, $periods, $periodLabels, $viewVars, $viewSpecs);
    }
    
    /**
     * Render account × years pivot table
     */
    private function renderAccountYears(array $accounts, array $atts, array $viewVars, array $viewSpecs): string
    {
        $years = ScopedDateResolver::extractYears($atts['scope']);
        $periods = array_map(fn($y) => (string)$y, $years);
        $periodLabels = $this->formatPeriodLabels($periods, 'year');
        
        $pivotData = $this->buildPivotData($accounts, $atts, $periods, 'year');
        
        return $this->renderPivotView('accounts-pivot-years', $pivotData, $periods, $periodLabels, $viewVars, $viewSpecs);
    }
    
    /**
     * Build pivot table data structure (used by both month and year views)
     * 
     * @param array $accounts Array of WP_Post account objects
     * @param array $atts Shortcode attributes
     * @param array $periods Array of period strings (YYYY-MM or YYYY)
     * @param string $periodType 'month' or 'year'
     * @return array Pivot data structure with rows and totals
     */
    private function buildPivotData(array $accounts, array $atts, array $periods, string $periodType = 'month'): array
    {
        $scope = $atts['scope'];
        $includeEmpty = $atts['include_empty'] === '1';
        
        $pivotRows = [];
        $periodTotals = array_fill_keys($periods, ['total' => 0, 'credits' => 0, 'debits' => 0]);
        $grandTotal = ['total' => 0, 'credits' => 0, 'debits' => 0];
        
        foreach ($accounts as $account) {
            $handler = new Account($account);
            $stats = $handler->getTransactionStats(['scope' => $scope]);
            
            // Build period data with gap detection
            $periodData = [];
            $previousHadData = false;
            
            foreach ($periods as $period) {
                $cellStats = $this->extractPeriodStats($stats, $period, $periodType);
                $hasData = $cellStats && $cellStats['total'] > 0; //$hasData = $cellStats !== null && ($cellStats['credits'] > 0 || $cellStats['debits'] > 0);
                $hasGap = $previousHadData && !$hasData;
                
                $cellData = [
                    'total' => $cellStats['total'] ?? 0,
                    'credits' => $cellStats['credits'] ?? 0,
                    'debits' => $cellStats['debits'] ?? 0,
                    'url' => $cellStats['url'] ?? Transaction::getFilteredAdminUrl([
                        'account' => $account->ID,
                        'scope' => $period,
                    ]),
                    'has_data' => $hasData,
                    'has_gap' => $hasGap,
                ];
                
                $periodData[$period] = $cellData;
                
                // Add to period totals
                if ($hasData) {
                    $periodTotals[$period]['total'] += $cellData['total'];
                    $periodTotals[$period]['credits'] += $cellData['credits'];
                    $periodTotals[$period]['debits'] += $cellData['debits'];
                }
                
                $previousHadData = $hasData;
            }
            
            // Calculate row totals
            $rowTotal = [
                'total' => array_sum(array_column($periodData, 'total')),
                'credits' => array_sum(array_column($periodData, 'credits')),
                'debits' => array_sum(array_column($periodData, 'debits')),
                'url' => Transaction::getFilteredAdminUrl([
                    'account' => $account->ID,
                    'scope' => $scope,
                ]),
            ];
            
            // Skip empty accounts if requested
            if (!$includeEmpty && $rowTotal['total'] === 0) {
                continue;
            }
            
            // Add to grand total
            $grandTotal['total'] += $rowTotal['total'];
            $grandTotal['credits'] += $rowTotal['credits'];
            $grandTotal['debits'] += $rowTotal['debits'];
            
            // Get account category for grouping
            $categories = wp_get_post_terms($account->ID, 'account_category', ['fields' => 'names']);
            $category = !empty($categories) && !is_wp_error($categories) ? $categories[0] : 'Uncategorized';
            
            $pivotRows[] = [
                'account' => $account,
                'category' => $category,
                'status' => get_post_meta($account->ID, 'account_status', true),
                'periods' => $periodData,
                'totals' => $rowTotal,
            ];
        }
        
        // Sort by category, then by title
        usort($pivotRows, function($a, $b) {
            $catCompare = strcmp($a['category'], $b['category']);
            if ($catCompare !== 0) return $catCompare;
            return strcmp($a['account']->post_title, $b['account']->post_title);
        });
        
        return [
            'rows' => $pivotRows,
            'period_totals' => $periodTotals,
            'grand_total' => $grandTotal,
        ];
    }
    
    /**
     * Extract stats for a specific period from the full stats array
     * 
     * @param array $stats Full transaction stats from Account::getTransactionStats()
     * @param string $period Period identifier (YYYY-MM or YYYY)
     * @param string $periodType 'month' or 'year'
     * @return array|null Stats for the period or null if not found
     */
    private function extractPeriodStats(array $stats, string $period, string $periodType): ?array
    {
        if ($periodType === 'month') {
            $year = substr($period, 0, 4);
            $month = substr($period, 5, 2);
            return $stats['monthly'][$year][$month] ?? null;
        } else {
            return $stats['yearly'][$period] ?? null;
        }
    }
    
    /**
     * Render pivot view with common structure
     */
    private function renderPivotView(string $viewName, array $pivotData, array $periods, array $periodLabels, array $viewVars, array $viewSpecs): string
	{
		$viewVars['pivot_data'] = $pivotData['rows'];
		$viewVars['periods'] = $periods;
		$viewVars['period_labels'] = $periodLabels;
		$viewVars['period_totals'] = $pivotData['period_totals'];
		$viewVars['grand_total'] = $pivotData['grand_total'];
		$viewVars['has_categories'] = $this->hasMultipleCategories($pivotData['rows']);
		
		$output = ViewLoader::renderToString($viewName, $viewVars, $viewSpecs);
		
		return $output;
	}
    
    /**
     * Check if accounts span multiple categories
     */
    private function hasMultipleCategories(array $pivotRows): bool
    {
        $categories = array_unique(array_column($pivotRows, 'category'));
        return count($categories) > 1;
    }
    
    /**
     * Render grouped account list (one row per account with summary)
     */
    private function renderAccountGrouped(array $accounts, array $atts, array $viewVars, array $viewSpecs): string
    {
        $scope = $atts['scope'];
        $includeEmpty = $atts['include_empty'] === '1';
        
        $accountData = [];
        $grandTotal = ['total' => 0, 'credits' => 0, 'debits' => 0];
        
        foreach ($accounts as $account) {
            $handler = new Account($account);
            $stats = $handler->getTransactionStats(['scope' => $scope]);
            
            $total = $stats['total_count'];
            $credits = array_sum(array_column($stats['yearly'], 'credits'));
            $debits = array_sum(array_column($stats['yearly'], 'debits'));
            
            if (!$includeEmpty && $total === 0) {
                continue;
            }
            
            $grandTotal['total'] += $total;
            $grandTotal['credits'] += $credits;
            $grandTotal['debits'] += $debits;
            
            $categories = wp_get_post_terms($account->ID, 'account_category', ['fields' => 'names']);
            $category = !empty($categories) && !is_wp_error($categories) ? $categories[0] : 'Uncategorized';
            
            $accountData[] = [
                'account' => $account,
                'category' => $category,
                'status' => get_post_meta($account->ID, 'account_status', true),
                'stats' => [
                    'total' => $total,
                    'credits' => $credits,
                    'debits' => $debits,
                ],
                'url' => Transaction::getFilteredAdminUrl([
                    'account' => $account->ID,
                    'scope' => $scope,
                ]),
            ];
        }
        
        usort($accountData, function($a, $b) {
            $catCompare = strcmp($a['category'], $b['category']);
            if ($catCompare !== 0) return $catCompare;
            return strcmp($a['account']->post_title, $b['account']->post_title);
        });
        
        $viewVars['account_data'] = $accountData;
        $viewVars['grand_total'] = $grandTotal;
        $viewVars['scope'] = $scope;
        
        return ViewLoader::renderToString('accounts-summary-grouped', $viewVars, $viewSpecs);
    }
    
    /**
     * Render simple account list
     */
    private function renderAccountSimple(array $accounts, array $atts, array $viewVars, array $viewSpecs): string
    {
        $viewVars['accounts'] = $accounts;
        $viewVars['scope'] = $atts['scope'];
        
        return ViewLoader::renderToString('accounts-summary', $viewVars, $viewSpecs);
    }
    
    /**
     * Format period labels for display
     */
    private function formatPeriodLabels(array $periods, string $type = 'month'): array
    {
        $labels = [];
        
        if ($type === 'month') {
            $monthNames = DateHelper::getMonthNames('short');
            foreach ($periods as $period) {
                $month = substr($period, 5, 2);
                $year = substr($period, 2, 2); // Just last 2 digits
                $labels[$period] = $monthNames[$month] . " '" . $year;
            }
        } else {
            foreach ($periods as $period) {
                $labels[$period] = $period;
            }
        }
        
        return $labels;
    }
}