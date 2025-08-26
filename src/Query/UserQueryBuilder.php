<?php
/**
 * Query Builder for WordPress Users
 * 
 * Provides an Eloquent-style query builder for WordPress user queries
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Query;

defined('ABSPATH') or exit;

class UserQueryBuilder {
    
    /**
     * WP_User_Query arguments
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
     * Constructor
     */
    public function __construct() {
        // Set sensible defaults
        $this->args = [
            'number' => 10
        ];
    }
    
    /**
     * Filter by user role
     * 
     * @param string|array $role Role name(s)
     * @return self
     */
    public function role($role) {
        if (is_array($role)) {
            $this->args['role__in'] = $role;
        } else {
            $this->args['role'] = $role;
        }
        return $this;
    }
    
    /**
     * Exclude users with specific role
     * 
     * @param string|array $role
     * @return self
     */
    public function notRole($role) {
        $this->args['role__not_in'] = (array) $role;
        return $this;
    }
    
    /**
     * Set number of users to retrieve
     * 
     * @param int $limit
     * @return self
     */
    public function limit($limit) {
        $this->args['number'] = $limit;
        return $this;
    }
    
    /**
     * Get all users (no limit)
     * 
     * @return self
     */
    public function all() {
        $this->args['number'] = -1;
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
    public function orderBy($field, $direction = 'ASC') {
        $this->args['orderby'] = $field;
        $this->args['order'] = strtoupper($direction);
        return $this;
    }
    
    /**
     * Search users by keyword
     * 
     * @param string $keyword
     * @return self
     */
    public function search($keyword) {
        $this->args['search'] = '*' . $keyword . '*';
        return $this;
    }
    
    /**
     * Search in specific columns
     * 
     * @param array $columns
     * @return self
     */
    public function searchColumns($columns) {
        $this->args['search_columns'] = $columns;
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
     * Check if meta key exists
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
     * Check if meta key doesn't exist
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
     * Include specific user IDs
     * 
     * @param array $ids
     * @return self
     */
    public function include($ids) {
        $this->args['include'] = (array) $ids;
        return $this;
    }
    
    /**
     * Exclude specific user IDs
     * 
     * @param array $ids
     * @return self
     */
    public function exclude($ids) {
        $this->args['exclude'] = (array) $ids;
        return $this;
    }
    
    /**
     * Filter by user login
     * 
     * @param string|array $logins
     * @return self
     */
    public function login($logins) {
        $this->args['login__in'] = (array) $logins;
        return $this;
    }
    
    /**
     * Filter by user nicename
     * 
     * @param string|array $nicenames
     * @return self
     */
    public function nicename($nicenames) {
        $this->args['nicename__in'] = (array) $nicenames;
        return $this;
    }
    
    /**
     * Filter by user email
     * 
     * @param string $email
     * @return self
     */
    public function email($email) {
        $this->args['search'] = $email;
        $this->args['search_columns'] = ['user_email'];
        return $this;
    }
    
    /**
     * Filter users registered after date
     * 
     * @param string $date
     * @return self
     */
    public function registeredAfter($date) {
        $this->args['date_query'][] = [
            'after' => $date,
            'column' => 'user_registered'
        ];
        return $this;
    }
    
    /**
     * Filter users registered before date
     * 
     * @param string $date
     * @return self
     */
    public function registeredBefore($date) {
        $this->args['date_query'][] = [
            'before' => $date,
            'column' => 'user_registered'
        ];
        return $this;
    }
    
    /**
     * Filter users registered in last X days
     * 
     * @param int $days
     * @return self
     */
    public function recentlyRegistered($days) {
        return $this->registeredAfter($days . ' days ago');
    }
    
    /**
     * Filter by blog (for multisite)
     * 
     * @param int $blogId
     * @return self
     */
    public function blog($blogId) {
        $this->args['blog_id'] = $blogId;
        return $this;
    }
    
    /**
     * Filter users who can perform capability
     * 
     * @param string $capability
     * @return self
     */
    public function can($capability) {
        $this->args['capability'] = $capability;
        return $this;
    }
    
    /**
     * Filter users who cannot perform capability
     * 
     * @param string $capability
     * @return self
     */
    public function cannot($capability) {
        $this->args['capability__not_in'] = [$capability];
        return $this;
    }
    
    /**
     * Get users with posts
     * 
     * @return self
     */
    public function hasPublishedPosts() {
        $this->args['has_published_posts'] = true;
        return $this;
    }
    
    /**
     * Get users with posts of specific type
     * 
     * @param string|array $postTypes
     * @return self
     */
    public function hasPublishedPostsType($postTypes) {
        $this->args['has_published_posts'] = $postTypes;
        return $this;
    }
    
    /**
     * Set custom field for WP_User_Query
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
     * Get fields to return
     * 
     * @param string|array $fields
     * @return self
     */
    public function fields($fields) {
        $this->args['fields'] = $fields;
        return $this;
    }
    
    /**
     * Get raw WP_User_Query arguments
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
        
        return $this->args;
    }
    
    /**
     * Execute query and get users
     * 
     * @return array Array of WP_User objects
     */
    public function get() {
        $query = new \WP_User_Query($this->getArgs());
        return $query->get_results();
    }
    
    /**
     * Get the first user
     * 
     * @return \WP_User|null
     */
    public function first() {
        $this->limit(1);
        $users = $this->get();
        return !empty($users) ? $users[0] : null;
    }
    
    /**
     * Find user by ID
     * 
     * @param int $id
     * @return \WP_User|null
     */
    public function find($id) {
        return get_user_by('id', $id) ?: null;
    }
    
    /**
     * Find user by email
     * 
     * @param string $email
     * @return \WP_User|null
     */
    public function findByEmail($email) {
        return get_user_by('email', $email) ?: null;
    }
    
    /**
     * Find user by login
     * 
     * @param string $login
     * @return \WP_User|null
     */
    public function findByLogin($login) {
        return get_user_by('login', $login) ?: null;
    }
    
    /**
     * Count users matching criteria
     * 
     * @return int
     */
    public function count() {
        $this->args['count_total'] = true;
        $query = new \WP_User_Query($this->getArgs());
        return $query->get_total();
    }
    
    /**
     * Check if users exist
     * 
     * @return bool
     */
    public function exists() {
        return $this->count() > 0;
    }
    
    /**
     * Get paginated results
     * 
     * @param int $page
     * @param int $perPage
     * @return object
     */
    public function paginate($page = 1, $perPage = null) {
        if ($perPage) {
            $this->limit($perPage);
        }
        
        $this->args['paged'] = $page;
        $this->args['count_total'] = true;
        
        $query = new \WP_User_Query($this->getArgs());
        
        return (object) [
            'users' => $query->get_results(),
            'total' => $query->get_total(),
            'pages' => ceil($query->get_total() / $this->args['number']),
            'current_page' => $page,
            'per_page' => $this->args['number']
        ];
    }
    
    /**
     * Get only user IDs
     * 
     * @return array
     */
    public function ids() {
        $this->fields('ID');
        $query = new \WP_User_Query($this->getArgs());
        return $query->get_results();
    }
    
    /**
     * Get users as array with selected fields
     * 
     * @param array $fields
     * @return array
     */
    public function toArray($fields = ['ID', 'user_login', 'user_email', 'display_name']) {
        $users = $this->get();
        $result = [];
        
        foreach ($users as $user) {
            $item = [];
            foreach ($fields as $field) {
                if (property_exists($user->data, $field)) {
                    $item[$field] = $user->data->$field;
                } elseif (property_exists($user, $field)) {
                    $item[$field] = $user->$field;
                }
            }
            $result[] = $item;
        }
        
        return $result;
    }
    
    /**
     * Execute callback for each user
     * 
     * @param callable $callback
     * @return self
     */
    public function each(callable $callback) {
        $users = $this->get();
        foreach ($users as $user) {
            $callback($user);
        }
        return $this;
    }
    
    /**
     * Map users to array using callback
     * 
     * @param callable $callback
     * @return array
     */
    public function map(callable $callback) {
        $users = $this->get();
        return array_map($callback, $users);
    }
    
    /**
     * Filter users using callback
     * 
     * @param callable $callback
     * @return array
     */
    public function filter(callable $callback) {
        $users = $this->get();
        return array_filter($users, $callback);
    }
    
    /**
     * Debug query (return WP_User_Query object)
     * 
     * @return \WP_User_Query
     */
    public function debug() {
        return new \WP_User_Query($this->getArgs());
    }
}