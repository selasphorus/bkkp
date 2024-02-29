<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/*** Taxonomies for GENERAL & ADMIN USE ***/

// Custom Taxonomy: Language
function bkkp_register_taxonomy_language() {
    //$cap = 'lectionary';
    $labels = array(
        'name'              => _x( 'Language', 'taxonomy general name' ),
        'singular_name'     => _x( 'Language', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Languages' ),
        'all_items'         => __( 'All Languages' ),
        'parent_item'       => __( 'Parent Language' ),
        'parent_item_colon' => __( 'Parent Language:' ),
        'edit_item'         => __( 'Edit Language' ),
        'update_item'       => __( 'Update Language' ),
        'add_new_item'      => __( 'Add New Language' ),
        'new_item_name'     => __( 'New Language Name' ),
        'menu_name'         => __( 'Languages' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'language' ],
    );
    register_taxonomy( 'language', [ 'word' ], $args );
}
//add_action( 'init', 'bkkp_register_taxonomy_language' );

/*** TAXES ***/

// Income Category
function bkkp_register_taxonomy_income_category() {
    //$cap = 'XXX';
    $labels = array(
        'name'              => _x( 'Income Category', 'taxonomy general name' ),
        'singular_name'     => _x( 'Income Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Income Categories' ),
        'all_items'         => __( 'All Income Categories' ),
        'parent_item'       => __( 'Parent Income Category' ),
        'parent_item_colon' => __( 'Parent Income Category:' ),
        'edit_item'         => __( 'Edit Income Category' ),
        'update_item'       => __( 'Update Income Category' ),
        'add_new_item'      => __( 'Add New Income Category' ),
        'new_item_name'     => __( 'New Income Category Name' ),
        'menu_name'         => __( 'Income Categories' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'income_category' ],
    );
    register_taxonomy( 'income_category', [ 'paycheck', 'tax_payment' ], $args );
}
add_action( 'init', 'bkkp_register_taxonomy_income_category' );

// Expense Category
function bkkp_register_taxonomy_expense_category() {
    //$cap = 'XXX';
    $labels = array(
        'name'              => _x( 'Expense Category', 'taxonomy general name' ),
        'singular_name'     => _x( 'Expense Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Expense Categories' ),
        'all_items'         => __( 'All Expense Categories' ),
        'parent_item'       => __( 'Parent Expense Category' ),
        'parent_item_colon' => __( 'Parent Expense Category:' ),
        'edit_item'         => __( 'Edit Expense Category' ),
        'update_item'       => __( 'Update Expense Category' ),
        'add_new_item'      => __( 'Add New Expense Category' ),
        'new_item_name'     => __( 'New Expense Category Name' ),
        'menu_name'         => __( 'Expense Categories' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'expense_category' ],
    );
    register_taxonomy( 'expense_category', [ 'expense' ], $args );
}
add_action( 'init', 'bkkp_register_taxonomy_expense_category' );

// Transaction Category
function bkkp_register_taxonomy_transaction_category() {
    //$cap = 'XXX';
    $labels = array(
        'name'              => _x( 'Transaction Category', 'taxonomy general name' ),
        'singular_name'     => _x( 'Transaction Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Transaction Categories' ),
        'all_items'         => __( 'All Transaction Categories' ),
        'parent_item'       => __( 'Parent Transaction Category' ),
        'parent_item_colon' => __( 'Parent Transaction Category:' ),
        'edit_item'         => __( 'Edit Transaction Category' ),
        'update_item'       => __( 'Update Transaction Category' ),
        'add_new_item'      => __( 'Add New Transaction Category' ),
        'new_item_name'     => __( 'New Transaction Category Name' ),
        'menu_name'         => __( 'Transaction Categories' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'transaction_category' ],
    );
    register_taxonomy( 'transaction_category', [ 'transaction' ], $args );
}
add_action( 'init', 'bkkp_register_taxonomy_transaction_category' );

// Transaction Tag
function bkkp_register_taxonomy_transaction_tag() {
    //$cap = 'XXX';
    $labels = array(
        'name'              => _x( 'Transaction Tag', 'taxonomy general name' ),
        'singular_name'     => _x( 'Transaction Tag', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Transaction Tags' ),
        'all_items'         => __( 'All Transaction Tags' ),
        'parent_item'       => __( 'Parent Transaction Tag' ),
        'parent_item_colon' => __( 'Parent Transaction Tag:' ),
        'edit_item'         => __( 'Edit Transaction Tag' ),
        'update_item'       => __( 'Update Transaction Tag' ),
        'add_new_item'      => __( 'Add New Transaction Tag' ),
        'new_item_name'     => __( 'New Transaction Tag Name' ),
        'menu_name'         => __( 'Transaction Tags' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'transaction_tag' ],
    );
    register_taxonomy( 'transaction_tag', [ 'transaction' ], $args );
}
add_action( 'init', 'bkkp_register_taxonomy_transaction_tag' );


// Account Category
function bkkp_register_taxonomy_account_category() {
    //$cap = 'XXX';
    $labels = array(
        'name'              => _x( 'Account Category', 'taxonomy general name' ),
        'singular_name'     => _x( 'Account Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Account Categories' ),
        'all_items'         => __( 'All Account Categories' ),
        'parent_item'       => __( 'Parent Account Category' ),
        'parent_item_colon' => __( 'Parent Account Category:' ),
        'edit_item'         => __( 'Edit Account Category' ),
        'update_item'       => __( 'Update Account Category' ),
        'add_new_item'      => __( 'Add New Account Category' ),
        'new_item_name'     => __( 'New Account Category Name' ),
        'menu_name'         => __( 'Account Categories' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'account_category' ],
    );
    register_taxonomy( 'account_category', [ 'account' ], $args );
}
add_action( 'init', 'bkkp_register_taxonomy_account_category' );

// Document Category
function bkkp_register_taxonomy_document_category() {
    //$cap = 'XXX';
    $labels = array(
        'name'              => _x( 'Document Category', 'taxonomy general name' ),
        'singular_name'     => _x( 'Document Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Document Categories' ),
        'all_items'         => __( 'All Document Categories' ),
        'parent_item'       => __( 'Parent Document Category' ),
        'parent_item_colon' => __( 'Parent Document Category:' ),
        'edit_item'         => __( 'Edit Document Category' ),
        'update_item'       => __( 'Update Document Category' ),
        'add_new_item'      => __( 'Add New Document Category' ),
        'new_item_name'     => __( 'New Document Category Name' ),
        'menu_name'         => __( 'Document Categories' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'document_category' ],
    );
    register_taxonomy( 'document_category', [ 'document' ], $args );
}
add_action( 'init', 'bkkp_register_taxonomy_document_category' );

// Item Label (for Form Fields, &c.)
function bkkp_register_taxonomy_item_label() {
    //$cap = 'XXX';
    $labels = array(
        'name'              => _x( 'Label', 'taxonomy general name' ),
        'singular_name'     => _x( 'Label', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Labels' ),
        'all_items'         => __( 'All Labels' ),
        'parent_item'       => __( 'Parent Label' ),
        'parent_item_colon' => __( 'Parent Label:' ),
        'edit_item'         => __( 'Edit Label' ),
        'update_item'       => __( 'Update Label' ),
        'add_new_item'      => __( 'Add New Label' ),
        'new_item_name'     => __( 'New Label Name' ),
        'menu_name'         => __( 'Labels' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'item_label' ],
    );
    register_taxonomy( 'item_label', [ 'document' ], $args );
}
add_action( 'init', 'bkkp_register_taxonomy_item_label' );

// Tax Category
function bkkp_register_taxonomy_tax_category() {
    //$cap = 'XXX';
    $labels = array(
        'name'              => _x( 'Tax Category', 'taxonomy general name' ),
        'singular_name'     => _x( 'Tax Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Tax Categories' ),
        'all_items'         => __( 'All Tax Categories' ),
        'parent_item'       => __( 'Parent Tax Category' ),
        'parent_item_colon' => __( 'Parent Tax Category:' ),
        'edit_item'         => __( 'Edit Tax Category' ),
        'update_item'       => __( 'Update Tax Category' ),
        'add_new_item'      => __( 'Add New Tax Category' ),
        'new_item_name'     => __( 'New Tax Category Name' ),
        'menu_name'         => __( 'Tax Categories' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'tax_category' ],
    );
    register_taxonomy( 'tax_category', [ 'tax_payment' ], $args );
}
add_action( 'init', 'bkkp_register_taxonomy_tax_category' );


?>