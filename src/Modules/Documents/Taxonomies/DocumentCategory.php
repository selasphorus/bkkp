<?php

namespace atc\Bkkp\Modules\Documents\Taxonomies;

use atc\BhWP\Core\TaxonomyHandler;

class DocumentCategory extends TaxonomyHandler
{
    public function __construct(\WP_Term|null $term = null)
    {
        parent::__construct([
            'slug'         => 'document_category',
            'plural_slug'  => 'document_categories',
            'object_types' => ['document'],
            'hierarchical' => true,
        ], $term);
    }
}
