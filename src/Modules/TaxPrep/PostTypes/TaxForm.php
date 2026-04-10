<?php

namespace atc\Bkkp\Modules\TaxPrep\PostTypes;

use atc\WXC\PostTypes\PostTypeHandler;

class TaxForm extends PostTypeHandler
{
    protected static function defineConfig(): array
    {
        return [
            'slug'             => 'tax_form',
            'plural_slug'      => 'tax_forms',
			'rewrite'          => ['slug' => 'ledger'],
            'menu_icon'        => 'dashicons-forms',
			'capability_type'  => ['account','accounts'],
            'supports'         => ['title', 'author', 'thumbnail', 'editor', 'excerpt', 'revisions', 'page-attributes'],
			'taxonomies'       => ['item_label'],
        ];
    }

    public function boot(): void
    {
        parent::boot(); // Optional if you add shared logic later
    }
}
