<?php

declare(strict_types=1);

namespace atc\Bkkp\Modules\Employment\Shortcodes;

use atc\BhWP\Core\WHx4;
use atc\WHx4\Utils\ClassInfo;
use atc\BhWP\Core\PostTypeHandler;
use atc\BhWP\Core\ViewLoader;
use atc\BhWP\Core\SubtypeRegistry;
use atc\BhWP\Core\Contracts\ShortcodeInterface;
use atc\BhWP\Core\Query\ScopedDateResolver;
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
        
        // v1
        // Fetch tax docs per employer (optionally scoped)
        // Bundle employers with their related tax docs
		//$employerBundles = [];
		/*foreach ($employerPosts as $post) {
			$docFilters = [];
		
			// Reuse resolved scope for documents if one has been set
			if (!empty($scope)) {
				$docFilters['scope'] = $scope;
			}
			//if (isset($atts['scope'])) { $docFilters['scope'] = $scope; }
		
			// Optional scoping basis and storage model (overrides via shortcode)
			if (isset($atts['date_key'])) { $docFilters['date_key'] = $atts['date_key']; }          // 'document_date' | 'tax_year'
			if (isset($atts['key_type'])) { $docFilters['key_type'] = $atts['key_type']; }          // 'single' | 'rows' | 'serialized' (for tax_year)
			if (isset($atts['limit']))    { $docFilters['limit']    = (int)$atts['limit']; }
		
			$docs = $module->findEmployerTaxDocs($post, $docFilters);
			//$docs = $module->findEmployerTaxDocs($post, $docFilters); // add another data set to the bundle?
			
			// NEW: Get transactions for this employer
			$transactionFilters = [];
			if (!empty($scope)) {
				$transactionFilters['scope'] = $scope;
			}
			$transactions = $module->findEmployerTransactions($post, $transactionFilters);
			
			// NEW: Aggregate transaction amounts by year
			$transactionTotalsByYear = [];
			foreach ($transactions['posts'] ?? [] as $txn) {
				$txnDate = get_post_meta($txn->ID, 'transaction_date', true);
				if ($txnDate) {
					// transaction_date is stored as NUMERIC yyyymmdd
					$year = (int)substr((string)$txnDate, 0, 4);
					$amount = (float)get_post_meta($txn->ID, 'amount', true);
					
					if (!isset($transactionTotalsByYear[$year])) {
						$transactionTotalsByYear[$year] = 0;
					}
					$transactionTotalsByYear[$year] += $amount;
				}
			}
		
			$employerBundles[] = [
				'post'            => $post,
				'docs'            => $docs['posts'] ?? [],
				//'docs_pagination' => $docs['pagination'] ?? null,
				'docs_debug'      => $docs['debug'] ?? null,
				'transaction_totals' => $transactionTotalsByYear,
			];
		}*/
		
		// WIP v2
		// Prepare all employer data with calculations done here
		$employerBundles = [];
		foreach ($employerPosts as $employer) {
			// Get docs
			$docFilters = ['scope' => $scope];
			if (isset($atts['date_key'])) $docFilters['date_key'] = $atts['date_key'];
			if (isset($atts['key_type'])) $docFilters['key_type'] = $atts['key_type'];
			if (isset($atts['limit'])) $docFilters['limit'] = (int)$atts['limit'];
			
			$docsResult = $module->findEmployerTaxDocs($employer, $docFilters);
			$docs = $docsResult['posts'] ?? [];
			
			// Get transactions
			$txnsResult = $module->findEmployerTransactions($employer, ['scope' => $scope]);
			$transactionTotalsByYear = [];
			foreach ($txnsResult['posts'] ?? [] as $txn) {
				$txnDate = get_post_meta($txn->ID, 'transaction_date', true);
				if ($txnDate) {
					$year = (int)substr((string)$txnDate, 0, 4);
					$amount = (float)get_post_meta($txn->ID, 'amount', true);
					if (!isset($transactionTotalsByYear[$year])) {
						$transactionTotalsByYear[$year] = 0;
					}
					$transactionTotalsByYear[$year] += $amount;
				}
			}
			
			// Organize docs by year WITH calculations
			$docsByYear = [];
			foreach ($docs as $doc) {
				$taxYear = get_post_meta($doc->ID, 'tax_year', true);
				if ($taxYear) {
					if (!isset($docsByYear[$taxYear])) {
						$docsByYear[$taxYear] = [];
					}
					
					// PRE-CALCULATE everything the view needs
					$totalComp = (float)get_post_meta($doc->ID, 'total_comp', true);
					$totalWithheld = (float)get_post_meta($doc->ID, 'total_withheld', true);
					
					$docsByYear[$taxYear][] = [
						'post' => $doc,
						'total_comp' => $totalComp,
						'total_withheld' => $totalWithheld,
						'net' => $totalComp - $totalWithheld,
						'comp_formatted' => $totalComp ? '$' . number_format($totalComp, 0) : '—',
						'withheld_formatted' => $totalWithheld ? '$' . number_format($totalWithheld, 0) : null,
					];
				}
			}
			
			// PRE-CALCULATE year data
			$yearlyData = [];
			foreach ($years as $year) {
				$docTotal = 0;
				$hasDocs = isset($docsByYear[$year]);
				
				if ($hasDocs) {
					foreach ($docsByYear[$year] as $doc) {
						$docTotal += $doc['net'];
					}
				}
				
				$txnTotal = $transactionTotalsByYear[$year] ?? 0;
				$mismatch = $hasDocs && (abs($docTotal - $txnTotal) > 0.01);
				$difference = $docTotal - $txnTotal;
				
				// Build transaction URL
				$txnUrl = Transaction::getFilteredAdminUrl([
					'tax_year' => $year,
					'related_group' => $employer->ID,
				]);
				
				$yearlyData[$year] = [
					'has_docs' => $hasDocs,
					'docs' => $docsByYear[$year] ?? [],
					'doc_total' => $docTotal,
					'txn_total' => $txnTotal,
					'txn_url' => $txnUrl,
					'mismatch' => $mismatch,
					'difference' => $difference,
					'has_activity' => $hasDocs || $txnTotal > 0,
				];
			}
			
			$employerBundles[] = [
				'post' => $employer,
				'work_category' => get_post_meta($employer->ID, 'work_category_tmp', true),
				'employment_classification' => get_post_meta($employer->ID, 'employment_classification', true),
				'yearly_data' => $yearlyData,
			];
		}
		
		// Calculate totals across all employers
		$totalsByYear = [];
		foreach ($years as $year) {
			$employerCount = 0;
			$grossTotal = 0;
			$netTotal = 0;
			
			foreach ($employerBundles as $bundle) {
				$yearData = $bundle['yearly_data'][$year];
				if ($yearData['has_activity']) {
					$employerCount++;
					if ($yearData['has_docs']) {
						foreach ($yearData['docs'] as $doc) {
							$grossTotal += $doc['total_comp'];
							$netTotal += $doc['net'];
						}
					} else {
						$grossTotal += $yearData['txn_total'];
						$netTotal += $yearData['txn_total'];
					}
				}
			}
			
			$totalsByYear[$year] = [
				'employer_count' => $employerCount,
				'gross' => $grossTotal,
				'net' => $netTotal,
			];
		}

        // Pagination info
        $pagination = $employers['pagination'] ?? ['found' => 0, 'max_pages' => 0, 'paged' => 1];

        // Troubleshooting info
        $info .= "[" . $employers['pagination']['found'] . "] employers found for scope: {$scope}<br />";
        if ($employers['pagination']['found'] == 0) {
            $info .= "findEmployers result: <pre>". print_r($employers, true) . "</pre>";
        }

        // Handler factory so views can call CPT methods safely.
        $handlerFactory = [PostTypeHandler::class, 'getHandlerForPost'];
        
        // Set the view
        $view = "employment-income"; //$view = "module-view-test";
        
        // WIP
        $debug = [];
        $debug['employers'] = $employers['debug'];
        //$debug['docs'] = $docs['debug'];
        
        $vars = [
			'employers'  => $employerBundles, // each item: ['post' => WP_Post, 'docs' => WP_Post[], ...]
			//'handler'    => $handlerFactory,
			'totals' => $totalsByYear,
			'pagination' => $pagination,
			//'years'      => ScopedDateResolver::extractYears($scope),
			'years' => $years,
			'print_header' => $atts['print_header'] ?? true,
			'print_footer' => $atts['print_footer'] ?? true,
			'info' => $info,
			'debug'      => $debug ?? null,
		];

        return ViewLoader::renderToString(
            $view,
            $vars,
            ['kind' => 'partial', 'module' => 'employment'] //, 'post_type' => self::CPT
        );
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
	 * Organize tax documents by year
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
	 * Prepare doc display data with formatted amounts
	 */
	private function prepareDocData(array $docs): array
	{
		$prepared = [];
		foreach ($docs as $doc) {
			$gross = (float) get_post_meta($doc->ID, 'gross_income', true);
			$netComp = (float) get_post_meta($doc->ID, 'net_compensation', true);
			$totalComp = (float) get_post_meta($doc->ID, 'total_compensation', true);
			
			$prepared[] = [
				'post'  => $doc,
				'gross' => $gross > 0 ? '$' . number_format($gross, 0) : '—',
				'net'   => $netComp > 0 ? '$' . number_format($netComp, 0) : '—',
				'total' => $totalComp > 0 ? '$' . number_format($totalComp, 0) : '—',
				'gross_raw' => $gross,
				'net_raw' => $netComp,
				'total_raw' => $totalComp,
			];
		}
		return $prepared;
	}
}