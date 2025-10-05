<?php

namespace atc\Bkkp\Modules\Employment;

use atc\WHx4\Core\Module as BaseModule;
use atc\WHx4\Core\Shortcodes\ShortcodeManager;
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

        add_filter( 'whx4_register_subtypes', function( array $providers ): array {
             // TODO: add use statement above to simplify these lines?
            $providers[] = new \atc\Bkkp\Modules\Employment\Subtypes\EmployersSubtype(); // Subtype of Group PostType
            $providers[] = new \atc\Bkkp\Modules\Employment\Subtypes\WorkPaymentsSubtype(); // Subtype of Document PostType
            $providers[] = new \atc\Bkkp\Modules\Employment\Subtypes\GigsSubtype(); // Subtype of Event PostType
            return $providers;
        } );
        
        ShortcodeManager::add(\atc\Bkkp\Modules\Employment\Shortcodes\EmploymentIncomeShortcode::class);
    }

    public function getModuleStats(): array
    {
        return [
            //'monsters'   => wp_count_posts('monster')->publish ?? 0,
            //'enchanters' => wp_count_posts('enchanter')->publish ?? 0,
        ];
    }
    
    /**
     * @return \WP_Post[]  All employer-category group posts, optionally limited by scope
     * This is a sample function to show a module-level find method by meta_key
     */
    // TODO: move this to EmployersSubtype, maybe? Eventually...
    public function findEmployers(string $scope, array $options = []): array
	{
		$postType = 'group';
		
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
			'posts_per_page' => -1,
			'orderby'   => 'title',
			'order'     => 'ASC',
		], $options);
	
		return $this->findViaHandler($postType, $filters);
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
}
