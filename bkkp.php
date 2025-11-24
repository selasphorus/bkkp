<?php
/**
 * Plugin Name:       Bkkp
 * Description:       A WordPress plugin for personal bookkeeping
 * Dependencies:      Requires WHx4-Core, WHx4
 * Requires Plugins:  whx4-core, whx4
 * Version:           0.1
 * Author:            atc
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bkkp
 *
 * @package           bkkp
 */

declare(strict_types=1);

namespace atc\Bkkp;

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

// Require Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use WXC\Plugin;

// WXC Add-on Modules
use atc\Bkkp\Modules\Accounting\AccountingModule as Accounting;
use atc\Bkkp\Modules\Employment\EmploymentModule as Employment;
use atc\Bkkp\Modules\Documents\DocumentsModule as Documents; // TODO, maybe?: create separate mini-plugin to handle documents
use atc\Bkkp\Modules\TaxPrep\TaxPrepModule as TaxPrep;
use atc\Bkkp\Modules\Communications\CommunicationsModule as Communications;

// Admin Controllers
use atc\Bkkp\Admin\MetaFieldCleanup;
use atc\Bkkp\Admin\TagCleanupPageController;

// Once plugins are loaded, boot everything up
add_action('wxc_pre_boot', function() {
    // Wait until WXC is loaded, but BEFORE it boots
    if (!class_exists(Plugin::class)) {
        return;
    }

    // Register the modules with WHx4
    add_filter('wxc_register_modules', function(array $modules): array {
        $modules['accounting'] = Accounting::class;
        $modules['employment'] = Employment::class;
        $modules['documents'] = Documents::class;
        $modules['taxprep'] = TaxPrep::class;
        $modules['communications'] = Communications::class;
        return $modules;
    });
    
    // Register Field Keys
    add_filter('wxc_registered_field_keys', function() {
        if (!function_exists('acf_get_local_fields')) {
            return [];
        }

        $fields = acf_get_local_fields();
        $keys = [];

        foreach ($fields as $field) {
            if (isset($field['key'])) {
                $keys[] = $field['key'];
            }
        }

        return $keys;
    });
    
    // Register Assets
    add_filter('wxc_assets', static function (array $assets): array {
        // CSS
        $relCss = 'assets/css/bkkp.css';
        $srcCss = plugins_url($relCss, __FILE__);
        $pathCss = plugin_dir_path(__FILE__) . $relCss;
    
        $assets['styles'][] = [
            'handle'   => 'bkkp',
            'src'      => $srcCss,
            'path'     => $pathCss,
            'deps'     => [],
            'ver'      => 'auto',
            'media'    => 'all',
            'where'    => 'front',
            'autoload' => true,
        ];
    
        // JS
        $relJs = 'assets/js/bkkp.js';
        $srcJs = plugins_url($relJs, __FILE__);
        $pathJs = plugin_dir_path(__FILE__) . $relJs;
    
        $assets['scripts'][] = [
            'handle'    => 'bkkp',
            'src'       => $srcJs,
            'path'      => $pathJs,
            'deps'      => [],        // e.g., ['jquery']
            'ver'       => 'auto',
            'in_footer' => true,
            'where'     => 'front',
            'autoload'  => true,
        ];
    
        return $assets;
    });
    
    // Register Admin Pages
    if (is_admin()) {
        (new TagCleanupPageController())->addHooks();
    }
    
}, 15); // Priority < 20 to run before WHx4 boot()
