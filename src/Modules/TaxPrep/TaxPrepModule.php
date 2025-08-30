<?php

namespace atc\Bkkp\Modules\TaxPrep;

use atc\WHx4\Core\Module as BaseModule;
//
use atc\Bkkp\Modules\TaxPrep\PostTypes\TaxForm;
use atc\Bkkp\Modules\TaxPrep\PostTypes\TaxPayment;

// Define the module class
final class TaxPrepModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();
        parent::boot();
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
