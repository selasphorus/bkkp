<?php

declare(strict_types=1);

namespace atc\Bkkp\Modules\Employment\Shortcodes;

use atc\WHx4\Core\WHx4;
use atc\WHx4\Utils\ClassInfo;
use atc\WHx4\Core\PostTypeHandler;
use atc\WHx4\Core\ViewLoader;
use atc\WHx4\Core\SubtypeRegistry;
use atc\WHx4\Core\Contracts\ShortcodeInterface;
use atc\WHx4\Core\Query\ScopedDateResolver;
//
use atc\Bkkp\Modules\Accounting\PostTypes\Transaction;
//use atc\Bkkp\Modules\Employment\EmploymentModule; // ?

final class EmploymentIncomeShortcode implements ShortcodeInterface
{
    // This is the tag by which the shortcode will be called
    public static function tag(): string
    {
        return 'employment_income';
    }
    
    public function render(array $atts = [], string $content = '', string $tag = ''): string
    {
        $info = "";
        
        $ctx = WHx4::ctx();
        $key = ClassInfo::getModuleKey(self::class);
        $module = $ctx->getModule($key);
        if (!$module) {
            return '<p>Employment module inactive.</p>';
        }

        // Determine scope
        if ( isset($atts['scope']) ) { $scope = $atts['scope']; } else { $scope = date('Y'); }
        
        // Query employers
        $employers = $module->findEmployers($scope) ?? [];
        $employerPosts = $employers['posts'] ?? [];
        
        // Check if scope was revised via findEmployers
        if (isset($employers['debug']['scope'])) { 
            $scope = $employers['debug']['scope'];
        }
        
        // Extract years for columns
        $years = ScopedDateResolver::extractYears($scope);
        
        // Prepare all employer data
        $employerData = $this->prepareEmployerData($module, $employerPosts, $years, $scope, $atts);
        
        // Calculate aggregated totals
        $totals = $this->calculateAggregatedTotals($employerData, $years);
        
        // Pagination info
        $pagination = $employers['pagination'] ?? ['found' => 0, 'max_pages' => 0, 'paged' => 1];
        
        // Troubleshooting info
        $info .= "[" . $employers['pagination']['found'] . "] employers found for scope: {$scope}<br />";
        if ($employers['pagination']['found'] == 0) {
            $info .= "findEmployers result: <pre>". print_r($employers, true) . "</pre>";
        }
        
        // Debug info
        $debug = [
            'employers' => $employers['debug'] ?? null,
        ];
        
        // Pass clean data to view
        $vars = [
            'employer_data'  => $employerData,
            'years'          => $years,
            'totals'         => $totals,
            'print_header'   => $atts['print_header'] ?? true,
            'print_footer'   => $atts['print_footer'] ?? true,
            'pagination'     => $pagination,
            'troubleshooting' => $info,
            'debug'          => $debug,
        ];

        return ViewLoader::renderToString(
            'employment-income',
            $vars,
            ['kind' => 'partial', 'module' => 'employment'] //, 'post_type' => self::CPT
        );
        
        //$view = ViewLoader::load('employment-income', $vars, $module);
        return $view;
    }
    
    /*
    // V1 -- not functional but keeping as WIP re Subtypes
    public function render(array $atts = [], string $content = '', string $tag = ''): string
    {
        $info = "";
        
        // 1) Resolve subtype instance
		$subtype = SubtypeRegistry::resolve(self::CPT, 'employers');
		if (!$subtype) {
			// Graceful failure: no subtype registered
			return '';
		}

        // Merge with canonical defaults from the CPT handler (parent-powered).
        $atts = shortcode_atts(PostTypeHandler::queryDefaults(), $atts, $tag);
        //$atts['date_meta']['key'] ='whx4_events_start_date'; // tft

        // Run the unified query pipeline.
        $result = $subType::find($atts);
        //$result = $subType->find($atts);
        $posts  = $result['posts'] ?? [];

        // Pagination info for the view.
        $pagination = $result['pagination'] ?? ['found' => 0, 'max_pages' => 0, 'paged' => 1];

        // Troubleshooting info
        $info .= "[" . $result['pagination']['found'] . "] posts found<br />";
        //$info .= "posts: <pre>" . print_r($posts, true) . "</pre>";
        //$info .= "atts: <pre>" . print_r($atts, true) . "</pre>";
        //$info .= "wp_args: <pre>" . print_r($result['debug']['args'], true) . "</pre>";
        //$info .= "query_request: <pre>" . $result['debug']['query_request'] . "</pre>";

        // Handler factory so views can call CPT methods safely.
        $handlerFactory = [PostTypeHandler::class, 'getHandlerForPost'];

        // Choose a view variant (list|grid|table); fall back to list.
        $viewVariant = in_array($atts['view'], ['list', 'grid', 'table'], true) ? $atts['view'] : 'list';
        $view = $viewVariant;

        $vars = [
            'posts'      => $posts,
            'handler'    => $handlerFactory,
            'atts'       => $atts,
            'pagination' => $pagination,
            'info' => $info, // for TS -- deprecate in favor of:
            // Optionally pass debug through when WHX4_DEBUG is on:
            'debug'      => $result['debug'] ?? null,
        ];

        return ViewLoader::renderToString(
            $view,
            $vars,
            ['kind' => 'partial', 'module' => 'employment', 'post_type' => self::CPT]
        );
    }
    */
    
    /**
     * Prepare all data for each employer across all years
     * 
     * @param object $module EmploymentModule instance
     * @param array $employers Array of employer WP_Post objects
     * @param array $years Array of years to process
     * @param string $scope Original scope string
     * @param array $atts Shortcode attributes
     * @return array Prepared employer data
     */
    private function prepareEmployerData($module, array $employers, array $years, string $scope, array $atts): array
    {
        $employerData = [];
        
        foreach ($employers as $employer) {
            // Get all docs for this employer (scoped)
            $taxDocs = $module->findEmployerTaxDocs($employer, ['scope' => $scope]);
            $docsByYear = $this->organizeDocsByYear($taxDocs, $years);
            
            // Get all transactions for this employer (scoped)
            $transactions = $module->findEmployerTransactions($employer, ['scope' => $scope]);
            $transactionsByYear = $this->organizeTransactionsByYear($transactions['posts'] ?? [], $years);
            
            // Calculate yearly data for this employer
            $yearlyData = $this->calculateEmployerYearlyData(
                $employer,
                $docsByYear,
                $transactionsByYear,
                $years
            );
            
            $employerData[] = [
                'employer'     => $employer,
                'yearly_data'  => $yearlyData,
            ];
        }
        
        return $employerData;
    }
    
    /**
     * Organize tax documents by year
     * 
     * @param array $docs Array of tax document posts
     * @param array $years Years to organize into
     * @return array Docs indexed by year
     */
    private function organizeDocsByYear(array $docs, array $years): array
    {
        $docsByYear = array_fill_keys($years, []);
        
        foreach ($docs as $doc) {
            $taxYear = get_post_meta($doc->ID, 'tax_year', true);
            if ($taxYear && isset($docsByYear[$taxYear])) {
                $docsByYear[$taxYear][] = $doc;
            }
        }
        
        return $docsByYear;
    }
    
    /**
     * Organize transactions by year and calculate totals
     * 
     * @param array $transactions Array of transaction posts
     * @param array $years Years to organize into
     * @return array Transaction totals indexed by year
     */
    private function organizeTransactionsByYear(array $transactions, array $years): array
    {
        $txnsByYear = array_fill_keys($years, 0);
        
        foreach ($transactions as $txn) {
            $txnDate = get_post_meta($txn->ID, 'transaction_date', true);
            if ($txnDate) {
                $txnYear = date('Y', strtotime($txnDate));
                if (isset($txnsByYear[$txnYear])) {
                    $amount = (float) get_post_meta($txn->ID, 'amount', true);
                    $txnsByYear[$txnYear] += $amount;
                }
            }
        }
        
        return $txnsByYear;
    }
    
    /**
     * Calculate all data for an employer across all years
     * 
     * @param \WP_Post $employer The employer post
     * @param array $docsByYear Docs organized by year
     * @param array $transactionTotalsByYear Transaction totals by year
     * @param array $years Years to calculate
     * @return array Yearly data for this employer
     */
    private function calculateEmployerYearlyData(
        \WP_Post $employer,
        array $docsByYear,
        array $transactionTotalsByYear,
        array $years
    ): array {
        $yearlyData = [];
        
        foreach ($years as $year) {
            $docs = $docsByYear[$year] ?? [];
            $hasDocs = !empty($docs);
            
            // Prepare doc data with metadata
            $preparedDocs = [];
            $docTotal = 0;
            $grossTotal = 0;
            $netTotal = 0;
            
            if ($hasDocs) {
                foreach ($docs as $doc) {
                    $gross = (float) get_post_meta($doc->ID, 'gross_income', true);
                    $netComp = (float) get_post_meta($doc->ID, 'net_compensation', true);
                    $totalComp = (float) get_post_meta($doc->ID, 'total_compensation', true);
                    
                    $docTotal += $totalComp;
                    $grossTotal += $gross;
                    $netTotal += $netComp;
                    
                    $preparedDocs[] = [
                        'post'       => $doc,
                        'gross'      => $gross > 0 ? '$' . number_format($gross, 0) : '—',
                        'net'        => $netComp > 0 ? '$' . number_format($netComp, 0) : '—',
                        'total'      => $totalComp > 0 ? '$' . number_format($totalComp, 0) : '—',
                    ];
                }
            }
            
            // Transaction data
            $txnTotal = $transactionTotalsByYear[$year] ?? 0;
            
            // Mismatch detection (only if we have docs to compare)
            $mismatch = $hasDocs && (abs($docTotal - $txnTotal) > 0.01);
            $difference = $docTotal - $txnTotal;
            
            // Build transaction filter URL
            $txnUrl = Transaction::getFilteredAdminUrl([
                'tax_year' => $year,
                'related_group' => $employer->ID,
            ]);
            
            $yearlyData[$year] = [
                'has_docs'        => $hasDocs,
                'docs'            => $preparedDocs,
                'doc_total'       => $docTotal,
                'gross_total'     => $grossTotal,
                'net_total'       => $netTotal,
                'txn_total'       => $txnTotal,
                'txn_url'         => $txnUrl,
                'mismatch'        => $mismatch,
                'difference'      => $difference,
                'has_activity'    => $hasDocs || $txnTotal > 0,
            ];
        }
        
        return $yearlyData;
    }
    
    /**
     * Calculate aggregated totals across all employers
     * 
     * @param array $employerData Prepared employer data
     * @param array $years Years to calculate
     * @return array Totals indexed by year
     */
    private function calculateAggregatedTotals(array $employerData, array $years): array
    {
        $totals = [];
        
        foreach ($years as $year) {
            $employerCount = 0;
            $grossTotal = 0;
            $netTotal = 0;
            
            foreach ($employerData as $employer) {
                $yearData = $employer['yearly_data'][$year] ?? null;
                
                if ($yearData && $yearData['has_activity']) {
                    $employerCount++;
                    
                    if ($yearData['has_docs']) {
                        $grossTotal += $yearData['gross_total'];
                        $netTotal += $yearData['net_total'];
                    } else {
                        // No docs but has transactions - use txn total for both
                        $grossTotal += $yearData['txn_total'];
                        $netTotal += $yearData['txn_total'];
                    }
                }
            }
            
            $totals[$year] = [
                'employer_count' => $employerCount,
                'gross_total'    => $grossTotal,
                'net_total'      => $netTotal,
            ];
        }
        
        return $totals;
    }
}