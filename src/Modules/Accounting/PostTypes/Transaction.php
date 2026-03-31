<?php

namespace atc\Bkkp\Modules\Accounting\PostTypes;

use atc\WXC\PostTypes\PostTypeHandler;
use atc\WXC\Query\PostQuery;
use atc\WXC\Traits\AppliesScopeToMainQuery;

class Transaction extends PostTypeHandler
{
	use AppliesScopeToMainQuery;
	public const DATE_META = 'transaction_date';
	
	// Store ACP hash IDs as class constants
	// TODO: Move to options table for portability across installations
	private const ACP_TAX_YEAR_HASH = '21fe4931b33334';
	private const ACP_TRANSACTION_DATE_HASH = '4ab8908983d70c';
	private const ACP_ACCOUNT_HASH = '634b1b1d09fbe8';
	private const ACP_RELATED_GROUP_HASH = '683bc82d0624dc';
	private const ACP_LAYOUT_ID = '69041301a94d2'; // "X-Check" layout
	// TODO: Expand to handle other layouts, including:
	// '68b645905c8d6'; // "Transaction Basics" layout
	// '68f62156839f4'; // "Transaction Basics+" layout
	// '68b630c2630dd' -- "Import Audit"
	
	public function __construct(?\WP_Post $post = null) {
		$config = [
			'slug'        => 'transaction',
			'menu_icon'   => 'dashicons-yes-alt',
			'capability_type' => ['account','accounts'],
			'taxonomies'   => [ 'transaction_tag', 'transaction_category' ],
		];

		parent::__construct( $config, $post );
	}

	public function boot(): void
	{
	    parent::boot(); // Optional if you add shared logic later
	    
	    // Register scope filtering
        $this->registerScopeFilter();
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
			'ttype' => [
				'sanitize' => [PostTypeHandler::class, 'sanitizeTermSlugsParam'],
				'map_to'   => ['arg' => 'ttype'],
				'override' => true,
			],
			/*'transaction_type' => [
				'sanitize' => [PostTypeHandler::class, 'sanitizeTermSlugsParam'],
				'map_to'   => ['tax' => 'transaction_type', 'field' => 'slug'], // TaxQueryBuilder input
				'override' => true,
			],*/
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
	 *   - 'scope' => string - Date scope (e.g., '2025-01', 'this_month', '2024-2025')
	 *   - 'related_group' => int - Employer/group ID
	 *   - 'account' => int - Account ID
	 *   - 'transaction_category' => int|string - Category term ID or slug
	 * @param bool $use_layout Whether to include the saved ACP layout ID
	 * @return string The filtered admin URL
	 */
	public static function getFilteredAdminUrl(array $filters = [], bool $use_layout = true): string
	{
		$args = ['post_type' => 'transaction'];
		
		if ($use_layout) {
			$args['layout'] = self::ACP_LAYOUT_ID;
		}
		
		$rules = [];
		
		// Tax year filter (range) - using ACP's custom tax_year field
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
		
		// Scope filter - converts to transaction_date range using Smart Filtering
		if (isset($filters['scope'])) {
			$scope = $filters['scope'];
			
			// Handle YYYY-MM format explicitly (first day to last day of month)
			if (is_string($scope) && preg_match('/^(\d{4})-(\d{2})$/', $scope, $matches)) {
				$year = $matches[1];
				$month = $matches[2];
				
				// Calculate last day of month
				$lastDay = date('t', strtotime("$year-$month-01"));
				
				$dateRange = [
					'start' => "$year-$month-01",
					'end' => "$year-$month-$lastDay"
				];
			} else {
				// Use ScopedDateResolver for other scope formats
				$dateRange = \atc\WXC\Query\ScopedDateResolver::resolve($scope, ['mode' => 'DATE']);
			}
			
			if (!empty($dateRange['start']) && !empty($dateRange['end'])) {
				$rules[] = [
					'uid' => self::generateRuleUid(),
					'id' => self::ACP_TRANSACTION_DATE_HASH,
					'operator' => 'between',
					'value' => [
						$dateRange['start'],
						$dateRange['end']
					]
				];
			}
		}
		
		// Account filter
		if (isset($filters['account'])) {
			$args["acp_filter[" . self::ACP_ACCOUNT_HASH . "]"] = $filters['account'];
		}
		
		// Transaction category filter (taxonomy - note the special key format)
		if (isset($filters['transaction_category'])) {
			$args["acp_filter[taxonomy-transaction_category]"] = $filters['transaction_category'];
		}
		
		// Related group filter
		if (isset($filters['related_group'])) {
			$args["acp_filter[" . self::ACP_RELATED_GROUP_HASH . "]"] = $filters['related_group'];
		}
		
		// Build base URL first
		$baseUrl = add_query_arg($args, admin_url('edit.php'));
		
		// Add Smart Filtering rules manually (after add_query_arg to avoid double encoding)
		if (!empty($rules)) {
			$rulesJson = json_encode([
				'condition' => 'AND',
				'rules' => $rules
			], JSON_UNESCAPED_SLASHES);
			
			// Manually append the ac-rules parameter with proper URL encoding
			$baseUrl .= '&ac-rules=' . rawurlencode($rulesJson);
		}
		
		// Add filter action if we have filters
		if (count($args) > 1) {
			$baseUrl .= '&filter_action=Filter';
		}
		
		return $baseUrl;
	}
	/*
	public static function getFilteredAdminUrl(array $filters = [], bool $use_layout = true): string
	{
		$args = ['post_type' => 'transaction'];
		
		if ($use_layout) {
			$args['layout'] = self::ACP_LAYOUT_ID;
		}
		
		$rules = [];
		
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
		
		// Scope filter - converts to transaction_date range using Smart Filtering
		if (isset($filters['scope'])) {
			$scope = $filters['scope'];
			
			// Handle YYYY-MM format explicitly (first day to last day of month)
			if (is_string($scope) && preg_match('/^(\d{4})-(\d{2})$/', $scope, $matches)) {
				$year = $matches[1];
				$month = $matches[2];
				
				// Use DateHelper to parse the start of month
				$startDate = \atc\WXC\Utils\DateHelper::parseFlexibleDate("$year-$month-01", true);
				$endDate = $startDate->modify('last day of this month');
				
				$dateRange = [
					'start' => $startDate->format('Y-m-d'),
					'end' => $endDate->format('Y-m-d')
				];
			} else {
				// Use ScopedDateResolver for other scope formats
				$dateRange = \atc\WXC\Query\ScopedDateResolver::resolve($scope, ['mode' => 'DATE']);
			}
			
			if (!empty($dateRange['start']) && !empty($dateRange['end'])) {
				$rules[] = [
					'uid' => self::generateRuleUid(),
					'id' => self::ACP_TRANSACTION_DATE_HASH,
					'operator' => 'between',
					'value' => [
						$dateRange['start'],
						$dateRange['end']
					]
				];
			}
		}
		
		// Account filter
		if (isset($filters['account'])) {
			$args["acp_filter[" . self::ACP_ACCOUNT_HASH . "]"] = $filters['account'];
		}
		
		// Transaction category filter (taxonomy - note the special key format)
		if (isset($filters['transaction_category'])) {
			$args["acp_filter[taxonomy-transaction_category]"] = $filters['transaction_category'];
		}
		
		// Related group filter
		if (isset($filters['related_group'])) {
			$args["acp_filter[" . self::ACP_RELATED_GROUP_HASH . "]"] = $filters['related_group'];
		}
		
		// Add Smart Filtering rules if we have any
		if (!empty($rules)) {
			// Properly encode as JSON string (WordPress will URL-encode it)
			$args['ac-rules'] = wp_json_encode([
				'condition' => 'AND',
				'rules' => $rules
			]);
		}
		
		// Add filter action if we have filters
		if (count($args) > 1) {
			$args['filter_action'] = 'Filter';
		}
		
		return add_query_arg($args, admin_url('edit.php'));
	}
	*/

	/**
	 * Generate a pseudo-random UID for ACP rule (matches ACP's format)
	 * 
	 * @return string 14-character hex string
	 */
	private static function generateRuleUid(): string
	{
		return bin2hex(random_bytes(7));
	}

	//private function resolveCategories(Transaction $handler, array $atts): array
	/*public function resolveCategories(array $atts): array
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
	}*/
	public function resolveCategories(array $atts): array
	{
		return $this->resolveTerms('transaction_category', $atts['categories'] ?? 'all', $atts);
	}
	
	/**
	 * Override parent to provide Transaction-specific active term logic
	 */
	protected function getTermsForTaxonomy(string $taxonomy, array $filters = [], bool $activeInScope = false): array
	{
		if (!$activeInScope || $taxonomy !== 'transaction_category') {
			return parent::getTermsForTaxonomy($taxonomy, $filters, $activeInScope);
		}
	
		// Active-in-scope for transaction_category
		$result = $this->getTransactions(array_merge($filters, ['limit' => -1]));
		$posts  = $result['posts'] ?? [];
	
		if ($posts === []) {
			return [];
		}
	
		$postIds = array_map(static fn($p) => (int)$p->ID, $posts);
		$termObjs = wp_get_object_terms($postIds, $taxonomy, ['fields' => 'all']);
		if (!is_array($termObjs) || $termObjs === []) {
			return [];
		}
	
		// De-dup by term_id
		$out = [];
		foreach ($termObjs as $t) {
			$out[$t->term_id] = $t;
		}
		return array_values($out);
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
		error_log('filters: ' . print_r($filters, true));
		
		// Force CPT + date meta (scope uses this key; DATE mode)
		$filters['post_type'] = 'transaction';
		$filters['date_meta'] = [
			'key'       => self::DATE_META, //'transaction_date',
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
		$sum = 0.0;
	
		foreach ($posts as $post){
			$raw = get_post_meta($post->ID, 'amount', true); // TODO: use getPostMeta instead?
			if ($raw === '' || $raw === null){
				error_log( "amount is empty for pID: " . $post->ID );
				continue;
			}
			error_log( "raw amount: {$raw} for pID: " . $post->ID );
			$num = is_numeric($raw) ? (float)$raw : 0.0;
			error_log( "amount: {$num} for pID: " . $post->ID );
			
			// Get ttype and apply sign
			$ttype = get_post_meta($post->ID, 'ttype', true);
			
			// TODO: consider making transaction_type a taxonomy instead of a custom field?
			//$type_terms = wp_get_post_terms($post->ID, 'transaction_type', ['fields' => 'slugs']);
			//$type = !empty($type_terms) && !is_wp_error($type_terms) ? $type_terms[0] : '';
			
			// Debits are negative, credits are positive
			if ($ttype === 'debit') {
				$num = -abs($num);
			} else {
				$num = abs($num);  // Ensure credits are positive
			}
			
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
	 * @param string $filterKey The filter key to map (e.g., 'transaction_category', 'ttype', 'transaction_type')
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
