<?php

namespace atc\Bkkp\Modules\Communications;

use atc\WXC\Core\Module as BaseModule;

//use atc\Bkkp\Modules\Communications\PostTypes\XXX;

// Define the module class
final class CommunicationsModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();

        parent::boot();

        add_filter( 'whx4_register_subtypes', function( array $providers ): array {
             // TODO: add use statement above to simplify these lines?
            $providers[] = new \Bkkp\Modules\Communications\Subtypes\CommunicationsSubtype(); // Subtype of Post PostType
            //$providers[] = new \Bkkp\Modules\Communications\Subtypes\WorkPaymentsSubtype(); // Subtype of Document PostType
            //$providers[] = new \Bkkp\Modules\Communications\Subtypes\GigsSubtype(); // Subtype of Event PostType
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
