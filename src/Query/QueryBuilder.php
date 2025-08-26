<?php
/**
 * Query Builder for WordPress Posts/Pages
 * 
 * Provides an Eloquent-style query builder for WordPress post queries
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Query;

defined('ABSPATH') or exit;

class QueryBuilder {
    
    /**
     * WP_Query arguments
     * 
     * @var array
     */
    protected $args = [];
    
    /**
     * Meta query conditions
     * 
     * @var array
     */
    protected $metaQueries = [];
    
    /**
     * Tax query conditions
     * 
     * @var array
     */
    protected $taxQueries = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        // Set sensible defaults
        $this->args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 10
        ];
    }
    
    /**
     * Set post type
     * 
     * @param string|array $type Post type(s)
     * @return self
     */
    public function post_type($type) {
        $this->args['post_type'] = $type;
        return $this;
    }
    
    /**
     * Alias for post_type
     * 
     * @param string|array $type
     * @return self
     */
    public function type($type) {
        return $this->post_type($type);
    }
    
    /**
     * Set post status
     * 
     * @param string|array $status
     * @return self
     */
    public function status($status) {
        $this->args['post_status'] = $status;
        return $this;
    }
    
    /**
     * Set posts per page limit
     * 
     * @param int $limit
     * @return self
     */
    public function limit($limit) {
        $this->args['posts_per_page'] = $limit;
        return $this;
    }
    
    /**
     * Alias for limit
     * 
     * @param int $count
     * @return self
     */
    public function take($count) {
        return $this->limit($count);
    }
    
    /**
     * Get all posts (no limit)
     * 
     * @return self
     */
    public function all() {
        $this->args['posts_per_page'] = -1;
        return $this;
    }
    
    /**
     * Set page number
     * 
     * @param int $page
     * @return self
     */
    public function page($page) {
        $this->args['paged'] = $page;
        return $this;
    }
    
    /**
     * Set offset
     * 
     * @param int $offset
     * @return self
     */
    public function offset($offset) {
        $this->args['offset'] = $offset;
        return $this;
    }
    
    /**
     * Order by field
     * 
     * @param string $field
     * @param string $direction
     * @return self
     */
    public function orderBy($field, $direction = 'DESC') {
        $this->args['orderby'] = $field;
        $this->args['order'] = strtoupper($direction);
        return $this;
    }
    
    /**
     * Order by multiple fields
     * 
     * @param array $orders Array of field => direction pairs
     * @return self
     */
    public function orderByMultiple($orders) {
        $this->args['orderby'] = $orders;
        return $this;
    }
    
    /**
     * Add meta query condition
     * 
     * @param string $key Meta key
     * @param mixed $value Meta value (optional)
     * @param string $compare Comparison operator
     * @param string $type Value type
     * @return self
     */
    public function meta($key, $value = null, $compare = '=', $type = 'CHAR') {
        $metaQuery = ['key' => $key];
        
        if ($value !== null) {
            $metaQuery['value'] = $value;
            $metaQuery['compare'] = $compare;
            $metaQuery['type'] = $type;
        } else {
            // If no value, check for existence
            $metaQuery['compare'] = 'EXISTS';
        }
        
        $this->metaQueries[] = $metaQuery;
        return $this;
    }
    
    /**
     * Add meta query with LIKE comparison
     * 
     * @param string $key
     * @param string $value
     * @return self
     */
    public function metaLike($key, $value) {
        return $this->meta($key, $value, 'LIKE');
    }
    
    /**
     * Add meta query for numeric comparison
     * 
     * @param string $key
     * @param mixed $value
     * @param string $compare
     * @return self
     */
    public function metaNumeric($key, $value, $compare = '=') {
        return $this->meta($key, $value, $compare, 'NUMERIC');
    }
    
    /**
     * Add meta query for date comparison
     * 
     * @param string $key
     * @param string $date
     * @param string $compare
     * @return self
     */
    public function metaDate($key, $date, $compare = '=') {
        return $this->meta($key, $date, $compare, 'DATE');
    }
    
    /**
     * Add meta query to check if key exists
     * 
     * @param string $key
     * @return self
     */
    public function metaExists($key) {
        $this->metaQueries[] = [
            'key' => $key,
            'compare' => 'EXISTS'
        ];
        return $this;
    }
    
    /**
     * Add meta query to check if key doesn't exist
     * 
     * @param string $key
     * @return self
     */
    public function metaNotExists($key) {
        $this->metaQueries[] = [
            'key' => $key,
            'compare' => 'NOT EXISTS'
        ];
        return $this;
    }
    
    /**
     * Add taxonomy query
     * 
     * @param string $taxonomy
     * @param string|array $terms
     * @param string $field
     * @param string $operator
     * @return self
     */
    public function tax($taxonomy, $terms, $field = 'slug', $operator = 'IN') {
        $this->taxQueries[] = [
            'taxonomy' => $taxonomy,
            'field' => $field,
            'terms' => $terms,
            'operator' => $operator
        ];
        return $this;
    }
    
    /**
     * Add category query
     * 
     * @param string|array $categories
     * @return self
     */
    public function category($categories) {
        return $this->tax('category', $categories);
    }
    
    /**
     * Add tag query
     * 
     * @param string|array $tags
     * @return self
     */
    public function tag($tags) {
        return $this->tax('post_tag', $tags);
    }
    
    /**
     * Search posts
     * 
     * @param string $keyword
     * @return self
     */
    public function search($keyword) {
        $this->args['s'] = $keyword;
        return $this;
    }
    
    /**
     * Filter by author
     * 
     * @param int|string $author Author ID or username
     * @return self
     */
    public function author($author) {
        if (is_string($author)) {
            $this->args['author_name'] = $author;
        } else {
            $this->args['author'] = $author;
        }
        return $this;
    }
    
    /**
     * Filter by parent post
     * 
     * @param int $parentId
     * @return self
     */
    public function parent($parentId) {
        $this->args['post_parent'] = $parentId;
        return $this;
    }
    
    /**
     * Include specific post IDs
     * 
     * @param array $ids
     * @return self
     */
    public function include($ids) {
        $this->args['post__in'] = (array) $ids;
        return $this;
    }
    
    /**
     * Exclude specific post IDs
     * 
     * @param array $ids
     * @return self
     */
    public function exclude($ids) {
        $this->args['post__not_in'] = (array) $ids;
        return $this;
    }
    
    /**
     * Filter by date
     * 
     * @param string $column Date column (post_date, post_modified)
     * @param string $after After this date
     * @param string $before Before this date
     * @return self
     */
    public function date($column = 'post_date', $after = null, $before = null) {
        $dateQuery = ['column' => $column];
        
        if ($after) {
            $dateQuery['after'] = $after;
        }
        
        if ($before) {
            $dateQuery['before'] = $before;
        }
        
        $this->args['date_query'][] = $dateQuery;
        return $this;
    }
    
    /**
     * Posts from last X days
     * 
     * @param int $days
     * @return self
     */
    public function recent($days) {
        return $this->date('post_date', $days . ' days ago');
    }
    
    /**
     * Get only sticky posts
     * 
     * @return self
     */
    public function sticky() {
        $this->args['post__in'] = get_option('sticky_posts');
        return $this;
    }
    
    /**
     * Exclude sticky posts
     * 
     * @return self
     */
    public function notSticky() {
        $this->args['ignore_sticky_posts'] = true;
        return $this;
    }
    
    /**
     * Set custom field for WP_Query
     * 
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function where($key, $value) {
        $this->args[$key] = $value;
        return $this;
    }
    
    /**
     * Get raw WP_Query arguments
     * 
     * @return array
     */
    public function getArgs() {
        // Add meta queries if any
        if (!empty($this->metaQueries)) {
            $this->args['meta_query'] = $this->metaQueries;
            if (count($this->metaQueries) > 1) {
                $this->args['meta_query']['relation'] = 'AND';
            }
        }
        
        // Add tax queries if any
        if (!empty($this->taxQueries)) {
            $this->args['tax_query'] = $this->taxQueries;
            if (count($this->taxQueries) > 1) {
                $this->args['tax_query']['relation'] = 'AND';
            }
        }
        
        return $this->args;
    }
    
    /**
     * Execute query and get posts
     * 
     * @return array Array of WP_Post objects
     */
    public function get() {
        $query = new \WP_Query($this->getArgs());
        return $query->posts;
    }
    
    /**
     * Get the first result
     * 
     * @return \WP_Post|null
     */
    public function first() {
        $this->limit(1);
        $posts = $this->get();
        return !empty($posts) ? $posts[0] : null;
    }
    
    /**
     * Get single post by ID
     * 
     * @param int $id
     * @return \WP_Post|null
     */
    public function find($id) {
        $this->args['p'] = $id;
        return $this->first();
    }
    
    /**
     * Count posts matching criteria
     * 
     * @return int
     */
    public function count() {
        $this->args['fields'] = 'ids';
        $query = new \WP_Query($this->getArgs());
        return $query->found_posts;
    }
    
    /**
     * Check if posts exist
     * 
     * @return bool
     */
    public function exists() {
        return $this->count() > 0;
    }
    
    /**
     * Get paginated results
     * 
     * @return object Object with posts and pagination data
     */
    public function paginate() {
        $query = new \WP_Query($this->getArgs());
        
        return (object) [
            'posts' => $query->posts,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => max(1, $query->get('paged')),
            'per_page' => $query->get('posts_per_page')
        ];
    }
    
    /**
     * Get only post IDs
     * 
     * @return array
     */
    public function ids() {
        $this->args['fields'] = 'ids';
        $query = new \WP_Query($this->getArgs());
        return $query->posts;
    }
    
    /**
     * Get posts as array (with selected fields)
     * 
     * @param array $fields Fields to include
     * @return array
     */
    public function toArray($fields = ['ID', 'post_title', 'post_content']) {
        $posts = $this->get();
        $result = [];
        
        foreach ($posts as $post) {
            $item = [];
            foreach ($fields as $field) {
                if (property_exists($post, $field)) {
                    $item[$field] = $post->$field;
                }
            }
            $result[] = $item;
        }
        
        return $result;
    }
    
    /**
     * Execute callback for each post
     * 
     * @param callable $callback
     * @return self
     */
    public function each(callable $callback) {
        $posts = $this->get();
        foreach ($posts as $post) {
            $callback($post);
        }
        return $this;
    }
    
    /**
     * Map posts to array using callback
     * 
     * @param callable $callback
     * @return array
     */
    public function map(callable $callback) {
        $posts = $this->get();
        return array_map($callback, $posts);
    }
    
    /**
     * Filter posts using callback
     * 
     * @param callable $callback
     * @return array
     */
    public function filter(callable $callback) {
        $posts = $this->get();
        return array_filter($posts, $callback);
    }
    
    /**
     * Debug query (return WP_Query object)
     * 
     * @return \WP_Query
     */
    public function debug() {
        return new \WP_Query($this->getArgs());
    }
}