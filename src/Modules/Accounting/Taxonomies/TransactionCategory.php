<?php

namespace atc\Bkkp\Modules\Accounting\Taxonomies;

use atc\WXC\Taxonomies\TaxonomyHandler;

class TransactionCategory extends TaxonomyHandler
{
    public function __construct(\WP_Term|null $term = null)
    {
        parent::__construct([
            'slug'         => 'transaction_category',
            'plural_slug'  => 'transaction_categories',
            'object_types' => ['transaction'],
            'hierarchical' => true,
        ], $term);
    }
}
