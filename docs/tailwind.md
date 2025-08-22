# Tailwind CSS Integration

WP-Skin is built around **Tailwind CSS v4**, providing a utility-first approach to styling your WordPress themes.

## Philosophy: Tailwind-First

WP-Skin follows a **Tailwind-first, minimal custom CSS** approach:

- **Use Tailwind utilities** instead of writing custom CSS
- **Keep custom styles minimal** - only add what Tailwind can't provide
- **Leverage Tailwind's design system** for consistency
- **Component-based styling** with utility classes

## Setup & Configuration

### Automatic Setup

WP-Skin uses **Tailwind CSS v4** which requires no configuration file. Tailwind is automatically configured through Vite and CSS imports.

### CSS Entry Point

Tailwind is imported in the main CSS file:

```css
/* resources/src/css/app.css */
@import "tailwindcss";

/* Custom styles (keep minimal) */
body {
  font-family: system-ui, -apple-system, sans-serif;
}
```

### Vite Integration

Tailwind CSS v4 is integrated via the `@tailwindcss/vite` plugin. The configuration also includes automatic PHP file watching for browser refresh:

```javascript
// resources/vite.config.mjs
import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
  plugins: [
    react(), 
    tailwindcss(),
    {
      // Watch PHP files and reload browser on changes
      name: "php",
      handleHotUpdate({ file, server }) {
        if (file.endsWith(".php")) {
          server.ws.send({
            type: "full-reload",
            path: "*",
          });
        }
      },
    },
  ],
  build: {
    outDir: "dist",
    emptyOutDir: true,
    rollupOptions: {
      input: {
        app: "./src/js/app.js",
      },
      output: {
        entryFileNames: "js/[name].[hash].js",
        chunkFileNames: "js/[name].[hash].js",
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.endsWith(".css")) {
            return "css/[name].[hash].css";
          }
          return "assets/[name].[hash][extname]";
        },
      },
    },
    manifest: true,
  },
  server: {
    host: "0.0.0.0",
    port: 5173,
    cors: true,
    strictPort: true,
    hmr: {
      host: "localhost", // Adjust for your local domain
    },
  },
});
```

## Development Workflow

### 1. Use Utility Classes

Style components directly with Tailwind utilities:

```php
<?php
// resources/components/ui/button.php
$variant = $variant ?? 'primary';
$size = $size ?? 'md';

$variants = [
    'primary' => 'bg-blue-500 hover:bg-blue-600 text-white',
    'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-800',
    'danger' => 'bg-red-500 hover:bg-red-600 text-white',
    'outline' => 'border-2 border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white'
];

$sizes = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2',
    'lg' => 'px-6 py-3 text-lg'
];

$classes = $variants[$variant] . ' ' . $sizes[$size] . ' rounded font-medium transition-colors duration-200';
?>

<button class="<?php echo esc_attr($classes); ?>" type="<?php echo esc_attr($type ?? 'button'); ?>">
    <?php echo esc_html($text ?? 'Button'); ?>
</button>
```

### 2. Responsive Design

Use Tailwind's responsive prefixes:

```php
<?php
// resources/components/layout/grid.php
$columns = $columns ?? 3;
$gap = $gap ?? 6;

$grid_classes = [
    1 => 'grid-cols-1',
    2 => 'grid-cols-1 md:grid-cols-2',
    3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4'
];
?>

<div class="grid <?php echo esc_attr($grid_classes[$columns]); ?> gap-<?php echo esc_attr($gap); ?>">
    <?php if (isset($content)) echo $content; ?>
</div>
```

### 3. Dark Mode Support

Enable dark mode in your CSS with Tailwind v4:

```css
/* resources/src/css/app.css */
@import "tailwindcss";

@media (prefers-color-scheme: dark) {
  /* Dark mode styles are automatically applied */
}

/* Or use class-based dark mode */
.dark {
  /* Dark mode utilities work automatically */
}
```

Use dark mode utilities:

```php
<?php
// resources/components/ui/card.php
?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
        <?php echo esc_html($title ?? 'Card Title'); ?>
    </h3>
    <p class="text-gray-600 dark:text-gray-300">
        <?php echo esc_html($content ?? 'Card content goes here.'); ?>
    </p>
</div>
```

## Common Patterns

### Layout Components

```php
<?php
// resources/components/layout/container.php
$size = $size ?? 'default';

$sizes = [
    'sm' => 'max-w-2xl',
    'default' => 'max-w-4xl',
    'lg' => 'max-w-6xl',
    'xl' => 'max-w-7xl',
    'full' => 'max-w-full'
];
?>

<div class="<?php echo esc_attr($sizes[$size]); ?> mx-auto px-4 sm:px-6 lg:px-8">
    <?php if (isset($content)) echo $content; ?>
</div>
```

### Typography Scale

```php
<?php
// resources/components/ui/heading.php
$level = $level ?? 1;
$size = $size ?? null;

// Responsive typography scale
$scales = [
    1 => $size ?? 'text-3xl sm:text-4xl lg:text-5xl font-bold',
    2 => $size ?? 'text-2xl sm:text-3xl lg:text-4xl font-bold',
    3 => $size ?? 'text-xl sm:text-2xl lg:text-3xl font-semibold',
    4 => $size ?? 'text-lg sm:text-xl font-semibold',
    5 => $size ?? 'text-base sm:text-lg font-medium',
    6 => $size ?? 'text-sm sm:text-base font-medium'
];

$tag = "h{$level}";
?>

<<?php echo esc_html($tag); ?> class="<?php echo esc_attr($scales[$level]); ?> text-gray-900 dark:text-white">
    <?php echo esc_html($text ?? 'Heading'); ?>
</<?php echo esc_html($tag); ?>>
```

### Form Elements

```php
<?php
// resources/components/forms/input.php
$type = $type ?? 'text';
$error = $error ?? false;

$base_classes = 'w-full px-3 py-2 border rounded-md transition-colors duration-200 focus:outline-none focus:ring-2';
$normal_classes = 'border-gray-300 focus:border-blue-500 focus:ring-blue-200';
$error_classes = 'border-red-500 focus:border-red-500 focus:ring-red-200';

$classes = $base_classes . ' ' . ($error ? $error_classes : $normal_classes);
?>

<div class="mb-4">
    <?php if (isset($label)) : ?>
        <label for="<?php echo esc_attr($id ?? ''); ?>" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            <?php echo esc_html($label); ?>
            <?php if ($required ?? false) : ?>
                <span class="text-red-500">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>
    
    <input 
        type="<?php echo esc_attr($type); ?>"
        id="<?php echo esc_attr($id ?? ''); ?>"
        name="<?php echo esc_attr($name ?? ''); ?>"
        value="<?php echo esc_attr($value ?? ''); ?>"
        placeholder="<?php echo esc_attr($placeholder ?? ''); ?>"
        class="<?php echo esc_attr($classes); ?>"
        <?php if ($required ?? false) echo 'required'; ?>
    >
    
    <?php if ($error) : ?>
        <p class="mt-1 text-sm text-red-600"><?php echo esc_html($error); ?></p>
    <?php endif; ?>
</div>
```

## Custom Theme Configuration

### Customizing Tailwind v4

With Tailwind CSS v4, customization is done directly in CSS using CSS variables and `@theme`:

```css
/* resources/src/css/app.css */
@import "tailwindcss";

@theme {
  --color-brand-50: #eff6ff;
  --color-brand-100: #dbeafe;
  --color-brand-500: #3b82f6;
  --color-brand-600: #2563eb;
  --color-brand-700: #1d4ed8;
  --color-brand-900: #1e3a8a;
  
  --font-family-sans: "Inter", system-ui, sans-serif;
  --font-family-serif: "Merriweather", Georgia, serif;
  --font-family-mono: "JetBrains Mono", monospace;
  
  --spacing-72: 18rem;
  --spacing-84: 21rem;
  --spacing-96: 24rem;
}

/* Use your custom theme values */
.brand-button {
  @apply bg-brand-500 hover:bg-brand-600 text-white;
}
```

### WordPress Integration

Create WordPress-specific utilities:

```css
/* resources/src/css/app.css */
@import "tailwindcss";

/* WordPress-specific utilities */
.wp-content {
  @apply prose prose-lg max-w-none;
}

.wp-content h1 { @apply text-3xl font-bold mb-6; }
.wp-content h2 { @apply text-2xl font-semibold mb-4; }
.wp-content h3 { @apply text-xl font-medium mb-3; }
.wp-content p { @apply mb-4; }
.wp-content ul { @apply list-disc ml-6 mb-4; }
.wp-content ol { @apply list-decimal ml-6 mb-4; }

/* WordPress alignments */
.alignleft { @apply float-left mr-4 mb-4; }
.alignright { @apply float-right ml-4 mb-4; }
.aligncenter { @apply mx-auto block; }
.alignwide { @apply w-full; }
.alignfull { @apply w-screen relative left-1/2 -ml-[50vw]; }

/* WordPress gallery */
.wp-block-gallery { @apply grid gap-4; }
.wp-block-gallery.columns-2 { @apply grid-cols-2; }
.wp-block-gallery.columns-3 { @apply grid-cols-3; }
.wp-block-gallery.columns-4 { @apply grid-cols-4; }
```

## Component Styling Patterns

### State-Based Styling

```php
<?php
// resources/components/ui/alert.php
$type = $type ?? 'info';
$dismissible = $dismissible ?? false;

$styles = [
    'info' => 'bg-blue-50 border-blue-200 text-blue-800',
    'success' => 'bg-green-50 border-green-200 text-green-800',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'error' => 'bg-red-50 border-red-200 text-red-800'
];

$icon_styles = [
    'info' => 'text-blue-400',
    'success' => 'text-green-400',
    'warning' => 'text-yellow-400',
    'error' => 'text-red-400'
];
?>

<div class="border-l-4 p-4 <?php echo esc_attr($styles[$type]); ?>" role="alert">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 <?php echo esc_attr($icon_styles[$type]); ?>" viewBox="0 0 20 20" fill="currentColor">
                <!-- Icon paths based on type -->
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-medium"><?php echo esc_html($message ?? ''); ?></p>
        </div>
        <?php if ($dismissible) : ?>
            <div class="ml-auto pl-3">
                <button class="-mx-1.5 -my-1.5 rounded-md p-1.5 hover:bg-black/5 focus:outline-none focus:ring-2">
                    <span class="sr-only">Dismiss</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
```

### Variant-Based Components

```php
<?php
// resources/components/ui/badge.php
$variant = $variant ?? 'default';
$size = $size ?? 'md';

$variants = [
    'default' => 'bg-gray-100 text-gray-800',
    'primary' => 'bg-blue-100 text-blue-800',
    'success' => 'bg-green-100 text-green-800',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'danger' => 'bg-red-100 text-red-800',
    'outline' => 'border border-gray-300 text-gray-700'
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-sm',
    'lg' => 'px-3 py-1.5 text-base'
];

$classes = 'inline-flex items-center rounded-full font-medium ' . 
           $variants[$variant] . ' ' . $sizes[$size];
?>

<span class="<?php echo esc_attr($classes); ?>">
    <?php echo esc_html($text ?? 'Badge'); ?>
</span>
```

## Performance Optimization

### Automatic Optimization

Tailwind CSS v4 automatically optimizes and purges unused styles based on your file usage. No configuration needed - it scans your PHP, JS, and CSS files automatically when imported via `@import "tailwindcss"`.

### Component-Specific Styles

For styles that Tailwind can't handle, use component-specific CSS:

```css
/* resources/src/css/components.css */

/* Component-specific styles that can't be achieved with Tailwind utilities */
.wp-skin-timeline::before {
  content: '';
  position: absolute;
  left: 50%;
  top: 0;
  bottom: 0;
  width: 2px;
  background: theme('colors.gray.300');
  transform: translateX(-50%);
}

.wp-skin-gradient-text {
  background: linear-gradient(135deg, theme('colors.blue.600'), theme('colors.purple.600'));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
```

Import component styles in your main CSS:

```css
/* resources/src/css/app.css */
@import "tailwindcss";
@import "./components.css";
```

## Best Practices

### 1. Use Semantic Class Names for Complex Components

```php
<?php
// Good: Semantic wrapper with utility classes
?>
<article class="post-card bg-white rounded-lg shadow-md overflow-hidden">
    <div class="post-card__image aspect-video bg-gray-200">
        <!-- Image content -->
    </div>
    <div class="post-card__content p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Title</h2>
        <p class="text-gray-600">Content</p>
    </div>
</article>
```

### 2. Extract Repeated Patterns

```php
<?php
// Create reusable component variants
// resources/components/ui/card-variants.php

$card_variants = [
    'default' => 'bg-white rounded-lg shadow-md',
    'elevated' => 'bg-white rounded-lg shadow-lg',
    'bordered' => 'bg-white rounded-lg border border-gray-200',
    'gradient' => 'bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg'
];

$padding_variants = [
    'sm' => 'p-4',
    'md' => 'p-6',
    'lg' => 'p-8'
];
?>
```

### 3. Use CSS Custom Properties for Dynamic Values

```css
/* resources/src/css/app.css */
@import "tailwindcss";

:root {
  --brand-primary: theme('colors.blue.600');
  --brand-secondary: theme('colors.purple.600');
  --content-width: theme('maxWidth.4xl');
}
```

```php
<?php
// Use in components with dynamic styling
?>
<div style="--dynamic-color: <?php echo esc_attr($color ?? '#3B82F6'); ?>;" 
     class="bg-[var(--dynamic-color)] text-white p-4 rounded">
    Dynamic colored content
</div>
```

### 4. Organize Utility Classes

```php
<?php
// Group related utilities for better readability
$layout_classes = 'flex items-center justify-between';
$spacing_classes = 'px-4 py-2 mb-4';
$appearance_classes = 'bg-white rounded-lg shadow-md';
$interactive_classes = 'hover:shadow-lg transition-shadow duration-200';

$all_classes = implode(' ', [
    $layout_classes,
    $spacing_classes, 
    $appearance_classes,
    $interactive_classes
]);
?>

<div class="<?php echo esc_attr($all_classes); ?>">
    Content
</div>
```

## Debugging & Development Tools

### Visual Debugging

Add debug utilities during development:

```css
/* resources/src/css/debug.css (only in development) */
@import "tailwindcss";

/* Visual debugging utilities */
.debug * { @apply outline outline-1 outline-red-500; }
.debug-grid { @apply bg-[linear-gradient(rgba(255,0,0,0.1)_1px,transparent_1px),linear-gradient(90deg,rgba(255,0,0,0.1)_1px,transparent_1px)] bg-[length:20px_20px]; }
```

### Tailwind Play Integration

Test component styles in [Tailwind Play](https://play.tailwindcss.com/) before implementing in your theme.

## Resources

- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Tailwind UI Components](https://tailwindui.com/)
- [Headless UI for Interactive Components](https://headlessui.com/)
- [Tailwind CSS IntelliSense](https://marketplace.visualstudio.com/items?itemName=bradlc.vscode-tailwindcss) for VS Code