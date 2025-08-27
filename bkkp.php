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

// TBD whether there's a way to streamline the following
// Add-on Modules
use atc\Bkkp\Modules\Accounting\AccountingModule as Accounting;
use atc\Bkkp\Modules\Employment\EmploymentModule as Employment;
//
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
