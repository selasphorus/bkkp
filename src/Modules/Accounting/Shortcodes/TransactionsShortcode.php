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
     * [transactions] — supports atts like:
     * scope="this_month" | "2025" | "2022-2025"
     * transaction_category="rent,utilities" (or array)
     * per_page="25" paged="1" order="DESC" orderby="date"
     */
    //public function render(array $atts, string $content, string $tag): string;
    //public function render(array $atts = [], ?string $content = null, string $tag = 'transactions'): string
    //public function render(array $atts = [], string $content = '', string $tag = ''): string
    public function render(array $atts, ?string $content = null, string $tag = ''): string
    {
        $handler = new Transaction();
        $info = "";
        
        // Defaults: start from handler, then ensure 'scope' exists, then add UI extras
        $defaults = method_exists($handler, 'queryDefaults') ? $handler->queryDefaults() : [];
        if (!array_key_exists('scope', $defaults)) { $defaults['scope'] = 'this_year'; }

		$defaults = array_merge($defaults, [
			'post_type'            => $defaults['post_type'] ?? 'transaction',
			'limit'                => $defaults['limit']     ?? -1,
			'order'                => $defaults['order']     ?? 'DESC',
			'orderby'              => $defaults['orderby']   ?? 'date',
			'group_by'             => 'category',        // none|category|category_years
			'categories'           => 'all',             // all|active|CSV|array
			'include_empty_groups' => '0',
			'account'              => '',                // account ID or slug to filter by
			'print_header'         => '', 
		]);

		// Merge user atts (use known tag to avoid odd filters if $tag is empty)
		$rawAtts = (array)$atts;
		$atts = shortcode_atts($defaults, $rawAtts, self::tag());

		// Resolve scope with query-var override, then persist back into $atts
		$scope = PostTypeHandler::getScopeFromRequest($atts, $atts['scope'] ?? 'this_year');
		// Ensure downstream filters/queries see the final scope
		$atts['scope'] = $scope;

		// Now it’s safe to resolve categories; 'active' mode needs $atts['scope']
		// Resolve category set
		$categories = $handler->resolveCategories($atts);
		
		//$info .= "preliminary atts: <pre>".print_r($atts,true)."</pre>"; // sanity check!
		
		// Normalize group mode (before branching)
		$groupMode = strtolower(trim((string)($atts['group_by'] ?? 'category')));
		if (!in_array($groupMode, ['none', 'category', 'category_years'], true)) {
			$groupMode = 'none';
		}
		$info .= "groupMode: {$groupMode}<br />";
		$atts['group_by'] = $groupMode;
		
		error_log('[TransactionsShortcode::render] atts: ' . print_r($atts, true));
		//$info .= "processed atts: <pre>".print_r($atts,true)."</pre>";
		$info .= "processed atts:<br />";
		$info .= "[scope]: ".$atts['scope']."<br />";
		$info .= "[account]: ".$atts['account']."<br />";
		
		// Prepare to render view according to groupMode
		$viewVars = [];
		$viewVars['atts'] = $atts;
		$viewVars['grouped_by'] = $groupMode;
		$viewVars['print_header'] = $atts['print_header'];
		//
		$viewSpecs = [ 'kind' => 'view', 'module' => 'accounting', 'post_type' => 'transaction' ];
		//
		if ($groupMode != "none") { $viewVars['grouped'] = true; } else { $viewVars['grouped'] = false; } // use ternary instead?
		
		// Branch
		if ($groupMode === 'category') {
			// Grouped by category
			$view = 'transactions-summary-grouped';
			
			// Build groups: fetch transactions for each category with remaining filters
			$groups = [];
			$overallTotal = 0.0;
			$includeEmpty = $atts['include_empty_groups'] === '0'; // WIP
		
			foreach ($categories as $term) {
				$filters = $atts;
				$filters['transaction_category'] = [$term->slug];
				
				// TODO: check for start/end date issues
		
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
			$viewVars['groups'] = $groups; // array of [term, posts, sum, result]
			$viewVars['overallTotal'] = $overallTotal; // sum across all groups
		
			// Optional: if no categories resolved (e.g., none active), return empty view
			if ($groups === [] && !$includeEmpty) {
			    $info .= "No categories resolved, therefore no posts to display.";
			    $viewVars['info'] = $info;
			    return ViewLoader::renderToString( $view, $viewVars, $viewSpecs );
			}
		
			// Render grouped
			$viewVars['info'] = $info;
			return ViewLoader::renderToString( $view, $viewVars, $viewSpecs );
	
		} elseif ($groupMode === 'category_years') {
			// Grouped by category per year
			$view = 'transactions-summary-grouped-catyears';
		
			// Resolve year window from scope
			$startY = $endY = null;
			$bounds  = ScopedDateResolver::resolve($scope, ['mode' => 'DATE']); // ['start'=>DT,'end'=>DT]
			//error_log('[TransactionsShortcode::render] bounds: ' . print_r($bounds, true));
			$start  = $bounds['start'] ?? null;
			$end    = $bounds['end'] ?? null;
			//
			$startY = $startY ?? (
				$start instanceof \DateTimeInterface ? (int)$start->format('Y') :
				(is_string($start) && $start !== '' ? (int)date('Y', strtotime($start)) : (int)date('Y'))
			);
			$endY = $endY ?? (
				$end instanceof \DateTimeInterface ? (int)$end->format('Y') :
				(is_string($end) && $end !== '' ? (int)date('Y', strtotime($end)) : $startY)
			);
			//error_log('[TransactionsShortcode::render] startY: ' . $startY . '; endY: ' . $endY);
			
			// Safety: swap if reversed; clamp to sane range
			if ($startY > $endY) { [$startY, $endY] = [$endY, $startY]; }
			$years = range($startY, $endY);
			//error_log('[TransactionsShortcode::render] years: ' . print_r($years, true));
			
			// Check if we need hierarchical display
			$hasHierarchy = $handler->hasHierarchicalRelationships($categories);
			$hierarchy = $hasHierarchy ? $handler->organizeTermsHierarchically($categories) : null;
			
			// Track which transactions we've already counted (to avoid double-counting in grand totals)
			$countedTransactions = [];

			// Build table rows: one row per category; columns per year: sum & count
			$rows = [];
			$overall = ['sum' => 0.0, 'count' => 0];
			
			// Organize iteration order: parents first, then their children recursively
			$orderedCategories = [];
			if ($hasHierarchy) {
				// Recursive function to add term and its children
				$addTermWithChildren = function($term, $level) use (&$addTermWithChildren, &$orderedCategories, $hierarchy) {
					$orderedCategories[] = ['term' => $term, 'level' => $level];
					
					// Add children if they exist
					if (isset($hierarchy['children'][$term->term_id])) {
						foreach ($hierarchy['children'][$term->term_id] as $child) {
							$addTermWithChildren($child, $level + 1);
						}
					}
				};
				
				// Start with root parents
				foreach ($hierarchy['parents'] as $parent) {
					$addTermWithChildren($parent, 0);
				}
				
				// Add any orphaned children
				/*foreach ($categories as $term) {
					if ($term->parent !== 0 && !in_array($term->parent, array_column($hierarchy['parents'], 'term_id'), true)) {
						$addTermWithChildren($term, 0);
					}
				}*/
				
				// Add any orphaned children (parent not in our term set at all)
				$allAddedIds = array_column($orderedCategories, 'term');
				$allAddedIds = array_map(fn($t) => $t->term_id, $allAddedIds);
				
				foreach ($categories as $term) {
					// Only add if: has a parent AND not already added AND parent is not in our categories list
					if ($term->parent !== 0 
						&& !in_array($term->term_id, $allAddedIds, true)
						&& !in_array($term->parent, array_column($categories, 'term_id'), true)) {
						$addTermWithChildren($term, 0);
					}
				}

			} else {
				// No hierarchy - treat all as top-level
				foreach ($categories as $term) {
					$orderedCategories[] = ['term' => $term, 'level' => 0];
				}
			}
			
			//foreach ($categories as $term) {
			foreach ($orderedCategories as $item) {
				$term = $item['term'];
				$level = $item['level'];
		
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
					    // field name in transition -- for now, check both transaction_amount and amount
						$amountRaw = get_post_meta($p->ID, 'transaction_amount', true);
						if ( empty($amountRaw) ) {
						    $amountRaw = get_post_meta($p->ID, 'amount', true);
						}						
						error_log('[TransactionsShortcode::render] amountRaw: ' . $amountRaw);
						$amount = is_numeric($amountRaw) ? (float)$amountRaw : 0.0;
						error_log('[TransactionsShortcode::render] $amount: ' . $amount);
						
						// Get transaction type and apply sign
						$type = get_post_meta($p->ID, 'transaction_type', true);
						
						// Debits are negative, credits are positive
						if ($type === 'debit') {
							$amount = -abs($amount);
						} else {
							$amount = abs($amount);
						}
						
						// Sums and counts and totals...
						$cols[$ty]['sum']   += $amount;
						$cols[$ty]['count'] += 1;
						/*
						$overall['sum']   += $amount;
						$overall['count'] += 1;*/
						
						// Only count towards grand total if not already counted
						// (parent categories include child transactions)
						$txnKey = $p->ID . '-' . $ty;
						if (!isset($countedTransactions[$txnKey])) {
							$overall['sum']   += $amount;
							$overall['count'] += 1;
							$countedTransactions[$txnKey] = true;
						}
					}
				}
		
				// optionally skip empty rows unless include_empty_groups="1"
				$hasAny = array_sum(array_column($cols, 'count')) > 0;
				if ($hasAny || ($atts['include_empty_groups'] ?? '0') === '1') {
					$rows[] = [
						'term'   => $term,     // \WP_Term
						'cols'   => $cols,     // year => ['sum','count']
						'result' => $result,   // raw payload if needed
						'level'  => $level,    // hierarchy depth (0 = parent, 1 = child)
					];
				}
			}
			
			// Calculate year totals from the completed rows
			$yearTotals = $this->calculateYearTotals($rows, $years);

			// Build URLs for each cell
			foreach ($rows as &$row) {
				foreach ($years as $year) {
					$row['cols'][$year]['url'] = Transaction::getFilteredAdminUrl([
						'tax_year' => $year,
						'transaction_category' => $row['term']->term_id,
					]);
				}
			}
			
			// Build year total URLs
			$yearUrls = [];
			foreach ($years as $year) {
				$yearUrls[$year] = Transaction::getFilteredAdminUrl(['tax_year' => $year]);
			}
			
			$viewVars['years'] = $years;
			$viewVars['rows'] = $rows; // iterate terms; within each, iterate $years for cols
			$viewVars['yearTotals'] = $yearTotals;
			$viewVars['yearUrls'] = $yearUrls;
			$viewVars['hasHierarchy'] = $hasHierarchy;
			//$viewVars['overallTotal'] = $overallTotal; // sum across all groups -- WIP -- ???
			$viewVars['overall'] = $overall; // grand totals across all years/categories
			$viewVars['info'] = $info;

			//
			//error_log('[TransactionsShortcode::render] viewVars: ' . print_r($viewVars, true));
			//
			return ViewLoader::renderToString( $view, $viewVars, $viewSpecs );
		
		} else {
			// Simple (ungrouped) path
			$view = 'transactions-summary';
			//
			$result = $handler->getTransactions($atts);
			$posts  = $result['posts'] ?? [];
			$total  = method_exists(Transaction::class, 'sumTransactionAmounts')
				? Transaction::sumTransactionAmounts($posts)
				: 0.0;
			$viewVars['posts'] = $posts;
			$viewVars['total'] = $total;
			$viewVars['result'] = $result;
			$viewVars['info'] = $info;
			//
			return ViewLoader::renderToString( $view, $viewVars, $viewSpecs );
		}
    }
    
    /**
	 * Calculate totals for each year from the pivot table rows
	 * 
	 * @param array $rows The pivot table rows
	 * @param array $years Array of years
	 * @return array Associative array keyed by year with 'sum' and 'count' values
	 */
	private function calculateYearTotals(array $rows, array $years): array
	{
		$yearTotals = array_fill_keys($years, ['sum' => 0.0, 'count' => 0]);
		
		foreach ($rows as $row) {
			foreach ($row['cols'] as $year => $col) {
				if (isset($yearTotals[$year])) {
					$yearTotals[$year]['sum'] += (float)$col['sum'];
					$yearTotals[$year]['count'] += (int)$col['count'];
				}
			}
		}
		
		return $yearTotals;
	}
}
