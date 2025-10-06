<?php

declare(strict_types=1);

namespace atc\Bkkp\Modules\Accounting\Shortcodes;

use atc\WHx4\Core\WHx4;
use atc\WHx4\Utils\ClassInfo;
use atc\WHx4\Core\PostTypeHandler;
use atc\WHx4\Core\ViewLoader;
use atc\WHx4\Core\SubtypeRegistry;
use atc\WHx4\Core\Contracts\ShortcodeInterface;
use atc\WHx4\Core\Query\ScopedDateResolver;
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
    //public function render(array $atts = [], ?string $content = null, string $tag = 'transactions'): string
    public function render(array $atts = [], string $content = '', string $tag = ''): string
    {
        $handler  = new Transaction();
        $info = "";

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
				'group_by' => 'none', // supports: none | category | category_years
			];
			
		// Additional controls:
		// group_by: none|category
		// categories: "all" | "active" | CSV slugs | array
		// include_empty_groups: "0"|"1" (only applies when group_by=category)
		$defaults = array_merge($defaults, [
			//'group_by'             => 'none',
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
		
		// Resolve category set
		$categories = $handler->resolveCategories($atts);

		// Check for scope in query_var and override atts/default scope if found
		$scope = PostTypeHandler::getScopeFromRequest($atts, 'this_year');
		
		// Ensure downstream filters/queries see the final scope
		$atts['scope'] = $scope;
		
		// Normalize group mode (before branching)
		$groupMode = strtolower(trim((string)($atts['group_by'] ?? 'none')));
		if (!in_array($groupMode, ['none', 'category', 'category_years'], true)) {
			$groupMode = 'none';
		}
		$info .= "groupMode: {$groupMode}<br />";
		$atts['group_by'] = $groupMode;
		
		// Branch
		if ($groupMode === 'category') {
			// grouped-by-category path
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
			    $info .= "No categories resolved, therefore no posts to display.";
				return ViewLoader::renderToString(
					'transactions-summary-grouped',
					[
						'posts'        => [],
						'total'        => 0.0,
						'grouped'      => true,
						'groups'       => [],
						'overallTotal' => 0.0,
						'atts'         => $atts,
						'info'         => $info,
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
					'info'         => $info,
				],
				[
					'kind'      => 'view',
					'module'    => 'accounting',
					'post_type' => 'transaction',
				]
			);
		} elseif ($groupMode === 'category_years') {
			// grouped-by-category-years path
		
			// Resolve year window from scope
			$bounds  = ScopedDateResolver::resolve($scope, ['mode' => 'DATE']); // ['start'=>DT,'end'=>DT]
			$startY  = (int)$bounds['start']->format('Y');
			$endY    = (int)$bounds['end']->format('Y');
			$years   = range($startY, $endY);
		
			// Build table rows: one row per category; columns per year: sum & count
			$rows = [];
			$overall = ['sum' => 0.0, 'count' => 0];
		
			foreach ($categories as $term) {
				// fetch all transactions in scope for this category (no paging)
				$filters = $atts;
				$filters['transaction_category'] = [$term->slug];
				$filters['limit'] = -1;
		
				$result = $handler->getTransactions($filters);
				$posts  = $result['posts'] ?? [];
		
				// initialize columns
				$cols = [];
				foreach ($years as $y) {
					$cols[$y] = ['sum' => 0.0, 'count' => 0];
				}
		
				// bucket by tax_year
				foreach ($posts as $p) {
					$ty = (int) get_post_meta($p->ID, 'tax_year', true);
					if ($ty >= $startY && $ty <= $endY) {
						$amtRaw = get_post_meta($p->ID, 'transaction_amount', true);
						$amt    = is_numeric($amtRaw) ? (float)$amtRaw : 0.0;
						$cols[$ty]['sum']   += $amt;
						$cols[$ty]['count'] += 1;
		
						$overall['sum']   += $amt;
						$overall['count'] += 1;
					}
				}
		
				// optionally skip empty rows unless include_empty_groups="1"
				$hasAny = array_sum(array_column($cols, 'count')) > 0;
				if ($hasAny || ($atts['include_empty_groups'] ?? '0') === '1') {
					$rows[] = [
						'term'   => $term,     // \WP_Term
						'cols'   => $cols,     // year => ['sum','count']
						'result' => $result,   // raw payload if needed
					];
				}
			}
		
			return ViewLoader::renderToString(
				'transactions-summary-grouped',
				[
					'grouped'      => 'category_years',
					'years'        => $years,
					'rows'         => $rows,     // iterate terms; within each, iterate $years for cols
					'overall'      => $overall,  // grand totals across all years/categories
					'atts'         => $atts,
				],
				[
					'kind'      => 'view',
					'module'    => 'accounting',
					'post_type' => 'transaction',
				]
			);
		
		} else {
			// Simple (ungrouped) path
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
					'info'   => $info,
				],
				[
					'kind'      => 'view',
					'module'    => 'accounting',
					'post_type' => 'transaction',
				]
			);
		}
    }
}
