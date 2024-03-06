<?php
/**
 * @package bkkp
 */

/*
Plugin Name: Birdhive Bookkeeping
Plugin URI: 
Description: 
Version: 0.1
Author: atc
Author URI: 
License: 
Text Domain: bkkp
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

$plugin_path = plugin_dir_path( __FILE__ );

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

// Include sub-files
// TODO: make them required? Otherwise dependencies may be an issue.
// TODO: maybe: convert to classes/methods approach??

$includes = array( 'posttypes', 'taxonomies' ); // , 'events', 'sermons'

foreach ( $includes as $inc ) {
    $filepath = $plugin_path . 'inc/'.$inc.'.php'; 
    if ( file_exists($filepath) ) { include_once( $filepath ); } else { echo "no $filepath found"; }
}

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */


// Add post_type query var to edit_post_link so as to be able to selectively load plugins via plugins-corral MU plugin
//add_filter( 'get_edit_post_link', 'add_post_type_query_var', 10, 3 );
function add_post_type_query_var( $url, $post_id, $context ) {

    $post_type = get_post_type( $post_id );
    
    // TODO: consider whether to add query_arg only for certain CPTS?
    if ( $post_type && !empty($post_type) ) { $url = add_query_arg( 'post_type', $post_type, $url ); }
    
    return $url;
}


/**
 * TODO: write functions to 
 * 1) calculate worklog_total_due for post_type paycheck, based on related event/worklog records and their amount_due field values, and
 * 2) update the record with the newly calculated value when saving a paycheck post. -- use $pod->save( 'worklog_total_due', $total_due ); 
 *
 */

//add_filter('pods_api_pre_save_pod_item_paycheck', 'bkkp_pre_pod_save_function', 10, 2); //pods_api_pre_save_pod_item_my_pod_name
function bkkp_pre_pod_save_function($pieces, $is_new_item) { 
    
    global $post;
    $post_id = $post->ID;
    
    $info = ""; // init
    $total_due = "0"; // init
    
    $total_due = calculate_worklog_total_due ( $post_id );
    $info .= "total_due: $total_due<br />";
    //echo $info;
    
    $pieces[ 'fields' ][ 'worklog_total_due' ][ 'value' ] = $total_due; 

    return $pieces; 
} 

function calculate_worklog_total_due ( $post_id ) {
    
    $total_due = 0; // init
    $info = ""; // init
    
    // Get the Paycheck pod record
    $pod = pods( 'paycheck', $post_id );
    if ($pod) {
        
        //$info .= 'pod retrieved for '.$pod->field('post_title')."<!--  -->";
    
        // Get all related event records
        //$worklog = get_post_meta( $post_id, 'worklog', true );
        if ($worklog = $pod->field('worklog')) {

            // Get amount_due for each
            foreach ( $worklog as $event ) {
                $event_id = $event['ID'];
                $amount_due = get_post_meta( $event_id, 'amount_due', true );
                //$amount_due = $event['amount_due'];
                //$total_due = $amount_due; // tft
                $total_due += $amount_due;
                //$total_due ++; // tft
                //$info .= "event_id: $event_id; amount_due: $amount_due<br />";
            }

        } else {
            $total_due = 111; //tft
            //$info .= "<!-- No Work Events found. -->";
        }
    }
    
    $info .= "total_due: $total_due";
    //echo $info;
    
    return $total_due;
    
}


/*** WIP FUNCTIONS ***/

add_shortcode('tax_docs', 'display_tax_docs');
function display_tax_docs ( $atts = [] ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    //sdg_log( "divline2", $do_log );
    //sdg_log( "function called: show_snippets", $do_log );
    
    // Init vars
    $info = "";
	$ts_info = "";
    
    $args = shortcode_atts( array(
		'dates'   => 'this_year', // 'last_year', 'YYYY-mm-dd, YYYY-mm-dd' [start/end]
        //'return' => 'info',
    ), $atts );
    
    // Extract
	extract( $args );
	
	// Get year or years
	$years = array();
	if ( $dates == 'this_year' ) {
		$years[] = date('Y');
	} else if ( $dates == 'last_year' ) {
		$years[] = date('Y')-1;
	} else {
		// WIP get year(s) from YYYY or YYYY-mm-dd or range
	}
	
	// Get Tax Docs per year
	foreach ( $years as $year ) {
		
		$info = "<h3>Tax Year: $year</h3>";
		
		// Set up basic query args
		$wp_args = array(
			'post_type'		=> 'document',
			'post_status'	=> 'publish',
			//'posts_per_page'=> $limit,
			'fields'		=> 'ids',
			//'orderby'		=> 'meta_value',
			//'order'			=> 'ASC',
		);	
	
		// Set up meta query
		$meta_query = array(
			'relation' => 'AND',
			'tax_year' => array(
				'key' => 'tax_year',
				'compare' => '=',
				'value' 	=> $year,
			),
		);
		$wp_args['meta_query'] = $meta_query;
	
		$arr_posts = new WP_Query( $wp_args );
		$docs = $arr_posts->posts;
		$ts_info .= "[".count($docs)."] docs found.<br />";
		//$ts_info .= "docs: <pre>".print_r($docs,true)."</pre>";
		if ( empty($docs) ) {
			//$ts_info .= "WP_Query run as follows:";
			$ts_info .= "wp_args: <pre>".print_r($wp_args, true)."</pre>";
			$ts_info .= "wp_query: <pre>".$arr_posts->request."</pre>"; // print sql tft
		} else {
			if ( function_exists( 'birdhive_display_collection' ) ) { // TBD: check instead if plugin_exists display-content?
				$content_type = 'posts';
				$display_format = 'table';
				$show_subtitles = true;
				$show_content = true;
				$display_atts = array( 'fields' => array( 'tax_year', 'title', 'total_comp' ), 'headers' => array( 'Tax Year', 'Title', 'Total Compensation' ) ); // fields, headers
				$display_args = array( 'content_type' => $content_type, 'display_format' => $display_format, 'show_subtitles' => $show_subtitles, 'show_content' => $show_content, 'items' => $docs, 'display_atts' => $display_atts, 'do_ts' => true ); //
				//$ts_info .= "display_args: <pre>".print_r($display_args,true)."</pre>";
				$info .= birdhive_display_collection( $display_args );
			}
        
		}
	
	}
	
	//
	//
	
	// Build table: Year, Doc Title, ....
	
	$info .= '<div class="code">'.$ts_info.'</div>';
	return $info;
	
}

add_shortcode('income', 'display_income');
function display_income ( $atts = [] ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    //sdg_log( "divline2", $do_log );
    //sdg_log( "function called: show_snippets", $do_log );
    
    // Init vars
    $info = "";
	$ts_info = "";
    
    $args = shortcode_atts( array(
    	'sources' => array( 'employment' ), // (employment/other; transactions, docs, events, etc)
    	//'sources' => array( 'employment' ), // 'interest', 'dividends', 'gifts', 'other'
    	//'categories' => array( 'employment' ), // 'interest', 'dividends', 'gifts', 'other'
		'groups' => 'all',
		'people' => 'all',
		'dates'   => 'ytd', // 'last_year', 'this_year', 'YYYY-mm-dd, YYYY-mm-dd' [start/end]
        //'return' => 'info',
        'show_headers'  => true,
    ), $atts );
    
    // Extract
	extract( $args );
	
	// Get year or years
	$years = array();
	if ( $dates == 'ytd' || $dates == 'this_year' ) {
		$years[] = date('Y');
	} else if ( $dates == 'last_year' ) {
		$years[] = date('Y')-1;
	} else {
		// WIP get year(s) from YYYY or YYYY-mm-dd or range
		if ( strlen($dates) == 4 ) { // && is_int($dates)
			$years[] = (int) $dates;
		}
	}
	
	foreach ( $years as $year ) {
	
		// Display header
		if ( $show_headers ) {
			if ( $year == date('Y') ) {
				$info .= "<h2>This Year ($year/YTD)</h2>";		
			} else if ( $year == date('Y')-1 ) {
				$info .= "<h2>Last Year ($year)</h2>";
			} else {
				$info .= "<h2>$year</h2>";
			}
		}		
		
		// TODO: if in_array( 'employment', $sources ) ...
		// If not dealing w/ employment income, then we'll query transactions and docs differently
		
		
		// Get Employers
		// +~+~+~+~+~+~+
		
		// Set up basic query args
		$wp_args = array(
			'post_type'		=> array('group', 'person'),
			'post_status'	=> 'publish',
			//'posts_per_page'=> $limit,
			'fields'		=> 'ids',
			'orderby'		=> 'title',
			//'orderby'		=> 'meta_value',
			'order'			=> 'ASC',
		);
	
		// Set up meta query
		$meta_query = array(
			'relation' => 'AND',
			'years_of_employment' => array(
				'key' => 'years_of_employment',
				'compare' => 'LIKE',
				'value' 	=> '"'.$year.'"', // matches exactly "123", not just 123. This prevents a match for "1234"
			),
		);
		$wp_args['meta_query'] = $meta_query;
	
		$arr_employers = new WP_Query( $wp_args );
		$employers = $arr_employers->posts;
		$ts_info .= "[".count($employers)."] employers found.<br />";
		if ( empty($employers) ) {
		
			//$ts_info .= "WP_Query run as follows:";
			$ts_info .= "wp_args: <pre>".print_r($wp_args, true)."</pre>";
			$ts_info .= "wp_query: <pre>".$arr_posts->request."</pre>"; // print sql tft
			
		} else {
			
			// Build array of items each of which includes:
			// Employer Name, Abbr, Work Cat, 1099/W2, Total Amnt (1099/W2), Total Taxes Withheld, % Withheld, Total Deposits (transactions), Deposits vs IRS (diff); Total Net Income, Total Gross Income, Notes
			
			$items = array();
			
			foreach ( $employers as $employer_id ) {
			
				$field_values = array();
				
				/*********************/
				// Get W2s/1099 amounts
				$total_comp = 0;
				$total_withheld = 0;
				// TODO: mod get_related_posts to accept meta_query? or use birdhive_get_posts instead? so as to not have to do tax_year check, below...
				$arr_obj_docs = get_related_posts( $employer_id, 'document', 'employer' ); // get_related_posts( $post_id = null, $related_post_type = null, $related_field_name = null, $return = 'all' )
				if ( $arr_obj_docs ) {
			
					//$info .= "<h3>Docs:</h3>";
			
					if ( is_array($arr_obj_docs) ) {
						//$info .= "<p>arr_compositions (".count($arr_compositions)."): <pre>".print_r($arr_compositions, true)."</pre></p>";
						foreach ( $arr_obj_docs as $doc ) {
							$doc_id = $doc->ID;
							$tax_year = get_field( 'tax_year', $doc_id );
							if ( $tax_year == $year ) { 
								//$info .= $doc->post_title."<br />";
								$comp = get_field( 'total_comp', $doc_id );
								$total_comp += $comp;
								// TODO: rename fields to match vars; update DB records
								$fed_tax = get_field( 'fed_tax_withheld', $doc_id );
								$total_withheld += (float) $fed_tax;
								//$info .= "fed_tax for doc_id [$doc_id]: $fed_tax<br />";
								$socsec_tax = get_field( 'ss_tax_withheld', $doc_id );
								$total_withheld += (float) $socsec_tax;
								$medicare_tax = get_field( 'medicare_tax_withheld', $doc_id );
								$total_withheld += (float) $medicare_tax;
								$state_tax = get_field( 'state_tax_withheld', $doc_id );
								$total_withheld += (float) $state_tax;
								$local_tax = get_field( 'local_tax_withheld', $doc_id );
								$total_withheld += (float) $local_tax;
								//$rep_info = get_rep_info( $doc->ID, 'display', false, true ); // ( $post_id = null, $format = 'display', $show_authorship = true, $show_title = true )
								//$info .= make_link( get_permalink($doc->ID), $rep_info, "TEST rep title" )."<br />";
							}
						}
					} else {
						//$info .= print_r($arr_obj_docs, true);
					}
					
				} else {
					$info .= "No docs found for get_related_posts (document -> employer = employer_id: $employer_id)<br />";
				}
				//$info .= "arr_obj_docs: ".print_r($arr_obj_docs, true)."<hr />"; // tft
				
				$field_values['total_comp'] = number_format_i18n($total_comp); // TODO: currency formatting
				$field_values['total_withheld'] = number_format_i18n($total_withheld); // TODO: currency formatting
				
				/*********************/
				// Get corresponding deposits total (transactions)
				$total_deposits = 0;
				
				// Set up basic args
				$wp_args = array(
					'post_type'		=> 'transaction',
					'post_status'	=> 'publish',
					'fields'		=> 'ids',
				);
	
				// Set up meta query
				$meta_query = array(
					'relation' => 'AND',
					'employer' => array(
						'relation' => 'OR',
						array(
							'key' => 'transactions_groups',
							'value' 	=> $employer_id,
						),
						array(
							'key' => 'transactions_people',
							'value' 	=> $employer_id,
						),
					),
					'tax_year' => array(
						'key' => 'tax_year',
						'value' 	=> $year,
					),
				);
				$wp_args['meta_query'] = $meta_query;
				$arr_transactions = new WP_Query( $wp_args );
				$transactions = $arr_transactions->posts;
				$info .= "[".count($transactions)."] transactions found for employer_id [$employer_id] in year $year.<br />";
				if ( empty($transactions) ) {
					//$info .= "[".count($transactions)."] transactions found for employer_id [$employer_id] in year $year.<br />";
					//$ts_info .= "WP_Query run as follows:";
					//$ts_info .= "wp_args: <pre>".print_r($wp_args, true)."</pre>";
					//$ts_info .= "wp_query: <pre>".$arr_transactions->request."</pre>"; // print sql tft			
				} else {
					foreach ( $transactions as $transaction_id ) {
						$amount = get_field( 'amount', $transaction_id );
						$total_deposits += $amount;
						//$rep_info = get_rep_info( $doc->ID, 'display', false, true ); // ( $post_id = null, $format = 'display', $show_authorship = true, $show_title = true )
						//$info .= make_link( get_permalink($doc->ID), $rep_info, "TEST rep title" )."<br />";
					}
				}
		
				$field_values['total_deposits'] = number_format_i18n($total_deposits); // TODO: currency formatting
				
				/*********************/
				// Calc comp/deposits diff
				$diff = $total_comp-$total_withheld-$total_deposits;
				if ( $diff <> 0 ) { $field_values['diff'] = number_format_i18n($diff); } else { $field_values['diff'] = '--'; }
				
				/*********************/
				// Finish assembling item and add to items array to be passed to birdhive_display_collection
				$arr_item = array();
				$arr_item['post_id'] = $employer_id;
				$arr_item['field_values'] = $field_values;
				$items[] = $arr_item;
			}
			
			if ( function_exists( 'birdhive_display_collection' ) ) { // TBD: check instead if plugin_exists display-content?
				$content_type = 'posts'; // ?
				$display_format = 'table';
				$show_subtitles = true;
				$show_content = true;
				$display_atts = array( 'fields' => array( 'title', 'abbr', 'total_comp', 'total_withheld', 'total_deposits', 'diff' ), 'headers' => array( 'Employer Name', 'Abbr', 'Total Compensation', 'Total Withheld', 'Total Deposits', 'diff' ) ); // fields, headers
				$display_args = array( 'content_type' => $content_type, 'display_format' => $display_format, 'show_subtitles' => $show_subtitles, 'show_content' => $show_content, 'items' => $items, 'display_atts' => $display_atts, 'do_ts' => true ); //
				//$ts_info .= "display_args: <pre>".print_r($display_args,true)."</pre>";
				$info .= birdhive_display_collection( $display_args );
			}
			
			
		}
	
		
	
	}
	
	//
	//
	
	
	
	$info .= '<div class="code">'.$ts_info.'</div>';
	return $info;
	
}

?>