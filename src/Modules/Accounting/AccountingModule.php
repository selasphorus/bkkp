<?php

namespace atc\Bkkp\Modules\Accounting;

use atc\WHx4\Core\Module as BaseModule;

// Post Types
use atc\Bkkp\Modules\Accounting\PostTypes\Account;
use atc\Bkkp\Modules\Accounting\PostTypes\Transaction;
use atc\Bkkp\Modules\Accounting\PostTypes\LedgerEntry;

// Taxonomies
use atc\Bkkp\Modules\Accounting\Taxonomies\AccountCategory;
use atc\Bkkp\Modules\Accounting\Taxonomies\TransactionCategory;
use atc\Bkkp\Modules\Accounting\Taxonomies\TransactionTag;

// Define the module class
final class AccountingModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();
        parent::boot();

        add_filter( 'whx4_register_subtypes', function( array $providers ): array {
            $providers[] = new \atc\Bkkp\Modules\Accounting\Subtypes\AccountantsSubtype(); // TODO: add use statement above to simplify this line?
            //$providers[] = new \atc\Bkkp\Modules\Accounting\Subtypes\WorkPaymentsSubtype();
            return $providers;
        } );

        // TODO: change this so that taxonomies are auto-detected, like field groups
        add_filter('whx4_register_taxonomy_handlers', function (array $handlers): array {
            $handlers['account_category'] = AccountCategory::class;
            $handlers['transaction_category'] = TransactionCategory::class;
            $handlers['transaction_tag'] = TransactionTag::class;
            return $handlers;
        });
        
        ShortcodeManager::add(\atc\Bkkp\Modules\Accounting\Shortcodes\TransactionsShortcode::class);
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
