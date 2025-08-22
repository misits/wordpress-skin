<?php
/**
 * Layout Manager
 * 
 * Handles template hierarchy, views, and layout management
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Core;

use WordPressSkin\Core\Skin;

defined('ABSPATH') or exit;

class LayoutManager {
    /**
     * Skin instance
     * 
     * @var Skin
     */
    private $skin;
    
    /**
     * Custom layouts
     * 
     * @var array
     */
    private $layouts = [];
    
    /**
     * View data
     * 
     * @var array
     */
    private $viewData = [];
    
    /**
     * Constructor
     * 
     * @param Skin $skin
     */
    public function __construct(Skin $skin) {
        $this->skin = $skin;
        
        // Hook into template hierarchy
        add_filter('template_include', [$this, 'templateInclude']);
        add_filter('body_class', [$this, 'addBodyClasses']);
    }
    
    /**
     * Register a custom layout
     * 
     * @param string $name Layout name
     * @param string|callable $template Template path or callback
     * @param array $conditions Conditions for when to use this layout
     * @return self
     */
    public function layout($name, $template = null, $conditions = []) {
        $this->layouts[$name] = [
            'template' => $template,
            'conditions' => $conditions,
            'data' => []
        ];
        
        return $this;
    }
    
    /**
     * Render a view with data
     * 
     * @param string $view View name (relative to resources/views/)
     * @param array $data Data to pass to view
     * @return void
     */
    public function view($view, $data = []) {
        $this->render($view, $data, 'views');
    }
    
    /**
     * Include a template part
     * 
     * @param string $part Template part name (relative to resources/layouts/)
     * @param array $data Data to pass to template part
     * @return void
     */
    public function part($part, $data = []) {
        $this->render($part, $data, 'layouts');
    }
    
    /**
     * Render a template file with data
     * 
     * @param string $template Template name
     * @param array $data Data to pass to template
     * @param string $subdirectory Subdirectory within resources/
     * @return void
     */
    public function render($template, $data = [], $subdirectory = 'views') {
        // Make data available globally
        $this->viewData = array_merge($this->viewData, $data);
        
        // Extract data to current scope
        if (!empty($data)) {
            extract($data, EXTR_SKIP);
        }
        
        // Find template file
        $templatePath = $this->findTemplate($template, $subdirectory);
        
        if ($templatePath && file_exists($templatePath)) {
            include $templatePath;
        } else {
            // Template not found, show debug info in development
            if (WP_DEBUG) {
                echo "<!-- Template not found: {$template} in {$subdirectory} -->";
            }
        }
    }
    
    /**
     * Get view data
     * 
     * @param string|null $key Specific data key or null for all data
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function getData($key = null, $default = null) {
        if ($key === null) {
            return $this->viewData;
        }
        
        return $this->viewData[$key] ?? $default;
    }
    
    /**
     * Set view data
     * 
     * @param string|array $key Data key or array of data
     * @param mixed $value Data value (if key is string)
     * @return self
     */
    public function setData($key, $value = null) {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * Handle template include filter
     * 
     * @param string $template Current template
     * @return string Modified template
     */
    public function templateInclude($template) {
        // Check if any custom layouts match current conditions
        foreach ($this->layouts as $name => $layout) {
            if ($this->layoutMatches($layout['conditions'])) {
                $customTemplate = $this->resolveTemplate($layout['template']);
                if ($customTemplate && file_exists($customTemplate)) {
                    return $customTemplate;
                }
            }
        }
        
        return $template;
    }
    
    /**
     * Add custom body classes
     * 
     * @param array $classes Current body classes
     * @return array Modified body classes
     */
    public function addBodyClasses($classes) {
        // Add theme-specific classes
        $classes[] = 'wp-skin-theme';
        
        // Add layout-specific classes
        foreach ($this->layouts as $name => $layout) {
            if ($this->layoutMatches($layout['conditions'])) {
                $classes[] = 'layout-' . sanitize_html_class($name);
            }
        }
        
        return $classes;
    }
    
    /**
     * Find template file
     * 
     * @param string $template Template name
     * @param string $subdirectory Subdirectory within resources/
     * @return string|null Template path or null if not found
     */
    private function findTemplate($template, $subdirectory = 'views') {
        // Add .php extension if not present
        if (!str_ends_with($template, '.php')) {
            $template .= '.php';
        }
        
        $possiblePaths = [
            $this->skin->getResourcesPath() . "/{$subdirectory}/{$template}",
            $this->skin->getThemeRoot() . "/{$subdirectory}/{$template}",
            $this->skin->getThemeRoot() . "/{$template}"
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Resolve template path or callback
     * 
     * @param string|callable $template Template path or callback
     * @return string|null Resolved template path
     */
    private function resolveTemplate($template) {
        if (is_callable($template)) {
            return call_user_func($template);
        }
        
        if (is_string($template)) {
            // If absolute path, return as is
            if (file_exists($template)) {
                return $template;
            }
            
            // Otherwise, look in theme directories
            return $this->findTemplate($template, 'layouts');
        }
        
        return null;
    }
    
    /**
     * Check if layout conditions match
     * 
     * @param array $conditions Layout conditions
     * @return bool True if conditions match
     */
    private function layoutMatches($conditions) {
        if (empty($conditions)) {
            return false;
        }
        
        foreach ($conditions as $condition => $value) {
            switch ($condition) {
                case 'is_front_page':
                    if ($value && !is_front_page()) return false;
                    if (!$value && is_front_page()) return false;
                    break;
                    
                case 'is_home':
                    if ($value && !is_home()) return false;
                    if (!$value && is_home()) return false;
                    break;
                    
                case 'is_page':
                    if ($value && !is_page($value === true ? null : $value)) return false;
                    break;
                    
                case 'is_single':
                    if ($value && !is_single($value === true ? null : $value)) return false;
                    break;
                    
                case 'post_type':
                    if (!is_singular($value)) return false;
                    break;
                    
                case 'template':
                    if (!is_page_template($value)) return false;
                    break;
                    
                case 'callback':
                    if (is_callable($value) && !call_user_func($value)) return false;
                    break;
            }
        }
        
        return true;
    }
}