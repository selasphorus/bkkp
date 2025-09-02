<?php

namespace atc\Bkkp\Modules\TaxPrep;

use atc\WHx4\Core\Module as BaseModule;

// Post Types
use atc\Bkkp\Modules\TaxPrep\PostTypes\TaxForm;
use atc\Bkkp\Modules\TaxPrep\PostTypes\TaxPayment;
// TODO: create separate CPT as equiv to annual Finances XLSX files? Or Documents Subtype?

// Taxonomies
use atc\Bkkp\Modules\TaxPrep\Taxonomies\IncomeCategory;
use atc\Bkkp\Modules\TaxPrep\Taxonomies\TaxCategory;
use atc\Bkkp\Modules\TaxPrep\Taxonomies\ItemLabel; // Shared taxonomy -- put this elsewhere?

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

        // TODO: change this so that taxonomies are auto-detected, like field groups
        add_filter('whx4_register_taxonomy_handlers', function (array $handlers): array {
            $handlers['income_category'] = IncomeCategory::class;
            $handlers['tax_category'] = TaxCategory::class;
            return $handlers;
        });

        add_filter('whx4_register_shared_taxonomy_handlers', function(array $handlers): array {
            $handlers[] = ItemLabel::class;
            return $handlers;
        });
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
