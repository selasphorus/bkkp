<?php

namespace atc\Bkkp\Admin;

/**
 * One-time utility to clean up meta field issues from initial data import
 * 
 * Tasks:
 * - Rename import_amount → amount_signed
 * - Copy amount → amount_signed (where missing)
 * - Convert negative amounts to positive
 */
class MetaFieldCleanup {
    
    /**
     * Run the cleanup process
     * 
     * @param bool $dry_run If true, don't make changes, just report what would happen
     * @return array Results summary
     */
    public static function run($dry_run = false) {
        global $wpdb;
        
        // Safety check (skip in dry-run mode)
        if (!$dry_run && get_option('bkkp_meta_cleanup_completed')) {
            return [
                'status' => 'skipped',
                'message' => 'Cleanup already completed. Remove option "bkkp_meta_cleanup_completed" to re-run.'
            ];
        }
        
        $results = [
            'dry_run' => $dry_run,
            'renamed' => 0,
            'copied' => 0,
            'unsigned' => 0,
            'errors' => []
        ];
        
        // Task 1: Rename "import_amount" to "amount_signed"
        self::rename_meta_key($results, $dry_run);
        
        // Task 2: Copy "amount" to "amount_signed" where missing
        self::copy_amount_to_signed($results, $dry_run);
        
        // Task 3: Convert negative amounts to positive
        self::make_amounts_unsigned($results, $dry_run);
        
        // Mark as complete (only if not dry-run)
        if (!$dry_run) {
            update_option('bkkp_meta_cleanup_completed', current_time('mysql'));
        }
        
        $results['status'] = 'success';
        $results['message'] = $dry_run ? 'Dry run completed - no changes made' : 'Cleanup completed successfully';
        
        return $results;
    }
    
    /**
     * Rename import_amount meta_key to amount_signed
     */
    private static function rename_meta_key(&$results, $dry_run) {
        global $wpdb;
        
        $post_ids = $wpdb->get_col(
            "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'import_amount'"
        );
        
        foreach ($post_ids as $post_id) {
            $value = get_post_meta($post_id, 'import_amount', true);
            if ($value !== '') {
                if (!$dry_run) {
                    update_post_meta($post_id, 'amount_signed', $value);
                    delete_post_meta($post_id, 'import_amount');
                }
                $results['renamed']++;
            }
        }
    }
    
    /**
     * Copy amount to amount_signed where amount_signed doesn't exist
     */
    private static function copy_amount_to_signed(&$results, $dry_run) {
        global $wpdb;
        
        $post_ids = $wpdb->get_col(
            "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'amount'"
        );
        
        foreach ($post_ids as $post_id) {
            $amount_signed = get_post_meta($post_id, 'amount_signed', true);
            
            // Only copy if amount_signed doesn't exist or is empty
            if ($amount_signed === '' || $amount_signed === false) {
                $amount = get_post_meta($post_id, 'amount', true);
                if ($amount !== '') {
                    if (!$dry_run) {
                        update_post_meta($post_id, 'amount_signed', $amount);
                    }
                    $results['copied']++;
                }
            }
        }
    }
    
    /**
	 * Convert negative amounts to positive (unsigned) only for debit transactions
	 */
	private static function make_amounts_unsigned(&$results, $dry_run) {
		global $wpdb;
		
		// Get only post_ids with negative amounts AND transaction_type='debit'
		$post_ids = $wpdb->get_col(
			"SELECT DISTINCT pm1.post_id 
			FROM {$wpdb->postmeta} pm1
			INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
			WHERE pm1.meta_key = 'amount' 
			AND CAST(pm1.meta_value AS DECIMAL(10,2)) < 0
			AND pm2.meta_key = 'transaction_type'
			AND pm2.meta_value = 'debit'"
		);
		
		foreach ($post_ids as $post_id) {
			$amount = get_post_meta($post_id, 'amount', true);
			
			if (!$dry_run) {
				update_post_meta($post_id, 'amount', abs(floatval($amount)));
			}
			$results['unsigned']++;
		}
	}
    
    // Add to your MetaFieldCleanup class or create a new command

	/**
	 * Convert related_group from single value to serialized array format.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Preview changes without updating
	 *
	 * @when after_wp_load
	 */
	public function convert_related_group_format($args, $assoc_args) {
		global $wpdb;
		
		$dry_run = isset($assoc_args['dry-run']);
		
		// Find all transaction posts with related_group that isn't already serialized
		$results = $wpdb->get_results("
			SELECT post_id, meta_value 
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.post_type = 'transaction'
			AND pm.meta_key = 'related_group'
			AND pm.meta_value NOT LIKE 'a:%'
			AND pm.meta_value != ''
		");
		
		WP_CLI::log(sprintf('Found %d records to convert', count($results)));
		
		foreach ($results as $row) {
			$old_value = $row->meta_value;
			$new_value = serialize([$old_value]); // Convert to array format
			
			WP_CLI::log(sprintf(
				'Post %d: %s → %s',
				$row->post_id,
				$old_value,
				$new_value
			));
			
			if (!$dry_run) {
				update_post_meta($row->post_id, 'related_group', [$old_value]);
			}
		}
		
		WP_CLI::success($dry_run ? 'Dry run complete' : 'Conversion complete');
	}

    /**
     * Reset the cleanup flag to allow re-running
     */
    public static function reset() {
        return delete_option('bkkp_meta_cleanup_completed');
    }
}