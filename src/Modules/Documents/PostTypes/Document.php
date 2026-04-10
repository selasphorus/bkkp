<?php

namespace atc\Bkkp\Modules\Documents\PostTypes;

use atc\WXC\PostTypes\PostTypeHandler;

class Document extends PostTypeHandler
{
    protected static function defineConfig(): array
    {
        return [
            'slug'             => 'document',
			//'rewrite'          => ['slug' => 'ledger'],
            'menu_icon'        => 'dashicons-media-document',
			'capability_type'  => ['document','documents'],
            'supports'         => ['title', 'author', 'thumbnail', 'editor', 'excerpt', 'revisions', 'page-attributes'],
			'taxonomies'       => ['document_category'],
            'default_taxonomy' => 'document_category',
            'labels'           => [
				//'add_new_item' => 'Gather a new Group',
            ],
			'hierarchical' => true,
        ];
    }

    public function boot(): void
    {
        parent::boot(); // Optional if you add shared logic later
    }
}
