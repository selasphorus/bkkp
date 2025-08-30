<?php

namespace atc\Bkkp\Modules\Employment\Taxonomies;

use atc\WHx4\Core\TaxonomyHandler;

// This Taxonomy may no longer be needed -- TBD
// e.g. "1099", "1099-INT", "1099-MISC", "W2"
class IncomeCategory extends TaxonomyHandler
{
    public function __construct(\WP_Term|null $term = null)
    {
        parent::__construct([
            'slug'         => 'income_category',
            'plural_slug'  => 'income_categories',
            'object_types' => [ 'paycheck', 'tax_payment' ],
            'hierarchical' => true,
        ], $term);
    }
}
