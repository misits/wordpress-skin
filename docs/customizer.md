# WordPress Customizer Integration

WP-Skin provides a modern, fluent API for WordPress Customizer integration that's much cleaner than the traditional WordPress approach.

## Table of Contents

- [Overview](#overview)
- [Basic Usage](#basic-usage)
- [Control Types](#control-types)
- [Advanced Configuration](#advanced-configuration)
- [Examples](#examples)
- [Migration from ACF Options](#migration-from-acf-options)

## Overview

The Customizer system allows you to create theme-wide settings that appear in **Appearance > Customize**. These settings provide live preview and are perfect for:

- Site branding (logo, colors, typography)
- Contact information
- Social media links
- Layout preferences
- Theme styling options

**NOT for:**
- Page/post content
- Block editor settings
- One-time configurations

## Basic Usage

Configure customizer sections in your `skin.php` file:

```php
<?php
// skin.php
use WordPressSkin\Core\Skin;

Skin::getInstance()->customizer(function($customizer) {
    
    $customizer->section('site_branding', function($section) {
        $section->title('Site Branding')
                ->description('Customize your site identity')
                ->priority(120);
        
        $section->setting('site_logo')
                ->label('Upload Logo')
                ->control('image');
                
        $section->setting('primary_color')
                ->label('Brand Color')
                ->control('color')
                ->default('#3490dc');
    });
    
});
?>
```

## Control Types

### Text Inputs

```php
// Text
$section->setting('company_name')
        ->label('Company Name')
        ->control('text')
        ->default('My Company');

// Email
$section->setting('contact_email')
        ->label('Contact Email')
        ->control('email');

// URL
$section->setting('website_url')
        ->label('Website URL')
        ->control('url');

// Phone
$section->setting('phone_number')
        ->label('Phone Number')
        ->control('tel');

// Number
$section->setting('max_posts')
        ->label('Max Posts')
        ->control('number')
        ->default(10);

// Date
$section->setting('launch_date')
        ->label('Launch Date')
        ->control('date');

// Range
$section->setting('opacity')
        ->label('Opacity')
        ->control('range')
        ->inputAttrs(['min' => 0, 'max' => 100, 'step' => 5])
        ->default(80);
```

### Textarea

```php
$section->setting('footer_text')
        ->label('Footer Text')
        ->control('textarea')
        ->description('HTML allowed')
        ->default('© 2024 My Company');
```

### Checkbox

```php
$section->setting('show_social_links')
        ->label('Show Social Links')
        ->control('checkbox')
        ->default(true);
```

### Radio Buttons

```php
$section->setting('layout_style')
        ->label('Layout Style')
        ->control('radio')
        ->choices([
            'boxed' => 'Boxed Layout',
            'full-width' => 'Full Width',
            'centered' => 'Centered'
        ])
        ->default('boxed');
```

### Select Dropdown

```php
$section->setting('header_style')
        ->label('Header Style')
        ->control('select')
        ->choices([
            'minimal' => 'Minimal',
            'classic' => 'Classic',
            'modern' => 'Modern'
        ])
        ->default('minimal');
```

### Page Dropdown

```php
$section->setting('privacy_page')
        ->label('Privacy Policy Page')
        ->control('dropdown-pages');
```

### Media Uploads

```php
// Image Upload
$section->setting('hero_image')
        ->label('Hero Background Image')
        ->control('image');

// Generic Media Upload  
$section->setting('company_logo')
        ->label('Company Logo')
        ->control('media');

// File Upload
$section->setting('company_brochure')
        ->label('Company Brochure PDF')
        ->control('upload');
```

### Color Picker

```php
$section->setting('accent_color')
        ->label('Accent Color')
        ->control('color')
        ->default('#ff6b6b');
```

## Advanced Configuration

### Setting Types

```php
// Theme mod (default) - stored as theme_mods_themename
$section->setting('site_logo')
        ->type('theme_mod');

// Option - stored in wp_options table
$section->setting('company_settings')
        ->type('option');
```

### Capabilities

```php
$section->setting('admin_email')
        ->capability('manage_options') // Only administrators
        ->control('email');
```

### Transport Methods

```php
// Refresh page on change (default)
$section->setting('layout_style')
        ->transport('refresh');

// Use postMessage for live preview (requires JS)
$section->setting('site_title_color')
        ->transport('postMessage');
```

### Input Attributes

```php
$section->setting('max_width')
        ->control('number')
        ->inputAttrs([
            'min' => 100,
            'max' => 2000,
            'step' => 10
        ]);
```

## Examples

### Complete Site Branding Section

```php
$customizer->section('site_branding', function($section) {
    $section->title('Site Branding')
            ->description('Customize your site identity');
    
    // Logo
    $section->setting('site_logo')
            ->label('Site Logo')
            ->control('image')
            ->description('Recommended size: 300x100px');
    
    // Colors
    $section->setting('primary_color')
            ->label('Primary Color')
            ->control('color')
            ->default('#3490dc');
            
    $section->setting('secondary_color')
            ->label('Secondary Color')
            ->control('color')
            ->default('#64748b');
    
    // Typography
    $section->setting('heading_font')
            ->label('Heading Font')
            ->control('select')
            ->choices([
                'system' => 'System Font',
                'roboto' => 'Roboto',
                'open-sans' => 'Open Sans',
                'lato' => 'Lato'
            ])
            ->default('system');
});
```

### Contact Information Section

```php
$customizer->section('contact_info', function($section) {
    $section->title('Contact Information');
    
    $section->setting('company_name')
            ->label('Company Name')
            ->control('text');
            
    $section->setting('phone')
            ->label('Phone Number')
            ->control('tel');
            
    $section->setting('email')
            ->label('Email Address')
            ->control('email');
            
    $section->setting('address')
            ->label('Address')
            ->control('textarea');
            
    $section->setting('show_contact_info')
            ->label('Display Contact Info')
            ->control('checkbox')
            ->default(true);
});
```

### Social Media Section

```php
$customizer->section('social_media', function($section) {
    $section->title('Social Media Links');
    
    $section->setting('facebook_url')
            ->label('Facebook URL')
            ->control('url');
            
    $section->setting('twitter_url')
            ->label('Twitter URL')
            ->control('url');
            
    $section->setting('instagram_url')
            ->label('Instagram URL')
            ->control('url');
            
    $section->setting('linkedin_url')
            ->label('LinkedIn URL')
            ->control('url');
            
    $section->setting('social_links_style')
            ->label('Social Links Style')
            ->control('radio')
            ->choices([
                'icons' => 'Icons Only',
                'text' => 'Text Only',
                'both' => 'Icons + Text'
            ])
            ->default('icons');
});
```

## Using Values in Templates

### Getting Values

```php
// Get setting value with fallback
$logo_id = get_theme_mod('site_branding_site_logo');
$primary_color = get_theme_mod('site_branding_primary_color', '#3490dc');
$show_contact = get_theme_mod('contact_info_show_contact_info', true);
```

### Display in Templates

```php
<!-- header.php -->
<?php $logo_id = get_theme_mod('site_branding_site_logo'); ?>
<?php if ($logo_id): ?>
    <img src="<?php echo wp_get_attachment_url($logo_id); ?>" 
         alt="<?php bloginfo('name'); ?>" class="site-logo">
<?php else: ?>
    <h1><?php bloginfo('name'); ?></h1>
<?php endif; ?>

<!-- Dynamic CSS -->
<style>
:root {
    --primary-color: <?php echo get_theme_mod('site_branding_primary_color', '#3490dc'); ?>;
    --secondary-color: <?php echo get_theme_mod('site_branding_secondary_color', '#64748b'); ?>;
}
</style>

<!-- footer.php -->
<?php if (get_theme_mod('contact_info_show_contact_info', true)): ?>
    <div class="contact-info">
        <?php $phone = get_theme_mod('contact_info_phone'); ?>
        <?php if ($phone): ?>
            <p>Phone: <a href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_html($phone); ?></a></p>
        <?php endif; ?>
        
        <?php $email = get_theme_mod('contact_info_email'); ?>
        <?php if ($email): ?>
            <p>Email: <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p>
        <?php endif; ?>
    </div>
<?php endif; ?>
```

## Migration from ACF Options

If you're currently using ACF Options Pages, here's how to migrate:

### Before (ACF Options)

```php
// ACF way
$logo = get_field('company_logo', 'option');
$phone = get_field('company_phone', 'option');
$primary_color = get_field('primary_color', 'option') ?: '#3490dc';
```

### After (WP-Skin Customizer)

```php
// WP-Skin way - better UX, no plugin dependency
$logo = get_theme_mod('site_branding_site_logo');
$phone = get_theme_mod('contact_info_phone');
$primary_color = get_theme_mod('site_branding_primary_color', '#3490dc');
```

### Benefits of Migration

- **No plugin dependency** - Built into WordPress
- **Live preview** - See changes immediately
- **Better user experience** - Intuitive interface
- **Automatic sanitization** - Security built-in
- **Mobile-friendly** - Works on all devices
- **Theme portability** - Works with any WordPress installation

## Best Practices

1. **Use descriptive section and setting names**
2. **Provide sensible defaults**
3. **Add helpful descriptions**
4. **Group related settings in sections**
5. **Use appropriate control types**
6. **Sanitize and validate user input** (handled automatically)
7. **Test on mobile devices**

## Troubleshooting

### Settings Not Showing
- Check that your `skin.php` file is being loaded
- Verify section and setting names are unique
- Ensure proper syntax in customizer configuration

### Values Not Saving
- Check user capabilities
- Verify setting type is correct
- Test sanitization callbacks

### Live Preview Not Working
- Use `transport('postMessage')` for live updates
- Add corresponding JavaScript for preview updates
- Check browser console for errors