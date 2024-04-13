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
    $do_ts = devmode_active();
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
        
    } else if ( $post_id ) {
        
        // If no array of data was submitted, get info via the post_id
        $tax_year = get_field('tax_year', $post_id);
		$statement_date = get_field('statement_date', $post_id); // returned in format e.g. 20230113 (YYYYMMDD)		
		$pay_date = get_field('pay_date', $post_id); // for earning statements		
		$payment_date = get_field('payment_date', $post_id); // for tax payments
		//$payment_quarter = get_field('payment_quarter', $post_id); // for tax payments		
		//
		$payment_amount = get_field('payment_amount', $post_id); // for tax payments (x-check with transactions)
		$estimated_payment = get_field('estimated_payment', $post_id); // for tax payments (T/F)
		$employer = get_field('employer', $post_id);
		$account = get_field('account', $post_id);
		$tax_forms = get_field('tax_forms', $post_id);
		//$check_num = get_field('check_num', $post_id);
		$jurisdiction = get_field('jurisdiction', $post_id); // federal, nys, nj
    
    }
    
    // Taxonomies
    if ( empty($arr) && $post_id ) {
    	//sdg_log( "[btt] get taxonomy info from post_id: ".$post_id, $do_log );
    	
    	$doc_categories = wp_get_post_terms( $post_id, 'document_category', array( 'fields' => 'ids' ) );
    	
    	//WIP
    	/*
    	// Keys    
        // Get term names for "key".
        $keys = wp_get_post_terms( $post_id, 'key', array( 'fields' => 'names' ) );
        if ( $keys ) { $keys_str = implode(", ",$keys); } else { $keys_str = ""; }
        if ( empty($keys_str) ) {
            $keys_str = get_field('key_name_txt', $post_id, false);
        }
        sdg_log( "[btt] keys_str: ".$keys_str, $do_log );*/
    }
    
    // Build the title
    $date_str = "";
    if ( $statement_date ) { $date_str = date_i18n("Y-m-d",strtotime($statement_date)); }
    if ( $pay_date ) { $date_str = date_i18n("Y-m-d",strtotime($pay_date)); }
    if ( $payment_date ) { $date_str = date_i18n("Y-m-d",strtotime($payment_date)); }
    if ( empty($date_str) ) { $date_str = $tax_year; }
	//
	if ( !empty($date_str) ) { $new_title = $date_str." -- "; }
    //
    //
    $forms_str = "";
	if ( !empty($tax_forms) ) {
		foreach ( $tax_forms as $form_id ) {
			$form_id = get_field('form_id', $form_id);
			$forms_str .= $form_id;
			//$form_name = get_field('form_name', $form_id);
			if ( is_array($tax_forms) && count($tax_forms) > 1 ) { $forms_str .= ", "; }
		}
		// Trim trailing comma and space
		if ( substr($forms_str, -2) == ', ' ) { $forms_str = substr($forms_str, 0, -2); }		
	}
    /*
    $hymn_cat_id = "1452"; // "Hymns" -- same id on live and dev
	$psalm_cat_id = "1461"; // "Psalms" -- same id on live and dev
	$chant_cat_id = "1528"; // "Anglican Chant" -- same id on live and dev
	
	if ( in_array($hymn_cat_id, $rep_categories) ) {
	
	}
	*/
    
    // Account Statements:
    // DATESTR -- ACCOUNT
    // todo: get employer post_title from id?
    if ( !empty($account) ) { $new_title .= $account->post_title." -- "; }
    
    // Tax Forms:
    // TAX_YEAR -- FORM NAME -- EMPLOYER NAME
    // todo: get employer post_title from id?
    if ( !empty($forms_str) ) { $new_title .= $forms_str." -- "; }
    
    // Earning Statements:
    // DATESTR -- EMPLOYER ABBR
    // todo: get employer post_title from id?
    if ( !empty($employer) ) { $new_title .= $employer." -- "; }
    
    // Tax Returns:
    // TAX_YEAR -- Tax Return[s] [-- JURISDICTION]
    // WIP! check for doc category
    if ( !empty($jurisdiction) ) { $new_title .= $jurisdiction." -- "; }
    
    // Other Docs:
    // TAX_YEAR -- Doc Name
    // WIP
    
    // Trim trailing hyphens and space
    if ( substr($new_title, -4) == ' -- ' ) { $new_title = substr($new_title, 0, -4); }
    
    return $new_title;
    
}

?>