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
		//$info .= "processed atts: <pre>".print_r($atts,true)."</pre>"; // another sanity check!
		$info .= "processed atts[scope]: ".$atts['scope']."<br />"; // another sanity check!
		
		// Prepare to render view according to groupMode
		$viewVars = [];
		$viewVars['atts'] = $atts;
		$viewVars['grouped_by'] = $groupMode;
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
			$includeEmpty = $atts['include_empty_groups'] === '1'; // ???
		
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
					    // field name in transition -- for now, check both transaction_amount and amount
						$amountRaw = get_post_meta($p->ID, 'transaction_amount', true);
						if ( empty($amountRaw) ) {
						    $amountRaw = get_post_meta($p->ID, 'amount', true);
						}						
						error_log('[TransactionsShortcode::render] amountRaw: ' . $amountRaw);
						$amount = is_numeric($amountRaw) ? (float)$amountRaw : 0.0;
						error_log('[TransactionsShortcode::render] $amount: ' . $amount);
						//
						$cols[$ty]['sum']   += $amount;
						$cols[$ty]['count'] += 1;
		
						$overall['sum']   += $amount;
						$overall['count'] += 1;
					}
				}
		
				// optionally skip empty rows unless include_empty_groups="1"
				$hasAny = array_sum(array_column($cols, 'count')) > 0;
				//if ($hasAny || ($atts['include_empty_groups'] ?? '0') === '1') {
					$rows[] = [
						'term'   => $term,     // \WP_Term
						'cols'   => $cols,     // year => ['sum','count']
						'result' => $result,   // raw payload if needed
					];
				//}
			}
			
			$viewVars['years'] = $years;
			$viewVars['rows'] = $rows; // iterate terms; within each, iterate $years for cols
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
}
