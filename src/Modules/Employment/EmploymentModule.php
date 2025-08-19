<?php

namespace atc\Bkkp\Modules\Employment;

use atc\WHx4\Core\Module as BaseModule;

use atc\Bkkp\Modules\Employment\PostTypes\Employer;
use atc\Bkkp\Modules\Employment\PostTypes\WorkPayment;
//use atc\Bkkp\Modules\Employment\PostTypes\EarningsStatement;

// Define the module class
final class EmploymentModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();

        parent::boot();
    }

    public function getPostTypeHandlerClasses(): array
    {
        return [
            Employer::class,
            WorkPayment::class,
            //EarningsStatement::class,
        ];
    }
}
