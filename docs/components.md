# Component System

WP-Skin provides a powerful component system with auto-discovery, making it easy to create reusable template parts for your WordPress theme.

## Auto-Discovery

Components are automatically discovered and registered from the `resources/components/` directory. No manual registration required!

### Directory Structure

```
resources/components/
├── button.php
├── card.php
├── navigation/
│   ├── menu.php
│   └── breadcrumbs.php
└── forms/
    ├── contact.php
    └── search.php
```

### Component Files

Components are PHP files that contain template markup:

```php
<?php
// resources/components/button.php
$classes = $classes ?? 'bg-blue-500 hover:bg-blue-600 text-white';
$text = $text ?? 'Click me';
$type = $type ?? 'button';
?>
<button type="<?php echo esc_attr($type); ?>" class="px-4 py-2 rounded <?php echo esc_attr($classes); ?>">
    <?php echo esc_html($text); ?>
</button>
```

## Using Components

### Basic Usage

Use the `skin_component()` helper function to render components:

```php
<?php
// Render a simple component
skin_component('button');

// Pass data to component
skin_component('button', [
    'text' => 'Get Started',
    'classes' => 'bg-green-500 hover:bg-green-600'
]);

// Nested component paths
skin_component('navigation/menu', [
    'menu' => 'primary'
]);
?>
```

### In WordPress Templates

```php
<?php
// wp-content/themes/your-theme/single.php
get_header();
?>

<main class="container mx-auto px-4 py-8">
    <article class="max-w-4xl mx-auto">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            
            <?php 
            // Render custom card component for post
            skin_component('card', [
                'title' => get_the_title(),
                'content' => get_the_content(),
                'date' => get_the_date(),
                'author' => get_the_author()
            ]); 
            ?>
            
            <?php
            // Render navigation buttons
            skin_component('button', [
                'text' => 'Previous Post',
                'classes' => 'mr-4'
            ]);
            
            skin_component('button', [
                'text' => 'Next Post',
                'classes' => 'bg-green-500 hover:bg-green-600'
            ]);
            ?>
            
        <?php endwhile; endif; ?>
    </article>
</main>

<?php get_footer(); ?>
```

## Advanced Components

### Component with Logic

```php
<?php
// resources/components/post-meta.php
$post = $post ?? get_post();
$show_author = $show_author ?? true;
$show_date = $show_date ?? true;
$show_category = $show_category ?? true;
?>

<div class="post-meta text-sm text-gray-600 mb-4 flex items-center space-x-4">
    <?php if ($show_author) : ?>
        <span class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>
            <?php echo esc_html(get_the_author_meta('display_name', $post->post_author)); ?>
        </span>
    <?php endif; ?>
    
    <?php if ($show_date) : ?>
        <span class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
            <?php echo esc_html(get_the_date('F j, Y', $post)); ?>
        </span>
    <?php endif; ?>
    
    <?php if ($show_category) : ?>
        <?php $categories = get_the_category($post->ID); ?>
        <?php if (!empty($categories)) : ?>
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>
                </svg>
                <?php echo esc_html($categories[0]->name); ?>
            </span>
        <?php endif; ?>
    <?php endif; ?>
</div>
```

### Navigation Component

```php
<?php
// resources/components/navigation/menu.php
$menu_location = $menu_location ?? 'primary';
$container_class = $container_class ?? 'flex space-x-6';
$link_class = $link_class ?? 'text-gray-700 hover:text-blue-600 font-medium';
?>

<nav class="<?php echo esc_attr($container_class); ?>">
    <?php
    wp_nav_menu([
        'theme_location' => $menu_location,
        'container' => false,
        'menu_class' => '',
        'fallback_cb' => false,
        'walker' => new class extends Walker_Nav_Menu {
            function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
                global $link_class;
                $classes = empty($item->classes) ? [] : (array) $item->classes;
                $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
                
                $output .= '<a href="' . esc_url($item->url) . '" class="' . esc_attr($link_class) . '">';
                $output .= esc_html($item->title);
                $output .= '</a>';
            }
        }
    ]);
    ?>
</nav>
```

### Form Components

```php
<?php
// resources/components/forms/contact.php
$form_id = $form_id ?? 'contact-form';
$action = $action ?? admin_url('admin-post.php');
$button_text = $button_text ?? 'Send Message';
?>

<form id="<?php echo esc_attr($form_id); ?>" action="<?php echo esc_url($action); ?>" method="POST" class="space-y-6">
    <input type="hidden" name="action" value="contact_form_submit">
    <?php wp_nonce_field('contact_form_nonce', 'contact_nonce'); ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
            <input type="text" id="name" name="name" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input type="email" id="email" name="email" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>
    
    <div>
        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
        <input type="text" id="subject" name="subject" required 
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    
    <div>
        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
        <textarea id="message" name="message" rows="5" required 
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
    </div>
    
    <?php 
    skin_component('button', [
        'text' => $button_text,
        'type' => 'submit',
        'classes' => 'w-full bg-blue-500 hover:bg-blue-600 text-white py-3'
    ]); 
    ?>
</form>
```

## Data Providers

Register global data providers for your components using the Skin API:

```php
<?php
// In your skin.php or functions.php
use WordPressSkin\Core\Skin;

// Global site data provider
Skin::components()
    ->data('site', function() {
        return [
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'logo' => get_theme_mod('custom_logo'),
            'url' => home_url()
        ];
    });

// User data provider
Skin::components()
    ->data('user', function() {
        $user = wp_get_current_user();
        return [
            'logged_in' => is_user_logged_in(),
            'name' => $user->display_name ?? '',
            'avatar' => get_avatar_url($user->ID ?? 0),
            'can_edit' => current_user_can('edit_posts')
        ];
    });

// Theme customizer data
Skin::components()
    ->data('theme', function() {
        return [
            'primary_color' => get_theme_mod('primary_color', '#3B82F6'),
            'secondary_color' => get_theme_mod('secondary_color', '#EF4444'),
            'font_family' => get_theme_mod('font_family', 'system-ui')
        ];
    });
?>
```

### Using Data Providers in Components

Data from providers is automatically available in components:

```php
<?php
// resources/components/site-header.php
// $site data is automatically available from the 'site' data provider
?>

<header class="bg-white shadow-md">
    <div class="container mx-auto px-4 py-6 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <?php if ($site['logo']) : ?>
                <img src="<?php echo esc_url(wp_get_attachment_image_src($site['logo'], 'full')[0]); ?>" 
                     alt="<?php echo esc_attr($site['name']); ?>" class="h-8">
            <?php endif; ?>
            
            <div>
                <h1 class="text-xl font-bold text-gray-900"><?php echo esc_html($site['name']); ?></h1>
                <?php if ($site['description']) : ?>
                    <p class="text-sm text-gray-600"><?php echo esc_html($site['description']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="flex items-center space-x-4">
            <?php if ($user['logged_in']) : ?>
                <span class="text-sm text-gray-600">Hello, <?php echo esc_html($user['name']); ?></span>
                <a href="<?php echo wp_logout_url(); ?>" class="text-blue-600 hover:text-blue-800">Logout</a>
            <?php else : ?>
                <a href="<?php echo wp_login_url(); ?>" class="text-blue-600 hover:text-blue-800">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
```

## Component Organization

### Naming Conventions

- Use kebab-case for component files: `hero-section.php`
- Use descriptive names: `post-grid.php` instead of `grid.php`
- Group related components in subdirectories

### Directory Structure

```
resources/components/
├── layout/
│   ├── header.php
│   ├── footer.php
│   ├── sidebar.php
│   └── hero-section.php
├── content/
│   ├── post-card.php
│   ├── post-grid.php
│   ├── post-meta.php
│   └── pagination.php
├── navigation/
│   ├── main-menu.php
│   ├── breadcrumbs.php
│   └── social-links.php
├── forms/
│   ├── contact.php
│   ├── search.php
│   └── newsletter.php
└── ui/
    ├── button.php
    ├── modal.php
    ├── alert.php
    └── card.php
```

## Best Practices

### 1. Keep Components Focused

Each component should have a single responsibility:

```php
// Good - focused component
// resources/components/ui/button.php

// Bad - component doing too much
// resources/components/contact-form-with-validation-and-email.php
```

### 2. Use Default Values

Always provide sensible defaults:

```php
<?php
// resources/components/ui/card.php
$title = $title ?? '';
$content = $content ?? '';
$image = $image ?? '';
$link = $link ?? '';
$classes = $classes ?? 'bg-white rounded-lg shadow-md p-6';
?>
```

### 3. Sanitize Output

Always sanitize dynamic content:

```php
<?php
// resources/components/post-title.php
$title = $title ?? get_the_title();
$link = $link ?? get_permalink();
$tag = $tag ?? 'h2';
?>

<<?php echo esc_html($tag); ?> class="text-2xl font-bold mb-4">
    <?php if ($link) : ?>
        <a href="<?php echo esc_url($link); ?>" class="text-gray-900 hover:text-blue-600">
            <?php echo esc_html($title); ?>
        </a>
    <?php else : ?>
        <?php echo esc_html($title); ?>
    <?php endif; ?>
</<?php echo esc_html($tag); ?>>
```

### 4. Make Components Flexible

Use conditional rendering and flexible parameters:

```php
<?php
// resources/components/ui/alert.php
$type = $type ?? 'info'; // info, success, warning, error
$message = $message ?? '';
$dismissible = $dismissible ?? false;

$type_classes = [
    'info' => 'bg-blue-100 border-blue-400 text-blue-700',
    'success' => 'bg-green-100 border-green-400 text-green-700',
    'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
    'error' => 'bg-red-100 border-red-400 text-red-700'
];

$classes = $type_classes[$type] ?? $type_classes['info'];
?>

<div class="border-l-4 p-4 mb-4 <?php echo esc_attr($classes); ?>" role="alert">
    <div class="flex justify-between items-start">
        <p><?php echo esc_html($message); ?></p>
        <?php if ($dismissible) : ?>
            <button type="button" class="ml-4 text-current opacity-75 hover:opacity-100" onclick="this.parentElement.parentElement.remove();">
                <span class="sr-only">Close</span>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        <?php endif; ?>
    </div>
</div>
```

## Component Examples

The documentation above provides comprehensive examples of reusable components including:

- **Layout components** - headers, footers, hero sections, containers
- **Content components** - post cards, grids, meta information, pagination
- **Form components** - contact forms, search widgets, newsletter signups
- **UI components** - buttons, modals, alerts, badges, cards
- **Navigation components** - menus, breadcrumbs, social links

All components follow the Tailwind-first, auto-discovery approach with proper data handling and WordPress integration.