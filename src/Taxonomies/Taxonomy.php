<?php
/**
 * Taxonomy Wrapper
 *
 * Provides a clean, object-oriented interface for working with WordPress taxonomy terms
 *
 * @package WP-Skin
 */

namespace WordPressSkin\Taxonomies;

use WordPressSkin\PostTypes\Traits\HandlesMeta;
use WordPressSkin\PostTypes\Traits\HandlesAcf;

defined('ABSPATH') or exit;

class Taxonomy {
    use HandlesMeta;
    use HandlesAcf;

    /**
     * Taxonomy name (override in child classes)
     *
     * @var string|null
     */
    const TAXONOMY = null;

    /**
     * The WordPress term object
     *
     * @var \WP_Term|null
     */
    protected $term;

    /**
     * Term ID
     *
     * @var int
     */
    protected $id;

    /**
     * Taxonomy name
     *
     * @var string
     */
    protected $taxonomy;


    /**
     * Constructor
     *
     * @param int|\WP_Term|null $term Term ID, WP_Term object, or null for current term
     * @param string|null $taxonomy Taxonomy name (optional if term is WP_Term object)
     */
    public function __construct($term = null, $taxonomy = null) {
        if ($term === null) {
            // Get current term from query
            $queriedObject = get_queried_object();
            if ($queriedObject instanceof \WP_Term) {
                $this->term = $queriedObject;
                $this->taxonomy = $this->term->taxonomy;
            }
        } elseif (is_numeric($term)) {
            // Get term by ID
            if ($taxonomy || static::TAXONOMY) {
                $this->term = get_term($term, $taxonomy ?: static::TAXONOMY);
            } else {
                $this->term = get_term($term);
            }
            if ($this->term && !is_wp_error($this->term)) {
                $this->taxonomy = $this->term->taxonomy;
            }
        } elseif ($term instanceof \WP_Term) {
            $this->term = $term;
            $this->taxonomy = $this->term->taxonomy;
        } else {
            $this->term = null;
        }

        if ($this->term && !is_wp_error($this->term)) {
            $this->id = $this->term->term_id;

            // Validate taxonomy if specified in child class
            if (static::TAXONOMY !== null && $this->term->taxonomy !== static::TAXONOMY) {
                $this->term = null;
                $this->id = null;
                $this->taxonomy = null;
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
     * Get current queried term as an instance of the child class
     *
     * @param callable|null $callback Optional callback to transform the term
     * @return \WordPressSkin\Taxonomies\Taxonomy|mixed|null
     */
    public static function current(?callable $callback = null)
    {
        $queriedObject = get_queried_object();

        if (!$queriedObject || !($queriedObject instanceof \WP_Term)) {
            return null;
        }

        // Use late static binding to instantiate the actual child class
        $taxonomy = new static($queriedObject);

        return $callback ? $callback($taxonomy) : $taxonomy;
    }

    /**
     * Get term ID
     *
     * @return int|null
     */
    public function id() {
        return $this->id;
    }

    /**
     * Get term name/title
     *
     * @param bool $filtered Apply filters
     * @return string
     */
    public function title($filtered = true) {
        if (!$this->term) return '';

        return $filtered
            ? apply_filters('single_term_title', $this->term->name)
            : $this->term->name;
    }

    /**
     * Alias for title()
     *
     * @param bool $filtered Apply filters
     * @return string
     */
    public function name($filtered = true) {
        return $this->title($filtered);
    }

    /**
     * Get term slug
     *
     * @return string
     */
    public function slug() {
        return $this->term ? $this->term->slug : '';
    }

    /**
     * Get term description
     *
     * @param bool $filtered Apply filters
     * @return string
     */
    public function description($filtered = true) {
        if (!$this->term) return '';

        return $filtered
            ? term_description($this->id, $this->taxonomy)
            : $this->term->description;
    }

    /**
     * Get term URL
     *
     * @return string
     */
    public function url() {
        return $this->term ? get_term_link($this->term) : '';
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
     * Get taxonomy name
     *
     * @return string
     */
    public function taxonomyName() {
        return $this->taxonomy ?: '';
    }

    /**
     * Get post count for this term
     *
     * @return int
     */
    public function count() {
        return $this->term ? (int) $this->term->count : 0;
    }

    /**
     * Get parent term
     *
     * @return Taxonomy|null
     */
    public function parent() {
        if (!$this->term || !$this->term->parent) {
            return null;
        }

        return new static($this->term->parent, $this->taxonomy);
    }

    /**
     * Get parent term ID
     *
     * @return int
     */
    public function parentId() {
        return $this->term ? (int) $this->term->parent : 0;
    }

    /**
     * Check if has parent
     *
     * @return bool
     */
    public function hasParent() {
        return $this->parentId() > 0;
    }

    /**
     * Get child terms
     *
     * @param array $args Additional query arguments
     * @return array Array of Taxonomy objects
     */
    public function children($args = []) {
        if (!$this->term) return [];

        $defaults = [
            'taxonomy' => $this->taxonomy,
            'parent' => $this->id,
            'hide_empty' => false
        ];

        $args = wp_parse_args($args, $defaults);
        $terms = get_terms($args);

        if (is_wp_error($terms)) {
            return [];
        }

        return array_map(function($term) {
            return new static($term);
        }, $terms);
    }

    /**
     * Check if has children
     *
     * @return bool
     */
    public function hasChildren() {
        return count($this->children()) > 0;
    }

    /**
     * Get sibling terms (terms with same parent)
     *
     * @param bool $includeSelf Include current term
     * @return array Array of Taxonomy objects
     */
    public function siblings($includeSelf = false) {
        if (!$this->term) return [];

        $args = [
            'taxonomy' => $this->taxonomy,
            'parent' => $this->term->parent,
            'hide_empty' => false
        ];

        if (!$includeSelf) {
            $args['exclude'] = $this->id;
        }

        $terms = get_terms($args);

        if (is_wp_error($terms)) {
            return [];
        }

        return array_map(function($term) {
            return new static($term);
        }, $terms);
    }

    /**
     * Get all ancestor terms
     *
     * @return array Array of Taxonomy objects from root to parent
     */
    public function ancestors() {
        if (!$this->term) return [];

        $ancestor_ids = get_ancestors($this->id, $this->taxonomy, 'taxonomy');

        return array_map(function($ancestor_id) {
            return new static($ancestor_id, $this->taxonomy);
        }, array_reverse($ancestor_ids));
    }

    /**
     * Get breadcrumb path
     *
     * @return array Array of Taxonomy objects from root to current
     */
    public function breadcrumb() {
        if (!$this->term) return [];

        $breadcrumb = $this->ancestors();
        $breadcrumb[] = $this;

        return $breadcrumb;
    }

    /**
     * Get posts in this term
     *
     * @param array $args Additional query arguments
     * @return array Array of WP_Post objects
     */
    public function posts($args = []) {
        if (!$this->term) return [];

        $defaults = [
            'tax_query' => [
                [
                    'taxonomy' => $this->taxonomy,
                    'field' => 'term_id',
                    'terms' => $this->id
                ]
            ],
            'posts_per_page' => -1
        ];

        $args = wp_parse_args($args, $defaults);
        return get_posts($args);
    }

    /**
     * Get posts as PostType objects
     *
     * @param string $postTypeClass The PostType class to instantiate
     * @param array $args Additional query arguments
     * @return array Array of PostType objects
     */
    public function getPostsAs($postTypeClass, $args = []) {
        $posts = $this->posts($args);

        return array_map(function($post) use ($postTypeClass) {
            return new $postTypeClass($post);
        }, $posts);
    }

    /**
     * Check if term is assigned to a post
     *
     * @param int $post_id Post ID
     * @return bool
     */
    public function hasPost($post_id) {
        if (!$this->term) return false;

        return has_term($this->id, $this->taxonomy, $post_id);
    }

    /**
     * Get term meta value
     *
     * @param string $key Meta key
     * @param bool $single Return single value
     * @return mixed
     */
    public function getMeta($key, $single = true) {
        if (!$this->term) return null;

        return get_term_meta($this->id, $key, $single);
    }

    /**
     * Update term meta value
     *
     * @param string $key Meta key
     * @param mixed $value Meta value
     * @return bool|int
     */
    public function updateMeta($key, $value) {
        if (!$this->term) return false;

        return update_term_meta($this->id, $key, $value);
    }

    /**
     * Delete term meta value
     *
     * @param string $key Meta key
     * @param mixed $value Meta value to delete (optional)
     * @return bool
     */
    public function deleteMeta($key, $value = '') {
        if (!$this->term) return false;

        return delete_term_meta($this->id, $key, $value);
    }

    /**
     * Get edit link
     *
     * @return string
     */
    public function editLink() {
        if (!$this->term) return '';

        return get_edit_term_link($this->id, $this->taxonomy);
    }

    /**
     * Check if current user can edit
     *
     * @return bool
     */
    public function canEdit() {
        if (!$this->term) return false;

        $taxonomy_obj = get_taxonomy($this->taxonomy);
        return current_user_can($taxonomy_obj->cap->edit_terms, $this->id);
    }

    /**
     * Check if current user can delete
     *
     * @return bool
     */
    public function canDelete() {
        if (!$this->term) return false;

        $taxonomy_obj = get_taxonomy($this->taxonomy);
        return current_user_can($taxonomy_obj->cap->delete_terms, $this->id);
    }

    /**
     * Convert to array
     *
     * @param array $fields Fields to include
     * @return array
     */
    public function toArray($fields = null) {
        if (!$this->term) return [];

        $defaultFields = ['id', 'title', 'slug', 'description', 'url', 'count'];
        $fields = $fields ?: $defaultFields;

        $array = [];
        foreach ($fields as $field) {
            if (method_exists($this, $field)) {
                $array[$field] = $this->$field();
            } elseif (property_exists($this->term, $field)) {
                $array[$field] = $this->term->$field;
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
     * Get the raw WP_Term object
     *
     * @return \WP_Term|null
     */
    public function raw() {
        return $this->term;
    }

    /**
     * Check if term exists
     *
     * @return bool
     */
    public function exists() {
        return $this->term !== null && !is_wp_error($this->term);
    }

    /**
     * Magic method to get term properties
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        if ($this->term && property_exists($this->term, $name)) {
            return $this->term->$name;
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
        return $this->term && property_exists($this->term, $name);
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
    // Static Methods for Taxonomy Extensions
    // ==========================================

    /**
     * Get the taxonomy name for this class
     *
     * @return string|null
     */
    public static function getTaxonomy() {
        return static::TAXONOMY;
    }

    /**
     * Find term by ID (returns instance of child class)
     *
     * @param int $id Term ID
     * @param string|null $taxonomy Taxonomy name
     * @return static|null
     */
    public static function find($id, $taxonomy = null) {
        $taxonomy = $taxonomy ?: static::TAXONOMY;

        if (!$taxonomy) {
            $term = get_term($id);
        } else {
            $term = get_term($id, $taxonomy);
        }

        if (!$term || is_wp_error($term)) {
            return null;
        }

        // Validate taxonomy if specified in child class
        if (static::TAXONOMY !== null && $term->taxonomy !== static::TAXONOMY) {
            return null;
        }

        return new static($term);
    }

    /**
     * Find term by slug
     *
     * @param string $slug Term slug
     * @param string|null $taxonomy Taxonomy name
     * @return static|null
     */
    public static function findBySlug($slug, $taxonomy = null) {
        $taxonomy = $taxonomy ?: static::TAXONOMY;

        if (!$taxonomy) {
            return null;
        }

        $term = get_term_by('slug', $slug, $taxonomy);

        if (!$term) {
            return null;
        }

        return new static($term);
    }

    /**
     * Find term by name
     *
     * @param string $name Term name
     * @param string|null $taxonomy Taxonomy name
     * @return static|null
     */
    public static function findByName($name, $taxonomy = null) {
        $taxonomy = $taxonomy ?: static::TAXONOMY;

        if (!$taxonomy) {
            return null;
        }

        $term = get_term_by('name', $name, $taxonomy);

        if (!$term) {
            return null;
        }

        return new static($term);
    }

    /**
     * Get all terms of this taxonomy
     *
     * @param array $args Additional query arguments
     * @return array Array of child class instances
     */
    public static function all($args = []) {
        $defaults = [
            'hide_empty' => false
        ];

        if (static::TAXONOMY !== null) {
            $defaults['taxonomy'] = static::TAXONOMY;
        }

        $args = wp_parse_args($args, $defaults);
        $terms = get_terms($args);

        if (is_wp_error($terms)) {
            return [];
        }

        return array_map(function($term) {
            return new static($term);
        }, $terms);
    }

    /**
     * Get terms by post
     *
     * @param int $post_id Post ID
     * @param string|null $taxonomy Taxonomy name
     * @return array Array of child class instances
     */
    public static function byPost($post_id, $taxonomy = null) {
        $taxonomy = $taxonomy ?: static::TAXONOMY;

        if (!$taxonomy) {
            return [];
        }

        $terms = get_the_terms($post_id, $taxonomy);

        if (!$terms || is_wp_error($terms)) {
            return [];
        }

        return array_map(function($term) {
            return new static($term);
        }, $terms);
    }

    /**
     * Create a new term
     *
     * @param string $name Term name
     * @param array $args Additional arguments
     * @return static|null
     */
    public static function create($name, $args = []) {
        $taxonomy = isset($args['taxonomy']) ? $args['taxonomy'] : static::TAXONOMY;

        if (!$taxonomy) {
            return null;
        }

        unset($args['taxonomy']);

        $term_data = wp_insert_term($name, $taxonomy, $args);

        if (is_wp_error($term_data)) {
            return null;
        }

        return new static($term_data['term_id'], $taxonomy);
    }

    /**
     * Update this term
     *
     * @param array $args Term data to update
     * @return bool
     */
    public function update($args = []) {
        if (!$this->term) {
            return false;
        }

        $result = wp_update_term($this->id, $this->taxonomy, $args);

        if ($result && !is_wp_error($result)) {
            // Refresh the term object
            $this->term = get_term($this->id, $this->taxonomy);
            return true;
        }

        return false;
    }

    /**
     * Delete this term
     *
     * @param array $args Additional arguments
     * @return bool
     */
    public function delete($args = []) {
        if (!$this->term) {
            return false;
        }

        $result = wp_delete_term($this->id, $this->taxonomy, $args);

        if ($result && !is_wp_error($result)) {
            $this->term = null;
            $this->id = null;
            $this->taxonomy = null;
            return true;
        }

        return false;
    }

    /**
     * Get taxonomy object
     *
     * @return object|null
     */
    public function getTaxonomyObject() {
        if (!$this->taxonomy) {
            return null;
        }

        return get_taxonomy($this->taxonomy);
    }

    /**
     * Get taxonomy labels
     *
     * @return object|null
     */
    public function getTaxonomyLabels() {
        $taxonomy_object = $this->getTaxonomyObject();
        return $taxonomy_object ? $taxonomy_object->labels : null;
    }

}