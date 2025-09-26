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

    public function getStatus( ?WP_Post $post = null ): string
    {
        $p = $post ?? $this->getPost();
        return $p ? (string)get_post_meta($p->ID, 'account_status', true) : 'Unknown';
    }

    public function getStatements( ?WP_Post $post = null, $scope = "this_year" ): string
    {
        $p = $post ?? $this->getPost();
        $related = getRelatedPosts( $post_id, 'transaction', 'account' ); // TODO: add 'scope' parameter
        return $related;
    }

    public function getTransactions( ?WP_Post $post = null, $scope = "this_month"): array //string
    {
        $p = $post ?? $this->getPost();
        //return $p ? (string)get_post_meta($p->ID, 'account_status', true) : 'Unknown';

        $related = PostTypeHandler::getRelatedPosts( $p->ID, 'transaction', 'account' ); // getRelatedPosts( $post_id = null, $related_post_type = null, $related_field_name = null, $limit = '1' )
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
		return $related;
    }
}

