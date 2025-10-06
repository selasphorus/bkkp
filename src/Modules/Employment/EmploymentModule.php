<?php

namespace atc\Bkkp\Modules\Employment;

use atc\WHx4\Core\Module as BaseModule;
use atc\WHx4\Core\Shortcodes\ShortcodeManager;
use atc\WHx4\Core\PostTypeHandler;
use atc\WHx4\Core\Query\PostQuery;

// Post Types
//use atc\Bkkp\Modules\Employment\PostTypes\Employer;

// Taxonomies
//use atc\Bkkp\Modules\Accounting\Taxonomies\AccountCategory;

// Define the module class
final class EmploymentModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();

        parent::boot();
        
        add_filter('query_vars', function(array $vars): array{
			$vars[] = 'scope';
			$vars[] = 'whx4_scope';
			return $vars;
		});

        add_filter( 'whx4_register_subtypes', function( array $providers ): array {
             // TODO: add use statement above to simplify these lines?
            $providers[] = new \atc\Bkkp\Modules\Employment\Subtypes\EmployersSubtype(); // Subtype of Group PostType
            $providers[] = new \atc\Bkkp\Modules\Employment\Subtypes\WorkPaymentsSubtype(); // Subtype of Document PostType
            $providers[] = new \atc\Bkkp\Modules\Employment\Subtypes\GigsSubtype(); // Subtype of Event PostType
            return $providers;
        } );
        
        ShortcodeManager::add(\atc\Bkkp\Modules\Employment\Shortcodes\EmploymentIncomeShortcode::class);
    }
	
	// Employment has only Subtypes, no CPTs of its own, but Module requires this method... TODO: improve this setup
    public function getPostTypeHandlerClasses(): array
    {
        return [
            //Event::class,
            //RecurringEvent::class,
            //EventSeries::class,
        ];
    }

    public function getModuleStats(): array
    {
        return [
            //'monsters'   => wp_count_posts('monster')->publish ?? 0,
            //'enchanters' => wp_count_posts('enchanter')->publish ?? 0,
        ];
    }
    
    // ===== Employers methods ===== //
    // TODO: move these to EmployersSubtype, maybe? Eventually...
    /**
     * @return \WP_Post[]  All employer-category group posts, optionally limited by scope
     * This is a sample function to show a module-level find method by meta_key
     */
    public function findEmployers(string $scope, array $options = []): array
	{
		$postType = 'group';
		
		//error_log('[findEmployers] scope: ' . $scope);
		$qvScope = get_query_var('whx4_scope') ?: get_query_var('scope') ?: ($_GET['whx4_scope'] ?? $_GET['scope'] ?? '');
		//error_log('[findEmployers] qvScope:' . $qvScope);
		$sanitized = PostTypeHandler::sanitizeScopeParam($qvScope);
		//error_log('[findEmployers] sanitized qvScope: ' . $sanitized);
		if ($sanitized !== null){ $scope = $sanitized; }
		//error_log('[findEmployers] FINAL scope: ' . $scope);
		
		// NTS: The array_replace() function replaces the values of the first array with the values from following arrays.
		$filters = array_replace([
			'post_type' => $postType,
			'scope'     => $scope,
			'date_meta' => [
			    'meta_type' => 'NUMERIC',
			    'key'   => 'years_of_employment',
			    'key_type' => 'serialized', // checkbox field -- multiple values stored
			],
			/*'meta'      => [
				['key' => 'years_of_employment', 'value' => 'employers', 'compare' => '='],
			],*/
			'tax'      => [
				['key' => 'group_category', 'value' => 'employers', 'compare' => '='],
				//['key' => 'group_category', 'equals' => 'employers'], // TODO: enable this shorthand for tax queries
			],
			//'limit'  => "-1",
			//'per_page'  => -1,
			'limit' => 30, //posts_per_page
			'orderby'   => 'title',
			'order'     => 'ASC',
		], $options);
	
		return $this->findViaHandler($postType, $filters);
	}
	
	public function findEmployerTaxDocs(int|\WP_Post $employer, array $filters=[]): array
	{
		$employerId = $employer instanceof \WP_Post ? (int)$employer->ID : (int)$employer;
		if($employerId <= 0){
			return [
				'posts' => [],
				'pagination' => ['found' => 0, 'max_pages' => 0, 'paged' => 1],
				'debug' => ['reason' => 'invalid_employer']
			];
		}
	
		// Require document.employer to match the given employer (group or person) post ID
		$metaSpec = [
			'relation' => 'AND',
			'clauses' => [
			    [
					'type' => 'equals',
					'key' => 'employer',
					'value' => $employerId,
					'cast' => 'NUMERIC',
			    ],
			    /*[
					'type' => 'equals',
					'key' => 'employer',
					'value' => $employerId,
					'cast' => 'NUMERIC',
			    ]*/
			],
		];
		
		// Taxonomies
		$tax = $filters['tax'] ?? [];
		$tax['document_category'] = array_unique(array_merge($tax['document_category'] ?? [], ['tax_forms'])); // limit to tax_forms
	
		// Base params (all docs by default)
		$params = [
			'post_type' => 'document',
			'limit' => isset($filters['limit']) ? (int)$filters['limit'] : -1, // -1 => all
			'order' => $filters['order'] ?? 'DESC',
			'orderby' => $filters['orderby'] ?? 'date',
			'meta' => $metaSpec,
			'tax' => $tax,
		];
	
		// Optional scope limiting: support DATE ('document_date') OR NUMERIC ('tax_year')
		if (isset($filters['scope'])) {
			$dateKey = $filters['date_key'] ?? 'tax_year'; // default to tax_year
		
			if ($dateKey === 'tax_year') {
				// Numeric year window (e.g., scope "2022-2025")
				$params['scope'] = $filters['scope'];
				$params['date_meta'] = array_merge([
					'key'      => 'tax_year',
					'meta_type'=> 'NUMERIC',
					// How the year is stored: 'single' (int in a single row), 'rows', or 'serialized'
					'key_type' => $filters['key_type'] ?? 'single',
				], $filters['date_meta'] ?? []);
			} else {
				// Default DATE-based window (e.g., 'document_date')
				$params['scope'] = $filters['scope'];
				$params['date_meta'] = array_merge([
					'key'       => $dateKey, // default 'document_date'
					'meta_type' => $filters['date_meta_type'] ?? 'DATE',
				], $filters['date_meta'] ?? []);
			}
		}
	
		// v1
		/*
		if(isset($filters['scope'])){
			$params['scope'] = $filters['scope'];
			$params['date_meta'] = $filters['date_meta']
				?? ['key' => ($filters['date_key'] ?? 'document_date'), 'meta_type' => ($filters['date_meta_type'] ?? 'DATE')];
		}*/
	
		if(isset($filters['paged'])){ $params['paged'] = max(1, (int)$filters['paged']); }
	
		// Optional extra meta constraints: merge with the employer clause via AND
		if(isset($filters['meta']) && is_array($filters['meta'])){
			$extra = $filters['meta'];
			$base = $metaSpec['clauses'];
			$extraClauses = $extra['clauses'] ?? [];
			$params['meta'] = ['relation' => 'AND', 'clauses' => array_merge($base, $extraClauses)];
		}
	
		$result = (new PostQuery())->find($params);
	
		return [
			'posts' => $result['posts'] ?? [],
			'pagination' => [
				'found' => $result['found'] ?? 0,
				'max_pages' => $result['max_pages'] ?? 0,
				'paged' => $params['paged'] ?? 1,
			],
			'debug' => [
				'args' => $result['args'] ?? [],
				'query_request' => $result['query_request'] ?? null,
				'params' => $params,
			],
		];
	}

	
}
