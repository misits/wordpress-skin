<?php
/**
 * Handles Customizer Trait
 * 
 * WordPress Customizer functionality for themes
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Traits;

use WordPressSkin\Customizer\CustomizerManager;

defined('ABSPATH') or exit;

trait HandlesCustomizer {
    /**
     * Customizer manager instance
     * 
     * @var CustomizerManager|null
     */
    private $customizerManager = null;
    
    /**
     * Initialize customizer functionality
     * 
     * @return void
     */
    protected function initializeCustomizer(): void {
        $this->customizerManager = new CustomizerManager($this);
    }
    
    /**
     * Get customizer manager instance
     * 
     * @return CustomizerManager
     */
    public function getCustomizerManager(): CustomizerManager {
        if ($this->customizerManager === null) {
            $this->initializeCustomizer();
        }
        
        return $this->customizerManager;
    }
    
    /**
     * Add customizer section
     * 
     * @param string $section Section ID
     * @param callable|null $callback Configuration callback
     * @return \WordPressSkin\Customizer\Section
     */
    public function addCustomizerSection($section, $callback = null) {
        return $this->getCustomizerManager()->section($section, $callback);
    }
}