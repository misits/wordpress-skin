<?php
/**
 * Main Skin Class
 * 
 * Unified interface for all theme management features
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Core;

use WordPressSkin\Traits\HandlesCustomizer;
use WordPressSkin\Traits\HandlesAssets;
use WordPressSkin\Traits\HandlesHooks;
use WordPressSkin\Traits\HandlesLayouts;
use WordPressSkin\Traits\HandlesComponents;
use WordPressSkin\Traits\HandlesPostTypes;
use WordPressSkin\Traits\HandlesTaxonomies;

defined('ABSPATH') or exit;

class Skin {
    use HandlesCustomizer;
    use HandlesAssets;
    use HandlesHooks;
    use HandlesLayouts;
    use HandlesComponents;
    use HandlesPostTypes;
    use HandlesTaxonomies;
    
    /**
     * Singleton instance
     * 
     * @var self|null
     */
    private static $instance = null;
    
    /**
     * Theme root directory
     * 
     * @var string
     */
    private $theme_root;
    
    /**
     * Resources directory
     * 
     * @var string
     */
    private $resources_path;
    
    /**
     * Resources URL
     * 
     * @var string
     */
    private $resources_url;
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->theme_root = get_template_directory();
        $this->resources_path = $this->theme_root . '/resources';
        $this->resources_url = get_template_directory_uri() . '/resources';
        
        $this->initializeTraits();
    }
    
    /**
     * Get singleton instance
     * 
     * @return self
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Static method to access customizer functionality
     * 
     * @param string|null $section Section name
     * @param callable|null $callback Configuration callback
     * @return \WordPressSkin\Customizer\CustomizerManager|\WordPressSkin\Customizer\Section
     */
    public static function customizer($section = null, $callback = null) {
        $instance = self::getInstance();
        
        if ($section === null) {
            return $instance->getCustomizerManager();
        }
        
        return $instance->addCustomizerSection($section, $callback);
    }
    
    /**
     * Static method to access assets functionality
     * 
     * @return \WordPressSkin\Assets\AssetManager
     */
    public static function assets() {
        return self::getInstance()->getAssetManager();
    }
    
    /**
     * Static method to access hooks functionality
     * 
     * @return \WordPressSkin\Hooks\HookManager
     */
    public static function hooks() {
        return self::getInstance()->getHookManager();
    }
    
    /**
     * Static method to access layouts functionality
     * 
     * @return \WordPressSkin\Core\LayoutManager
     */
    public static function layouts() {
        return self::getInstance()->getLayoutManager();
    }
    
    /**
     * Static method to access components functionality
     * 
     * @return \WordPressSkin\Core\ComponentManager
     */
    public static function components() {
        return self::getInstance()->getComponentManager();
    }
    
    /**
     * Static method to access post types functionality
     * 
     * @return \WordPressSkin\PostTypes\PostTypeManager
     */
    public static function postTypes() {
        return self::getInstance()->getPostTypeManager();
    }
    
    /**
     * Static method to access taxonomies functionality
     * 
     * @return \WordPressSkin\Taxonomies\TaxonomyManager
     */
    public static function taxonomies() {
        return self::getInstance()->getTaxonomyManager();
    }
    
    /**
     * Get theme root directory
     * 
     * @return string
     */
    public function getThemeRoot(): string {
        return $this->theme_root;
    }
    
    /**
     * Get resources directory path
     * 
     * @return string
     */
    public function getResourcesPath(): string {
        return $this->resources_path;
    }
    
    /**
     * Get resources directory URL
     * 
     * @return string
     */
    public function getResourcesUrl(): string {
        return $this->resources_url;
    }
    
    /**
     * Initialize all traits
     * 
     * @return void
     */
    private function initializeTraits(): void {
        $this->initializeCustomizer();
        $this->initializeAssets();
        $this->initializeHooks();
        $this->initializeLayouts();
        $this->initializeComponents();
        $this->initializePostTypes();
        $this->initializeTaxonomies();
    }
    
    /**
     * Check if resources directory exists
     * 
     * @return bool
     */
    public function hasResourcesDirectory(): bool {
        return is_dir($this->resources_path);
    }
    
    /**
     * Create resources directory structure if it doesn't exist
     * 
     * @return bool
     */
    public function createResourcesDirectory(): bool {
        if ($this->hasResourcesDirectory()) {
            return true;
        }
        
        $directories = [
            $this->resources_path,
            $this->resources_path . '/assets',
            $this->resources_path . '/assets/css',
            $this->resources_path . '/assets/js',
            $this->resources_path . '/assets/fonts',
            $this->resources_path . '/assets/images',
            $this->resources_path . '/components',
            $this->resources_path . '/layouts',
            $this->resources_path . '/views'
        ];
        
        foreach ($directories as $dir) {
            if (!wp_mkdir_p($dir)) {
                return false;
            }
        }
        
        return true;
    }
}