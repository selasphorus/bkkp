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
    
    public function getRelatedTransactions(array $options = []): array
	{
		$limit = isset($options['limit']) && (int)$options['limit'] > 0 ? (int)$options['limit'] : 20;
	
		$base = [
			'post_type' => 'transaction', // adjust if your CPT slug differs
			//'account'   => $this->getPostId(),
			'limit'     => $limit,
			// Forward date scoping to PostQuery → ScopedDateResolver
			'date_meta' => is_array(Transaction::DATE_META) ? Transaction::DATE_META : ['key' => Transaction::DATE_META],
			//'date_meta' => ['key' => \smith\Rex\Modules\Moneybags\PostTypes\Transaction::DATE_META],
	
			// Ensure results are tied to THIS account. Choose the approach that matches your schema:
			/*
			(A) If PostQuery supports a first-class 'account' arg:
			    'account' => $this->getPostId(),
	
			(B) If account is stored in postmeta (adjust key name as needed):
				'meta' => [
					 'relation' => 'AND',
					 'clauses'  => [[
						 'type'  => 'equals',
						 'key'   => 'account', // e.g., 'rex_account' or 'account_id'
						 'value' => $this->getPostId(),
						 'cast'  => 'NUMERIC',
					 ]],
				],
			*/
		];
	
		// Collect URL params for Transactions (scope + category). Include both keys for compatibility.
		$urlArgs = UrlParamBridge::collect(Transaction::class, ['scope','transaction_category','transaction_type']);
	
		// Merge with override semantics from Transaction::allowedUrlParams()
		$args = UrlParamBridge::merge(Transaction::class, $base, $urlArgs);
	
		// Optional programmatic overrides/extensions
		if(!empty($options)){
			$args = array_merge($args, $options);
		}
	
		// Run the query; result shape per your PostQuery draft: ['posts','found','max_pages','args','query_request']
		$result = (new PostQuery())->find($args);
	
		return $result['posts'] ?? [];
		//return PostQuery::fromRequest(\smith\Rex\Modules\Moneybags\PostTypes\Transaction::class, $args)->getPosts();
	}
    
    public function getTransactions( $scope = "this_month"): array //string
    {
        /*
        $args = [
			'post_type' => 'transaction',
			'account'   => $this->getPostId(),
			'limit'     => (int)($options['limit'] ?? 20),
			'date_meta' => ['key' => Transaction::DATE_META],
		];
		return PostQuery::fromRequest(Transaction::class, $args)->getPosts();
		*/
        $related = PostTypeHandler::getRelatedPosts( $this->getPostId(), 'transaction', 'account' ); // getRelatedPosts( $post_id = null, $related_post_type = null, $related_field_name = null, $limit = '1' )
		/*if ( $arr_obj_transactions ) {

			//$info .= "<h3>Transactions:</h3>";

			//$info .= "<p>arr_obj_transactions (".count($arr_obj_transactions)."): <pre>".print_r($arr_obj_transactions, true)."</pre></p>";
			foreach ( $arr_obj_transactions as $transaction ) {
				//$info .= $transaction->post_title."<br />";
				// TODO: load table view...
				$rep_info = Transaction::getSummary( $transaction->ID, 'display', false, true );
				$info .= make_link( get_permalink($transaction->ID), $rep_info, "TEST rep title" )."<br />";
			}
		}*/
		if (empty($related)) { $related = []; } // tmp
		return $related;
    }
    
    public function getCredits( $scope = "this_month"): array //string
    {
    }
    
    public function getDebits( $scope = "this_month"): array //string
    {
    }
}
