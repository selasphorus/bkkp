<?php
/**
 * Plugin Name:       Birdhive Bookkeeping
 * Description:       A WordPress plugin for personal bookkeeping
 * Dependencies:      Requires WHx4 -- and SDG? for various utility functions?
 * Requires Plugins:  whx4
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
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Require Composer autoloader
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use atc\WHx4\Plugin;

// WHx4 Add-on Modules
use atc\Bkkp\Modules\Accounting\AccountingModule as Accounting;
use atc\Bkkp\Modules\Employment\EmploymentModule as Employment;
use atc\Bkkp\Modules\Documents\DocumentsModule as Documents; // TODO, maybe?: create separate mini-plugin to handle documents
use atc\Bkkp\Modules\TaxPrep\TaxPrepModule as TaxPrep;
use atc\Bkkp\Modules\Communications\CommunicationsModule as Communications;
//
use atc\Bkkp\Admin\MetaFieldCleanup;

// Once plugins are loaded, boot everything up
add_action( 'whx4_pre_boot', function() {
    // Wait until WHx4 is loaded, but BEFORE it boots
    //error_log( '$$$ whx4_pre_boot $$$' );
    if ( class_exists( Plugin::class ) ) {
        //error_log( '$$$ about to attempt to register additional modules $$$' );

        // Register the module with WHx4
        add_filter( 'whx4_register_modules', function( array $modules ): array {
            //error_log( '$$$ whx4_register_modules hook fired $$$' );
            $modules['accounting'] = Accounting::class;
            $modules['employment'] = Employment::class;
            $modules['documents'] = Documents::class;
            $modules['taxprep'] = TaxPrep::class;
            $modules['communications'] = Communications::class;
            return $modules;
        } );
        
        // Register Field Keys
        add_filter( 'whx4_registered_field_keys', function() {
            //error_log( '$$$ whx4_registered_field_keys hook fired $$$' );
            if ( ! function_exists( 'acf_get_local_fields' ) ) {
                return [];
            }

            $fields = acf_get_local_fields();
            $keys = [];

            foreach ( $fields as $field ) {
                if ( isset( $field['key'] ) ) {
                    $keys[] = $field['key'];
                }
            }

            return $keys;
        });
        
        // Register Assets
        add_filter('whx4_assets', static function (array $assets): array {
			// Compute URLs and paths safely
			$relCss = 'assets/css/bkkp.css';
			$src    = plugins_url($relCss, __FILE__);
			$path   = plugin_dir_path(__FILE__) . $relCss;
		
			$assets['styles'][] = [
				'handle'   => 'bkkp',
				'src'      => $src,
				'path'     => $path,      // enables 'ver' => 'auto' filemtime
				'deps'     => [],         // e.g., ['dashicons']
				'ver'      => 'auto',     // cache-bust on file change
				'media'    => 'all',
				'where'    => 'front',    // 'front' | 'admin' | 'both'
				'autoload' => true,      // set true to always load where-matched
			];
		
			return $assets;
		});
    } else {
       //error_log( '$$$ Plugin class DNE $$$' );
    }
}, 15 ); // Priority < 20 to run before WHx4 boot()


// Register WP-CLI commands
if (defined('WP_CLI') && \WP_CLI) {
    require_once plugin_dir_path(__FILE__) . 'src/Admin/MetaFieldCleanup.php';
    
    \WP_CLI::add_command('bkkp cleanup-meta', function($args, $assoc_args) {
        $dry_run = isset($assoc_args['dry-run']);
        
        if ($dry_run) {
            \WP_CLI::log(\WP_CLI::colorize('%Y=== DRY RUN MODE - No changes will be made ===%n'));
            \WP_CLI::log('');
        }
        
        \WP_CLI::log('Starting BKKP meta field cleanup...');
        
        $results = MetaFieldCleanup::run($dry_run);
        
        if ($results['status'] === 'skipped') {
            \WP_CLI::warning($results['message']);
        } else {
            \WP_CLI::log('');
            if ($dry_run) {
                \WP_CLI::log(\WP_CLI::colorize('%YChanges that WOULD be made:%n'));
            } else {
                \WP_CLI::success('Cleanup completed!');
            }
            
            \WP_CLI::log(sprintf('- Renamed: %d records (import_amount → amount_signed)', $results['renamed']));
            \WP_CLI::log(sprintf('- Copied: %d records (amount → amount_signed)', $results['copied']));
            \WP_CLI::log(sprintf('- Made unsigned: %d records (negative → positive)', $results['unsigned']));
            
            if ($dry_run) {
                \WP_CLI::log('');
                \WP_CLI::log(\WP_CLI::colorize('%GTo perform these changes, run without --dry-run flag%n'));
            }
        }
    });
    
    // Add reset command for testing
    \WP_CLI::add_command('bkkp cleanup-meta-reset', function() {
        if (\BKKP\Admin\MetaFieldCleanup::reset()) {
            \WP_CLI::success('Cleanup flag reset. You can now re-run the cleanup.');
        } else {
            \WP_CLI::warning('Flag was not set or could not be reset.');
        }
    });
}