<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

// WIP
function build_document_title( $post_id = null, $arr = array() ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    //sdg_log( "divline2", $do_log );
    //sdg_log( "function called: build_document_title", $do_log );
    
	if ( $post_id == null ) { return false; }
    
    // Init vars
    $ts_info = "";
    $new_title = "";
    
     // Set var values
    if ( !empty($arr) ) {
        
        //sdg_log( "[btt] running btt using array derived from _POST.", $do_log );
        //$field_name = $arr['field_name'];
        
    }
    
    // Taxonomies
    /*if ( empty($arr) && $post_id ) {
    	//sdg_log( "[btt] get taxonomy info from post_id: ".$post_id, $do_log );
    	//WIP
    	
    	// Keys    
        // Get term names for "key".
        $keys = wp_get_post_terms( $post_id, 'key', array( 'fields' => 'names' ) );
        if ( $keys ) { $keys_str = implode(", ",$keys); } else { $keys_str = ""; }
        if ( empty($keys_str) ) {
            $keys_str = get_field('key_name_txt', $post_id, false);
        }
        sdg_log( "[btt] keys_str: ".$keys_str, $do_log );
    }*/
    
    // Build the title
    // Account Statements:
    // DATESTR -- ACCOUNT
    
    // Earning Statements:
    // DATESTR -- EMPLOYER ABBR
    
    // Tax Forms:
    // TAX_YEAR -- FORM NAME -- EMPLOYER NAME
    
    // Tax Returns:
    // TAX_YEAR -- Tax Return[s] [-- FED/STATE]
    
    // Other Docs:
    // TAX_YEAR -- Doc Name
    
}

?>