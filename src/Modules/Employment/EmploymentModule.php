<?php

namespace atc\Bkkp\Modules\Employment;

use atc\WHx4\Core\Module as BaseModule;

//use atc\Bkkp\Modules\Employment\PostTypes\Employer;
//use atc\Bkkp\Modules\Employment\PostTypes\WorkPayment;
//use atc\Bkkp\Modules\Employment\PostTypes\EarningsStatement;

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
