<?php
/**
 * Handles Custom Post Types
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Traits;

use WordPressSkin\PostTypes\PostTypeManager;

defined('ABSPATH') or exit;

trait HandlesPostTypes {
    /**
     * Post type manager instance
     * 
     * @var PostTypeManager|null
     */
    private $post_type_manager = null;
    
    /**
     * Initialize post types functionality
     * 
     * @return void
     */
    protected function initializePostTypes(): void {
        $this->post_type_manager = new PostTypeManager();
    }
    
    /**
     * Get post type manager instance
     * 
     * @return PostTypeManager
     */
    public function getPostTypeManager(): PostTypeManager {
        if ($this->post_type_manager === null) {
            $this->initializePostTypes();
        }
        
        return $this->post_type_manager;
    }
}