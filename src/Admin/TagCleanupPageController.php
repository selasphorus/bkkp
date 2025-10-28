<?php

namespace atc\Bkkp\Admin;

/**
 * Admin page controller for cleaning up transaction tags
 * 
 * Provides UI for mapping combo tags to separate tags and performing the cleanup
 */
class TagCleanupPageController
{
    /**
     * Register hooks to add the admin page
     */
    public function addHooks(): void
    {
        // Register the page using WHx4's AdminPageRegistry
        add_action('whx4_admin_pages_init', [$this, 'registerPage']);
        
        // Handle form submission
        add_action('admin_post_bkkp_cleanup_tags', [$this, 'handleCleanup']);
    }
    
    /**
     * Register the tag cleanup page as a submenu under WHx4 settings
     */
    public function registerPage($registry): void
    {
        // Check if registry exists (in case WHx4 isn't active)
        if (!method_exists($registry, 'registerPage')) {
            return;
        }
        
        $registry->registerPage('bkkp-tag-cleanup', [
            'type' => 'submenu',
            'parent_slug' => 'options-general.php', // Under Settings menu
            'page_title' => 'Bkkp Tag Cleanup',
            'menu_title' => 'Bkkp Tag Cleanup',
            'capability' => 'manage_options',
            'menu_slug' => 'bkkp-tag-cleanup',
            'controller' => [$this, 'renderPage'],
        ]);
    }
    
    /**
     * Render the admin page
     */
    public function renderPage(): void
    {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Get all transaction_tag terms
        $tags = get_terms([
            'taxonomy' => 'transaction_tag',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
        
        ?>
        <div class="wrap">
            <h1>Transaction Tag Cleanup</h1>
            
            <?php if (isset($_GET['cleanup_success'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Success!</strong> Tags have been cleaned up.</p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['cleanup_error'])): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>Error:</strong> <?php echo esc_html($_GET['cleanup_error']); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="card" style="max-width: 1200px;">
                <h2>Instructions</h2>
                <p>This tool helps you split combo tags into separate individual tags.</p>
                <ol>
                    <li>Review the tag mapping below</li>
                    <li>Modify the mapping in the code if needed (<code>getTagMapping()</code> method)</li>
                    <li>Click "Run Cleanup" to process the tags</li>
                </ol>
                <p><strong>Note:</strong> This is a one-time operation. Make sure you have a backup before proceeding.</p>
            </div>
            
            <div class="card" style="max-width: 1200px; margin-top: 20px;">
                <h2>Current Tag Mapping</h2>
                <p>The following combo tags will be split:</p>
                
                <table class="widefat striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Combo Tag</th>
                            <th style="width: 10%; text-align: center;">Count</th>
                            <th style="width: 50%;">Will Be Split Into</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $mapping = $this->getTagMapping();
                        $tagCounts = [];
                        
                        // Get counts for each tag
                        foreach ($tags as $tag) {
                            $tagCounts[$tag->name] = $tag->count;
                        }
                        
                        if (empty($mapping)):
                        ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #666;">
                                    <em>No tag mapping defined yet. Add mappings to the <code>getTagMapping()</code> method.</em>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mapping as $comboTag => $separateTags): ?>
                                <tr>
                                    <td><code><?php echo esc_html($comboTag); ?></code></td>
                                    <td style="text-align: center;">
                                        <?php 
                                        $count = $tagCounts[$comboTag] ?? 0;
                                        echo $count > 0 ? $count : '<span style="color: #999;">0</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php foreach ($separateTags as $tag): ?>
                                            <code style="margin-right: 8px; background: #e1f5fe; padding: 2px 6px; border-radius: 3px;">
                                                <?php echo esc_html($tag); ?>
                                            </code>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($mapping)): ?>
            <div style="margin-top: 20px;">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" 
                      onsubmit="return confirm('Are you sure you want to run the tag cleanup? This cannot be undone. Make sure you have a backup!');">
                    <?php wp_nonce_field('bkkp_cleanup_tags_action', 'bkkp_cleanup_tags_nonce'); ?>
                    <input type="hidden" name="action" value="bkkp_cleanup_tags">
                    
                    <p>
                        <button type="submit" class="button button-primary button-large">
                            Run Cleanup
                        </button>
                        <span style="margin-left: 15px; color: #666;">
                            <strong><?php echo count($mapping); ?></strong> combo tag(s) will be processed
                        </span>
                    </p>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="card" style="max-width: 1200px; margin-top: 20px; background: #fff9e6;">
                <h3 style="margin-top: 0;">⚠️ Important Notes</h3>
                <ul>
                    <li>This operation will modify your transaction tags permanently</li>
                    <li>Combo tags will be deleted after their component tags are assigned</li>
                    <li>Always test on a staging site first if possible</li>
                    <li>Have a database backup before proceeding</li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle the tag cleanup form submission
     */
    public function handleCleanup(): void
    {
        // Verify nonce
        if (!isset($_POST['bkkp_cleanup_tags_nonce']) || 
            !wp_verify_nonce($_POST['bkkp_cleanup_tags_nonce'], 'bkkp_cleanup_tags_action')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions');
        }
        
        try {
            $this->cleanupTags();
            
            // Redirect with success message
            wp_redirect(add_query_arg(
                'cleanup_success',
                '1',
                admin_url('options-general.php?page=bkkp-tag-cleanup')
            ));
            exit;
            
        } catch (\Exception $e) {
            // Redirect with error message
            wp_redirect(add_query_arg(
                'cleanup_error',
                urlencode($e->getMessage()),
                admin_url('options-general.php?page=bkkp-tag-cleanup')
            ));
            exit;
        }
    }
    
    /**
     * Perform the actual tag cleanup
     */
    private function cleanupTags(): void
    {
        $tagMapping = $this->getTagMapping();
        $processedCount = 0;
        $errors = [];
        
        foreach ($tagMapping as $comboTag => $separateTags) {
            try {
                // Find the combo tag term
                $term = get_term_by('name', $comboTag, 'transaction_tag');
                
                if (!$term) {
                    continue; // Skip if term doesn't exist
                }
                
                // Get all posts with this combo tag
                $args = [
                    'post_type' => 'transaction',
                    'tax_query' => [
                        [
                            'taxonomy' => 'transaction_tag',
                            'field' => 'term_id',
                            'terms' => $term->term_id,
                        ],
                    ],
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                ];
                
                $posts = get_posts($args);
                
                // For each post, add the separate tags
                foreach ($posts as $post_id) {
                    // Add the new separate tags (append, don't replace)
                    wp_set_object_terms($post_id, $separateTags, 'transaction_tag', true);
                    
                    // Remove the combo tag
                    wp_remove_object_terms($post_id, $term->term_id, 'transaction_tag');
                }
                
                // Delete the now-unused combo tag
                wp_delete_term($term->term_id, 'transaction_tag');
                
                $processedCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Error processing '{$comboTag}': " . $e->getMessage();
            }
        }
        
        if (!empty($errors)) {
            throw new \Exception(implode(' | ', $errors));
        }
        
        if ($processedCount === 0) {
            throw new \Exception('No tags were processed. Check if the mapped tags exist.');
        }
    }
    
    /**
     * Get the tag mapping configuration
     * 
     * Override this method or modify TagMappingData::getMappings() to define your tag mappings
     * 
     * @return array<string, string[]> Mapping of combo tag => [separate, tags]
     */
    protected function getTagMapping(): array
    {
        // Get mappings from the data class (easier to manage large lists)
        return TagMappingData::getMappings();
    }
}
