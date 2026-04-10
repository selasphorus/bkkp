<?php

namespace atc\Bkkp\Modules\Accounting\Taxonomies;

use atc\WXC\Taxonomies\TaxonomyHandler;

class AccountCategory extends TaxonomyHandler
{
    protected static function defineConfig(): array
    {
        return [
            'slug'         => 'account_category',
            'plural_slug'  => 'account_categories',
            'object_types' => ['account'],
            'hierarchical' => true,
        ];
    }
}
