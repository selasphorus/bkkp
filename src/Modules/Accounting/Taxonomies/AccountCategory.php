<?php

namespace atc\Bkkp\Modules\Accounting\Taxonomies;

use atc\WXC\Core\TaxonomyHandler;

class AccountCategory extends TaxonomyHandler
{
    public function __construct(\WP_Term|null $term = null)
    {
        parent::__construct([
            'slug'         => 'account_category',
            'plural_slug'  => 'account_categories',
            'object_types' => ['account'],
            'hierarchical' => true,
        ], $term);
    }
}
