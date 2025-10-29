<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use atc\WHx4\Core\PostTypeHandler;
use atc\WHx4\Core\Query\PostQuery;
use atc\WHx4\Core\Http\UrlParamBridge;
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
	}

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
}
