<?php

namespace atc\Bkkp\Modules\Accounting\Taxonomies;

use atc\WXC\Taxonomies\TaxonomyHandler;

class TransactionCategory extends TaxonomyHandler
{
    protected static function defineConfig(): array
    {
        return [
            'slug'         => 'transaction_category',
            'plural_slug'  => 'transaction_categories',
            'object_types' => ['transaction'],
            'hierarchical' => true,
        ];
    }
}
