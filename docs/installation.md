# Installation & Setup

This guide covers installing and configuring WP-Skin in your WordPress theme.

## Basic Installation

### 1. Include WP-Skin in Your Theme

Add WP-Skin to your theme's `functions.php`:

```php
<?php
// wp-content/themes/your-theme/functions.php


// Include WP-Skin
require_once get_template_directory() . '/vendor/wordpress-skin/bootstrap.php';
```

### 2. Auto-Scaffolding

WP-Skin automatically creates the necessary files and directories when first loaded:

- **Theme root files**: `style.css`, `header.php`, `footer.php`, `index.php`, `front-page.php`
- **Resources directory**: Complete build setup with `package.json`, Vite config, CSS/JS entry points
- **Component directory**: Ready for auto-discovery components
- **Configuration files**: `.gitignore`, `vite.config.mjs`

## Directory Structure

After installation, your theme will have this structure:

```
wp-content/themes/your-theme/
├── vendor/
│   └── wordpress-skin/                    # WP-Skin framework
├── resources/                      # Auto-created by WP-Skin
│   ├── src/
│   │   ├── css/
│   │   │   └── app.css            # Tailwind CSS entry
│   │   └── js/
│   │       └── app.js             # JavaScript entry
│   ├── components/                 # PHP components (auto-discovered)
│   ├── dist/                       # Built assets (auto-created)
│   ├── package.json               # Node dependencies
│   └── vite.config.mjs            # Vite build configuration
├── style.css                      # WordPress theme file
├── skin.php                       # Theme configuration (auto-created)
├── functions.php                   # Your theme functions
├── header.php                      # Theme header (auto-created)
├── footer.php                      # Theme footer (auto-created)
├── index.php                       # WordPress template (auto-created)
├── front-page.php                  # Homepage template (auto-created)
└── .gitignore                      # Git ignore rules (auto-created)
```

## Configuration Options

### Environment Variables

Set these constants in `wp-config.php` or your theme's `functions.php`:

```php
// Development mode - enables Vite dev server integration
define('WPSKIN_DEV', true);


// Disable auto-scaffolding (optional)
define('WPSKIN_NO_AUTO_SCAFFOLD', true);
```

### Development vs Production

WP-Skin automatically detects the environment:

**Development Mode** (uses Vite dev server):
- `WP_DEBUG` is true
- `WPSKIN_DEV` is defined and true
- `SCRIPT_DEBUG` is true

**Production Mode** (uses built assets):
- Assets built with `npm run build`
- Files served from `resources/dist/`

### Asset Configuration

Configure assets in your `skin.php` file:

```php
<?php
// wp-content/themes/your-theme/skin.php

use WordPressSkin\Core\Skin;

// CSS assets (auto-detected from resources/dist/)
Skin::assets()
    ->css('style')      // Loads resources/dist/style.css
    ->css('app');       // Loads resources/dist/app.css

// JavaScript assets (auto-detected from resources/dist/)
Skin::assets()
    ->js('main')        // Loads resources/dist/main.js
    ->js('app');        // Loads resources/dist/app.js

// WordPress theme support
Skin::hooks()
    ->action('after_setup_theme', function() {
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');
        add_theme_support('html5', [
            'search-form', 'comment-form', 'comment-list',
            'gallery', 'caption', 'style', 'script'
        ]);

        // Register navigation menus
        register_nav_menus([
            'primary' => 'Primary Navigation',
            'footer' => 'Footer Navigation'
        ]);
    });
```

## Node.js Setup

### 1. Install Dependencies

```bash
cd wp-content/themes/your-theme/resources
npm install
```

### 2. Development Server

Start the Vite development server with hot module reloading:

```bash
npm run dev
```

This starts a dev server at `http://localhost:5173` with:
- Automatic CSS injection
- JavaScript hot module reloading
- **PHP file watching** - Browser automatically refreshes when you edit PHP files (components, templates, theme files)

### 3. Production Build

Build optimized assets for production:

```bash
npm run build
```

Built files are output to `resources/dist/`.

## WordPress Integration

### Theme Requirements

Ensure your `style.css` has the proper WordPress theme header:

```css
/*
Theme Name: Your Theme Name
Description: Theme description
Version: 1.0.0
*/
```

### Essential WordPress Files

WP-Skin creates minimal WordPress template files if they don't exist:

**header.php**:
```php
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
```

**footer.php**:
```php
<?php wp_footer(); ?>
</body>
</html>
```

**index.php** (minimal fallback):
```php
<?php
// Silence is gold
```

**front-page.php** (homepage template):
```php
<?php get_header(); ?>
<main class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-6">Welcome</h1>
    <p class="text-lg">Your homepage content goes here.</p>
</main>
<?php get_footer(); ?>
```

## Customization

### Custom Skin Configuration

Create or modify `skin.php` to customize theme behavior:

```php
<?php
use WordPressSkin\Core\Skin;

// Customize WordPress Customizer
Skin::customizer()
    ->section('theme_options', 'Theme Options')
    ->setting('logo', 'text', 'Site Logo URL')
    ->setting('primary_color', 'color', 'Primary Color', '#3B82F6');

// Add custom hooks
Skin::hooks()
    ->action('wp_enqueue_scripts', function() {
        // Enqueue additional scripts or styles
        wp_enqueue_script('custom-script', get_template_directory_uri() . '/custom.js');
    })
    ->filter('wp_nav_menu_items', function($items, $args) {
        if ($args->theme_location === 'primary') {
            // Modify menu items
        }
        return $items;
    }, 10, 2);

// Component data providers
Skin::components()
    ->data('site_info', function() {
        return [
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'logo' => get_theme_mod('logo', '')
        ];
    });
```

### Custom Build Configuration

Modify `vite.config.mjs` for advanced build customization:

```javascript
import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'src/js/app.js'),
        style: resolve(__dirname, 'src/css/app.css'),
        // Add more entry points
        admin: resolve(__dirname, 'src/js/admin.js'),
      }
    }
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    cors: true
  },
  // Add framework-specific plugins
  plugins: [
    // For React: '@vitejs/plugin-react'
    // For Vue: '@vitejs/plugin-vue'
  ]
});
```

## Troubleshooting

### Common Issues

**Assets not loading in development**:
- Ensure Vite dev server is running (`npm run dev`)
- Check that `WPSKIN_DEV` is defined
- Verify CORS settings in `vite.config.mjs`

**Tailwind classes not working**:
- Ensure CSS file imports Tailwind: `@import "tailwindcss";`
- Rebuild assets and check CSS import
- Rebuild assets: `npm run build`

**Component functions not found**:
- Verify `skin_component()` function is available
- Check component file placement in `resources/components/`
- Ensure proper PHP syntax in component files

For more troubleshooting, see [troubleshooting.md](troubleshooting.md).
