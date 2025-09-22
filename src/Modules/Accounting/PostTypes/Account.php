<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use WP_Post;
use atc\WHx4\Core\PostTypeHandler;

class Account extends PostTypeHandler
{
	public function __construct(WP_Post|null $post = null) {
		$config = [
			'slug'        => 'account',
			'menu_icon'   => 'dashicons-bank',
			'capability_type' => ['account','accounts'],
			//'taxonomies'   => [ 'habitat' ],
		];

		parent::__construct( $config, $post );
	}

	public function boot(): void
	{
	    parent::boot(); // Optional if you add shared logic later
	}

    public function getStatus() {
        return isset($this->post) ? get_post_meta($this->post->ID, 'account_status', true) : "Unknown";
    }
}

