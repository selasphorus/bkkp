<?php

namespace atc\Bkkp\Modules\TaxPrep\PostTypes;

use atc\WHx4\Core\PostTypeHandler;

// DEPRECATED! TODO: convert all TaxPayment posts to Documents in tax-payments category
class TaxPayment extends PostTypeHandler
{
    public function __construct(WP_Post|null $post = null) {
        $config = [
            'slug'        => 'tax_payment',
            //'plural_slug' => 'tax_payments',
            //'rewrite' => ['slug' => 'whimsy'],
            'menu_icon'   => 'dashicons-hourglass', //'dashicons-money-alt'
            'capability_type' => ['account','accounts'], // ??? separate caps? or fold in to accounts?
            //'hierarchical' => false,
            //'taxonomies' => ['document_category'],//'admin_tag', 'income_category'
        ];

        parent::__construct( $config, $post );
    }

    public function boot(): void
    {
        parent::boot(); // Optional if you add shared logic later
    }

}

