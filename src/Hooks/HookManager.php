<?php
/**
 * Hook Manager
 * 
 * Handles WordPress actions and filters with fluent interface
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Hooks;

use WordPressSkin\Core\Skin;

defined('ABSPATH') or exit;

class HookManager {
    /**
     * Skin instance
     * 
     * @var Skin
     */
    private $skin;
    
    /**
     * Registered hooks
     * 
     * @var array
     */
    private $hooks = [];
    
    /**
     * Constructor
     * 
     * @param Skin $skin
     */
    public function __construct(Skin $skin) {
        $this->skin = $skin;
    }
    
    /**
     * Add an action hook
     * 
     * @param string $hook Hook name
     * @param callable|string $callback Callback function or method
     * @param int $priority Priority
     * @param int $acceptedArgs Number of accepted arguments
     * @return self
     */
    public function action($hook, $callback, $priority = 10, $acceptedArgs = 1) {
        $this->hooks[] = [
            'type' => 'action',
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $acceptedArgs
        ];
        
        add_action($hook, $callback, $priority, $acceptedArgs);
        
        return $this;
    }
    
    /**
     * Add a filter hook
     * 
     * @param string $hook Hook name
     * @param callable|string $callback Callback function or method
     * @param int $priority Priority
     * @param int $acceptedArgs Number of accepted arguments
     * @return self
     */
    public function filter($hook, $callback, $priority = 10, $acceptedArgs = 1) {
        $this->hooks[] = [
            'type' => 'filter',
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $acceptedArgs
        ];
        
        add_filter($hook, $callback, $priority, $acceptedArgs);
        
        return $this;
    }
    
    /**
     * Remove an action hook
     * 
     * @param string $hook Hook name
     * @param callable|string $callback Callback function or method
     * @param int $priority Priority
     * @return self
     */
    public function removeAction($hook, $callback, $priority = 10) {
        remove_action($hook, $callback, $priority);
        return $this;
    }
    
    /**
     * Remove a filter hook
     * 
     * @param string $hook Hook name
     * @param callable|string $callback Callback function or method
     * @param int $priority Priority
     * @return self
     */
    public function removeFilter($hook, $callback, $priority = 10) {
        remove_filter($hook, $callback, $priority);
        return $this;
    }
    
    /**
     * Add multiple actions at once
     * 
     * @param array $actions Array of actions
     * @return self
     */
    public function actions($actions) {
        foreach ($actions as $hook => $config) {
            if (is_callable($config)) {
                $this->action($hook, $config);
            } elseif (is_array($config)) {
                $callback = $config['callback'] ?? $config[0] ?? null;
                $priority = $config['priority'] ?? $config[1] ?? 10;
                $acceptedArgs = $config['accepted_args'] ?? $config[2] ?? 1;
                
                if ($callback) {
                    $this->action($hook, $callback, $priority, $acceptedArgs);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Add multiple filters at once
     * 
     * @param array $filters Array of filters
     * @return self
     */
    public function filters($filters) {
        foreach ($filters as $hook => $config) {
            if (is_callable($config)) {
                $this->filter($hook, $config);
            } elseif (is_array($config)) {
                $callback = $config['callback'] ?? $config[0] ?? null;
                $priority = $config['priority'] ?? $config[1] ?? 10;
                $acceptedArgs = $config['accepted_args'] ?? $config[2] ?? 1;
                
                if ($callback) {
                    $this->filter($hook, $callback, $priority, $acceptedArgs);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Add conditional action
     * 
     * @param string $hook Hook name
     * @param callable|string $callback Callback function or method
     * @param callable|bool $condition Condition to check
     * @param int $priority Priority
     * @param int $acceptedArgs Number of accepted arguments
     * @return self
     */
    public function actionIf($hook, $callback, $condition, $priority = 10, $acceptedArgs = 1) {
        $conditionalCallback = function(...$args) use ($callback, $condition) {
            $shouldRun = is_callable($condition) ? call_user_func($condition) : $condition;
            
            if ($shouldRun) {
                return call_user_func_array($callback, $args);
            }
        };
        
        return $this->action($hook, $conditionalCallback, $priority, $acceptedArgs);
    }
    
    /**
     * Add conditional filter
     * 
     * @param string $hook Hook name
     * @param callable|string $callback Callback function or method
     * @param callable|bool $condition Condition to check
     * @param int $priority Priority
     * @param int $acceptedArgs Number of accepted arguments
     * @return self
     */
    public function filterIf($hook, $callback, $condition, $priority = 10, $acceptedArgs = 1) {
        $conditionalCallback = function($value, ...$args) use ($callback, $condition) {
            $shouldRun = is_callable($condition) ? call_user_func($condition) : $condition;
            
            if ($shouldRun) {
                return call_user_func_array($callback, array_merge([$value], $args));
            }
            
            return $value;
        };
        
        return $this->filter($hook, $conditionalCallback, $priority, $acceptedArgs);
    }
    
    /**
     * Get all registered hooks
     * 
     * @return array
     */
    public function getHooks() {
        return $this->hooks;
    }
}