<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use atc\WHx4\Core\PostTypeHandler;
use atc\WHx4\Core\Query\PostQuery;

class Transaction extends PostTypeHandler
{
	public const DATE_META = 'transaction_date';
	
	// Store ACP hash IDs as class constants
	// TODO: Move to options table for portability across installations
	private const ACP_TAX_YEAR_HASH = '21fe4931b33334';
	private const ACP_RELATED_GROUP_HASH = '683bc82d0624dc';
	private const ACP_LAYOUT_ID = '68b645905c8d6'; // "Transaction Basics" layout
	
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
			'related_group' => [
			    'sanitize' => [PostTypeHandler::class, 'sanitizePostIdOrSlugParam'],
			    'map_to'   => ['arg' => 'related_group'],
			    'override' => true,
			],
		];

		// Optional extension point for add-ons/themes.
		return apply_filters('whx4_allowed_url_params_transaction', $spec);
	}
	
	/**
	 * Build a URL to the admin transactions list with ACP filters applied
	 * 
	 * @param array $filters Associative array of filters to apply:
	 *   - 'tax_year' => int|array - Single year or [start, end] range
	 *   - 'related_group' => int - Employer/group ID
	 *   - 'account' => int - Account ID
	 * @param bool $use_layout Whether to include the saved ACP layout ID
	 * @return string The filtered admin URL
	 */
	public static function getFilteredAdminUrl(array $filters = [], bool $use_layout = true): string
	{
		$args = ['post_type' => 'transaction'];
		
		if ($use_layout) {
			$args['layout'] = self::ACP_LAYOUT_ID;
		}
		
		// Tax year filter (range)
		if (isset($filters['tax_year'])) {
			$year = $filters['tax_year'];
			if (is_array($year)) {
				$args["acp_filter[" . self::ACP_TAX_YEAR_HASH . "][0]"] = $year[0];
				$args["acp_filter[" . self::ACP_TAX_YEAR_HASH . "][1]"] = $year[1];
			} else {
				$args["acp_filter[" . self::ACP_TAX_YEAR_HASH . "][0]"] = $year;
				$args["acp_filter[" . self::ACP_TAX_YEAR_HASH . "][1]"] = $year;
			}
		}
		
		// Related group filter
		if (isset($filters['related_group'])) {
			$args["acp_filter[" . self::ACP_RELATED_GROUP_HASH . "]"] = $filters['related_group'];
		}
		
		// Add filter action if we have filters
		if (count($args) > 1) {
			$args['filter_action'] = 'Filter';
		}
		
		return add_query_arg($args, admin_url('edit.php'));
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
			'hide_empty' => true, // ??? better to default to false? TBD
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
			'meta_type' => 'NUMERIC', // because ACF stores dates funny yyyymmdd so can't use DATE
		];
		
		// Map taxonomies to tax queries
		//$this->mapTaxonomyToTaxQuery($filters, 'transaction_category');
		//$this->mapTaxonomyToTaxQuery($filters, 'transaction_type');
		// Map transaction_category → tax map
		if(isset($filters['transaction_category'])){
			$tc = $filters['transaction_category'];
			unset($filters['transaction_category']);
			$filters['tax'] = array_merge($filters['tax'] ?? [], [
				'transaction_category' => is_array($tc) ? $tc : [$tc],
			]);
		}
		
		// Map ACF post object fields to meta queries
		$this->mapPostObjectFieldToMeta($filters, 'account', 'account', null, false);  // post_object
		$this->mapPostObjectFieldToMeta($filters, 'related_group', 'group', null, true);  // relationship
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
	
	//====== INTERNALS =======/
	
	/**
	 * Map a taxonomy filter to a tax query spec.
	 * Supports comma-separated input.
	 *
	 * @param array $filters The filters array (passed by reference)
	 * @param string $filterKey The filter key to map (e.g., 'transaction_category', 'transaction_type')
	 * @param string $taxonomy The taxonomy name (defaults to same as $filterKey)
	 */
	// WIP -- this doesn't work yet -- breaks the transactions shortcode (no records found)
	private function mapTaxonomyToTaxQuery(array &$filters, string $filterKey, string $taxonomy = null): void
	{
		if (!isset($filters[$filterKey]) || $filters[$filterKey] === '') {
			return;
		}
		
		$taxonomy = $taxonomy ?? $filterKey;
		$value = $filters[$filterKey];
		unset($filters[$filterKey]);
		
		// Normalize to array - split comma-separated strings
		$inputs = is_array($value) ? $value : explode(',', $value);
		$inputs = array_map('trim', $inputs);
		$inputs = array_filter($inputs);
		
		if ($inputs === []) {
			return;
		}
		
		// Ensure tax spec structure exists
		if (!isset($filters['tax'])) {
			$filters['tax'] = ['relation' => 'AND', 'clauses' => []];
		}
		if (!isset($filters['tax']['clauses'])) {
			$filters['tax']['clauses'] = [];
		}
		
		// Append clause
		$filters['tax']['clauses'][] = [
			'taxonomy' => $taxonomy,
			'field' => 'slug',  // Always use slug
			'terms' => $inputs,
		];
	}

    /**
	 * Map an ACF post object or relationship field to a meta query spec.
	 * Supports both post IDs and slugs, with comma-separated input.
	 *
	 * @param array $filters The filters array (passed by reference)
	 * @param string $filterKey The filter key to map (e.g., 'account', 'related_group')
	 * @param string $postType The post type for slug resolution
	 * @param string $metaKey The ACF field name (defaults to same as $filterKey)
	 * @param bool $isRelationship Whether this is a relationship field (vs post_object)
	 */
	private function mapPostObjectFieldToMeta(array &$filters, string $filterKey, string $postType, string $metaKey = null, bool $isRelationship = false): void
	{
		if (!isset($filters[$filterKey]) || $filters[$filterKey] === '') {
			return;
		}
		
		$metaKey = $metaKey ?? $filterKey;
		$value = $filters[$filterKey];
		unset($filters[$filterKey]);
		
		// Normalize to array - split comma-separated strings
		$inputs = is_array($value) ? $value : explode(',', $value);
		$inputs = array_map('trim', $inputs);
		$postIds = [];
		
		foreach ($inputs as $input) {
			if (is_numeric($input)) {
				// Already an ID
				$postIds[] = (int)$input;
			} else {
				// Resolve slug to ID
				$post = get_page_by_path($input, OBJECT, $postType);
				if ($post) {
					$postIds[] = $post->ID;
				}
			}
		}
		
		$postIds = array_filter($postIds);
		
		if ($postIds === []) {
			return;
		}
		
		// Ensure meta spec structure exists
		if (!isset($filters['meta'])) {
			$filters['meta'] = ['relation' => 'AND', 'clauses' => []];
		}
		if (!isset($filters['meta']['clauses'])) {
			$filters['meta']['clauses'] = [];
		}
		
		// Build clause based on field type
		if ($isRelationship) {
			// Relationship fields store serialized arrays - use containsSerialized
			$filters['meta']['clauses'][] = [
				'type' => 'containsSerialized',
				'key' => $metaKey,
				'values' => $postIds,  // Array of IDs
			];
		} else {
			// Post object fields store simple values
			$filters['meta']['clauses'][] = [
				'type' => count($postIds) === 1 ? 'equals' : 'in',
				'key' => $metaKey,
				'value' => count($postIds) === 1 ? $postIds[0] : $postIds,
			];
		}
	}

}
