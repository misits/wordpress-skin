<?php
/**
 * Handles Taxonomies
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Traits;

use WordPressSkin\Taxonomies\TaxonomyManager;

defined('ABSPATH') or exit;

trait HandlesTaxonomies {
    /**
     * Taxonomy manager instance
     * 
     * @var TaxonomyManager|null
     */
    private $taxonomy_manager = null;
    
    /**
     * Initialize taxonomies functionality
     * 
     * @return void
     */
    protected function initializeTaxonomies(): void {
        $this->taxonomy_manager = new TaxonomyManager();
    }
    
    /**
     * Get taxonomy manager instance
     * 
     * @return TaxonomyManager
     */
    public function getTaxonomyManager(): TaxonomyManager {
        if ($this->taxonomy_manager === null) {
            $this->initializeTaxonomies();
        }
        
        return $this->taxonomy_manager;
    }
}