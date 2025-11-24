<?php

namespace atc\Bkkp\Modules\Accounting;

use WXC\Core\Module as BaseModule;
use WXC\Core\Shortcodes\ShortcodeManager;

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
        
        // Register Assets
		add_filter('whx4_assets', function (array $assets): array {
			$moduleDir = __DIR__; // Get the module directory path
			
			// CSS
			$relCss = 'Assets/accounting.css';
			$srcCss = plugins_url($relCss, __FILE__);
			$pathCss = $moduleDir . '/' . $relCss;
		
			$assets['styles'][] = [
				'handle'   => 'bkkp-accounting',  // More specific handle
				'src'      => $srcCss,
				'path'     => $pathCss,
				'deps'     => [],
				'ver'      => 'auto',
				'media'    => 'all',
				'where'    => 'front',
				'autoload' => true,
			];
		
			return $assets;
		});

        ShortcodeManager::add(\atc\Bkkp\Modules\Accounting\Shortcodes\TransactionsShortcode::class);
        ShortcodeManager::add(\atc\Bkkp\Modules\Accounting\Shortcodes\AccountsShortcode::class);
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
