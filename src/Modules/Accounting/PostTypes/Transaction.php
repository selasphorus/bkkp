<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use atc\WHx4\Core\PostTypeHandler;

class Transaction extends PostTypeHandler
{
	public function __construct(WP_Post|null $post = null) {
		$config = [
			'slug'        => 'transaction',
			'menu_icon'   => 'dashicons-yes-alt',
			'capability_type' => ['account','accounts'],
		];

		parent::__construct( $config, $post );
	}

	public function boot(): void
	{
	    parent::boot(); // Optional if you add shared logic later
	}

    // Other methods related to the Account...
}

