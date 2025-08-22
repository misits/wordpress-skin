<?php
/**
 * WP-Skin Helper Functions
 * 
 * Global helper functions for theme development
 * 
 * @package WP-Skin
 */

defined('ABSPATH') or exit;

if (!function_exists('skin')) {
    /**
     * Get the main Skin instance
     * 
     * @return \WordPressSkin\Core\Skin
     */
    function skin() {
        return \WordPressSkin\Core\Skin::getInstance();
    }
}

if (!function_exists('skin_asset')) {
    /**
     * Get asset URL with versioning
     * 
     * @param string $path Asset path relative to resources/assets/
     * @return string Asset URL
     */
    function skin_asset($path) {
        return skin()->assets()->url($path);
    }
}

if (!function_exists('skin_component')) {
    /**
     * Render a theme component
     * 
     * @param string $name Component name
     * @param array $data Data to pass to component
     * @return void
     */
    function skin_component($name, $data = []) {
        skin()->components()->render($name, $data);
    }
}

if (!function_exists('skin_view')) {
    /**
     * Render a view template
     * 
     * @param string $name View name
     * @param array $data Data to pass to view
     * @return void
     */
    function skin_view($name, $data = []) {
        skin()->layouts()->view($name, $data);
    }
}

if (!function_exists('skin_customizer_get')) {
    /**
     * Get customizer setting value
     * 
     * @param string $setting Setting name
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    function skin_customizer_get($setting, $default = null) {
        return get_theme_mod($setting, $default);
    }
}

if (!function_exists('skin_react')) {
    /**
     * Configure React application
     * 
     * @param string $containerId Container element ID
     * @param array $props Initial props to pass to React app
     * @param array $config Additional configuration
     * @return \WordPressSkin\Assets\ModernJsManager
     */
    function skin_react($containerId = 'react-app', $props = [], $config = []) {
        return skin()->react($containerId, $props, $config);
    }
}

if (!function_exists('skin_vue')) {
    /**
     * Configure Vue application
     * 
     * @param string $containerId Container element ID
     * @param array $data Initial data to pass to Vue app
     * @param array $config Additional configuration
     * @return \WordPressSkin\Assets\ModernJsManager
     */
    function skin_vue($containerId = 'vue-app', $data = [], $config = []) {
        return skin()->vue($containerId, $data, $config);
    }
}

if (!function_exists('skin_svelte')) {
    /**
     * Configure Svelte application
     * 
     * @param string $containerId Container element ID
     * @param array $props Initial props to pass to Svelte app
     * @param array $config Additional configuration
     * @return \WordPressSkin\Assets\ModernJsManager
     */
    function skin_svelte($containerId = 'svelte-app', $props = [], $config = []) {
        return skin()->svelte($containerId, $props, $config);
    }
}

if (!function_exists('skin_alpine')) {
    /**
     * Configure Alpine.js
     * 
     * @param array $data Global Alpine data
     * @param array $config Additional configuration
     * @return \WordPressSkin\Assets\ModernJsManager
     */
    function skin_alpine($data = [], $config = []) {
        return skin()->alpine($data, $config);
    }
}

if (!function_exists('skin_vite')) {
    /**
     * Configure Vite build tool
     * 
     * @param array $config Vite configuration
     * @return \WordPressSkin\Assets\ModernJsManager
     */
    function skin_vite($config = []) {
        return skin()->buildTool('vite', $config);
    }
}

if (!function_exists('skin_webpack')) {
    /**
     * Configure Webpack build tool
     * 
     * @param array $config Webpack configuration
     * @return \WordPressSkin\Assets\ModernJsManager
     */
    function skin_webpack($config = []) {
        return skin()->buildTool('webpack', $config);
    }
}