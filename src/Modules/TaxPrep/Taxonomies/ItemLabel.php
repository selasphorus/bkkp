<?php

namespace atc\Bkkp\Modules\TaxPrep\Taxonomies;

use atc\WXC\Core\TaxonomyHandler;

// For tax forms etc. e.g. "Federal income tax withheld", "Net Long-Term Capital Gain or (Loss)"
// This is WIP -- to be integrated properly into repeater-row form building or similar
class ItemLabel extends TaxonomyHandler
{
    public function __construct(\WP_Term|null $term = null)
    {
        parent::__construct([
            'slug'         => 'item_label',
            //'plural_slug'  => 'item_labels',
            'object_types' => [ 'document', 'tax_form' ],
            'hierarchical' => false,
            //'menu_name' => 'Labels',
        ], $term);
    }
}
