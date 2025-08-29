<?php

namespace atc\Bkkp\Modules\Accounting;

use atc\WHx4\Core\Module as BaseModule;
//
use atc\Bkkp\Modules\Accounting\PostTypes\Account;
use atc\Bkkp\Modules\Accounting\PostTypes\Transaction;
//use atc\Bkkp\Modules\Accounting\PostTypes\LedgerEntry;

// Define the module class
final class AccountingModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();
        parent::boot();
    }

    public function getPostTypeHandlerClasses(): array
    {
        return [
            Account::class,
            Transaction::class,
            LedgerEntry::class,
        ];
    }
}
