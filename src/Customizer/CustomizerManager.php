<?php
/**
 * Customizer Manager
 * 
 * Handles WordPress Customizer sections, settings, and controls
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Customizer;

use WordPressSkin\Core\Skin;

defined('ABSPATH') or exit;

class CustomizerManager {
    /**
     * Skin instance
     * 
     * @var Skin
     */
    private $skin;
    
    /**
     * Registered sections
     * 
     * @var array
     */
    private $sections = [];
    
    /**
     * Constructor
     * 
     * @param Skin $skin
     */
    public function __construct(Skin $skin) {
        $this->skin = $skin;
        
        // Hook into WordPress Customizer
        add_action('customize_register', [$this, 'registerCustomizer']);
    }
    
    /**
     * Create or get a section
     * 
     * @param string $sectionId Section ID
     * @param callable|null $callback Configuration callback
     * @return Section
     */
    public function section($sectionId, $callback = null) {
        if (!isset($this->sections[$sectionId])) {
            $this->sections[$sectionId] = new Section($sectionId, $this);
        }
        
        if ($callback && is_callable($callback)) {
            $callback($this->sections[$sectionId]);
        }
        
        return $this->sections[$sectionId];
    }
    
    /**
     * Register all sections with WordPress Customizer
     * 
     * @param \WP_Customize_Manager $wp_customize
     * @return void
     */
    public function registerCustomizer($wp_customize) {
        foreach ($this->sections as $section) {
            $section->register($wp_customize);
        }
    }
    
    /**
     * Get all sections
     * 
     * @return array
     */
    public function getSections() {
        return $this->sections;
    }
}