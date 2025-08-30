<?php

namespace atc\Bkkp\Modules\TaxPrep\PostTypes;

use atc\WHx4\Core\PostTypeHandler;

class TaxForm extends PostTypeHandler
{
    public function __construct(WP_Post|null $post = null) {
        $config = [
            'slug'        => 'tax_form',
            'plural_slug' => 'tax_forms',
            //'rewrite' => ['slug' => 'whimsy'],
            'menu_icon'   => 'dashicons-forms',
            'capability_type' => ['account','accounts'], // ??? separate caps? or fold in to accounts?
            //'hierarchical' => false,
            'taxonomies' => ['item_label'],//'admin_tag', 'income_category'
        ];

        parent::__construct( $config, $post );
    }

    public function boot(): void
    {
        parent::boot(); // Optional if you add shared logic later
    }

}

