<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use WP_Post;
use atc\WHx4\Core\PostTypeHandler;
use atc\Bkkp\Modules\Accounting\PostTypes\Transaction;

class Account extends PostTypeHandler
{
	public function __construct(WP_Post|null $post = null) {
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

    /*
    public function getSN(): string
    {
        return (string)$this->getPostMeta('secret_name', 'orange');
    }
    */

    public function getStatements( $scope = "this_year" ): string
    {
        $related = getRelatedPosts( $this->getPostId, 'document', 'account' ); // TODO: add 'scope' parameter
        return $related;
    }

    public function getTransactions( $scope = "this_month"): array //string
    {
        $related = PostTypeHandler::getRelatedPosts( $this->getPostId, 'transaction', 'account' ); // getRelatedPosts( $post_id = null, $related_post_type = null, $related_field_name = null, $limit = '1' )
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
}

