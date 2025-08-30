<?php

namespace atc\Bkkp\Modules\Employment\PostTypes;

use atc\WHx4\Core\PostTypeHandler;

// TBD: is this actually a post type, or merely a transaction/document category?
class WorkPayment extends PostTypeHandler
{
	public function __construct(WP_Post|null $post = null) {
		$config = [
			'slug'        => 'workpayment',
			//'menu_icon'   => 'dashicons-palmtree',
			'capability_type' => ['account','accounts'],
		];

		parent::__construct( $config, $post );
	}

	public function boot(): void
	{
	    parent::boot(); // Optional if you add shared logic later
	}

    // Other methods related to the WorkPayment...
}

