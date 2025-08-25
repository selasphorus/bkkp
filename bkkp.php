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
//use atc\Bkkp\Plugin; // NO -- no separate plugin singleton needed -- use WHx4
//use atc\Bkkp\Core\PostUtils;
// TBD whether there's a way to streamline the following
use atc\Bkkp\Modules\Accounting\AccountingModule as Accounting;
use atc\Bkkp\Modules\Employment\EmploymentModule as Employment;
//
//use atc\Bkkp\Modules\Transactions\TransactionsModule as Transactions;
//se atc\Bkkp\Modules\PayDocs\PayDocsModule as PayDocs;
//use atc\Bkkp\Modules\TaxPrep\TaxPrepModule as TaxPrep;
//use atc\Bkkp\Modules\Documents\DocumentsModule as Documents; // TODO: create separate mini-plugin to handle documents

// Once plugins are loaded, boot everything up
add_action( 'whx4_pre_boot', function() {
    // Wait until WHx4 is loaded, but BEFORE it boots
    //error_log( '$$$ whx4_pre_boot $$$' );
    if ( class_exists( Plugin::class ) ) {
        //error_log( '$$$ about to attempt to register additional modules $$$' );

        // Register the module with WHx4
        add_filter( 'whx4_register_modules', function( array $modules ): array {
            error_log( '$$$ whx4_register_modules hook fired $$$' );
            /*return array_merge( $modules, [
                //'paydocs'      => PayDocs::class,
                //'taxprep'      => TaxPrep::class,
                //'documents'  => Documents::class
            ]);*/
            $modules['accounting'] = Accounting::class;
            $modules['employment'] = Employment::class;
            return $modules;
        } );

        add_filter( 'whx4_registered_field_keys', function() {
            error_log( '$$$ whx4_registered_field_keys hook fired $$$' );
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
    } else {
       error_log( '$$$ Plugin class DNE $$$' );
    }
}, 15 ); // Priority < 20 to run before WHx4 boot()

// On activation, set up post types and capabilities
/*
// Once plugins are loaded, boot everything up
add_action( 'plugins_loaded', function() {
    Plugin::getInstance()->boot();
}, 20 ); // Use a priority high enough to allow addons to hook before it runs

// On activation, set up post types and capabilities
register_activation_hook( __FILE__, function() {
    $plugin = Plugin::getInstance();
    $plugin->boot();
});
*/

/*
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

$plugin_path = plugin_dir_path( __FILE__ );
*/

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */
/*
// The old non-OOP way:
// Get plugin options -- WIP
$options = get_option( 'bkkp_settings' );
if ( isset($options['bkkp_modules']) ) { $modules = $options['bkkp_modules']; } else { $modules = array( 'documents' ); }
$includes = array( 'posttypes', 'taxonomies' );

// Include sub-files
// TODO: make them required? Otherwise dependencies may be an issue.
// TODO: maybe: convert to classes/methods approach??
foreach ( $includes as $inc ) {
    $filepath = $plugin_path . 'inc/'.$inc.'.php';
    if ( file_exists($filepath) ) { include_once( $filepath ); } else { echo "no $filepath found"; }
}

foreach ( $modules as $module ) {
    $filepath = $plugin_path . 'modules/'.$module.'.php';
    $arr_exclusions = array(); //$arr_exclusions = array ( 'admin_notes', 'data_tables', 'links', 'organizations', 'ensembles', 'organs', 'press', 'projects', 'sources' );
    if ( !in_array( $module, $arr_exclusions) ) { // skip modules w/ no files
        if ( file_exists($filepath) ) { include_once( $filepath ); } else { echo "module file $filepath not found"; }
    }
}
*/
/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */


?>
