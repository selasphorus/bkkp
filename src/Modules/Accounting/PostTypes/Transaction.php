<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use atc\WHx4\Core\PostTypeHandler;

class Transaction extends PostTypeHandler
{
	public const DATE_META = 'transaction_date';
	
	public function __construct(?\WP_Post $post = null) {
		$config = [
			'slug'        => 'transaction',
			'menu_icon'   => 'dashicons-yes-alt',
			'capability_type' => ['account','accounts'],
		];

		parent::__construct( $config, $post );
	}

	public function boot(): void
	{
	    parent::boot(); // Optional if you add shared logic later
	}

	/**
	 * Canonical allow-list of URL params that can shape Transaction queries.
	 * Consumers (shortcodes, controllers, handler methods) can pass this to a
	 * UrlParamBridge to sanitize & map into PostQuery inputs.
	 */
	public static function allowedUrlParams(): array
	{
		$spec = [
			'scope' => [
				'sanitize' => [PostTypeHandler::class, 'sanitizeScopeParam'],
				'map_to'   => ['arg' => 'scope'], // PostQuery will forward to ScopedDateResolver
				'override' => true,
			],
			'transaction_type' => [
				'sanitize' => [PostTypeHandler::class, 'sanitizeTermSlugsParam'],
				'map_to'   => ['tax' => 'transaction_type', 'field' => 'slug'], // TaxQueryBuilder input
				'override' => true,
			],
			'transaction_category' => [
				'sanitize' => [PostTypeHandler::class, 'sanitizeTermSlugsParam'],
				'map_to'   => ['tax' => 'transaction_category', 'field' => 'slug'], // TaxQueryBuilder input
				'override' => true,
			],
		];

		// Optional extension point for add-ons/themes.
		return apply_filters('whx4_allowed_url_params_transaction', $spec);
	}
}
