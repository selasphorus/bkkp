<?php

namespace atc\Bkkp\Modules\TaxPrep;

use atc\WHx4\Core\Module as BaseModule;
//
use atc\Bkkp\Modules\TaxPrep\PostTypes\TaxForm;
use atc\Bkkp\Modules\TaxPrep\PostTypes\TaxPayment;
// TODO: create separate CPT as equiv to annual Finances XLSX files? Or Documents Subtype?

// Define the module class
final class TaxPrepModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();
        parent::boot();

        add_filter( 'whx4_register_subtypes', function( array $providers ): array {
            $providers[] = new \atc\Bkkp\Modules\TaxPrep\Subtypes\TaxDocumentsSubtype(); // TODO: add use statement above to simplify this line?
            //$providers[] = new \atc\Bkkp\Modules\TaxPrep\Subtypes\WorkPaymentsSubtype();
            return $providers;
        } );
    }

    public function getPostTypeHandlerClasses(): array
    {
        return [
            TaxForm::class,
            TaxPayment::class, // temporary
            //ZZZ::class,
        ];
    }
}
