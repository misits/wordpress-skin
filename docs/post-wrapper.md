# PostType Wrapper

WP-Skin provides a clean, object-oriented interface for working with WordPress posts, pages, and custom post types. The PostType wrapper eliminates the need for repetitive WordPress functions and provides a fluent API.

## Table of Contents

- [Overview](#overview)
- [Getting Started](#getting-started)
- [Basic Properties](#basic-properties)
- [Content Methods](#content-methods)
- [Meta Data](#meta-data)
  - [Type-Safe Meta Methods](#type-safe-meta-methods)
  - [Array/Row Management (ACF-Style)](#arrayrow-management-acf-style)
- [Taxonomies](#taxonomies)
- [Relationships](#relationships)
- [Images](#images)
- [Comments](#comments)
- [Utility Methods](#utility-methods)
- [Examples](#examples)

## Overview

Instead of using WordPress functions like `get_the_title()`, `get_the_content()`, `get_post_meta()`, etc., you can use the clean PostType wrapper:

```php
// Traditional WordPress way
$title = get_the_title($post_id);
$content = apply_filters('the_content', get_post_field('post_content', $post_id));
$featured_image = get_the_post_thumbnail_url($post_id, 'large');
$meta = get_post_meta($post_id, 'custom_field', true);

// WP-Skin PostType wrapper
$post = Skin::post($post_id);
$title = $post->title();
$content = $post->content();
$featured_image = $post->featuredImage('large');
$meta = $post->meta('custom_field');
```

## Getting Started

### Creating PostType Instances

```php
use WordPressSkin\Core\Skin;

// Current post/page
$current = Skin::current();

// By post ID
$post = Skin::post(123);

// Direct instantiation
$post = new PostType(123);
$post = new PostType($wp_post_object);
$post = new PostType(); // Current post
```

### Using with Callbacks

```php
// Transform current post
$data = Skin::current(function($post) {
    return [
        'title' => $post->title(),
        'excerpt' => $post->excerpt(20),
        'image' => $post->featuredImage('medium'),
        'url' => $post->url()
    ];
});

// Use in templates
Skin::current(function($post) {
    echo '<h1>' . $post->title() . '</h1>';
    echo '<div>' . $post->content() . '</div>';
});
```

## Basic Properties

### Post Information

```php
$post = Skin::post(123);

// Basic properties
echo $post->id();           // Post ID
echo $post->title();        // Post title (filtered)
echo $post->title(false);   // Raw title (unfiltered)
echo $post->slug();         // Post slug
echo $post->status();       // Post status
echo $post->type();         // Post type
echo $post->url();          // Permalink
echo $post->permalink();    // Alias for url()

// Status checks
if ($post->isPublished()) {
    echo 'Post is published';
}
```

### Dates

```php
// Formatted dates
echo $post->date();                    // Default format
echo $post->date('F j, Y');           // Custom format
echo $post->modified();               // Modified date
echo $post->modified('Y-m-d H:i:s');  // Custom format
```

### Author Information

```php
// Author object
$author = $post->author();
echo $author->display_name;

// Author shortcuts
echo $post->authorName();    // Display name
echo $post->authorUrl();     // Author archive URL
```

## Content Methods

### Post Content

```php
// Full content with filters applied
echo $post->content();

// Raw content without filters
echo $post->content(false);

// Excerpt
echo $post->excerpt();        // Auto excerpt
echo $post->excerpt(25);      // 25 words
```

### Content Processing

```php
// Check if post exists
if ($post->exists()) {
    echo $post->title();
}

// Convert to array
$data = $post->toArray();
$data = $post->toArray(['id', 'title', 'excerpt', 'url']);

// Convert to JSON
$json = $post->toJson();
```

## Meta Data

### Getting Meta

```php
// Single meta value
$price = $post->meta('price', 0);           // With default
$featured = $post->meta('featured');        // Default null
$gallery = $post->meta('gallery', [], false); // Array of values

// Check if meta exists
if ($post->meta('video_url')) {
    echo 'Has video';
}

// Get all meta
$all_meta = $post->allMeta();
```

### Updating Meta

```php
// Update meta
$post->updateMeta('price', 99.99);
$post->updateMeta('featured', true);

// Delete meta
$post->deleteMeta('old_field');
```

### Meta Caching

Meta values are automatically cached to prevent multiple database queries:

```php
// First call hits database
$price = $post->meta('price');

// Subsequent calls use cache
$price_again = $post->meta('price');

// Clear cache if needed
$post->clearAllMetaCache();
```

### Type-Safe Meta Methods

For better type safety and cleaner code, use typed meta methods:

```php
// Type-safe meta retrieval
$price = $post->getFloatMeta('price', 0.0);
$featured = $post->getBooleanMeta('featured', false);
$tags = $post->getArrayMeta('custom_tags', []);
$settings = $post->getJsonMeta('widget_settings', []);
$eventDate = $post->getDateMeta('event_date');

// Set JSON meta
$post->setJsonMeta('configuration', ['theme' => 'dark', 'lang' => 'en']);

// Set date meta
$post->setDateMeta('event_date', new DateTime('2024-12-25'));
```

### Array/Row Management (ACF-Style)

**Note**: These methods provide programmatic array/row management when ACF is not installed. They are not intended to replace ACF, but offer similar functionality for managing complex meta structures.

#### Basic Row Operations

```php
// Get row count
$galleryCount = $post->getCount('gallery');

// Get all rows
$gallery = $post->getRows('gallery');
// Returns: [
//   ['image' => 123, 'caption' => 'Photo 1', '_index' => 0],
//   ['image' => 456, 'caption' => 'Photo 2', '_index' => 1]
// ]

// Get specific row
$firstImage = $post->getRow('gallery', 0);
// Returns: ['image' => 123, 'caption' => 'Photo 1', '_index' => 0]

// Get only specific fields from rows
$captions = $post->getRows('gallery', ['caption']);
```

#### Adding and Updating Rows

```php
// Add new row
$newIndex = $post->addRow('gallery', [
    'image' => 789,
    'caption' => 'New photo'
]);

// Update specific row
$post->updateRow('gallery', 0, [
    'caption' => 'Updated caption'
]);

// Replace all rows
$post->updateRows('gallery', [
    ['image' => 111, 'caption' => 'First'],
    ['image' => 222, 'caption' => 'Second'],
    ['image' => 333, 'caption' => 'Third']
]);
```

#### Deleting Rows

```php
// Delete specific row (automatically reindexes remaining rows)
$post->deleteRow('gallery', 1);

// Clear all rows
$post->updateRows('gallery', []);
```

#### Working with Group Fields

```php
// Get group field data
$address = $post->getGroupField('contact', ['street', 'city', 'zip']);
// Returns: ['street' => '123 Main St', 'city' => 'Anytown', 'zip' => '12345']

// Update group field
$post->updateGroupField('contact', [
    'street' => '456 Oak Ave',
    'city' => 'New City',
    'phone' => '555-0123'
]);

// Delete entire group
$post->deleteGroupField('contact');
```

#### Practical Examples

**Photo Gallery Management:**

```php
// Add photo to gallery
$post->addRow('gallery', [
    'image_id' => 123,
    'caption' => 'Beautiful sunset',
    'alt_text' => 'Sunset over mountains'
]);

// Display gallery
$gallery = $post->getRows('gallery');
foreach ($gallery as $photo) {
    $imageUrl = wp_get_attachment_image_url($photo['image_id'], 'medium');
    echo '<figure>';
    echo '<img src="' . $imageUrl . '" alt="' . esc_attr($photo['alt_text']) . '">';
    echo '<figcaption>' . esc_html($photo['caption']) . '</figcaption>';
    echo '</figure>';
}
```

**Team Member Contact Info:**

```php
// Set team member details
$post->updateGroupField('contact_info', [
    'email' => 'john@example.com',
    'phone' => '555-0123',
    'linkedin' => 'https://linkedin.com/in/johndoe',
    'department' => 'Engineering'
]);

// Get contact info
$contact = $post->getGroupField('contact_info');
if (!empty($contact['email'])) {
    echo '<a href="mailto:' . $contact['email'] . '">Contact ' . $post->title() . '</a>';
}
```

**Product Features List:**

```php
// Add product features
$post->updateRows('features', [
    ['icon' => 'speed', 'title' => 'Fast Performance', 'description' => 'Lightning fast load times'],
    ['icon' => 'shield', 'title' => 'Secure', 'description' => 'Bank-level security'],
    ['icon' => 'mobile', 'title' => 'Mobile Ready', 'description' => 'Works on all devices']
]);

// Display features
$features = $post->getRows('features');
foreach ($features as $feature) {
    echo '<div class="feature">';
    echo '<i class="icon-' . $feature['icon'] . '"></i>';
    echo '<h3>' . $feature['title'] . '</h3>';
    echo '<p>' . $feature['description'] . '</p>';
    echo '</div>';
}
```

## Taxonomies

### Categories and Tags

```php
// Get categories
$categories = $post->categories();
foreach ($categories as $category) {
    echo $category->name;
}

// Get tags  
$tags = $post->tags();

// Custom taxonomy
$colors = $post->terms('product_color');

// Check if has term
if ($post->hasTerm('featured', 'product_category')) {
    echo 'Featured product';
}
```

## Relationships

### Parent/Child

```php
// Parent post
$parent = $post->parent();
if ($parent) {
    echo 'Parent: ' . $parent->title();
}

// Child posts
$children = $post->children();
foreach ($children as $child) {
    echo $child->title();
}

// Siblings
$siblings = $post->siblings();
$siblings = $post->siblings(true); // Include self
```

### Navigation

```php
// Next/previous posts
$next = $post->next();
$prev = $post->previous();

// Within same category
$next = $post->next(true, 'category');

// Related posts
$related = $post->related(5, 'category');
```

### Breadcrumbs

```php
// Get breadcrumb path
$breadcrumb = $post->breadcrumb();
foreach ($breadcrumb as $item) {
    echo '<a href="' . $item->url() . '">' . $item->title() . '</a> > ';
}
```

## Images

### Featured Images

```php
// Check if has featured image
if ($post->hasThumbnail()) {
    // Get image URL
    echo $post->thumbnail('large');
    echo $post->featuredImage('medium'); // Alias
    
    // Get image ID
    $image_id = $post->thumbnailId();
    
    // Get image HTML
    echo $post->thumbnailHtml('large', ['class' => 'featured-img']);
}
```

### Image Sizes

```php
// Different sizes
$thumb = $post->thumbnail('thumbnail');
$medium = $post->thumbnail('medium');
$large = $post->thumbnail('large');
$full = $post->thumbnail('full');

// Custom size
$custom = $post->thumbnail('custom-size');
```

## Comments

### Comment Information

```php
// Check if comments open
if ($post->commentsOpen()) {
    echo 'Comments enabled';
}

// Comment count
echo $post->commentCount() . ' comments';

// Get comments
$comments = $post->comments();
$approved = $post->comments(['status' => 'approve']);
```

## Utility Methods

### Permissions

```php
// Check if current user can edit
if ($post->canEdit()) {
    echo '<a href="' . $post->editLink() . '">Edit</a>';
}
```

### Raw Access

```php
// Get raw WP_Post object
$wp_post = $post->raw();

// Access WP_Post properties directly
echo $post->post_title;       // Via magic method
echo $post->raw()->post_title; // Via raw object
```

### String Conversion

```php
// PostType can be used as string
echo $post; // Outputs title

// Explicit conversion
echo (string) $post;
```

## Examples

### Blog Post Template

```php
$current = Skin::current();
?>
<article>
    <header>
        <h1><?php echo $current->title(); ?></h1>
        <div class="meta">
            <span>By <?php echo $current->authorName(); ?></span>
            <time><?php echo $current->date(); ?></time>
            <?php if ($current->commentCount() > 0): ?>
                <span><?php echo $current->commentCount(); ?> comments</span>
            <?php endif; ?>
        </div>
    </header>
    
    <?php if ($current->hasThumbnail()): ?>
        <div class="featured-image">
            <?php echo $current->thumbnailHtml('large'); ?>
        </div>
    <?php endif; ?>
    
    <div class="content">
        <?php echo $current->content(); ?>
    </div>
    
    <footer>
        <div class="categories">
            <?php foreach ($current->categories() as $category): ?>
                <a href="<?php echo get_category_link($category); ?>"><?php echo $category->name; ?></a>
            <?php endforeach; ?>
        </div>
        
        <?php if ($current->canEdit()): ?>
            <a href="<?php echo $current->editLink(); ?>">Edit</a>
        <?php endif; ?>
    </footer>
</article>
```

### Product Card Component

```php
// components/product-card.php
$product = Skin::post($product_id);
?>
<div class="product-card">
    <?php if ($product->hasThumbnail()): ?>
        <img src="<?php echo $product->featuredImage('medium'); ?>" alt="<?php echo $product->title(); ?>">
    <?php endif; ?>
    
    <h3><a href="<?php echo $product->url(); ?>"><?php echo $product->title(); ?></a></h3>
    
    <div class="price">$<?php echo $product->meta('price', '0'); ?></div>
    
    <?php if ($product->meta('featured')): ?>
        <span class="badge">Featured</span>
    <?php endif; ?>
    
    <p><?php echo $product->excerpt(15); ?></p>
</div>
```

### Related Posts Widget

```php
$current = Skin::current();
$related = $current->related(3, 'category');

if (!empty($related)):
?>
<aside class="related-posts">
    <h3>Related Posts</h3>
    <ul>
        <?php foreach ($related as $post): ?>
            <li>
                <a href="<?php echo $post->url(); ?>">
                    <?php echo $post->title(); ?>
                </a>
                <small><?php echo $post->date(); ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>
<?php endif; ?>
```

### Page Breadcrumb

```php
$current = Skin::current();
$breadcrumb = $current->breadcrumb();

if (count($breadcrumb) > 1):
?>
<nav class="breadcrumb">
    <?php foreach ($breadcrumb as $index => $item): ?>
        <?php if ($index === count($breadcrumb) - 1): ?>
            <span><?php echo $item->title(); ?></span>
        <?php else: ?>
            <a href="<?php echo $item->url(); ?>"><?php echo $item->title(); ?></a>
            <span> / </span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
<?php endif; ?>
```

### Data Export

```php
// Export post data for JavaScript
$current = Skin::current();
$data = $current->toArray([
    'id', 'title', 'excerpt', 'url', 'date', 
    'authorName', 'featuredImage'
]);

// Add custom meta
$data['price'] = $current->meta('price');
$data['gallery'] = $current->meta('gallery', [], false);

// Output as JSON
echo '<script>window.postData = ' . json_encode($data) . ';</script>';
```

## Integration with Query Builder

The PostType wrapper works seamlessly with the Query Builder:

```php
// Query returns PostType objects when using map()
$products = Skin::query()
    ->post_type('product')
    ->meta('featured', true)
    ->limit(4)
    ->map(function($wp_post) {
        return new PostType($wp_post);
    });

// Or create wrapper for each result
$posts = Skin::query()->limit(5)->get();
foreach ($posts as $wp_post) {
    $post = Skin::post($wp_post);
    echo $post->title();
}
```

## Best Practices

1. **Use caching**: Meta values are cached automatically, but create instances wisely
2. **Check existence**: Always check `$post->exists()` when post might not exist
3. **Use callbacks**: Use `Skin::current($callback)` for template transformations
4. **Combine with Query Builder**: Use together for powerful data handling
5. **Type hints**: Use `PostType` in function parameters for better IDE support

```php
function renderPost(PostType $post) {
    if (!$post->exists()) {
        return 'Post not found';
    }
    
    return $post->title() . ': ' . $post->excerpt(20);
}
```