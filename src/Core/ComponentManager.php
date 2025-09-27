<?php
/**
 * Component Manager
 * 
 * Handles theme components and reusable template parts
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Core;

use WordPressSkin\Core\Skin;

defined('ABSPATH') or exit;

class ComponentManager {
    /**
     * Skin instance
     * 
     * @var Skin
     */
    private $skin;
    
    /**
     * Registered components
     * 
     * @var array
     */
    private $components = [];
    
    /**
     * Component data
     * 
     * @var array
     */
    private $componentData = [];
    
    /**
     * Constructor
     * 
     * @param Skin $skin
     */
    public function __construct(Skin $skin) {
        $this->skin = $skin;
        
        // Auto-register components from resources/components/
        $this->autoRegisterComponents();
    }
    
    /**
     * Register a component
     * 
     * @param string $name Component name
     * @param string|callable $template Template path or render callback
     * @param array $defaultData Default data for component
     * @return self
     */
    public function register($name, $template, $defaultData = []) {
        $this->components[$name] = [
            'template' => $template,
            'default_data' => $defaultData,
            'cached' => false
        ];
        
        return $this;
    }
    
    /**
     * Render a component
     * 
     * @param string $name Component name
     * @param array $data Data to pass to component
     * @param bool $return Whether to return output instead of echoing
     * @return string|void Component output or void
     */
    public function render($name, $data = [], $return = false) {
        if (!isset($this->components[$name])) {
            if (WP_DEBUG) {
                $message = "Component '{$name}' not found.";
                echo $return ? "<!-- {$message} -->" : $message;
            }
            return $return ? '' : null;
        }
        
        $component = $this->components[$name];
        $mergedData = array_merge($component['default_data'], $data);
        
        if ($return) {
            ob_start();
            $this->renderComponent($component, $mergedData);
            return ob_get_clean();
        } else {
            $this->renderComponent($component, $mergedData);
        }
    }
    
    /**
     * Get component output as string
     * 
     * @param string $name Component name
     * @param array $data Data to pass to component
     * @return string Component output
     */
    public function get($name, $data = []) {
        return $this->render($name, $data, true);
    }
    
    /**
     * Check if component exists
     * 
     * @param string $name Component name
     * @return bool True if component exists
     */
    public function has($name) {
        return isset($this->components[$name]);
    }
    
    /**
     * Get all registered components
     * 
     * @return array All components
     */
    public function all() {
        return $this->components;
    }
    
    /**
     * Set global component data
     * 
     * @param string|array $key Data key or array of data
     * @param mixed $value Data value (if key is string)
     * @return self
     */
    public function setData($key, $value = null) {
        if (is_array($key)) {
            $this->componentData = array_merge($this->componentData, $key);
        } else {
            $this->componentData[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * Get global component data
     * 
     * @param string|null $key Specific data key or null for all data
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function getData($key = null, $default = null) {
        if ($key === null) {
            return $this->componentData;
        }
        
        return $this->componentData[$key] ?? $default;
    }
    
    /**
     * Create a component alias
     * 
     * @param string $alias Alias name
     * @param string $original Original component name
     * @return self
     */
    public function alias($alias, $original) {
        if (isset($this->components[$original])) {
            $this->components[$alias] = $this->components[$original];
        }
        
        return $this;
    }
    
    /**
     * Remove a component
     * 
     * @param string $name Component name
     * @return self
     */
    public function remove($name) {
        unset($this->components[$name]);
        return $this;
    }
    
    /**
     * Auto-register components from resources/components/ directory
     *
     * @return void
     */
    private function autoRegisterComponents() {
        $componentsDir = $this->skin->getResourcesPath() . '/components';

        if (!is_dir($componentsDir)) {
            return;
        }

        // Register components from root directory
        $files = glob($componentsDir . '/*.php');
        foreach ($files as $file) {
            $name = basename($file, '.php');
            $this->register($name, $file);
        }

        // Register components from subdirectories (supports nested folders)
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($componentsDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Get relative path from components directory
                $relativePath = str_replace($componentsDir . '/', '', $file->getPathname());
                // Remove .php extension and use forward slashes
                $componentName = str_replace('.php', '', $relativePath);
                // Register if not already registered (root files already registered above)
                if (!$this->has($componentName)) {
                    $this->register($componentName, $file->getPathname());
                }
            }
        }
    }
    
    /**
     * Render component implementation
     * 
     * @param array $component Component configuration
     * @param array $data Component data
     * @return void
     */
    private function renderComponent($component, $data) {
        // Merge with global component data
        $data = array_merge($this->componentData, $data);
        
        if (is_callable($component['template'])) {
            // Render using callback
            call_user_func($component['template'], $data);
        } elseif (is_string($component['template'])) {
            // Render using template file
            $templatePath = $this->resolveTemplatePath($component['template']);
            
            if ($templatePath && file_exists($templatePath)) {
                // Extract data to current scope
                if (!empty($data)) {
                    extract($data, EXTR_SKIP);
                }
                
                // Make component data available globally
                $GLOBALS['component_data'] = $data;
                
                include $templatePath;
                
                // Clean up global data
                unset($GLOBALS['component_data']);
            } elseif (WP_DEBUG) {
                echo "<!-- Component template not found: {$component['template']} -->";
            }
        }
    }
    
    /**
     * Resolve template path
     *
     * @param string $template Template path
     * @return string|null Resolved path or null if not found
     */
    private function resolveTemplatePath($template) {
        // If absolute path, return as is
        if (file_exists($template)) {
            return $template;
        }

        // Add .php extension if not present
        if (!str_ends_with($template, '.php')) {
            $template .= '.php';
        }

        // Support both forward and backward slashes
        $template = str_replace('\\', '/', $template);

        $possiblePaths = [
            $this->skin->getResourcesPath() . '/components/' . $template,
            $this->skin->getThemeRoot() . '/components/' . $template,
            $this->skin->getThemeRoot() . '/' . $template
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}