<?php
/**
 * Handles Assets Trait
 * 
 * Asset management functionality for themes
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Traits;

use WordPressSkin\Assets\AssetManager;
use WordPressSkin\Assets\ModernJsManager;

defined('ABSPATH') or exit;

trait HandlesAssets {
    /**
     * Asset manager instance
     * 
     * @var AssetManager|null
     */
    private $assetManager = null;
    
    /**
     * Modern JS manager instance
     * 
     * @var ModernJsManager|null
     */
    private $modernJsManager = null;
    
    /**
     * Initialize assets functionality
     * 
     * @return void
     */
    protected function initializeAssets(): void {
        $this->assetManager = new AssetManager($this);
        $this->modernJsManager = new ModernJsManager($this);
    }
    
    /**
     * Get asset manager instance
     * 
     * @return AssetManager
     */
    public function getAssetManager(): AssetManager {
        if ($this->assetManager === null) {
            $this->initializeAssets();
        }
        
        return $this->assetManager;
    }
    
    /**
     * Register CSS file
     * 
     * @param string $handle Handle name
     * @param string|null $src Source path (optional, auto-detected)
     * @param array $deps Dependencies
     * @param string|bool|null $ver Version
     * @param string $media Media type
     * @return AssetManager
     */
    public function css($handle, $src = null, $deps = [], $ver = null, $media = 'all') {
        return $this->getAssetManager()->css($handle, $src, $deps, $ver, $media);
    }
    
    /**
     * Register JavaScript file
     * 
     * @param string $handle Handle name
     * @param string|null $src Source path (optional, auto-detected)
     * @param array $deps Dependencies
     * @param string|bool|null $ver Version
     * @param bool $in_footer Load in footer
     * @return AssetManager
     */
    public function js($handle, $src = null, $deps = [], $ver = null, $in_footer = true) {
        return $this->getAssetManager()->js($handle, $src, $deps, $ver, $in_footer);
    }
    
    /**
     * Register font
     * 
     * @param string $family Font family name
     * @param array $variants Font variants
     * @param string $display Font display property
     * @return AssetManager
     */
    public function font($family, $variants = ['400'], $display = 'swap') {
        return $this->getAssetManager()->font($family, $variants, $display);
    }
    
    /**
     * Get modern JS manager instance
     * 
     * @return ModernJsManager
     */
    public function getModernJsManager(): ModernJsManager {
        if ($this->modernJsManager === null) {
            $this->initializeAssets();
        }
        
        return $this->modernJsManager;
    }
    
    /**
     * Configure React
     * 
     * @param string $containerId Container element ID
     * @param array $props Initial props
     * @param array $config Additional configuration
     * @return ModernJsManager
     */
    public function react($containerId = 'react-app', $props = [], $config = []) {
        return $this->getModernJsManager()->react($containerId, $props, $config);
    }
    
    /**
     * Configure Vue
     * 
     * @param string $containerId Container element ID
     * @param array $data Initial data
     * @param array $config Additional configuration
     * @return ModernJsManager
     */
    public function vue($containerId = 'vue-app', $data = [], $config = []) {
        return $this->getModernJsManager()->vue($containerId, $data, $config);
    }
    
    /**
     * Configure Svelte
     * 
     * @param string $containerId Container element ID
     * @param array $props Initial props
     * @param array $config Additional configuration
     * @return ModernJsManager
     */
    public function svelte($containerId = 'svelte-app', $props = [], $config = []) {
        return $this->getModernJsManager()->svelte($containerId, $props, $config);
    }
    
    /**
     * Configure Alpine.js
     * 
     * @param array $data Global Alpine data
     * @param array $config Additional configuration
     * @return ModernJsManager
     */
    public function alpine($data = [], $config = []) {
        return $this->getModernJsManager()->alpine($data, $config);
    }
    
    /**
     * Configure build tool
     * 
     * @param string $tool Build tool name
     * @param array $config Build configuration
     * @return ModernJsManager
     */
    public function buildTool($tool, $config = []) {
        return $this->getModernJsManager()->buildTool($tool, $config);
    }
}