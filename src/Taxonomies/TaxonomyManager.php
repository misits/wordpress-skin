<?php
/**
 * Taxonomy Manager
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Taxonomies;

defined('ABSPATH') or exit;

class TaxonomyManager {
    /**
     * Registered taxonomies
     * 
     * @var array
     */
    private $taxonomies = [];
    
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
        add_action('init', [$this, 'registerTaxonomies'], 0);
    }
    
    /**
     * Register a custom taxonomy
     * 
     * @param string $taxonomy Taxonomy key
     * @param array|string $object_type Post type(s) to attach to
     * @param array $args Taxonomy arguments
     * @return self
     */
    public function register(string $taxonomy, $object_type, array $args = []): self {
        // Default arguments
        $defaults = [
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_tagcloud' => true,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'hierarchical' => true,
            'query_var' => true,
            'rewrite' => ['slug' => $taxonomy]
        ];
        
        // Merge with defaults
        $args = wp_parse_args($args, $defaults);
        
        // Auto-generate labels if not provided
        if (!isset($args['labels'])) {
            $args['labels'] = $this->generateLabels($taxonomy);
        }
        
        // Store for registration
        $this->taxonomies[$taxonomy] = [
            'object_type' => (array) $object_type,
            'args' => $args
        ];
        
        return $this;
    }
    
    /**
     * Quick method to register a taxonomy with minimal configuration
     * 
     * @param string $taxonomy Taxonomy key
     * @param array|string $object_type Post type(s) to attach to
     * @param string $singular Singular label
     * @param string $plural Plural label
     * @param array $additional_args Additional arguments
     * @param string $text_domain Text domain for translations
     * @return self
     */
    public function add(string $taxonomy, $object_type, string $singular, string $plural, array $additional_args = [], ?string $text_domain = null): self {
        $domain = $text_domain ?: $this->text_domain;
        
        $args = [
            'labels' => [
                'name' => __($plural, $domain),
                'singular_name' => __($singular, $domain),
                'menu_name' => __($plural, $domain),
                'all_items' => sprintf(__('All %s', $domain), $plural),
                'edit_item' => sprintf(__('Edit %s', $domain), $singular),
                'view_item' => sprintf(__('View %s', $domain), $singular),
                'update_item' => sprintf(__('Update %s', $domain), $singular),
                'add_new_item' => sprintf(__('Add New %s', $domain), $singular),
                'new_item_name' => sprintf(__('New %s Name', $domain), $singular),
                'search_items' => sprintf(__('Search %s', $domain), $plural),
                'popular_items' => sprintf(__('Popular %s', $domain), $plural),
                'separate_items_with_commas' => sprintf(__('Separate %s with commas', $domain), strtolower($plural)),
                'add_or_remove_items' => sprintf(__('Add or remove %s', $domain), strtolower($plural)),
                'choose_from_most_used' => sprintf(__('Choose from the most used %s', $domain), strtolower($plural)),
                'not_found' => sprintf(__('No %s found', $domain), strtolower($plural))
            ]
        ];
        
        // Merge additional arguments
        $args = array_merge($args, $additional_args);
        
        return $this->register($taxonomy, $object_type, $args);
    }
    
    /**
     * Register categories for custom post types
     * 
     * @param array|string $post_types Post type(s) to attach to
     * @param array $args Additional arguments
     * @return self
     */
    public function categories($post_types, array $args = []): self {
        $post_types = (array) $post_types;
        $taxonomy_name = count($post_types) === 1 ? $post_types[0] . '_category' : 'categories';
        
        $defaults = [
            'hierarchical' => true,
            'rewrite' => ['slug' => $taxonomy_name]
        ];
        
        return $this->add($taxonomy_name, $post_types, 'Category', 'Categories', array_merge($defaults, $args));
    }
    
    /**
     * Register tags for custom post types
     * 
     * @param array|string $post_types Post type(s) to attach to
     * @param array $args Additional arguments
     * @return self
     */
    public function tags($post_types, array $args = []): self {
        $post_types = (array) $post_types;
        $taxonomy_name = count($post_types) === 1 ? $post_types[0] . '_tag' : 'tags';
        
        $defaults = [
            'hierarchical' => false,
            'rewrite' => ['slug' => $taxonomy_name]
        ];
        
        return $this->add($taxonomy_name, $post_types, 'Tag', 'Tags', array_merge($defaults, $args));
    }
    
    
    /**
     * Register all taxonomies
     * 
     * @return void
     */
    public function registerTaxonomies(): void {
        foreach ($this->taxonomies as $taxonomy => $config) {
            register_taxonomy($taxonomy, $config['object_type'], $config['args']);
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
     * Generate default labels for a taxonomy
     * 
     * @param string $taxonomy Taxonomy key
     * @param string $text_domain Text domain for translations
     * @return array
     */
    private function generateLabels(string $taxonomy, ?string $text_domain = null): array {
        $domain = $text_domain ?: $this->text_domain;
        
        // Convert taxonomy to proper case
        $singular = ucwords(str_replace(['_', '-'], ' ', $taxonomy));
        $plural = $singular . 's';
        
        return [
            'name' => __($plural, $domain),
            'singular_name' => __($singular, $domain),
            'menu_name' => __($plural, $domain),
            'all_items' => sprintf(__('All %s', $domain), $plural),
            'edit_item' => sprintf(__('Edit %s', $domain), $singular),
            'view_item' => sprintf(__('View %s', $domain), $singular),
            'update_item' => sprintf(__('Update %s', $domain), $singular),
            'add_new_item' => sprintf(__('Add New %s', $domain), $singular),
            'new_item_name' => sprintf(__('New %s Name', $domain), $singular),
            'parent_item' => sprintf(__('Parent %s', $domain), $singular),
            'parent_item_colon' => sprintf(__('Parent %s:', $domain), $singular),
            'search_items' => sprintf(__('Search %s', $domain), $plural),
            'popular_items' => sprintf(__('Popular %s', $domain), $plural),
            'separate_items_with_commas' => sprintf(__('Separate %s with commas', $domain), strtolower($plural)),
            'add_or_remove_items' => sprintf(__('Add or remove %s', $domain), strtolower($plural)),
            'choose_from_most_used' => sprintf(__('Choose from the most used %s', $domain), strtolower($plural)),
            'not_found' => sprintf(__('No %s found', $domain), strtolower($plural)),
            'back_to_items' => sprintf(__('Back to %s', $domain), $plural)
        ];
    }
    
    /**
     * Get all registered taxonomies
     * 
     * @return array
     */
    public function getRegistered(): array {
        return $this->taxonomies;
    }
}