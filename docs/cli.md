# WP-CLI Commands

WP-Skin provides a comprehensive set of WP-CLI commands for building assets, managing components, and deploying your theme.

## Available Commands

### Build Commands

#### `wp borps skin:build`

Builds assets for production or development.

```bash
# Build for development
wp borps skin:build

# Build for production
wp borps skin:build --production

# Build with specific environment
wp borps skin:build --env=staging
```

**Options:**
- `--production` - Build optimized assets for production
- `--env=<environment>` - Set build environment (development, production, staging)
- `--watch` - Watch for changes and rebuild automatically

**Examples:**
```bash
# Development build with file watching
wp borps skin:build --watch

# Production build with minification
wp borps skin:build --production

# Staging build
wp borps skin:build --env=staging
```

#### `wp borps skin:clean`

Cleans build artifacts and temporary files.

```bash
# Clean all build files
wp borps skin:clean

# Clean only CSS files
wp borps skin:clean --css

# Clean only JavaScript files
wp borps skin:clean --js
```

### Component Commands

#### `wp borps skin:component`

Creates new components with scaffolded templates.

```bash
# Create a basic component
wp borps skin:component button

# Create a component in a subdirectory
wp borps skin:component navigation/menu

# Create a component with specific template
wp borps skin:component card --template=advanced
```

**Options:**
- `--template=<template>` - Use specific component template (basic, advanced, form, layout)
- `--force` - Overwrite existing component
- `--style=<style>` - Component style (tailwind, vanilla, scss)

**Templates:**

**Basic Component:**
```bash
wp borps skin:component hero --template=basic
```

Creates:
```php
<?php
// resources/components/hero.php
$title = $title ?? 'Welcome';
$subtitle = $subtitle ?? '';
$background = $background ?? 'bg-blue-500';
?>

<section class="<?php echo esc_attr($background); ?> text-white py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-4">
            <?php echo esc_html($title); ?>
        </h1>
        <?php if ($subtitle) : ?>
            <p class="text-xl md:text-2xl opacity-90">
                <?php echo esc_html($subtitle); ?>
            </p>
        <?php endif; ?>
    </div>
</section>
```

**Advanced Component:**
```bash
wp borps skin:component post-grid --template=advanced
```

Creates a more complex component with data handling, pagination, and filtering.

**Form Component:**
```bash
wp borps skin:component contact-form --template=form
```

Creates a complete form with validation, nonce handling, and AJAX submission.

**Layout Component:**
```bash
wp borps skin:component page-header --template=layout
```

Creates a layout component with flexible positioning and responsive design.

#### `wp borps skin:list-components`

Lists all available components.

```bash
# List all components
wp borps skin:list-components

# List components in specific directory
wp borps skin:list-components --path=forms

# Show component details
wp borps skin:list-components --details
```

### Deployment Commands

#### `wp borps skin:deploy`

Creates a production-ready theme package for deployment.

```bash
# Create deployment package
wp borps skin:deploy

# Deploy to specific directory
wp borps skin:deploy --output=/path/to/deploy

# Create deployment with custom exclusions
wp borps skin:deploy --exclude=logs,temp
```

**Options:**
- `--output=<path>` - Output directory for deployment package
- `--exclude=<patterns>` - Additional patterns to exclude (comma-separated)
- `--zip` - Create a ZIP archive of the deployment
- `--clean` - Clean the output directory before deployment

**Automatic Exclusions:**

The deployment command automatically excludes development files based on `.gitignore` patterns:

- `node_modules/`
- `resources/src/` (source files)
- `*.log` files
- Development configuration files
- Build tools and package files

**Custom Exclusions:**

Create a `.deployignore` file in your theme root for additional exclusions:

```bash
# .deployignore
design/
docs/
*.psd
*.sketch
*.fig
temp/
.env.local
```

### Information Commands

#### `wp borps skin:info`

Shows theme information and status.

```bash
# Show basic theme info
wp borps skin:info

# Show detailed information
wp borps skin:info --detailed

# Show build status
wp borps skin:info --build-status
```

**Output:**
```
WP-Skin Theme Information
========================
Theme Name: Your Theme
Version: 1.0.0
WP-Skin Version: 1.0.0
Mode: theme

Build Status:
- Node.js: 18.17.0 ✓
- NPM: 9.6.7 ✓
- Assets: Built (2 hours ago)
- Components: 12 discovered
- Vite Dev Server: Running (port 5173)

File Structure:
✓ resources/package.json
✓ resources/vite.config.mjs
✓ resources/src/css/app.css
✓ resources/src/js/app.js
✓ resources/components/ (12 components)
```

#### `wp borps skin:status`

Checks system requirements and theme health.

```bash
# Check system status
wp borps skin:status

# Run health checks
wp borps skin:status --health

# Check specific component
wp borps skin:status --check=build
```

**Health Checks:**
- Node.js and NPM versions
- Required dependencies
- File permissions
- Build configuration
- Asset compilation status
- Component discovery

### Advanced Commands

#### `wp borps skin:optimize`

Optimizes theme assets and performance.

```bash
# Optimize images
wp borps skin:optimize --images

# Optimize CSS and JS
wp borps skin:optimize --assets

# Full optimization
wp borps skin:optimize --all
```

**Optimization Features:**
- Image compression and format conversion
- CSS and JavaScript minification
- Asset bundling optimization
- Unused CSS removal
- Critical CSS generation

#### `wp borps skin:scaffold`

Scaffolds theme structure and files.

```bash
# Scaffold complete theme structure
wp borps skin:scaffold

# Scaffold only specific parts
wp borps skin:scaffold --components-only

# Force scaffolding (overwrite existing)
wp borps skin:scaffold --force
```

**Scaffolded Structure:**
```
resources/
├── src/
│   ├── css/app.css
│   └── js/app.js
├── components/
├── package.json
└── vite.config.mjs

theme-root/
├── style.css
├── header.php
├── footer.php
├── index.php
├── front-page.php
├── skin.php
└── .gitignore
```

### Configuration Commands

#### `wp borps skin:config`

Manages WP-Skin configuration.

```bash
# Show current configuration
wp borps skin:config

# Set configuration value
wp borps skin:config set build.production true

# Get specific configuration
wp borps skin:config get assets.css_path

# Reset to defaults
wp borps skin:config reset
```

**Configuration Options:**
```json
{
  "build": {
    "production": false,
    "watch": false,
    "sourcemaps": true
  },
  "assets": {
    "css_path": "resources/dist",
    "js_path": "resources/dist",
    "public_path": "/wp-content/themes/your-theme/resources/dist"
  },
  "deployment": {
    "exclude_patterns": ["node_modules", "src", "*.log"],
    "create_zip": false
  }
}
```

## Command Customization

### Custom Commands

Create custom WP-CLI commands for your theme:

```php
<?php
// In your theme's functions.php or plugin

if (defined('WP_CLI') && WP_CLI) {
    class Custom_WPSkin_Commands {
        
        /**
         * Sync theme assets with CDN
         */
        public function sync_cdn($args, $assoc_args) {
            WP_CLI::line('Syncing assets to CDN...');
            
            $cdn_path = $assoc_args['cdn-path'] ?? '';
            if (empty($cdn_path)) {
                WP_CLI::error('CDN path is required. Use --cdn-path=/path/to/cdn');
            }
            
            // Your CDN sync logic here
            
            WP_CLI::success('Assets synced successfully!');
        }
        
        /**
         * Generate critical CSS
         */
        public function critical_css($args, $assoc_args) {
            $urls = $assoc_args['urls'] ?? home_url();
            $output = $assoc_args['output'] ?? get_template_directory() . '/critical.css';
            
            WP_CLI::line("Generating critical CSS for: {$urls}");
            
            // Critical CSS generation logic
            
            WP_CLI::success("Critical CSS saved to: {$output}");
        }
    }
    
    WP_CLI::add_command('wpskin:sync-cdn', 'Custom_WPSkin_Commands::sync_cdn');
    WP_CLI::add_command('wpskin:critical-css', 'Custom_WPSkin_Commands::critical_css');
}
```

### Command Hooks

Hook into WP-Skin commands:

```php
<?php
// Before build command
add_action('wpskin_before_build', function($production_mode) {
    if ($production_mode) {
        // Pre-production tasks
        do_action('optimize_images');
        do_action('generate_critical_css');
    }
});

// After deployment
add_action('wpskin_after_deploy', function($deploy_path) {
    // Post-deployment tasks
    wp_cache_flush();
    do_action('notify_deployment', $deploy_path);
});
```

## Troubleshooting Commands

### Common Issues

**Command not found:**
```bash
# Verify WP-CLI can find WP-Skin
wp cli info
wp package list

# If WP-Skin is not listed, verify installation
wp eval 'var_dump(class_exists("WordPressSkin\\Core\\Skin"));'
```

**Build failures:**
```bash
# Check system requirements
wp borps skin:status --health

# Verbose build output
wp borps skin:build --verbose

# Reset and rebuild
wp borps skin:clean && wp borps skin:build
```

**Permission errors:**
```bash
# Check file permissions
ls -la resources/
sudo chown -R www-data:www-data resources/
sudo chmod -R 755 resources/
```

For more troubleshooting information, see [troubleshooting.md](troubleshooting.md).