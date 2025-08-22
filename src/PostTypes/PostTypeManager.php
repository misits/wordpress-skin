<?php
/**
 * Custom Post Type Manager
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\PostTypes;

defined('ABSPATH') or exit;

class PostTypeManager {
    /**
     * Registered post types
     * 
     * @var array
     */
    private $post_types = [];
    
    /**
     * Text domain for translations
     * 
     * @var string
     */
    private $text_domain = 'wp-skin';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', [$this, 'registerPostTypes'], 0);
    }
    
    /**
     * Register a custom post type
     * 
     * @param string $post_type Post type key
     * @param array $args Post type arguments
     * @return self
     */
    public function register(string $post_type, array $args = []): self {
        // Default arguments
        $defaults = [
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => ['slug' => $post_type],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'comments']
        ];
        
        // Merge with defaults
        $args = wp_parse_args($args, $defaults);
        
        // Auto-generate labels if not provided
        if (!isset($args['labels'])) {
            $args['labels'] = $this->generateLabels($post_type);
        }
        
        // Store for registration
        $this->post_types[$post_type] = $args;
        
        return $this;
    }
    
    /**
     * Quick method to register a post type with minimal configuration
     * 
     * @param string $post_type Post type key
     * @param string $singular Singular label
     * @param string $plural Plural label
     * @param array $additional_args Additional arguments
     * @param string $text_domain Text domain for translations
     * @return self
     */
    public function add(string $post_type, string $singular, string $plural, array $additional_args = [], ?string $text_domain = null): self {
        $domain = $text_domain ?: $this->text_domain;
        
        $args = [
            'labels' => [
                'name' => __($plural, $domain),
                'singular_name' => __($singular, $domain),
                'menu_name' => __($plural, $domain),
                'add_new' => __('Add New', $domain),
                'add_new_item' => sprintf(__('Add New %s', $domain), $singular),
                'edit_item' => sprintf(__('Edit %s', $domain), $singular),
                'new_item' => sprintf(__('New %s', $domain), $singular),
                'view_item' => sprintf(__('View %s', $domain), $singular),
                'view_items' => sprintf(__('View %s', $domain), $plural),
                'search_items' => sprintf(__('Search %s', $domain), $plural),
                'not_found' => sprintf(__('No %s found', $domain), strtolower($plural)),
                'not_found_in_trash' => sprintf(__('No %s found in Trash', $domain), strtolower($plural))
            ]
        ];
        
        // Merge additional arguments
        $args = array_merge($args, $additional_args);
        
        return $this->register($post_type, $args);
    }
    
    
    /**
     * Register all post types
     * 
     * @return void
     */
    public function registerPostTypes(): void {
        foreach ($this->post_types as $post_type => $args) {
            register_post_type($post_type, $args);
        }
    }
    
    /**
     * Set text domain for translations
     * 
     * @param string $text_domain Text domain
     * @return self
     */
    public function setTextDomain(string $text_domain): self {
        $this->text_domain = $text_domain;
        return $this;
    }
    
    /**
     * Generate default labels for a post type
     * 
     * @param string $post_type Post type key
     * @param string $text_domain Text domain for translations
     * @return array
     */
    private function generateLabels(string $post_type, ?string $text_domain = null): array {
        $domain = $text_domain ?: $this->text_domain;
        
        // Convert post type to proper case
        $singular = ucwords(str_replace(['_', '-'], ' ', $post_type));
        $plural = $singular . 's';
        
        return [
            'name' => __($plural, $domain),
            'singular_name' => __($singular, $domain),
            'menu_name' => __($plural, $domain),
            'add_new' => __('Add New', $domain),
            'add_new_item' => sprintf(__('Add New %s', $domain), $singular),
            'edit_item' => sprintf(__('Edit %s', $domain), $singular),
            'new_item' => sprintf(__('New %s', $domain), $singular),
            'view_item' => sprintf(__('View %s', $domain), $singular),
            'view_items' => sprintf(__('View %s', $domain), $plural),
            'search_items' => sprintf(__('Search %s', $domain), $plural),
            'not_found' => sprintf(__('No %s found', $domain), strtolower($plural)),
            'not_found_in_trash' => sprintf(__('No %s found in Trash', $domain), strtolower($plural)),
            'parent_item_colon' => sprintf(__('Parent %s:', $domain), $singular),
            'all_items' => sprintf(__('All %s', $domain), $plural),
            'archives' => sprintf(__('%s Archives', $domain), $singular),
            'attributes' => sprintf(__('%s Attributes', $domain), $singular),
            'insert_into_item' => sprintf(__('Insert into %s', $domain), $singular),
            'uploaded_to_this_item' => sprintf(__('Uploaded to this %s', $domain), $singular),
            'featured_image' => __('Featured Image', $domain),
            'set_featured_image' => __('Set featured image', $domain),
            'remove_featured_image' => __('Remove featured image', $domain),
            'use_featured_image' => __('Use as featured image', $domain),
            'filter_items_list' => sprintf(__('Filter %s list', $domain), $plural),
            'items_list_navigation' => sprintf(__('%s list navigation', $domain), $plural),
            'items_list' => sprintf(__('%s list', $domain), $plural)
        ];
    }
    
    /**
     * Get all registered post types
     * 
     * @return array
     */
    public function getRegistered(): array {
        return $this->post_types;
    }
}