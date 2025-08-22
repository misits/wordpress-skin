# Custom Post Types & Taxonomies

WP-Skin provides an intuitive API for registering Custom Post Types (CPTs) and Taxonomies with minimal configuration.

## Custom Post Types

### Flexible Post Type Registration

WP-Skin provides a simple `add()` method for registering any post type you need:

```php
<?php
// In your skin.php file
use WordPressSkin\Core\Skin;

// Create any post types you need - you choose the names!
Skin::postTypes()
    ->add('portfolio', 'Portfolio Item', 'Portfolio')        // Portfolio
    ->add('service', 'Service', 'Services')                  // Services
    ->add('testimonial', 'Testimonial', 'Testimonials')      // Testimonials
    ->add('team_member', 'Team Member', 'Team')              // Team members
    ->add('project', 'Project', 'Projects')                  // Projects
    ->add('product', 'Product', 'Products');                 // Products
```

### Custom Configuration

For more control, use the `add()` method:

```php
<?php
Skin::postTypes()
    ->add('project', 'Project', 'Projects', [
        'menu_icon' => 'dashicons-hammer',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'projects']
    ]);
```

### Advanced Registration

For complete control, use the `register()` method:

```php
<?php
Skin::postTypes()
    ->register('event', [
        'labels' => [
            'name' => 'Events',
            'singular_name' => 'Event',
            'menu_name' => 'Events',
            'add_new' => 'Add New Event',
            'edit_item' => 'Edit Event'
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'events']
    ]);
```

## Custom Post Type Examples

### Portfolio

```php
<?php
Skin::postTypes()->add('portfolio', 'Portfolio Item', 'Portfolio', [
    'menu_icon' => 'dashicons-portfolio',
    'rewrite' => ['slug' => 'work'],                          // Custom slug
    'menu_position' => 20,                                    // Menu position
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt']
]);
```

### Services

```php
<?php
Skin::postTypes()->add('service', 'Service', 'Services', [
    'menu_icon' => 'dashicons-admin-tools',
    'has_archive' => false,                                   // Disable archive
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt']
]);
```

### Team Members

```php
<?php
Skin::postTypes()->add('team_member', 'Team Member', 'Team', [
    'menu_icon' => 'dashicons-groups',
    'rewrite' => ['slug' => 'our-team'],
    'supports' => ['title', 'editor', 'thumbnail', 'custom-fields']
]);
```

### Testimonials

```php
<?php
Skin::postTypes()->add('testimonial', 'Testimonial', 'Testimonials', [
    'menu_icon' => 'dashicons-testimonial',
    'publicly_queryable' => false,                           // Hide from front-end queries
    'supports' => ['title', 'editor', 'thumbnail', 'custom-fields']
]);
```

**All post types get these defaults automatically:**
- Public: true
- Archive page: enabled
- REST API: enabled
- Admin UI: enabled
- Standard WordPress capabilities

## Taxonomies

### Flexible Taxonomy Registration

WP-Skin provides a flexible `add()` method for registering any taxonomy:

```php
<?php
Skin::taxonomies()
    // Built-in categories and tags
    ->categories(['portfolio', 'service'])                                   // Categories for multiple post types
    ->tags(['portfolio'])                                                   // Tags for portfolio
    
    // Custom taxonomies - you choose the names!
    ->add('skill', ['portfolio', 'team_member'], 'Skill', 'Skills')         // Any taxonomy name you want
    ->add('industry', ['portfolio', 'service'], 'Industry', 'Industries')   // Flexible naming
    ->add('location', ['team_member'], 'Location', 'Locations')             // Your taxonomy, your choice
    ->add('difficulty', ['project'], 'Difficulty', 'Difficulties')          // Completely custom
    ->add('color', ['product'], 'Color', 'Colors');                        // Whatever makes sense for your site
```

### Custom Taxonomies

Create custom taxonomies with the `add()` method:

```php
<?php
Skin::taxonomies()
    ->add('difficulty', 'project', 'Difficulty Level', 'Difficulty Levels', [
        'hierarchical' => true,
        'show_admin_column' => true,
        'rewrite' => ['slug' => 'difficulty']
    ]);
```

### Advanced Registration

For complete control:

```php
<?php
Skin::taxonomies()
    ->register('project_status', 'project', [
        'labels' => [
            'name' => 'Project Status',
            'singular_name' => 'Status',
            'menu_name' => 'Status'
        ],
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => false,
        'show_tagcloud' => false,
        'rewrite' => ['slug' => 'status']
    ]);
```

## Built-in Taxonomy Types

### Categories

Creates hierarchical categories for specified post types:

```php
<?php
// Single post type
Skin::taxonomies()->categories('portfolio');

// Multiple post types
Skin::taxonomies()->categories(['portfolio', 'service', 'product']);

// With custom configuration
Skin::taxonomies()->categories('portfolio', [
    'rewrite' => ['slug' => 'portfolio-categories'],
    'show_admin_column' => false
]);
```

### Tags

Creates non-hierarchical tags for specified post types:

```php
<?php
// Single post type
Skin::taxonomies()->tags('portfolio');

// Multiple post types with configuration
Skin::taxonomies()->tags(['portfolio', 'service'], [
    'rewrite' => ['slug' => 'portfolio-tags']
]);
```

### Custom Taxonomies with add()

Create any taxonomy you need with the flexible `add()` method:

```php
<?php
// Skills taxonomy (non-hierarchical)
Skin::taxonomies()->add('skill', ['portfolio', 'team_member'], 'Skill', 'Skills', [
    'hierarchical' => false,
    'show_admin_column' => true,
    'show_in_nav_menus' => true
]);

// Industry taxonomy (hierarchical)
Skin::taxonomies()->add('industry', ['portfolio', 'service'], 'Industry', 'Industries', [
    'hierarchical' => true,
    'rewrite' => ['slug' => 'sectors']
]);

// Location taxonomy
Skin::taxonomies()->add('location', ['team_member', 'service'], 'Location', 'Locations', [
    'hierarchical' => true,
    'show_admin_column' => true
]);

// Any custom taxonomy you can imagine
Skin::taxonomies()->add('difficulty_level', 'project', 'Difficulty', 'Difficulty Levels', [
    'hierarchical' => false,
    'show_admin_column' => true
]);
```

## Complete Example

Here's a complete example for a design agency:

```php
<?php
// In your skin.php file
use WordPressSkin\Core\Skin;

// Register Custom Post Types
Skin::postTypes()
    ->add('portfolio', 'Portfolio Item', 'Portfolio', [
        'menu_icon' => 'dashicons-portfolio',
        'rewrite' => ['slug' => 'work'],
        'menu_position' => 20
    ])
    ->add('service', 'Service', 'Services', [
        'menu_icon' => 'dashicons-admin-tools', 
        'rewrite' => ['slug' => 'what-we-do']
    ])
    ->add('team_member', 'Team Member', 'Team', [
        'menu_icon' => 'dashicons-groups',
        'rewrite' => ['slug' => 'our-team']
    ])
    ->add('testimonial', 'Testimonial', 'Testimonials', [
        'menu_icon' => 'dashicons-testimonial'
    ]);

// Register Taxonomies
Skin::taxonomies()
    // Portfolio taxonomies
    ->categories('portfolio', ['rewrite' => ['slug' => 'work-categories']])
    ->add('skill', 'portfolio', 'Skill', 'Skills')
    ->add('industry', 'portfolio', 'Industry', 'Industries')
    
    // Service taxonomies
    ->categories('service', ['rewrite' => ['slug' => 'service-categories']])
    ->add('industry', 'service', 'Industry', 'Industries')  // Can share taxonomies across post types
    
    // Team taxonomies
    ->add('department', 'team_member', 'Department', 'Departments')
    ->add('skill', 'team_member', 'Skill', 'Skills')        // Skills shared between portfolio and team
    ->add('location', 'team_member', 'Location', 'Locations')
    
    // Custom project status taxonomy
    ->add('project_status', 'portfolio', 'Status', 'Statuses', [
        'hierarchical' => false,
        'show_admin_column' => true,
        'meta_box_cb' => 'post_categories_meta_box' // Radio buttons instead of checkbox
    ]);
```

## Using in Templates

### Query Custom Post Types

```php
<?php
// Single post type query
$portfolio_items = new WP_Query([
    'post_type' => 'portfolio',
    'posts_per_page' => 6,
    'meta_key' => '_thumbnail_id' // Only posts with featured images
]);

// Multiple post types
$content_items = new WP_Query([
    'post_type' => ['portfolio', 'service'],
    'posts_per_page' => 10
]);
?>
```

### Query by Taxonomy

```php
<?php
// Query by taxonomy terms
$web_projects = new WP_Query([
    'post_type' => 'portfolio',
    'tax_query' => [
        [
            'taxonomy' => 'skill',
            'field' => 'slug',
            'terms' => 'web-development'
        ]
    ]
]);

// Query by multiple taxonomies
$complex_query = new WP_Query([
    'post_type' => 'portfolio',
    'tax_query' => [
        'relation' => 'AND',
        [
            'taxonomy' => 'industry',
            'field' => 'slug',
            'terms' => 'technology'
        ],
        [
            'taxonomy' => 'skill',
            'field' => 'slug',
            'terms' => ['php', 'javascript'],
            'operator' => 'IN'
        ]
    ]
]);
?>
```

### Display Taxonomy Terms

```php
<?php
// In single post templates
$skills = get_the_terms(get_the_ID(), 'skill');
if ($skills && !is_wp_error($skills)) : ?>
    <div class="skills flex flex-wrap gap-2 mt-4">
        <?php foreach ($skills as $skill) : ?>
            <a href="<?php echo esc_url(get_term_link($skill)); ?>" 
               class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full hover:bg-blue-200 transition-colors">
                <?php echo esc_html($skill->name); ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

### Archive Templates

Create archive templates for your custom post types:

**archive-portfolio.php**
```php
<?php get_header(); ?>

<main class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Portfolio</h1>
    
    <?php if (have_posts()) : ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php while (have_posts()) : the_post(); ?>
                <article class="bg-white rounded-lg shadow-md overflow-hidden">
                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('medium', ['class' => 'w-full h-48 object-cover']); ?>
                        </a>
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-2">
                            <a href="<?php the_permalink(); ?>" class="text-gray-900 hover:text-blue-600">
                                <?php the_title(); ?>
                            </a>
                        </h2>
                        
                        <?php the_excerpt(); ?>
                        
                        <?php
                        // Display skills
                        $skills = get_the_terms(get_the_ID(), 'skill');
                        if ($skills && !is_wp_error($skills)) : ?>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <?php foreach ($skills as $skill) : ?>
                                    <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                                        <?php echo esc_html($skill->name); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        
        <?php the_posts_pagination([
            'class' => 'mt-12',
            'prev_text' => '← Previous',
            'next_text' => 'Next →'
        ]); ?>
    <?php else : ?>
        <p class="text-gray-600">No portfolio items found.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
```

## WordPress ORM Integration

If using WordPress ORM, you can create models for your custom post types:

```php
<?php
// Create a Portfolio model
namespace App\Models;

use WordpressORM\Models\Post;

class Portfolio extends Post {
    protected $post_type = 'portfolio';
    
    // Get skills for this portfolio item
    public function skills() {
        return get_the_terms($this->ID, 'skill') ?: [];
    }
    
    // Get industry for this portfolio item  
    public function industry() {
        $terms = get_the_terms($this->ID, 'industry');
        return $terms && !is_wp_error($terms) ? $terms[0] : null;
    }
}

// Usage
$portfolio_items = Portfolio::where('post_status', 'publish')->get();
foreach ($portfolio_items as $item) {
    echo $item->post_title;
    foreach ($item->skills() as $skill) {
        echo $skill->name;
    }
}
```

## Best Practices

1. **Use descriptive slugs** - Make URLs SEO-friendly
2. **Enable REST API** - For headless WordPress or AJAX functionality
3. **Choose appropriate supports** - Only include what you need
4. **Use hierarchical taxonomies** - For categories and organizational terms
5. **Use non-hierarchical taxonomies** - For tags and descriptive terms
6. **Show admin columns** - For quick overview in admin lists
7. **Set proper capabilities** - Control who can edit your post types
8. **Use consistent naming** - Follow WordPress naming conventions

## Troubleshooting

### Post Type Not Appearing

1. **Flush rewrite rules**: Go to Settings > Permalinks and click "Save Changes"
2. **Check post type arguments**: Ensure `public` is true
3. **Verify capabilities**: Make sure current user has proper permissions

### Taxonomy Terms Not Saving

1. **Check taxonomy registration**: Ensure it's attached to the correct post type
2. **Verify capabilities**: Check user permissions for taxonomy
3. **Clear object cache**: If using object caching, clear it

### Archive Pages Not Working

1. **Set `has_archive` to true** in post type registration
2. **Create archive template**: Create `archive-{post_type}.php`
3. **Flush permalinks**: Visit Settings > Permalinks

## Translation Support

Both post types and taxonomies support WordPress translations out of the box.

### Text Domains

By default, WP-Skin uses the `'wp-skin'` text domain. You can customize this:

#### Per-Registration Text Domain

```php
<?php
// Custom text domain per post type
Skin::postTypes()->add('portfolio', 'Portfolio Item', 'Portfolio', [
    'menu_icon' => 'dashicons-portfolio'
], 'my-theme'); // Custom text domain

// Custom text domain per taxonomy  
Skin::taxonomies()->add('skill', 'portfolio', 'Skill', 'Skills', [
    'hierarchical' => false
], 'my-theme'); // Custom text domain
```

#### Global Text Domain

```php
<?php
// Set global text domain for all post types
Skin::postTypes()
    ->setTextDomain('my-theme')
    ->add('portfolio', 'Portfolio Item', 'Portfolio')
    ->add('service', 'Service', 'Services');

// Set global text domain for all taxonomies
Skin::taxonomies()
    ->setTextDomain('my-theme')
    ->add('skill', 'portfolio', 'Skill', 'Skills')
    ->add('industry', 'service', 'Industry', 'Industries');
```

### Translation Strings

WP-Skin automatically makes all labels translatable. The following strings are available for translation:

#### Post Type Labels

- `'Add New'` - Generic "Add New" button
- `'Add New %s'` - "Add New Portfolio Item" 
- `'Edit %s'` - "Edit Portfolio Item"
- `'New %s'` - "New Portfolio Item"
- `'View %s'` - "View Portfolio Item"
- `'Search %s'` - "Search Portfolio"
- `'No %s found'` - "No portfolio items found"
- `'No %s found in Trash'` - "No portfolio items found in Trash"
- `'Featured Image'`, `'Set featured image'`, etc.

#### Taxonomy Labels

- `'All %s'` - "All Skills"
- `'Edit %s'` - "Edit Skill"
- `'Add New %s'` - "Add New Skill"  
- `'New %s Name'` - "New Skill Name"
- `'Search %s'` - "Search Skills"
- `'Popular %s'` - "Popular Skills"
- `'No %s found'` - "No skills found"

### Creating Translation Files

1. **Generate POT file** from your labels:
```bash
wp i18n make-pot . languages/my-theme.pot --domain=my-theme
```

2. **Create language files** (e.g., `languages/my-theme-fr_FR.po`):
```po
msgid "Portfolio Item"
msgstr "Élément de Portfolio"

msgid "Add New %s"
msgstr "Ajouter un nouvel %s"

msgid "No %s found"
msgstr "Aucun %s trouvé"
```

3. **Load translations** in your theme:
```php
// In functions.php
add_action('after_setup_theme', function() {
    load_theme_textdomain('my-theme', get_template_directory() . '/languages');
});
```