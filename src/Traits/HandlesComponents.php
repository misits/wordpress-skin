<?php
/**
 * Handles Components Trait
 * 
 * Theme component management functionality
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Traits;

use WordPressSkin\Core\ComponentManager;

defined('ABSPATH') or exit;

trait HandlesComponents {
    /**
     * Component manager instance
     * 
     * @var ComponentManager|null
     */
    private $componentManager = null;
    
    /**
     * Initialize components functionality
     * 
     * @return void
     */
    protected function initializeComponents(): void {
        $this->componentManager = new ComponentManager($this);
    }
    
    /**
     * Get component manager instance
     * 
     * @return ComponentManager
     */
    public function getComponentManager(): ComponentManager {
        if ($this->componentManager === null) {
            $this->initializeComponents();
        }
        
        return $this->componentManager;
    }
}