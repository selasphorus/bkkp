<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use atc\WHx4\Core\PostTypeHandler;
use atc\WHx4\Core\Query\PostQuery;
use atc\WHx4\Http\UrlParamBridge;
use atc\Bkkp\Modules\Accounting\PostTypes\Transaction;

class Account extends PostTypeHandler
{
	public function __construct(?\WP_Post $post = null) {
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
		//$urlArgs = UrlParamBridge::collect(Transaction::class, ['scope','transaction_category','transaction_type']);
		
		// Set default scope? TBD
		
		// Merge: base → URL params → programmatic filters
		//$merged = UrlParamBridge::merge(Transaction::class, $base, $urlArgs);
		if (!empty($filters)) {
			//$merged = array_merge($merged, $filters);
			$merged = array_merge($base, $filters);
		} else {
		    $merged = $base;
		}
		
		$transactionHandler = PostTypeHandler::getHandler('transaction');
		if ( $transactionHandler ) {
		    $result = $transactionHandler->getTransactions($merged);
		    return $result['posts'] ?? [];
		}
		
		return [];		
	}

	/**
	 * Get credit transactions only
	 */
	//public function getCredits( $scope = "this_month"): array //string
	public function getCredits(array $filters = []): array
	{
		$filters['transaction_type'] = 'credit';
		return $this->getTransactions($filters);
	}
	
	/**
	 * Get debit transactions only
	 */
	public function getDebits(array $filters = []): array
	{
		$filters['transaction_type'] = 'debit';
		return $this->getTransactions($filters);
	}
}
