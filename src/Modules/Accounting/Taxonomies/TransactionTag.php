<?php

namespace atc\Bkkp\Modules\Accounting\Taxonomies;

use atc\WXC\Core\TaxonomyHandler;

class TransactionTag extends TaxonomyHandler
{
    public function __construct(\WP_Term|null $term = null)
    {
        parent::__construct([
            'slug'         => 'transaction_tag',
            //'plural_slug'  => 'transaction_tags',
            'object_types' => ['transaction'],
            'hierarchical' => true,
        ], $term);
    }
}
