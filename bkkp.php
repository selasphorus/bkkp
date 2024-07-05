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



/*** WIP FUNCTIONS ***/

add_action( 'save_post', 'bkkp_save_post_callback', 10, 3 );
function bkkp_save_post_callback( $post_id, $post, $update ) {
    
    // TS/logging setup
    $do_ts = devmode_active();
    $do_log = false;
    sdg_log( "divline1", $do_log );
    //sdg_log( "action: save_post", $do_log );
    //sdg_log( "action: save_post_event", $do_log );
    sdg_log( "function called: bkkp_save_post_callback", $do_log );
    
   // if ( is_dev_site() ) { sdg_add_post_term( $post_id, 'dev-test-tmp', 'admin_tag', true ); }
    
    // Don't run if this is an auto-draft
    if ( isset($post->post_status) && 'auto-draft' == $post->post_status ) {
        return;
    }

    // Don't run if this is an Autosave operation
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        return;
    }
    
    // If this is a post revision, then abort
    if ( wp_is_post_revision( $post_id ) ) { 
        sdg_log( "[sspc] is post revision >> abort.", $do_log );
        return;
    }
    
    $post_type = $post->post_type;
    //$post_type = get_post_type( $post_id );
    // get custom fields from $_POST var?
    sdg_log( "[sspc] post_type: ".$post_type, $do_log );
    
    $post_type = get_post_type( $post_id );
    
    // Update transaction types, as needed
    if ( $post_type == 'transaction' ) {
    	// TODO: detect whether amount is positive or negative number => if transaction type is not set already, set it, and update negative amounts to abs value
    	// Also add transaction_tag to indicate that record has been updated post-import?
    	$transaction_type = get_field('transaction_type', $post_id);
    	if ( empty($transaction_type) || $transaction_type == "unknown" ) {
    		$amount = get_field('amount', $post_id);
    		if ( $amount > 0 ) {
    			$transaction_type = 'credit';
    		} else {
    			$transaction_type = 'debit';
    			$amount = abs($amount);
    			update_field('amount', $amount, $post_id);
    		}
    		update_field('transaction_type', $transaction_type, $post_id); // update_field($selector, $value, $post_id);
    		sdg_add_post_term( $post_id, 'programmatically-updated', 'transaction_tag', true );
    	}
    }
    
    
    // Check for CPT-specific build_the_title function
    // WIP
    
    $function_name = "build_".$post_type."_title";
    if ( function_exists($function_name) ) {
    
    	// Get post obj, post_title
        $the_post = get_post( $post_id );
        $post_title = $the_post->post_title;
        
        // Get title/slug based on post field values
        $new_title = $function_name( $post_id );
        if ( function_exists('sanitize_title') ) { $new_slug = sanitize_title($new_title); }

        // If we've got a new post_title, prep to run the update
    
    	// Check to see if new_slug is really new. If it's identical to the existing slug, skip the update process.
        if ( $new_title != $post_title ) {

			sdg_log( "[sspc] update the post_title", $do_log );
			
			// TODO: figure out how NOT to trigger wp_insert_post_data when running this update...
			
            // unhook this function to prevent infinite looping
            remove_action( 'save_post', 'bkkp_save_post_callback' );

            // Update the post
            $update_args = array(
                'ID'       	=> $post_id,
                'post_title'=> $new_title,
                'post_name'	=> $new_slug,
            );

            // Update the post into the database
            wp_update_post( $update_args, true );    

            if ( ! is_wp_error($post_id) ) {
                // If update was successful, add admin tag to note that slug has been updated
                sdg_add_post_term( $post_id, 'title-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
                //$info .= sdg_add_post_term( $post_id, 'slug-updated', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
            }

            // re-hook this function
            add_action( 'save_post', 'bkkp_save_post_callback', 10, 3 );

        }
      
    } //
	
}

function bkkp_acf_field_calc($post_id) {

	$calc = get_field('field_name_one') + get_field('field_name_two');
	$value = $calc;
	$field_to_update = "field_name_three";
	update_field($field_to_update, $value, $post_id);
	
	//total_earnings == calc from earnings repeater
	//total_deductions == calc from deductions repeater
	//net_xcheck = total_earnings - total_deductions;
	
	//worklog_total_due
	
	/*
	$total_due = calculate_worklog_total_due ( $post_id );
    $info .= "total_due: $total_due<br />";
    //echo $info;
    */
	
}
add_action('save_post', 'bkkp_acf_field_calc');




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

// Shortcode/function for displaying bookkeeping data.
// Default: transactions relating to employment income
add_shortcode('bkkp', 'bkkp');
function bkkp ( $atts = [] ) {

	// TS/logging setup
    $do_ts = devmode_active();
    $do_log = false;
    //sdg_log( "divline2", $do_log );
    //sdg_log( "function called: bkkp", $do_log );
    
    // Init vars
    $info = "";
	$ts_info = "";
    
    $args = shortcode_atts( array(
		'dates'   => 'ytd', // 'last_year', 'this_year', 'YYYY-mm-dd, YYYY-mm-dd' [start/end]
		// TODO/WIP: change 'dates' to 'scope' and integrate bkkp with birdhive-events with display-content so they all handle scope the same way
		'scope'		=> 'ytd',
		'data_type'	=> 'transactions', // income, tax_docs, transactions...
		//
    	'sources' => array( 'employment' ), // (employment/other; transactions, docs, events, etc) // 'interest', 'dividends', 'gifts', 'other'
    	//'categories' => array( 'employment' ), // 'interest', 'dividends', 'gifts', 'other'
    	
		'accounts' => 'all',
		'groups' => 'all',
		'people' => 'all',
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
		} else if ( strpos($dates,",") ) {
			// multiple years
			$years = explode(",",$dates);
		}
	}
	
	// TODO: do this only for income queries?
	if ( $data_type == "income" ) {
		if ( !is_array($sources) ) {
			$sources = explode(",",$sources);
			$args['sources'] = $sources;
		}		
	} else {
		unset($args['sources']);
	}
	
	foreach ( $years as $year ) {
	
		$args['year'] = $year;
	
		// Display header
		if ( $show_headers ) {
			if ( count($years) > 1 ) {
				$info .= "<h3>$year</h3>";
			} else if ( $year == date('Y') ) {
				$info .= "<h3>This Year ($year/YTD)</h3>";		
			} else if ( $year == date('Y')-1 ) {
				$info .= "<h3>Last Year ($year)</h3>";
			} else {
				$info .= "<h3>$year</h3>";
			}
		}
		
		// call display_tax_docs; display_income
		$function_name = "display_".$data_type;
		if ( function_exists($function_name) ) {
			$info .= $function_name($args);
		}		
		
	}	
	
	if ( $do_ts ) { $info .= $ts_info; } //$info .= '<div class="code">'.$ts_info.'</div>';
	return $info;
	
}

// Call via bkkp shortcode
function display_tax_docs ( $args = array() ) {

	// TS/logging setup
    $do_ts = devmode_active();
    $do_log = false;
    //sdg_log( "divline2", $do_log );
    //sdg_log( "function called: show_snippets", $do_log );
    
    // Init vars
    $info = "";
	$ts_info = "";
	
    // Extract
	extract( $args );
    
    ////	
		
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
			$display_atts = array( 'fields' => array( 'tax_year', 'title', 'total_comp' ), 'headers' => array( 'Tax Year', 'Title', 'Total Compensation' ), 'totals' => array('total_comp') ); // fields, headers
			$display_args = array( 'content_type' => $content_type, 'display_format' => $display_format, 'show_subtitles' => $show_subtitles, 'show_content' => $show_content, 'items' => $docs, 'display_atts' => $display_atts, 'do_ts' => $do_ts ); //
			//$ts_info .= "display_args: <pre>".print_r($display_args,true)."</pre>";
			$info .= birdhive_display_collection( $display_args );
		}	
	}
	
	if ( $do_ts ) { $info .= $ts_info; } //$info .= '<div class="code">'.$ts_info.'</div>';
	return $info;
	
}

function display_income ( $args = array() ) {

	// TS/logging setup
    $do_ts = devmode_active();
    $do_log = false;
    //sdg_log( "divline2", $do_log );
    //sdg_log( "function called: show_snippets", $do_log );
    
    // Init vars
    $info = "";
	$ts_info = "";
	
    // Extract
	extract( $args );
	$items = array();
	
	// WIP
	//$info .= "display_income -- args: <pre>".print_r($args, true).'</pre>';
	// If not dealing w/ employment income, then we'll query transactions and docs differently
	if ( in_array( 'employment', $sources ) ) { // is_array($sources) && 
	
		// Get Employers
		// +~+~+~+~+~+~+
	
		// Set up basic query args
		$wp_args = array(
			'post_type'		=> array('group', 'person'),
			'post_status'	=> 'publish',
			//'posts_per_page'=> $limit,
			'posts_per_page'=> '-1',
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
					$ts_info .= "No docs found for get_related_posts (document -> employer = employer_id: $employer_id)<br />";
				}
				//$info .= "arr_obj_docs: ".print_r($arr_obj_docs, true)."<hr />"; // tft
			
				$field_values['total_comp'] = $total_comp; // TODO: currency formatting
				$field_values['total_withheld'] = $total_withheld; // TODO: currency formatting
			
				/*********************/
				// Get corresponding deposits total (transactions)
				$total_deposits = 0;
			
				// Set up basic args
				$wp_args = array(
					'post_type'		=> 'transaction',
					'post_status'	=> 'publish',
					'fields'		=> 'ids',
					'posts_per_page'=> '-1',
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
				//$info .= "[".count($transactions)."] transactions found for ".get_the_title($employer_id)." (employer_id [$employer_id]) in year $year.<br />";
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
	
				$field_values['total_deposits'] = $total_deposits; // TODO: currency formatting
			
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
				
			} // END foreach employers
		
		} // END if ( empty($employers) )
	
		// Set display_atts for display_collection
		$table_fields = array( 'title', 'abbr', 'total_comp', 'total_withheld', 'total_deposits', 'diff' );
		$table_headers = array( 'Employer Name', 'Abbr', 'Total Compensation', 'Total Withheld', 'Total Deposits', 'diff' );
		$table_totals = array('total_comp', 'total_withheld', 'total_deposits' );
			
	} else {
	
		// NOT employment income
		// other -- or more specific?
		
		// WIP
		// investments -- table summary to include dividends, capital gains, foreign tax paid, etc.
		
		// interest income, gifts, jury duty, etc...
		
		// Set display_atts for display_collection
		$table_fields = array( 'title', 'abbr', 'total_comp', 'total_withheld', 'total_deposits', 'diff' );
		$table_headers = array( 'Employer Name', 'Abbr', 'Total Compensation', 'Total Withheld', 'Total Deposits', 'diff' );
		$table_totals = array('total_comp', 'total_withheld', 'total_deposits' );
		
	}
	
	if ( !empty($items) && function_exists( 'birdhive_display_collection' ) ) { // TBD: check instead if plugin_exists display-content?
		$content_type = 'posts'; // ?
		$display_format = 'table';
		$show_subtitles = true;
		$show_content = true;
		
		// TODO: Add cols: num docs, num transactions?
		// Set display_atts for display_collection
		$display_atts = array( 'fields' => $table_fields, 'headers' => $table_headers, 'totals' => $table_totals ); // fields, headers, totals
		$display_args = array( 'content_type' => $content_type, 'display_format' => $display_format, 'show_subtitles' => $show_subtitles, 'show_content' => $show_content, 'items' => $items, 'display_atts' => $display_atts, 'do_ts' => $do_ts ); //
		//$ts_info .= "display_args: <pre>".print_r($display_args,true)."</pre>";
		$info .= birdhive_display_collection( $display_args );
	}
	
	if ( $do_ts ) { $info .= $ts_info; } //$info .= '<div class="code">'.$ts_info.'</div>';
	return $info;
	
}

?>