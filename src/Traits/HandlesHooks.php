<?php
/**
 * Handles Hooks Trait
 * 
 * WordPress hooks and filters management
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Traits;

use WordPressSkin\Hooks\HookManager;

defined('ABSPATH') or exit;

trait HandlesHooks {
    /**
     * Hook manager instance
     * 
     * @var HookManager|null
     */
    private $hookManager = null;
    
    /**
     * Initialize hooks functionality
     * 
     * @return void
     */
    protected function initializeHooks(): void {
        $this->hookManager = new HookManager($this);
    }
    
    /**
     * Get hook manager instance
     * 
     * @return HookManager
     */
    public function getHookManager(): HookManager {
        if ($this->hookManager === null) {
            $this->initializeHooks();
        }
        
        return $this->hookManager;
    }
}