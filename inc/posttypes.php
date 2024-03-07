<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/*** BOOKKEEPING ***/


// TODO: Phase out Ledger, Documents >> loaded via recordkeeper instead


/*** LEDGER ***/

// Accounts -- account
function bkkp_register_post_type_account() {

	if ( custom_caps() ) { $caps = "account"; } else { $caps = "post"; }
	
	$labels = array(
		'name' => __( 'Accounts', 'bkkp' ),
		'singular_name' => __( 'Account', 'bkkp' ),
		'add_new' => __( 'New Account', 'bkkp' ),
		'add_new_item' => __( 'Add New Account', 'bkkp' ),
		'edit_item' => __( 'Edit Account', 'bkkp' ),
		'new_item' => __( 'New Account', 'bkkp' ),
		'view_item' => __( 'View Account', 'bkkp' ),
		'search_items' => __( 'Search Account', 'bkkp' ),
		'not_found' =>  __( 'No Accounts Found', 'bkkp' ),
		'not_found_in_trash' => __( 'No Accounts found in Trash', 'bkkp' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'accounts' ),
        'capability_type' => $caps,
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	'menu_icon'          => 'dashicons-bank',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
		'taxonomies' => array( 'admin_tag', 'account_category' ), 
		'show_in_rest' => false,    
	);

	register_post_type( 'account', $args );
	
}
add_action( 'init', 'bkkp_register_post_type_account' );

// Transactions -- transaction -- WIP -- necessary now that Mint is dead
// ACF fields: 
function bkkp_register_post_type_transaction() {

	if ( custom_caps() ) { $caps = "account"; } else { $caps = "post"; }
	
	$labels = array(
		'name' => __( 'Transactions', 'bkkp' ),
		'singular_name' => __( 'Transaction', 'bkkp' ),
		'add_new' => __( 'New Transaction', 'bkkp' ),
		'add_new_item' => __( 'Add New Transaction', 'bkkp' ),
		'edit_item' => __( 'Edit Transaction', 'bkkp' ),
		'new_item' => __( 'New Transaction', 'bkkp' ),
		'view_item' => __( 'View Transaction', 'bkkp' ),
		'search_items' => __( 'Search Transactions', 'bkkp' ),
		'not_found' =>  __( 'No Transactions Found', 'bkkp' ),
		'not_found_in_trash' => __( 'No Transactions found in Trash', 'bkkp' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true, //'show_in_menu'       => 'edit.php?post_type=account',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'transactions' ),
        'capability_type' => $caps,
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	'menu_icon'          => 'dashicons-yes-alt',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
		'taxonomies' => array( 'admin_tag', 'ledger_category' ), 
		'show_in_rest' => false,    
	);

	register_post_type( 'transaction', $args );
	
}
add_action( 'init', 'bkkp_register_post_type_transaction' );

// Ledger -- ledger_entry
// First draft: Expenses -- expense
// ACF fields: tax_year (date/num); amount (currency/number); notes
function bkkp_register_post_type_ledger_entry() {

	if ( custom_caps() ) { $caps = "account"; } else { $caps = "post"; }
	
	$labels = array(
		'name' => __( 'Ledger', 'bkkp' ),
		'singular_name' => __( 'Ledger Entry', 'bkkp' ),
		'add_new' => __( 'New Ledger Entry', 'bkkp' ),
		'add_new_item' => __( 'Add New Ledger Entry', 'bkkp' ),
		'edit_item' => __( 'Edit Ledger Entry', 'bkkp' ),
		'new_item' => __( 'New Ledger Entry', 'bkkp' ),
		'view_item' => __( 'View Ledger Entry', 'bkkp' ),
		'search_items' => __( 'Search Ledger Entries', 'bkkp' ),
		'not_found' =>  __( 'No Ledger Entries Found', 'bkkp' ),
		'not_found_in_trash' => __( 'No Ledger Entries found in Trash', 'bkkp' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true, //'show_in_menu'       => 'edit.php?post_type=account',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'ledger' ),
        'capability_type' => $caps,
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	//'menu_icon'          => 'dashicons-yes-alt',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
		'taxonomies' => array( 'admin_tag', 'ledger_category' ), 
		'show_in_rest' => false,    
	);

	register_post_type( 'ledger_entry', $args );
	
}
add_action( 'init', 'bkkp_register_post_type_ledger_entry' );

/*** EMPLOYMENT & INCOME ***/
// Eliminated: special posttype for paycheck >> folded into document posttype

/*** TAXES ***/

// Documents -- document
function bkkp_register_post_type_document() {

	if ( custom_caps() ) { $caps = "account"; } else { $caps = "post"; }

	$labels = array(
		'name' => __( 'Documents', 'bkkp' ),
		'singular_name' => __( 'Document', 'bkkp' ),
		'add_new' => __( 'New Document', 'bkkp' ),
		'add_new_item' => __( 'Add New Document', 'bkkp' ),
		'edit_item' => __( 'Edit Document', 'bkkp' ),
		'new_item' => __( 'New Document', 'bkkp' ),
		'view_item' => __( 'View Document', 'bkkp' ),
		'search_items' => __( 'Search Documents', 'bkkp' ),
		'not_found' =>  __( 'No Documents Found', 'bkkp' ),
		'not_found_in_trash' => __( 'No Documents found in Trash', 'bkkp' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true, //'show_in_menu'       => 'edit.php?post_type=account',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'documents' ),
        'capability_type' => $caps,
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	'menu_icon'          => 'dashicons-media-document',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ),
		'taxonomies' => array( 'admin_tag', 'document_category', 'income_category' ), // WIP re 'income_category'
		'show_in_rest' => false,    
	);

	register_post_type( 'document', $args );
	
}
add_action( 'init', 'bkkp_register_post_type_document' );


// Tax Forms -- tax_form
function bkkp_register_post_type_tax_form() {

	if ( custom_caps() ) { $caps = "account"; } else { $caps = "post"; }

	$labels = array(
		'name' => __( 'Tax Forms', 'bkkp' ),
		'singular_name' => __( 'Tax Form', 'bkkp' ),
		'add_new' => __( 'New Tax Form', 'bkkp' ),
		'add_new_item' => __( 'Add New Tax Form', 'bkkp' ),
		'edit_item' => __( 'Edit Tax Form', 'bkkp' ),
		'new_item' => __( 'New Tax Form', 'bkkp' ),
		'view_item' => __( 'View Tax Form', 'bkkp' ),
		'search_items' => __( 'Search Tax Forms', 'bkkp' ),
		'not_found' =>  __( 'No Tax Forms Found', 'bkkp' ),
		'not_found_in_trash' => __( 'No Tax Forms found in Trash', 'bkkp' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true, //'show_in_menu'       => 'edit.php?post_type=account',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'tax_forms' ),
        'capability_type' => $caps,
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	//'menu_icon'          => 'dashicons-playlist-audio',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ),
		'taxonomies' => array( 'admin_tag' ), 
		'show_in_rest' => false,    
	);

	register_post_type( 'tax_form', $args );
	
}
add_action( 'init', 'bkkp_register_post_type_tax_form' );

// Tax Payments
function bkkp_register_post_type_tax_payment() {

	if ( custom_caps() ) { $caps = "account"; } else { $caps = "post"; }

	$labels = array(
		'name' => __( 'Tax Payments', 'bkkp' ),
		'singular_name' => __( 'Tax Payment', 'bkkp' ),
		'add_new' => __( 'New Tax Payment', 'bkkp' ),
		'add_new_item' => __( 'Add New Tax Payment', 'bkkp' ),
		'edit_item' => __( 'Edit Tax Payment', 'bkkp' ),
		'new_item' => __( 'New Tax Payment', 'bkkp' ),
		'view_item' => __( 'View Tax Payment', 'bkkp' ),
		'search_items' => __( 'Search Tax Payments', 'bkkp' ),
		'not_found' =>  __( 'No Tax Payments Found', 'bkkp' ),
		'not_found_in_trash' => __( 'No Tax Payments found in Trash', 'bkkp' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true, //'show_in_menu'       => 'edit.php?post_type=account',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'tax_payments' ),
        'capability_type' => $caps,
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	'menu_icon'          => 'dashicons-money-alt',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
		'taxonomies' => array( 'admin_tag', 'tax_category' ), 
		'show_in_rest' => false,    
	);

	register_post_type( 'tax_payment', $args );
	
}
add_action( 'init', 'bkkp_register_post_type_tax_payment' );

// TODO: create separate CPT as equiv to annual Finances XLSX files?
// Tax Returns
function bkkp_register_post_type_tax_return() {

	if ( custom_caps() ) { $caps = "account"; } else { $caps = "post"; }

	$labels = array(
		'name' => __( 'Tax Returns', 'bkkp' ),
		'singular_name' => __( 'Tax Return', 'bkkp' ),
		'add_new' => __( 'New Tax Return', 'bkkp' ),
		'add_new_item' => __( 'Add New Tax Return', 'bkkp' ),
		'edit_item' => __( 'Edit Tax Return', 'bkkp' ),
		'new_item' => __( 'New Tax Return', 'bkkp' ),
		'view_item' => __( 'View Tax Return', 'bkkp' ),
		'search_items' => __( 'Search Tax Returns', 'bkkp' ),
		'not_found' =>  __( 'No Tax Returns Found', 'bkkp' ),
		'not_found_in_trash' => __( 'No Tax Returns found in Trash', 'bkkp' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true, //'show_in_menu'       => 'edit.php?post_type=account',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'tax_returns' ),
        'capability_type' => $caps,
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
	 	//'menu_icon'          => 'dashicons-playlist-audio',
        'menu_position'      => null,
        'supports'           => array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
		'taxonomies' => array( 'admin_tag' ), 
		'show_in_rest' => false,    
	);

	register_post_type( 'tax_return', $args );
	
}
add_action( 'init', 'bkkp_register_post_type_tax_return' );


/*** +~+~+~+~+~+~+ ***/

// Add existing taxonomies to post types
add_action( 'init', 'bkkp_add_taxonomies_to_cpts', 20 );
function bkkp_add_taxonomies_to_cpts() {
	register_taxonomy_for_object_type( 'admin_tag', 'paycheck' );
}

?>