<?php

namespace atc\Bkkp\Modules\TaxPrep\Taxonomies;

use WXC\Core\TaxonomyHandler;

// e.g. "Extension Payment"
class TaxCategory extends TaxonomyHandler
{
    public function __construct(\WP_Term|null $term = null)
    {
        parent::__construct([
            'slug'         => 'tax_category',
            'plural_slug'  => 'tax_categories',
            'object_types' => [ 'document', 'tax_payment' ],
            'hierarchical' => true,
        ], $term);
    }
}
