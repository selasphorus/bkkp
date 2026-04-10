<?php

namespace atc\Bkkp\Modules\Documents\Taxonomies;

use atc\WXC\Taxonomies\TaxonomyHandler;

class DocumentCategory extends TaxonomyHandler
{
    protected static function defineConfig(): array
    {
        return [
            'slug'         => 'document_category',
            'plural_slug'  => 'document_categories',
            'object_types' => ['document'],
            'hierarchical' => true,
        ];
    }
}
