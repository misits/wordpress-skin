# Query Builder

WP-Skin provides an Eloquent-style query builder that makes WordPress database queries clean, intuitive, and chainable. No more complex `WP_Query` arrays!

## Table of Contents

- [Overview](#overview)
- [Post Queries](#post-queries)
- [User Queries](#user-queries)
- [Common Examples](#common-examples)
- [Advanced Usage](#advanced-usage)
- [Comparison with WP_Query](#comparison-with-wp_query)

## Overview

The Query Builder provides a fluent interface for building WordPress queries:

```php
use WordPressSkin\Core\Skin;

// Simple post query
$posts = Skin::query()
    ->post_type('product')
    ->limit(10)
    ->get();

// User query
$users = Skin::users()
    ->role('subscriber')
    ->limit(5)
    ->get();
```

## Post Queries

### Basic Usage

```php
// Get recent posts
$posts = Skin::query()
    ->limit(5)
    ->orderBy('date', 'desc')
    ->get();

// Get single post
$post = Skin::query()
    ->find(123); // By ID

// Get first matching post
$post = Skin::query()
    ->post_type('page')
    ->meta('featured', true)
    ->first();
```

### Post Types

```php
// Single post type
$products = Skin::query()
    ->post_type('product')
    ->get();

// Multiple post types
$items = Skin::query()
    ->post_type(['post', 'page', 'product'])
    ->get();

// Alias method
$products = Skin::query()
    ->type('product')
    ->get();
```

### Status Filtering

```php
// Published posts (default)
$posts = Skin::query()
    ->status('publish')
    ->get();

// Draft posts
$drafts = Skin::query()
    ->status('draft')
    ->get();

// Multiple statuses
$all = Skin::query()
    ->status(['publish', 'draft', 'pending'])
    ->get();
```

### Limiting Results

```php
// Limit number of posts
$posts = Skin::query()
    ->limit(20)
    ->get();

// Get all posts (no limit)
$all = Skin::query()
    ->all()
    ->get();

// Pagination
$posts = Skin::query()
    ->limit(10)
    ->page(2) // Page 2
    ->get();

// With offset
$posts = Skin::query()
    ->limit(10)
    ->offset(5)
    ->get();
```

### Ordering

```php
// Order by date (newest first)
$posts = Skin::query()
    ->orderBy('date', 'desc')
    ->get();

// Order by title (alphabetical)
$posts = Skin::query()
    ->orderBy('title', 'asc')
    ->get();

// Order by menu order
$posts = Skin::query()
    ->orderBy('menu_order', 'asc')
    ->get();

// Order by multiple fields
$posts = Skin::query()
    ->orderByMultiple([
        'menu_order' => 'ASC',
        'date' => 'DESC'
    ])
    ->get();
```

### Meta Queries

```php
// Simple meta query
$featured = Skin::query()
    ->meta('featured', true)
    ->get();

// Meta comparison
$expensive = Skin::query()
    ->metaNumeric('price', 100, '>')
    ->get();

// Meta LIKE query
$results = Skin::query()
    ->metaLike('description', 'wordpress')
    ->get();

// Check if meta exists
$hasVideo = Skin::query()
    ->metaExists('video_url')
    ->get();

// Multiple meta conditions (AND)
$products = Skin::query()
    ->meta('featured', true)
    ->metaNumeric('price', 50, '>=')
    ->metaNumeric('stock', 0, '>')
    ->get();

// Date meta comparison
$events = Skin::query()
    ->post_type('event')
    ->metaDate('event_date', date('Y-m-d'), '>=')
    ->get();
```

### Taxonomy Queries

```php
// Category query
$posts = Skin::query()
    ->category('news')
    ->get();

// Multiple categories
$posts = Skin::query()
    ->category(['news', 'updates'])
    ->get();

// Tag query
$posts = Skin::query()
    ->tag('featured')
    ->get();

// Custom taxonomy
$products = Skin::query()
    ->post_type('product')
    ->tax('product_category', 'electronics')
    ->get();

// Multiple taxonomy conditions
$products = Skin::query()
    ->post_type('product')
    ->tax('product_category', 'electronics')
    ->tax('product_brand', 'apple')
    ->get();

// Taxonomy with ID
$posts = Skin::query()
    ->tax('category', [1, 2, 3], 'term_id')
    ->get();
```

### Search

```php
// Search posts
$results = Skin::query()
    ->search('wordpress')
    ->get();

// Search in specific post type
$products = Skin::query()
    ->post_type('product')
    ->search('laptop')
    ->get();
```

### Author Queries

```php
// By author ID
$posts = Skin::query()
    ->author(1)
    ->get();

// By author username
$posts = Skin::query()
    ->author('john')
    ->get();
```

### Parent/Child Relationships

```php
// Get child pages
$children = Skin::query()
    ->post_type('page')
    ->parent(10)
    ->get();

// Get top-level pages
$parents = Skin::query()
    ->post_type('page')
    ->parent(0)
    ->get();
```

### Include/Exclude Posts

```php
// Include specific posts
$posts = Skin::query()
    ->include([1, 2, 3, 4, 5])
    ->get();

// Exclude posts
$posts = Skin::query()
    ->exclude([10, 20, 30])
    ->get();
```

### Date Queries

```php
// Posts from last 7 days
$recent = Skin::query()
    ->recent(7)
    ->get();

// Posts after specific date
$posts = Skin::query()
    ->date('post_date', '2024-01-01')
    ->get();

// Posts between dates
$posts = Skin::query()
    ->date('post_date', '2024-01-01', '2024-12-31')
    ->get();
```

### Sticky Posts

```php
// Only sticky posts
$sticky = Skin::query()
    ->sticky()
    ->get();

// Exclude sticky posts
$posts = Skin::query()
    ->notSticky()
    ->get();
```

### Return Methods

```php
// Get array of posts
$posts = Skin::query()->get();

// Get first post
$post = Skin::query()->first();

// Find by ID
$post = Skin::query()->find(123);

// Count posts
$count = Skin::query()
    ->post_type('product')
    ->count();

// Check if posts exist
if (Skin::query()->post_type('product')->exists()) {
    // Has products
}

// Get only IDs
$ids = Skin::query()->ids();

// Get paginated results
$result = Skin::query()->paginate();
// Returns object with:
// - posts: array of posts
// - total: total posts found
// - pages: total pages
// - current_page: current page
// - per_page: posts per page
```

## User Queries

### Basic Usage

```php
// Get users
$users = Skin::users()
    ->limit(10)
    ->get();

// Get single user
$user = Skin::users()
    ->find(1); // By ID

// Find by email
$user = Skin::users()
    ->findByEmail('john@example.com');

// Find by login
$user = Skin::users()
    ->findByLogin('john');
```

### Role Filtering

```php
// Single role
$subscribers = Skin::users()
    ->role('subscriber')
    ->get();

// Multiple roles
$staff = Skin::users()
    ->role(['editor', 'author'])
    ->get();

// Exclude roles
$nonAdmins = Skin::users()
    ->notRole('administrator')
    ->get();
```

### User Meta

```php
// Meta query
$active = Skin::users()
    ->meta('active', true)
    ->get();

// Check if meta exists
$verified = Skin::users()
    ->metaExists('email_verified')
    ->get();

// Numeric meta comparison
$vip = Skin::users()
    ->metaNumeric('points', 1000, '>=')
    ->get();
```

### Search Users

```php
// Search by keyword
$users = Skin::users()
    ->search('john')
    ->get();

// Search in specific columns
$users = Skin::users()
    ->search('john')
    ->searchColumns(['user_email', 'display_name'])
    ->get();

// By email
$users = Skin::users()
    ->email('john@example.com')
    ->get();
```

### Registration Date

```php
// Recent registrations (last 30 days)
$newUsers = Skin::users()
    ->recentlyRegistered(30)
    ->get();

// Registered after date
$users = Skin::users()
    ->registeredAfter('2024-01-01')
    ->get();

// Registered before date
$oldUsers = Skin::users()
    ->registeredBefore('2023-01-01')
    ->get();
```

### Capabilities

```php
// Users who can edit posts
$editors = Skin::users()
    ->can('edit_posts')
    ->get();

// Users who cannot publish
$limited = Skin::users()
    ->cannot('publish_posts')
    ->get();
```

### Users with Posts

```php
// Users with published posts
$authors = Skin::users()
    ->hasPublishedPosts()
    ->get();

// Users with specific post type
$productAuthors = Skin::users()
    ->hasPublishedPostsType('product')
    ->get();
```

## Common Examples

### Featured Products

```php
$products = Skin::query()
    ->post_type('product')
    ->meta('featured', true)
    ->meta('in_stock', true)
    ->orderBy('menu_order', 'asc')
    ->limit(4)
    ->get();
```

### Recent Blog Posts with Category

```php
$posts = Skin::query()
    ->post_type('post')
    ->category('news')
    ->recent(30) // Last 30 days
    ->limit(5)
    ->get();
```

### Team Members

```php
$team = Skin::query()
    ->post_type('team_member')
    ->meta('active', true)
    ->orderBy('menu_order', 'asc')
    ->all()
    ->get();
```

### Event Calendar

```php
$events = Skin::query()
    ->post_type('event')
    ->metaDate('event_date', date('Y-m-d'), '>=')
    ->orderBy('meta_value', 'asc')
    ->where('meta_key', 'event_date')
    ->limit(10)
    ->get();
```

### Active Subscribers

```php
$subscribers = Skin::users()
    ->role('subscriber')
    ->meta('subscription_status', 'active')
    ->metaDate('subscription_expires', date('Y-m-d'), '>')
    ->orderBy('registered', 'desc')
    ->get();
```

## Advanced Usage

### Collection Methods

```php
// Execute callback for each post
Skin::query()
    ->post_type('product')
    ->each(function($post) {
        update_post_meta($post->ID, 'processed', true);
    });

// Map posts
$titles = Skin::query()
    ->limit(10)
    ->map(function($post) {
        return $post->post_title;
    });

// Filter posts
$published = Skin::query()
    ->all()
    ->filter(function($post) {
        return $post->post_status === 'publish';
    });

// Convert to array
$data = Skin::query()
    ->limit(5)
    ->toArray(['ID', 'post_title', 'post_date']);
```

### Custom Query Parameters

```php
// Add any WP_Query parameter
$posts = Skin::query()
    ->where('meta_key', 'custom_field')
    ->where('meta_value_num', 100)
    ->where('meta_compare', '>')
    ->get();
```

### Debug Queries

```php
// Get WP_Query object
$query = Skin::query()
    ->post_type('product')
    ->debug();

// Get SQL
echo $query->request;

// Get query vars
print_r($query->query_vars);
```

### Raw Arguments

```php
// Get raw query arguments
$args = Skin::query()
    ->post_type('product')
    ->meta('featured', true)
    ->getArgs();

// Use with WP_Query directly
$query = new WP_Query($args);
```

## Comparison with WP_Query

### Traditional WP_Query

```php
// Complex and hard to read
$args = [
    'post_type' => 'product',
    'posts_per_page' => 10,
    'post_status' => 'publish',
    'meta_query' => [
        [
            'key' => 'featured',
            'value' => true,
            'compare' => '='
        ],
        [
            'key' => 'price',
            'value' => 100,
            'compare' => '>=',
            'type' => 'NUMERIC'
        ]
    ],
    'tax_query' => [
        [
            'taxonomy' => 'product_category',
            'field' => 'slug',
            'terms' => 'electronics'
        ]
    ],
    'orderby' => 'date',
    'order' => 'DESC'
];

$query = new WP_Query($args);
$posts = $query->posts;
```

### WP-Skin Query Builder

```php
// Clean and intuitive
$posts = Skin::query()
    ->post_type('product')
    ->meta('featured', true)
    ->metaNumeric('price', 100, '>=')
    ->tax('product_category', 'electronics')
    ->orderBy('date', 'desc')
    ->limit(10)
    ->get();
```

## Best Practices

1. **Use specific methods**: Instead of `where()`, use specific methods like `meta()`, `tax()`, etc.
2. **Chain methods**: Build queries step by step for readability
3. **Check existence**: Use `exists()` before processing results
4. **Limit results**: Always set a reasonable limit unless you need all posts
5. **Use first()**: When you only need one result, use `first()` instead of `get()[0]`
6. **Debug carefully**: Use `debug()` to see the actual query being run

## Performance Tips

- Use `ids()` when you only need post IDs
- Use `count()` instead of `count(get())` for counting
- Use `fields()` for users when you don't need all data
- Add indexes to meta_keys you query frequently
- Use caching for expensive queries