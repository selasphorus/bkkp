<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use atc\BhWP\Core\PostTypeHandler;
use atc\BhWP\Core\Query\PostQuery;
use atc\BhWP\Core\Http\UrlParamBridge;
use atc\WHx4\Utils\DateHelper;
//
use atc\Bkkp\Modules\Accounting\PostTypes\Transaction;

class Account extends PostTypeHandler
{
	public function __construct(?\WP_Post $post = null) 
	{
		$config = [
			'slug'        => 'account',
			'menu_icon'   => 'dashicons-bank',
			'capability_type' => ['account','accounts'],
			//'taxonomies'   => [ 'habitat' ],
		];

		parent::__construct( $config, $post );
	}

	public function boot(): void
	{
	    parent::boot(); // Optional if you add shared logic later
	}

    public function getStatus(): string
    {
        return (string)$this->getPostMeta('account_status', 'Unknown');
    }

    public function getStatements( $scope = "this_year" ): string
    {
        $related = getRelatedPosts( $this->getPostId(), 'document', 'account' ); // TODO: add 'scope' parameter
        return $related;
    }
      
    /**
	 * Get transactions for this account with optional filters.
	 * Automatically respects URL parameters (scope, category, type) when present.
	 */
	public function getTransactions(array $filters = []): array
	{
		$base = [
			'account' => $this->getPostId(),  // Always filter to THIS account
			'limit'   => -1,  // Get all by default
		];
		
		// Collect URL params if available (scope, category, type)
		$urlArgs = UrlParamBridge::collect(Transaction::class, ['scope','transaction_category','ttype','transaction_type']);
		
		// Set default scope? TBD
		
		// Merge: base → URL params → programmatic filters
		$merged = UrlParamBridge::merge(Transaction::class, $base, $urlArgs);
		if (!empty($filters)) {
			$merged = array_merge($merged, $filters);
		}
		
		// Instantiate a Transaction handler
		$transactionHandler = new Transaction();
		if ( $transactionHandler ) {
		    $result = $transactionHandler->getTransactions($merged);
		    return $result['posts'] ?? [];
		}
		
		return [];		
	}
	
	/**
	 * Get transaction statistics grouped by year and month
	 * 
	 * @param array $filters Optional filters to pass to getTransactions()
	 * @return array ['yearly' => [...], 'monthly' => [...]]
	 */
	public function getTransactionStats(array $filters = []): array
	{
		$transactions = $this->getTransactions($filters);
		
		$yearData = [];
		$monthData = [];
		
		foreach ($transactions as $transaction) {
			$dateValue = get_post_meta($transaction->ID, 'transaction_date', true);
			$ttype = get_post_meta($transaction->ID, 'ttype', true);
			
			// Extract year and month from yyyymmdd format
			$year = substr($dateValue, 0, 4);
			$month = substr($dateValue, 4, 2);
			
			// Initialize year data if needed
			if (!isset($yearData[$year])) {
				$yearData[$year] = ['total' => 0, 'credits' => 0, 'debits' => 0];
			}
			
			// Initialize month data if needed
			if (!isset($monthData[$year])) {
				$monthData[$year] = [];
			}
			if (!isset($monthData[$year][$month])) {
				$monthData[$year][$month] = [
					'total' => 0,
					'credits' => 0,
					'debits' => 0,
					'url' => null
				];
			}
			
			// Increment counters
			$yearData[$year]['total']++;
			$monthData[$year][$month]['total']++;
			
			if ($ttype === 'credit') {
				$yearData[$year]['credits']++;
				$monthData[$year][$month]['credits']++;
			} elseif ($ttype === 'debit') {
				$yearData[$year]['debits']++;
				$monthData[$year][$month]['debits']++;
			}
		}
		
		// Sort by year descending
		krsort($yearData);
		krsort($monthData);
		
		// Sort months within each year descending and generate URLs
		foreach ($monthData as $year => &$months) {
			krsort($months);
			
			// Generate filtered URLs for each month
			foreach ($months as $month => &$data) {
				$data['url'] = Transaction::getFilteredAdminUrl([
					'account' => $this->getPostId(),
					'scope' => $year . '-' . $month
				]);
			}
		}
		
		return [
			'yearly' => $yearData,
			'monthly' => $monthData,
			'total_count' => count($transactions)
		];
	}

	/**
	 * Get transaction statistics grouped by year and month
	 * 
	 * @param array $filters Optional filters to pass to getTransactions()
	 * @return array ['yearly' => [...], 'monthly' => [...]]
	 */
	// v1 -- works but lacks CMS links
	/*
	public function getTransactionStats(array $filters = []): array
	{
		$transactions = $this->getTransactions($filters);
		
		$yearData = [];
		$monthData = [];
		
		foreach ($transactions as $transaction) {
			$dateValue = get_post_meta($transaction->ID, 'transaction_date', true);
			$ttype = get_post_meta($transaction->ID, 'ttype', true);
			
			// Extract year and month from yyyymmdd format
			$year = substr($dateValue, 0, 4);
			$month = substr($dateValue, 4, 2);
			
			// Initialize year data if needed
			if (!isset($yearData[$year])) {
				$yearData[$year] = ['total' => 0, 'credits' => 0, 'debits' => 0];
			}
			
			// Initialize month data if needed
			if (!isset($monthData[$year])) {
				$monthData[$year] = [];
			}
			if (!isset($monthData[$year][$month])) {
				$monthData[$year][$month] = ['total' => 0, 'credits' => 0, 'debits' => 0];
			}
			
			// Increment counters
			$yearData[$year]['total']++;
			$monthData[$year][$month]['total']++;
			
			if ($ttype === 'credit') {
				$yearData[$year]['credits']++;
				$monthData[$year][$month]['credits']++;
			} elseif ($ttype === 'debit') {
				$yearData[$year]['debits']++;
				$monthData[$year][$month]['debits']++;
			}
		}
		
		// Sort by year descending
		krsort($yearData);
		krsort($monthData);
		
		// Sort months within each year descending
		foreach ($monthData as &$months) {
			krsort($months);
		}
		
		return [
			'yearly' => $yearData,
			'monthly' => $monthData,
			'total_count' => count($transactions)
		];
	}*/

	/**
	 * Get credit transactions only
	 */
	//public function getCredits( $scope = "this_month"): array //string
	public function getCredits(array $filters = []): array
	{
		$filters['ttype'] = 'credit';
		return $this->getTransactions($filters);
	}
	
	/**
	 * Get debit transactions only
	 */
	public function getDebits(array $filters = []): array
	{
		$filters['ttype'] = 'debit';
		return $this->getTransactions($filters);
	}
	
	/**
	 * Prepare transaction statistics for display
	 * Pre-calculates all view data to keep templates clean
	 * 
	 * @param array $filters Optional filters to pass to getTransactions()
	 * @return array Prepared data ready for view rendering
	 */
	public function prepareTransactionStatsForView(array $filters = []): array
	{
		$stats = $this->getTransactionStats($filters);
		
		// Full month names for display
		/*$monthNames = [
			'01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
			'05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
			'09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
		];*/
		$monthNames = DateHelper::getMonthNames('long');
		
		$preparedYears = [];
		
		foreach ($stats['yearly'] as $year => $yearStats) {
			$preparedMonths = [];
			$previousMonth = null; 
			
			if (isset($stats['monthly'][$year])) {
			
				foreach ($stats['monthly'][$year] as $month => $monthData) {
					// Detect if there's a data gap from the previous month
					$hasGap = false;
					if ($previousMonth !== null) {
						$prevMonthNum = (int)$previousMonth;
						$currMonthNum = (int)$month;
						
						// Check if months are not consecutive (descending order)
						if ($currMonthNum !== ($prevMonthNum - 1)) {
							$hasGap = true;
						}
					}
					
					$preparedMonths[] = [
						'month_number' => $month,
						'month_name' => $monthNames[$month],
						'total' => $monthData['total'],
						'credits' => $monthData['credits'],
						'debits' => $monthData['debits'],
						'url' => $monthData['url'],
						'has_gap' => $hasGap
					];
					
					$previousMonth = $month;
				}
			}
			
			$preparedYears[] = [
				'year' => $year,
				'stats' => $yearStats,
				'months' => $preparedMonths,
				'has_months' => !empty($preparedMonths)
			];
		}
		
		return [
			'years' => $preparedYears,
			'total_count' => $stats['total_count'],
			'has_data' => !empty($stats['yearly'])
		];
	}
	
	/**
	 * Prepare all data needed for the content view
	 * This keeps the view clean and dependency-free
	 * 
	 * @return array Variables ready for view consumption
	 */
	public function prepareViewData(): array
	{
		return [
			'status' => $this->getStatus(),
			'viewData' => $this->prepareTransactionStatsForView(),
			'postMeta' => $this->getPostMeta(),
		];
	}
}
