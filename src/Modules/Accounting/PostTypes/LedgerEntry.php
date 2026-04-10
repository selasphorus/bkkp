<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use atc\WXC\PostTypes\PostTypeHandler;

// TBD: should this class be related in some way to more general LogEntry class?
class LedgerEntry extends PostTypeHandler
{
	protected static function defineConfig(): array
    {
        return [
            'slug'             => 'ledger_entry',
            'plural_slug'      => 'ledger_entries',
			'rewrite'          => ['slug' => 'ledger'],
            //'menu_icon'        => 'dashicons-bank',
			'capability_type'  => ['account','accounts'],
            'supports'         => ['title', 'author', 'thumbnail', 'editor', 'excerpt', 'revisions'],
			'taxonomies'       => ['ledger_category'],
            'default_taxonomy' => 'ledger_category',
            'labels'           => [
                //'not_found' => 'No people loitering nearby',
            ],
			'hierarchical' => false,
        ];
    }

	public function boot(): void
	{
	    parent::boot(); // Optional if you add shared logic later
	}
}

