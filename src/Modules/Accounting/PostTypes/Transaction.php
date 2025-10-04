<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use atc\WHx4\Core\PostTypeHandler;

class Transaction extends PostTypeHandler
{
	public const DATE_META = 'transaction_date';
	
	public function __construct(?\WP_Post|null $post = null) {
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
				'sanitize' => [self::class, 'sanitizeScope'],
				'map_to'   => ['arg' => 'scope'], // PostQuery will forward to ScopedDateResolver
				'override' => true,
			],
			'transaction_type' => [
				'sanitize' => [self::class, 'sanitizeTransactionType'],
				'map_to'   => ['tax' => 'transaction_type', 'field' => 'slug'], // TaxQueryBuilder input
				'override' => true,
			],
		];

		// Optional extension point for add-ons/themes.
		return apply_filters('whx4_allowed_url_params_transaction', $spec);
	}

	/**
	 * Accepts named scopes (today,this_week,last_year), bare years (e.g., 2024),
	 * or other tokens you support. Semantics are enforced by ScopedDateResolver.
	 */
	private static function sanitizeScope(mixed $value): ?string
	{
		if ($value === null) {
			return null;
		}
		if (is_array($value)) {
			$value = reset($value);
		}
		$value = strtolower(trim((string)$value));
		if ($value === '') {
			return null;
		}
		// Guardrails: keep only [a-z0-9,_-].
		$value = preg_replace('/[^a-z0-9,_-]/', '', $value) ?? '';
		return $value !== '' ? $value : null;
	}

	/**
	 * Normalizes a single slug or CSV into a unique array of slugs.
	 * Example: "income, expense,transfer" => ['income','expense','transfer']
	 */
	private static function sanitizeTransactionType(mixed $value): array
	{
		$raw = [];
		if (is_array($value)) {
			$raw = $value;
		} elseif ($value !== null && $value !== '') {
			$raw = explode(',', (string)$value);
		}

		$slugs = [];
		foreach ($raw as $item) {
			$slug = strtolower(trim((string)$item));
			$slug = preg_replace('/[^a-z0-9_-]/', '', $slug) ?? '';
			if ($slug !== '') {
				$slugs[] = $slug;
			}
		}

		return array_values(array_unique($slugs));
	}

}
