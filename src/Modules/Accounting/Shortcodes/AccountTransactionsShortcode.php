<?php

declare(strict_types=1);

namespace smith\Rex\Modules\Moneybags\Shortcodes;

use smith\Rex\Shortcodes\ShortcodeInterface;
use atc\WHx4\Core\PostTypeHandler;

// WIP!

/**
 * [rex_account_transactions account="123" limit="20"]
 *
 * Step 1 focus: declare which URL params are allowed and how they map.
 * Merging and querying happen in later steps (UrlParamBridge + PostQuery + ScopedDateResolver).
 */
final class AccountTransactionsShortcode implements ShortcodeInterface
{
    /**
     * Shortcode tag used by ShortcodeManager::add().
     */
    public static function tag(): string
    {
        return 'account_transactions'; //'rex_account_transactions'
    }

    /**
     * Default shortcode atts (not the focus here, but included for context).
     * - account: required context (post ID or slug) deciding which account's transactions to show
     * - limit:   optional, max results per page
     */
    public static function defaultAtts(): array
    {
        // Scope
        $qvScope = get_query_var('whx4_scope') ?: get_query_var('scope') ?: ($_GET['whx4_scope'] ?? $_GET['scope'] ?? '');
		$sanitized = PostTypeHandler::sanitizeScopeParam($qvScope);
		if ($sanitized !== null){ $scope = $sanitized; }
		
        return [
            'account' => '',
            'limit'   => -1,
            // You can also expose 'scope' and 'transaction_type' as shortcode atts;
            // URL params can later be set to override or not via precedence rules.
            'scope'            => '',
            'transaction_type' => '',
        ];
    }

    /**
     * Required by ShortcodeInterface. Not implemented here—Step 1 only.
     * Later, render() will:
     *  - merge default atts + user atts + allowed URL params (via UrlParamBridge)
     *  - hand the final args to PostQuery
     *  - rely on ScopedDateResolver for date scoping
     */
    public static function render(array $atts = [], ?string $content = null, string $tag = ''): string
    {
        return '<!-- AccountTransactionsShortcode: Step 1 (URL param spec) in place. Query/render to be added next. -->';
    }
}
