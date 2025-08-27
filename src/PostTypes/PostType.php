<?php
/**
 * PostType Wrapper
 * 
 * Provides a clean, object-oriented interface for working with WordPress posts
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\PostTypes;

use WordPressSkin\PostTypes\Traits\HandlesMeta;
use WordPressSkin\PostTypes\Traits\HandlesAcf;

defined('ABSPATH') or exit;

class PostType {
    use HandlesMeta;
    use HandlesAcf;
    
    /**
     * Post type name (override in child classes)
     * 
     * @var string|null
     */
    const POST_TYPE = null;
    
    /**
     * The WordPress post object
     * 
     * @var \WP_Post|null
     */
    protected $post;
    
    /**
     * Post ID
     * 
     * @var int
     */
    protected $id;
    
    
    /**
     * Constructor
     * 
     * @param int|\WP_Post|null $post Post ID, WP_Post object, or null for current post
     */
    public function __construct($post = null) {
        if ($post === null) {
            // Get current post
            global $post;
            $this->post = $post;
        } elseif (is_numeric($post)) {
            // Get post by ID
            $this->post = get_post($post);
        } elseif ($post instanceof \WP_Post) {
            $this->post = $post;
        } else {
            $this->post = null;
        }
        
        if ($this->post) {
            $this->id = $this->post->ID;
            
            // Validate post type if specified in child class
            if (static::POST_TYPE !== null && $this->post->post_type !== static::POST_TYPE) {
                $this->post = null;
                $this->id = null;
            }
        }
        
        // Call initialization hook for child classes
        $this->initialize();
    }
    
    /**
     * Initialize hook for child classes
     * Override this method to add custom initialization logic
     * 
     * @return void
     */
    protected function initialize() {
        // Override in child classes
    }
    
    /**
     * Get post ID
     * 
     * @return int|null
     */
    public function id() {
        return $this->id;
    }
    
    /**
     * Get post title
     * 
     * @param bool $filtered Apply filters
     * @return string
     */
    public function title($filtered = true) {
        if (!$this->post) return '';
        
        return $filtered 
            ? get_the_title($this->post) 
            : $this->post->post_title;
    }
    
    /**
     * Get post content
     * 
     * @param bool $filtered Apply the_content filters
     * @return string
     */
    public function content($filtered = true) {
        if (!$this->post) return '';
        
        return $filtered 
            ? apply_filters('the_content', $this->post->post_content)
            : $this->post->post_content;
    }
    
    /**
     * Get post excerpt
     * 
     * @param int $length Excerpt length in words
     * @return string
     */
    public function excerpt($length = null) {
        if (!$this->post) return '';
        
        $excerpt = $this->post->post_excerpt ?: $this->post->post_content;
        
        if ($length !== null) {
            return wp_trim_words($excerpt, $length);
        }
        
        return apply_filters('get_the_excerpt', $excerpt, $this->post);
    }
    
    /**
     * Get post URL
     * 
     * @return string
     */
    public function url() {
        return $this->post ? get_permalink($this->post) : '';
    }
    
    /**
     * Alias for url()
     * 
     * @return string
     */
    public function permalink() {
        return $this->url();
    }
    
    /**
     * Get post slug
     * 
     * @return string
     */
    public function slug() {
        return $this->post ? $this->post->post_name : '';
    }
    
    /**
     * Get post status
     * 
     * @return string
     */
    public function status() {
        return $this->post ? $this->post->post_status : '';
    }
    
    /**
     * Check if post is published
     * 
     * @return bool
     */
    public function isPublished() {
        return $this->status() === 'publish';
    }
    
    /**
     * Get post type
     * 
     * @return string
     */
    public function type() {
        return $this->post ? $this->post->post_type : '';
    }
    
    /**
     * Get post date
     * 
     * @param string $format Date format
     * @return string
     */
    public function date($format = null) {
        if (!$this->post) return '';
        
        $format = $format ?: get_option('date_format');
        return get_the_date($format, $this->post);
    }
    
    /**
     * Get post modified date
     * 
     * @param string $format Date format
     * @return string
     */
    public function modified($format = null) {
        if (!$this->post) return '';
        
        $format = $format ?: get_option('date_format');
        return get_the_modified_date($format, $this->post);
    }
    
    /**
     * Get post author
     * 
     * @return \WP_User|null
     */
    public function author() {
        if (!$this->post) return null;
        
        return get_user_by('id', $this->post->post_author);
    }
    
    /**
     * Get author name
     * 
     * @return string
     */
    public function authorName() {
        $author = $this->author();
        return $author ? $author->display_name : '';
    }
    
    /**
     * Get author URL
     * 
     * @return string
     */
    public function authorUrl() {
        if (!$this->post) return '';
        
        return get_author_posts_url($this->post->post_author);
    }
    
    /**
     * Get featured image URL
     * 
     * @param string $size Image size
     * @return string
     */
    public function thumbnail($size = 'thumbnail') {
        if (!$this->post) return '';
        
        return get_the_post_thumbnail_url($this->post, $size) ?: '';
    }
    
    /**
     * Alias for thumbnail()
     * 
     * @param string $size
     * @return string
     */
    public function featuredImage($size = 'full') {
        return $this->thumbnail($size);
    }
    
    /**
     * Get featured image ID
     * 
     * @return int
     */
    public function thumbnailId() {
        return $this->post ? get_post_thumbnail_id($this->post) : 0;
    }
    
    /**
     * Check if has featured image
     * 
     * @return bool
     */
    public function hasThumbnail() {
        return $this->post && has_post_thumbnail($this->post);
    }
    
    /**
     * Get featured image HTML
     * 
     * @param string $size Image size
     * @param array $attr HTML attributes
     * @return string
     */
    public function thumbnailHtml($size = 'thumbnail', $attr = []) {
        if (!$this->post) return '';
        
        return get_the_post_thumbnail($this->post, $size, $attr);
    }
    
    
    /**
     * Get post categories
     * 
     * @return array Array of WP_Term objects
     */
    public function categories() {
        if (!$this->post) return [];
        
        return get_the_category($this->id);
    }
    
    /**
     * Get post tags
     * 
     * @return array Array of WP_Term objects
     */
    public function tags() {
        if (!$this->post) return [];
        
        return get_the_tags($this->id) ?: [];
    }
    
    /**
     * Get post terms for a taxonomy
     * 
     * @param string $taxonomy
     * @return array Array of WP_Term objects
     */
    public function terms($taxonomy) {
        if (!$this->post) return [];
        
        $terms = get_the_terms($this->id, $taxonomy);
        return is_array($terms) ? $terms : [];
    }
    
    /**
     * Check if post has term
     * 
     * @param string|int $term Term name, slug, or ID
     * @param string $taxonomy
     * @return bool
     */
    public function hasTerm($term, $taxonomy) {
        if (!$this->post) return false;
        
        return has_term($term, $taxonomy, $this->id);
    }
    
    /**
     * Get parent post
     * 
     * @return PostType|null
     */
    public function parent() {
        if (!$this->post || !$this->post->post_parent) {
            return null;
        }
        
        return new static($this->post->post_parent);
    }
    
    /**
     * Get child posts
     * 
     * @param array $args Additional query arguments
     * @return array Array of PostType objects
     */
    public function children($args = []) {
        if (!$this->post) return [];
        
        $defaults = [
            'post_parent' => $this->id,
            'post_type' => $this->type(),
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        $posts = get_posts($args);
        
        return array_map(function($post) {
            return new static($post);
        }, $posts);
    }
    
    /**
     * Get siblings (posts with same parent)
     * 
     * @param bool $includeSelf Include current post
     * @return array Array of PostType objects
     */
    public function siblings($includeSelf = false) {
        if (!$this->post) return [];
        
        $args = [
            'post_parent' => $this->post->post_parent,
            'post_type' => $this->type(),
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ];
        
        if (!$includeSelf) {
            $args['exclude'] = $this->id;
        }
        
        $posts = get_posts($args);
        
        return array_map(function($post) {
            return new static($post);
        }, $posts);
    }
    
    /**
     * Get next post
     * 
     * @param bool $inSameTerm Whether to limit to same taxonomy term
     * @param string $taxonomy Taxonomy name if $inSameTerm is true
     * @return PostType|null
     */
    public function next($inSameTerm = false, $taxonomy = 'category') {
        if (!$this->post) return null;
        
        $next = get_next_post($inSameTerm, '', $taxonomy);
        return $next ? new static($next) : null;
    }
    
    /**
     * Get previous post
     * 
     * @param bool $inSameTerm Whether to limit to same taxonomy term
     * @param string $taxonomy Taxonomy name if $inSameTerm is true
     * @return PostType|null
     */
    public function previous($inSameTerm = false, $taxonomy = 'category') {
        if (!$this->post) return null;
        
        $prev = get_previous_post($inSameTerm, '', $taxonomy);
        return $prev ? new static($prev) : null;
    }
    
    /**
     * Get related posts
     * 
     * @param int $limit Number of posts
     * @param string $relatedBy 'category' or 'tag' or custom taxonomy
     * @return array Array of PostType objects
     */
    public function related($limit = 5, $relatedBy = 'category') {
        if (!$this->post) return [];
        
        $termIds = [];
        $terms = $this->terms($relatedBy);
        
        foreach ($terms as $term) {
            $termIds[] = $term->term_id;
        }
        
        if (empty($termIds)) {
            return [];
        }
        
        $args = [
            'post_type' => $this->type(),
            'posts_per_page' => $limit,
            'exclude' => $this->id,
            'tax_query' => [
                [
                    'taxonomy' => $relatedBy,
                    'field' => 'term_id',
                    'terms' => $termIds
                ]
            ]
        ];
        
        $posts = get_posts($args);
        
        return array_map(function($post) {
            return new static($post);
        }, $posts);
    }
    
    /**
     * Get breadcrumb path
     * 
     * @return array Array of PostType objects from root to current
     */
    public function breadcrumb() {
        if (!$this->post) return [];
        
        $breadcrumb = [];
        $current = $this;
        
        while ($current) {
            array_unshift($breadcrumb, $current);
            $current = $current->parent();
        }
        
        return $breadcrumb;
    }
    
    /**
     * Get edit link
     * 
     * @return string
     */
    public function editLink() {
        if (!$this->post) return '';
        
        return get_edit_post_link($this->id);
    }
    
    /**
     * Check if current user can edit
     * 
     * @return bool
     */
    public function canEdit() {
        if (!$this->post) return false;
        
        return current_user_can('edit_post', $this->id);
    }
    
    /**
     * Check if comments are open
     * 
     * @return bool
     */
    public function commentsOpen() {
        if (!$this->post) return false;
        
        return comments_open($this->id);
    }
    
    /**
     * Get comment count
     * 
     * @return int
     */
    public function commentCount() {
        if (!$this->post) return 0;
        
        return (int) $this->post->comment_count;
    }
    
    /**
     * Get comments
     * 
     * @param array $args Comment query arguments
     * @return array
     */
    public function comments($args = []) {
        if (!$this->post) return [];
        
        $defaults = [
            'post_id' => $this->id,
            'status' => 'approve'
        ];
        
        $args = wp_parse_args($args, $defaults);
        return get_comments($args);
    }
    
    /**
     * Convert to array
     * 
     * @param array $fields Fields to include
     * @return array
     */
    public function toArray($fields = null) {
        if (!$this->post) return [];
        
        $defaultFields = ['id', 'title', 'content', 'excerpt', 'url', 'date', 'author'];
        $fields = $fields ?: $defaultFields;
        
        $array = [];
        foreach ($fields as $field) {
            if (method_exists($this, $field)) {
                $array[$field] = $this->$field();
            } elseif (property_exists($this->post, $field)) {
                $array[$field] = $this->post->$field;
            }
        }
        
        return $array;
    }
    
    /**
     * Convert to JSON
     * 
     * @param array $fields Fields to include
     * @return string
     */
    public function toJson($fields = null) {
        return json_encode($this->toArray($fields));
    }
    
    /**
     * Get the raw WP_Post object
     * 
     * @return \WP_Post|null
     */
    public function raw() {
        return $this->post;
    }
    
    /**
     * Check if post exists
     * 
     * @return bool
     */
    public function exists() {
        return $this->post !== null;
    }
    
    /**
     * Magic method to get post properties
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        if ($this->post && property_exists($this->post, $name)) {
            return $this->post->$name;
        }
        
        return null;
    }
    
    /**
     * Magic method to check if property is set
     * 
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        return $this->post && property_exists($this->post, $name);
    }
    
    /**
     * String representation
     * 
     * @return string
     */
    public function __toString() {
        return $this->title();
    }
    
    
    // ==========================================
    // Methods for Custom Post Type Extensions
    // ==========================================
    
    /**
     * Get the post type name for this class
     * 
     * @return string|null
     */
    public static function getPostType() {
        return static::POST_TYPE;
    }
    
    /**
     * Create a new query for this post type
     * 
     * @return \WordPressSkin\Query\QueryBuilder
     */
    public static function query() {
        $query = new \WordPressSkin\Query\QueryBuilder();
        
        if (static::POST_TYPE !== null) {
            $query->post_type(static::POST_TYPE);
        }
        
        return $query;
    }
    
    /**
     * Find post by ID (returns instance of child class)
     * 
     * @param int $id
     * @return static|null
     */
    public static function find($id) {
        $post = get_post($id);
        
        if (!$post) {
            return null;
        }
        
        // Validate post type if specified
        if (static::POST_TYPE !== null && $post->post_type !== static::POST_TYPE) {
            return null;
        }
        
        return new static($post);
    }
    
    /**
     * Get all posts of this type
     * 
     * @param array $args Additional query arguments
     * @return array Array of child class instances
     */
    public static function all($args = []) {
        $defaults = [
            'posts_per_page' => -1
        ];
        
        if (static::POST_TYPE !== null) {
            $defaults['post_type'] = static::POST_TYPE;
        }
        
        $args = wp_parse_args($args, $defaults);
        $posts = get_posts($args);
        
        return array_map(function($post) {
            return new static($post);
        }, $posts);
    }
    
    /**
     * Create a new post of this type
     * 
     * @param array $data Post data
     * @return static|null
     */
    public static function create($data = []) {
        $defaults = [
            'post_status' => 'publish'
        ];
        
        if (static::POST_TYPE !== null) {
            $defaults['post_type'] = static::POST_TYPE;
        }
        
        $data = wp_parse_args($data, $defaults);
        
        $post_id = wp_insert_post($data);
        
        if (is_wp_error($post_id)) {
            return null;
        }
        
        return new static($post_id);
    }
    
    /**
     * Update this post
     * 
     * @param array $data Post data to update
     * @return bool
     */
    public function update($data = []) {
        if (!$this->post) {
            return false;
        }
        
        $data['ID'] = $this->id;
        $result = wp_update_post($data);
        
        if ($result && !is_wp_error($result)) {
            // Refresh the post object
            $this->post = get_post($this->id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete this post
     * 
     * @param bool $forceDelete Force delete (skip trash)
     * @return bool
     */
    public function delete($forceDelete = false) {
        if (!$this->post) {
            return false;
        }
        
        $result = wp_delete_post($this->id, $forceDelete);
        
        if ($result) {
            $this->post = null;
            $this->id = null;
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if this post type supports a feature
     * 
     * @param string $feature
     * @return bool
     */
    public function supports($feature) {
        if (!$this->post) {
            return false;
        }
        
        return post_type_supports($this->type(), $feature);
    }
    
    /**
     * Get post type object
     * 
     * @return \WP_Post_Type|null
     */
    public function getPostTypeObject() {
        if (!$this->post) {
            return null;
        }
        
        return get_post_type_object($this->type());
    }
    
    /**
     * Get post type labels
     * 
     * @return object|null
     */
    public function getPostTypeLabels() {
        $post_type_object = $this->getPostTypeObject();
        return $post_type_object ? $post_type_object->labels : null;
    }
    
}