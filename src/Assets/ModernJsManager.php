<?php
/**
 * Modern JS Manager
 * 
 * Handles modern JavaScript frameworks integration
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Assets;

use WordPressSkin\Core\Skin;

defined('ABSPATH') or exit;

class ModernJsManager {
    /**
     * Skin instance
     * 
     * @var Skin
     */
    private $skin;
    
    /**
     * Framework configurations
     * 
     * @var array
     */
    private $frameworks = [];
    
    /**
     * Build tool configuration
     * 
     * @var array
     */
    private $buildConfig = [];
    
    /**
     * Constructor
     * 
     * @param Skin $skin
     */
    public function __construct(Skin $skin) {
        $this->skin = $skin;
        
        // Hook into WordPress
        add_action('wp_enqueue_scripts', [$this, 'enqueueModernAssets'], 15);
        add_action('wp_head', [$this, 'outputFrameworkConfig'], 5);
    }
    
    /**
     * Configure React
     * 
     * @param string $containerId Container element ID
     * @param array $props Initial props to pass to React app
     * @param array $config Additional configuration
     * @return self
     */
    public function react($containerId = 'react-app', $props = [], $config = []) {
        $this->frameworks['react'] = [
            'container_id' => $containerId,
            'props' => $props,
            'config' => array_merge([
                'entry' => 'app',
                'dev_server' => 'http://localhost:5173',
                'production_build' => true
            ], $config)
        ];
        
        return $this;
    }
    
    /**
     * Configure Vue
     * 
     * @param string $containerId Container element ID
     * @param array $data Initial data to pass to Vue app
     * @param array $config Additional configuration
     * @return self
     */
    public function vue($containerId = 'vue-app', $data = [], $config = []) {
        $this->frameworks['vue'] = [
            'container_id' => $containerId,
            'data' => $data,
            'config' => array_merge([
                'entry' => 'app',
                'dev_server' => 'http://localhost:5173',
                'production_build' => true
            ], $config)
        ];
        
        return $this;
    }
    
    /**
     * Configure Svelte
     * 
     * @param string $containerId Container element ID
     * @param array $props Initial props to pass to Svelte app
     * @param array $config Additional configuration
     * @return self
     */
    public function svelte($containerId = 'svelte-app', $props = [], $config = []) {
        $this->frameworks['svelte'] = [
            'container_id' => $containerId,
            'props' => $props,
            'config' => array_merge([
                'entry' => 'app',
                'dev_server' => 'http://localhost:5173',
                'production_build' => true
            ], $config)
        ];
        
        return $this;
    }
    
    /**
     * Configure Alpine.js
     * 
     * @param array $data Global Alpine data
     * @param array $config Additional configuration
     * @return self
     */
    public function alpine($data = [], $config = []) {
        $this->frameworks['alpine'] = [
            'data' => $data,
            'config' => array_merge([
                'cdn' => true,
                'version' => '3.x.x'
            ], $config)
        ];
        
        return $this;
    }
    
    /**
     * Configure build tool (Vite, Webpack, etc.)
     * 
     * @param string $tool Build tool name
     * @param array $config Build configuration
     * @return self
     */
    public function buildTool($tool, $config = []) {
        $this->buildConfig = array_merge([
            'tool' => $tool,
            'manifest_path' => 'resources/dist/manifest.json',
            'dev_server' => 'http://localhost:5173',
            'hot_reload' => WP_DEBUG,
            'output_dir' => 'resources/dist'
        ], $config);
        
        return $this;
    }
    
    /**
     * Enqueue modern framework assets
     * 
     * @return void
     */
    public function enqueueModernAssets() {
        foreach ($this->frameworks as $framework => $config) {
            $this->enqueueFramework($framework, $config);
        }
    }
    
    /**
     * Output framework configuration in head
     * 
     * @return void
     */
    public function outputFrameworkConfig() {
        if (empty($this->frameworks)) {
            return;
        }
        
        echo '<script type="text/javascript">' . "\n";
        echo 'window.WPSkinFrameworks = ' . wp_json_encode($this->frameworks) . ';' . "\n";
        echo '</script>' . "\n";
    }
    
    /**
     * Enqueue specific framework
     * 
     * @param string $framework Framework name
     * @param array $config Framework configuration
     * @return void
     */
    private function enqueueFramework($framework, $config) {
        switch ($framework) {
            case 'react':
                $this->enqueueReact($config);
                break;
                
            case 'vue':
                $this->enqueueVue($config);
                break;
                
            case 'svelte':
                $this->enqueueSvelte($config);
                break;
                
            case 'alpine':
                $this->enqueueAlpine($config);
                break;
        }
    }
    
    /**
     * Enqueue React assets
     * 
     * @param array $config React configuration
     * @return void
     */
    private function enqueueReact($config) {
        if ($this->isDevelopment() && $this->isDevServerRunning()) {
            // Development mode with hot reload
            wp_enqueue_script(
                'vite-client',
                $this->buildConfig['dev_server'] . '/@vite/client',
                [],
                null,
                false
            );
            
            wp_enqueue_script(
                'react-app',
                $this->buildConfig['dev_server'] . '/resources/src/js/' . $config['config']['entry'] . '.jsx',
                [],
                null,
                true
            );
        } else {
            // Production mode
            $assets = $this->getProductionAssets($config['config']['entry']);
            
            if ($assets) {
                if (isset($assets['css'])) {
                    wp_enqueue_style('react-app', $assets['css'], [], $this->getAssetVersion());
                }
                
                if (isset($assets['js'])) {
                    wp_enqueue_script('react-app', $assets['js'], [], $this->getAssetVersion(), true);
                }
            }
        }
    }
    
    /**
     * Enqueue Vue assets
     * 
     * @param array $config Vue configuration
     * @return void
     */
    private function enqueueVue($config) {
        if ($this->isDevelopment() && $this->isDevServerRunning()) {
            // Development mode with hot reload
            wp_enqueue_script(
                'vite-client',
                $this->buildConfig['dev_server'] . '/@vite/client',
                [],
                null,
                false
            );
            
            wp_enqueue_script(
                'vue-app',
                $this->buildConfig['dev_server'] . '/resources/src/js/' . $config['config']['entry'] . '.js',
                [],
                null,
                true
            );
        } else {
            // Production mode
            $assets = $this->getProductionAssets($config['config']['entry']);
            
            if ($assets) {
                if (isset($assets['css'])) {
                    wp_enqueue_style('vue-app', $assets['css'], [], $this->getAssetVersion());
                }
                
                if (isset($assets['js'])) {
                    wp_enqueue_script('vue-app', $assets['js'], [], $this->getAssetVersion(), true);
                }
            }
        }
    }
    
    /**
     * Enqueue Svelte assets
     * 
     * @param array $config Svelte configuration
     * @return void
     */
    private function enqueueSvelte($config) {
        // Similar to React/Vue but for Svelte
        if ($this->isDevelopment() && $this->isDevServerRunning()) {
            wp_enqueue_script(
                'svelte-app',
                $this->buildConfig['dev_server'] . '/resources/src/js/' . $config['config']['entry'] . '.js',
                [],
                null,
                true
            );
        } else {
            $assets = $this->getProductionAssets($config['config']['entry']);
            
            if ($assets) {
                if (isset($assets['css'])) {
                    wp_enqueue_style('svelte-app', $assets['css'], [], $this->getAssetVersion());
                }
                
                if (isset($assets['js'])) {
                    wp_enqueue_script('svelte-app', $assets['js'], [], $this->getAssetVersion(), true);
                }
            }
        }
    }
    
    /**
     * Enqueue Alpine.js
     * 
     * @param array $config Alpine configuration
     * @return void
     */
    private function enqueueAlpine($config) {
        if ($config['config']['cdn']) {
            wp_enqueue_script(
                'alpinejs',
                'https://cdn.jsdelivr.net/npm/alpinejs@' . $config['config']['version'] . '/dist/cdn.min.js',
                [],
                null,
                true
            );
        }
    }
    
    /**
     * Get production assets from manifest
     * 
     * @param string $entry Entry point name
     * @return array|null Asset URLs
     */
    private function getProductionAssets($entry) {
        if (empty($this->buildConfig['manifest_path'])) {
            return null;
        }
        
        $manifestPath = $this->skin->getThemeRoot() . '/' . $this->buildConfig['manifest_path'];
        
        if (!file_exists($manifestPath)) {
            return null;
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        if (!$manifest) {
            return null;
        }
        
        $entryKey = "resources/src/js/{$entry}.js";
        if (!isset($manifest[$entryKey])) {
            $entryKey = "resources/src/js/{$entry}.jsx";
        }
        
        if (!isset($manifest[$entryKey])) {
            return null;
        }
        
        $assets = [];
        $baseUrl = get_template_directory_uri() . '/' . $this->buildConfig['output_dir'] . '/';
        
        if (isset($manifest[$entryKey]['file'])) {
            $assets['js'] = $baseUrl . $manifest[$entryKey]['file'];
        }
        
        if (isset($manifest[$entryKey]['css']) && !empty($manifest[$entryKey]['css'])) {
            $assets['css'] = $baseUrl . $manifest[$entryKey]['css'][0];
        }
        
        return $assets;
    }
    
    /**
     * Check if in development mode
     * 
     * @return bool
     */
    private function isDevelopment() {
        return WP_DEBUG && !empty($this->buildConfig['hot_reload']);
    }
    
    /**
     * Check if dev server is running
     * 
     * @return bool
     */
    private function isDevServerRunning() {
        if (empty($this->buildConfig['dev_server'])) {
            return false;
        }
        
        // Simple check - could be enhanced
        $response = wp_remote_get($this->buildConfig['dev_server'], [
            'timeout' => 1,
            'sslverify' => false
        ]);
        
        return !is_wp_error($response);
    }
    
    /**
     * Get asset version
     * 
     * @return string
     */
    private function getAssetVersion() {
        return WPSKIN_VERSION;
    }
}