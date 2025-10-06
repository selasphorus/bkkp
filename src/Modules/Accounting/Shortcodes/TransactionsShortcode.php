<?php

declare(strict_types=1);

namespace atc\Bkkp\Modules\Accounting\Shortcodes;

use atc\WHx4\Core\WHx4;
use atc\WHx4\Utils\ClassInfo;
use atc\WHx4\Core\PostTypeHandler;
use atc\WHx4\Core\ViewLoader;
use atc\WHx4\Core\SubtypeRegistry;
use atc\WHx4\Core\Contracts\ShortcodeInterface;
//
use atc\Bkkp\Modules\Accounting\PostTypes\Transaction;

final class TransactionsShortcode implements ShortcodeInterface
{
    // This is the tag by which the shortcode will be called -- but is this method actually necessary?
    public static function tag(): string
    {
        return 'transactions';
    }
    
    /**
     * [rex_transactions] — supports atts like:
     * scope="this_month" | "2025" | "2022-2025"
     * transaction_category="rent,utilities" (or array)
     * per_page="25" paged="1" order="DESC" orderby="date"
     */
    //public function render(array $atts, string $content, string $tag): string;
    public function render(array $atts = [], string $content = '', string $tag = ''): string
    //public function render(array $atts = [], ?string $content = null, string $tag = 'transactions'): string
    {
        $handler  = new Transaction();

        // Prefer handler/CPT defaults if available; fall back to sane basics.
        // Defaults (prefer CPT defaults when available)
		$defaults = method_exists($handler, 'queryDefaults')
			? $handler->queryDefaults()
			: [
				'post_type' => 'transaction',
				'scope'     => 'this_month',
				'limit'     => -1,
				'order'     => 'DESC',
				'orderby'   => 'date',
			];
			
		// Additional controls:
		// group_by: none|category
		// categories: "all" | "active" | CSV slugs | array
		// include_empty_groups: "0"|"1" (only applies when group_by=category)
		$defaults = array_merge($defaults, [
			'group_by'             => 'none',
			'categories'           => 'all',
			'include_empty_groups' => '0',
		]);
		
		// TODO: set default to group by category?
	
		// Merge shortcode atts with defaults
		$atts = shortcode_atts($defaults, $atts, $tag);
	
		// Normalize transaction_category if provided (non-grouped path still supported)
		// (works whether param is missing, empty string, CSV, or array)
		$atts['transaction_category'] = PostTypeHandler::sanitizeTermSlugsParam($atts['transaction_category'] ?? null);
		if ($atts['transaction_category'] === []) { unset($atts['transaction_category']); }

		// Check for scope in query_var and override atts/default scope if found
        $qvScope = get_query_var('whx4_scope') ?: get_query_var('scope') ?: ($_GET['whx4_scope'] ?? $_GET['scope'] ?? '');
		$sanitized = PostTypeHandler::sanitizeScopeParam($qvScope);
		if ($sanitized !== null){ $scope = $sanitized; }
		
		$grouped = ($atts['group_by'] === 'category');
	
		if (!$grouped){
			// Simple (existing) path
			$result = $handler->getTransactions($atts);
			$posts  = $result['posts'] ?? [];
			$total  = method_exists(Transaction::class, 'sumTransactionAmounts')
				? Transaction::sumTransactionAmounts($posts)
				: 0.0;
	
			return ViewLoader::renderToString(
				'transactions-summary',
				[
					'posts'  => $posts,
					'total'  => $total,
					'result' => $result,
					'atts'   => $atts,
				],
				[
					'kind'      => 'view',
					'module'    => 'accounting',
					'post_type' => 'transaction',
				]
			);
		}
	
		// Grouped-by-category path
		// Resolve category set:
		$catParam = $atts['categories'];
		$categories = [];
	
		if (is_string($catParam)) {
			$mode = strtolower(trim($catParam));
			if ($mode === 'all'){
				$categories = $handler->getTransactionCategories([], false);
			} elseif($mode === 'active'){
				// Use scope-aware filter set to find only active categories
				$categories = $handler->getTransactionCategories($atts, true);
			} else {
				// CSV slugs → get_terms by slug
				$slugs = PostTypeHandler::sanitizeTermSlugsParam($catParam);
				if ($slugs !== []) {
					$found = get_terms([
						'taxonomy'   => 'transaction_category',
						'slug'       => $slugs,
						'hide_empty' => false,
					]);
					$categories = is_array($found) ? $found : [];
				}
			}
		} elseif (is_array($catParam)){
			$slugs = PostTypeHandler::sanitizeTermSlugsParam($catParam);
			if ($slugs !== []) {
				$found = get_terms([
					'taxonomy'   => 'transaction_category',
					'slug'       => $slugs,
					'hide_empty' => false,
				]);
				$categories = (!is_wp_error($found) && is_array($found)) ? $found : []; //$categories = is_array($found) ? $found : [];
			}
		}
	
		// Build groups: fetch transactions for each category with remaining filters
		$groups = [];
		$overallTotal = 0.0;
		$includeEmpty = $atts['include_empty_groups'] === '1';
	
		foreach ($categories as $term) {
			$filters = $atts;
			$filters['transaction_category'] = [$term->slug];
	
			// Fetch results via the CPT helper (handles scope + date_meta wiring)
			$result = $handler->getTransactions($filters);
			$posts  = $result['posts'] ?? [];
			if (!$includeEmpty && $posts === []){
				continue;
			}
	
			$sum = method_exists(Transaction::class, 'sumTransactionAmounts')
				? Transaction::sumTransactionAmounts($posts)
				: 0.0;
	
			$overallTotal += $sum;
	
			$groups[] = [
				'term'   => $term,     // \WP_Term
				'posts'  => $posts,    // \WP_Post[]
				'sum'    => $sum,      // float
				'result' => $result,   // raw query payload (for pagination/debug if needed)
			];
		}
	
		// Optional: if no categories resolved (e.g., none active), return empty view
		if ($groups === [] && !$includeEmpty) {
			return ViewLoader::renderToString(
				'transactions-summary',
				[
					'posts'        => [],
					'total'        => 0.0,
					'grouped'      => true,
					'groups'       => [],
					'overallTotal' => 0.0,
					'atts'         => $atts,
				],
				[
					'kind'      => 'view',
					'module'    => 'accounting',
					'post_type' => 'transaction',
				]
			);
		}
	
		// Render grouped
		return ViewLoader::renderToString(
			'transactions-summary-grouped',
			[
				'grouped'      => true,
				'groups'       => $groups,       // array of [term, posts, sum, result]
				'overallTotal' => $overallTotal, // sum across all groups
				'atts'         => $atts,
			],
			[
				'kind'      => 'view',
				'module'    => 'accounting',
				'post_type' => 'transaction',
			]
		);

        /*
        // Pass everything useful for the template.
        return ViewLoader::renderToString(
            'transactions-summary',
            [
                'posts'      => $posts,
                'total'      => $total,
                'result'     => $result, // includes pagination/debug if your PostQuery returns it
                'atts'       => $atts,
            ],
            [
                'kind'      => 'view',
                'module'    => 'accounting',
                'post_type' => 'transaction',
            ]
        );
        */
    }
}
