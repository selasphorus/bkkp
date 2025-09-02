<?php

namespace atc\Bkkp\Modules\Employment;

use atc\WHx4\Core\Module as BaseModule;

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

    }

    public function getPostTypeHandlerClasses(): array
    {
        return [
            //Employer::class, // GroupEntity subtype
            //WorkPayment::class, // Document subtype
            //EarningsStatement::class,
        ];
    }
}
