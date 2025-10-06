<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use atc\WHx4\Core\PostTypeHandler;
use atc\WHx4\Core\Query\PostQuery;

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

	//private function resolveCategories(Transaction $handler, array $atts): array
	public function resolveCategories(array $atts): array
	{
		$param = $atts['categories'] ?? 'all';
	
		// String modes
		if (is_string($param)) {
			$mode = strtolower(trim($param));
			if ($mode === 'all') {
				return $this->getTransactionCategories([], false);
			}
			if ($mode === 'active') {
				// scope-aware: uses $atts (ensure $atts['scope'] already resolved)
				return $this->getTransactionCategories($atts, true); // scope-aware
			}
			// fall through to slug handling
		}
	
		// Slug list (CSV or array)
		$slugs = PostTypeHandler::sanitizeTermSlugsParam($param);
		if ($slugs === []) { return []; }
	
		$found = get_terms([
			'taxonomy'   => 'transaction_category',
			'slug'       => $slugs,
			'hide_empty' => false,
		]);
	
		return (!is_wp_error($found) && is_array($found)) ? $found : [];
	}
	
	/**
	 * Get transaction categories. If $activeInScope is true, restrict to categories
	 * that actually appear on transactions within the provided scope (and other filters).
	 *
	 * $filters may include 'scope' and anything your getTransactions() already supports.
	 *
	 * @return \WP_Term[] Indexed by term_id (default WP_Term shape).
	 */
	public function getTransactionCategories(array $filters = [], bool $activeInScope = false): array
	{
		$taxonomy = 'transaction_category';
	
		if(!$activeInScope){
			$terms = get_terms([
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			]);
			return is_array($terms) ? $terms : [];
		}
	
		// Active-in-scope: fetch posts in scope, collect their categories.
		$result = $this->getTransactions(array_merge($filters, ['limit' => -1]));
		$posts  = $result['posts'] ?? [];
	
		if($posts === []){
			return [];
		}
	
		$postIds = array_map(static fn($p) => (int)$p->ID, $posts);
		$termObjs = wp_get_object_terms($postIds, $taxonomy, ['fields' => 'all']);
		if(!is_array($termObjs) || $termObjs === []){
			return [];
		}
	
		// De-dup by term_id
		$out = [];
		foreach($termObjs as $t){
			$out[$t->term_id] = $t;
		}
		return array_values($out);
	}

	//
	public function getTransactions(array $filters = []): array
	{
		error_log( "Transaction::getTransactions" );
		error_log('[getTransactions] filters: ' . print_r($filters, true));
		
		// Force CPT + date meta (scope uses this key; DATE mode)
		$filters['post_type'] = 'transaction';
		$filters['date_meta'] = [
			'key'       => 'transaction_date',
			'meta_type' => 'DATE',
		];
	
		// Map transaction_category → tax map
		if(isset($filters['transaction_category'])){
			$tc = $filters['transaction_category'];
			unset($filters['transaction_category']);
			$filters['tax'] = array_merge($filters['tax'] ?? [], [
				'transaction_category' => is_array($tc) ? $tc : [$tc],
			]);
		}
	
		// Normalize per_page alias
		if(isset($filters['per_page']) && !isset($filters['limit'])){
			$filters['limit'] = (int)$filters['per_page'];
		}
	
		return (new PostQuery())->find($filters);
	}
	
	/**
	 * @param \WP_Post[] $posts  Array of transaction posts.
	 */
	public static function sumTransactionAmounts(array $posts): float
	{
		error_log( "Transaction::sumTransactionAmounts" );
		$sum = 0.0;
	
		foreach ($posts as $post){
			$raw = get_post_meta($post->ID, 'amount', true); // use getPostMeta instead?
			if ($raw === '' || $raw === null){
				error_log( "amount is empty for pID: " . $post->ID );
				continue;
			}
			error_log( "raw amount: {$raw} for pID: " . $post->ID );
			$num = is_numeric($raw) ? (float)$raw : 0.0;
			error_log( "amount: {$num} for pID: " . $post->ID );
			$sum += $num;
		}
	
		return $sum;
	}


}
