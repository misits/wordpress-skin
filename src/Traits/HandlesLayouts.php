<?php
/**
 * Handles Layouts Trait
 * 
 * Template and layout management functionality
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Traits;

use WordPressSkin\Core\LayoutManager;

defined('ABSPATH') or exit;

trait HandlesLayouts {
    /**
     * Layout manager instance
     * 
     * @var LayoutManager|null
     */
    private $layoutManager = null;
    
    /**
     * Initialize layouts functionality
     * 
     * @return void
     */
    protected function initializeLayouts(): void {
        $this->layoutManager = new LayoutManager($this);
    }
    
    /**
     * Get layout manager instance
     * 
     * @return LayoutManager
     */
    public function getLayoutManager(): LayoutManager {
        if ($this->layoutManager === null) {
            $this->initializeLayouts();
        }
        
        return $this->layoutManager;
    }
}