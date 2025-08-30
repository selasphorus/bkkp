<?php

namespace atc\Bkkp\Modules\Documents\PostTypes;

use atc\WHx4\Core\PostTypeHandler;

class Document extends PostTypeHandler
{
    public function __construct(WP_Post|null $post = null) {
        $config = [
            'slug'        => 'document',
            //'plural_slug' => 'documents',
            //'rewrite' => ['slug' => 'whimsy'],
            //'menu_icon'   => 'dashicons-palmtree',
            'capability_type' => ['account','accounts'],
            //'hierarchical' => false,
            //'taxonomies' => ['admin_tag', 'secret_category'],
        ];

        parent::__construct( $config, $post );
    }

    public function boot(): void
    {
        parent::boot(); // Optional if you add shared logic later
    }

}

