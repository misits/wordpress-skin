# WordPress Skin (WP-Skin)

A minimal WordPress theme framework powered by **Tailwind CSS v4** with auto-discovery components, modern build tools, and zero-configuration setup.

## Features

- 🎨 **Tailwind-First** - Built around Tailwind CSS v4 utilities
- 🧩 **Auto-Discovery Components** - PHP components automatically registered
- ⚡ **Modern Build Tools** - Vite with HMR and React/Vue support
- 📦 **Zero Configuration** - Convention over configuration
- 🔧 **Auto-Scaffolding** - Automatic directory structure creation
- 🎛️ **Fluent Customizer API** - Modern WordPress Customizer integration
- 🔒 **Security Hardening** - Built-in WordPress security features
- 🚀 **Production Ready** - Built-in deployment and optimization
- 🎛️ **Essential WordPress** - Only necessary WordPress features

## Installation

Include WP-Skin in your WordPress theme:

```php
// In your theme's functions.php
require_once get_template_directory() . '/lib/wp-skin/bootstrap.php';
```

## Zero-Configuration Setup

WP-Skin automatically scaffolds your theme structure and creates essential files:

### Theme Structure
```
wp-content/themes/your-theme/
├── lib/wp-skin/              # Framework
├── resources/                # Auto-created assets
│   ├── src/css/app.css      # Tailwind CSS
│   ├── src/js/app.js        # JavaScript entry
│   ├── components/          # PHP components (auto-discovered)
│   └── package.json         # Node dependencies
├── style.css                # WordPress theme file
├── skin.php                 # Theme configuration
└── functions.php            # WordPress functions
```

## Quick Start

### 1. Development
```bash
cd resources
npm install
npm run dev    # Start Vite dev server
```

### 2. Components (Auto-Discovered)
```php
// resources/components/button.php
<button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
    <?php echo esc_html($text ?? 'Click me'); ?>
</button>
```

```php
// Use in templates
skin_component('button', ['text' => 'Get Started']);
```

### 3. Theme Configuration (skin.php)
```php
use WordPressSkin\Core\Skin;

// Essential assets (auto-detected)
Skin::assets()
    ->css('style')
    ->js('main');

// Custom Post Types (optional)
Skin::postTypes()
    ->add('portfolio', 'Portfolio Item', 'Portfolio')         // Portfolio post type
    ->add('service', 'Service', 'Services')                   // Services post type
    ->add('project', 'Project', 'Projects');                  // Custom project post type

// Taxonomies (optional)
Skin::taxonomies()
    ->categories(['portfolio'])                                    // Categories for portfolio
    ->add('skill', 'portfolio', 'Skill', 'Skills')                // Custom skill taxonomy
    ->add('industry', 'portfolio', 'Industry', 'Industries');     // Custom industry taxonomy

// WordPress features
Skin::hooks()
    ->action('after_setup_theme', function() {
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');
        register_nav_menus(['primary' => 'Primary Navigation']);
    });
```

## Tailwind CSS Integration

### CSS Structure
```css
/* resources/src/css/app.css - Auto-created */
@import "tailwindcss";

/* Custom styles (keep minimal) */
body {
  font-family: system-ui, -apple-system, sans-serif;
}
```

### JavaScript Entry Point
```javascript
// resources/src/js/app.js - Auto-created
import '../css/app.css';  // Import Tailwind CSS

console.log('Theme loaded');
```

## WP-CLI Commands

```bash
# Build and deployment
wp skin:build                     # Build assets
wp skin:build --production        # Build for production
wp skin:deploy                    # Create production theme package

# Component scaffolding
wp skin:component navbar          # Create new component

# Theme information
wp skin:info                      # Show theme information
wp skin:status                    # Check theme status
```

## Documentation

For comprehensive documentation, see the [docs/](docs/) directory:

- [Installation](docs/installation.md) - Setup and configuration
- [Components](docs/components.md) - Component system and auto-discovery
- [Query Builder](docs/query-builder.md) - Eloquent-style database queries
- [Post Wrapper](docs/post-wrapper.md) - Object-oriented post interface
- [Customizer](docs/customizer.md) - WordPress Customizer integration with fluent API
- [Security](docs/security.md) - WordPress security hardening features
- [Custom Post Types & Taxonomies](docs/post-types-taxonomies.md) - CPTs and taxonomies made easy
- [Tailwind CSS](docs/tailwind.md) - Styling with Tailwind utilities
- [Modern JavaScript](docs/javascript.md) - React, Vue, and build tools
- [CLI Commands](docs/cli.md) - WP-CLI commands and deployment
- [Troubleshooting](docs/troubleshooting.md) - Common issues and solutions

## Philosophy

WP-Skin follows a **Tailwind-first, minimal configuration** approach:
- Use Tailwind utility classes instead of custom CSS
- Components auto-discovered, no manual registration
- Convention over configuration
- Essential WordPress features only

## Requirements

- WordPress 5.0+
- PHP 8.0+
- Node.js 18+
- WP-CLI 2.0+ (for CLI commands)

## License

Open-source software, following WordPress GPL licensing.
