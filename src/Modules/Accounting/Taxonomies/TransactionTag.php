<?php

namespace atc\Bkkp\Modules\Accounting\Taxonomies;

use atc\WXC\Taxonomies\TaxonomyHandler;

class TransactionTag extends TaxonomyHandler
{
    protected static function defineConfig(): array
    {
        return [
            'slug'         => 'transaction_tag',
            'plural_slug'  => 'transaction_tags',
            'object_types' => ['transaction'],
            'hierarchical' => true,
        ];
    }
}
