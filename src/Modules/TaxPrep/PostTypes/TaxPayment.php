<?php

namespace atc\Bkkp\Modules\TaxPrep\PostTypes;

use atc\WXC\PostTypes\PostTypeHandler;

// DEPRECATED! TODO: convert all TaxPayment posts to Documents in tax-payments category
class TaxPayment extends PostTypeHandler
{
    protected static function defineConfig(): array
    {
        return [
            'slug'             => 'tax_payment',
            'menu_icon'        => 'dashicons-hourglass',
			'capability_type'  => ['account','account'],
            'supports'         => ['title', 'author', 'thumbnail', 'editor', 'excerpt', 'revisions', 'page-attributes'],
			'taxonomies'       => ['income_category'],
            'default_taxonomy' => 'income_category',
            'labels'           => [
				//'add_new_item' => 'Gather a new Group',
            ],
        ];
    }

    public function boot(): void
    {
        parent::boot(); // Optional if you add shared logic later
    }
}
