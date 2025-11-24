<?php

namespace atc\Bkkp\Modules\TaxPrep\Taxonomies;

use atc\WXC\Taxonomies\TaxonomyHandler;

// This Taxonomy may no longer be needed -- TBD
// e.g. "1099", "1099-INT", "1099-MISC", "W2"
class IncomeCategory extends TaxonomyHandler
{
    public function __construct(\WP_Term|null $term = null)
    {
        parent::__construct([
            'slug'         => 'income_category',
            'plural_slug'  => 'income_categories',
            'object_types' => [ 'tax_payment' ], // 'paycheck',
            'hierarchical' => true,
        ], $term);
    }
}
