<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use atc\WXC\Core\PostTypeHandler;

// TBD: should this class be related in some way to more general LogEntry class?
class LedgerEntry extends PostTypeHandler
{
	public function __construct(?\WP_Post $post = null) {
		$config = [
			'slug'        => 'ledger_entry',
			'plural_slug' => 'ledger_entries',
			'rewrite' => ['slug' => 'ledger'],
			//'menu_icon'   => 'dashicons-bank',
			'capability_type' => ['account','accounts'],
			'hierarchical' => false,
			'taxonomies' => ['admin_tag', 'ledger_category'],
		];

		parent::__construct( $config, $post );
	}

	public function boot(): void
	{
	    parent::boot(); // Optional if you add shared logic later
	}
}

