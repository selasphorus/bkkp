<?php

namespace atc\Bkkp\Modules\Documents\PostTypes;

use atc\WHx4\Core\PostTypeHandler;

class Document extends PostTypeHandler
{
    public function __construct(?\WP_Post $post = null) {
        $config = [
            'slug'        => 'document',
            //'plural_slug' => 'documents',
            //'rewrite' => ['slug' => 'whimsy'],
            'menu_icon'   => 'dashicons-media-document',
            'capability_type' => ['document','documents'],
            //'hierarchical' => false,
            'taxonomies' => ['document_category'],//'admin_tag', 'income_category'
        ];

        parent::__construct( $config, $post );
    }

    public function boot(): void
    {
        parent::boot(); // Optional if you add shared logic later
    }
}
