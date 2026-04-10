<?php

namespace atc\Bkkp\Modules\TaxPrep\Taxonomies;

use atc\WXC\Taxonomies\TaxonomyHandler;

// e.g. "Extension Payment"
class TaxCategory extends TaxonomyHandler
{
    protected static function defineConfig(): array
    {
        return [
            'slug'         => 'tax_category',
            'plural_slug'  => 'tax_categories',
            'object_types' => ['document', 'tax_payment'],
            'hierarchical' => true,
        ];
    }
}
