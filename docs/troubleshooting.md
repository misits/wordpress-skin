# Troubleshooting

Common issues and solutions when working with WP-Skin.

## Installation Issues

### WP-Skin Not Loading

**Problem:** WP-Skin functions are not available or components not working.

**Solutions:**

1. **Check include path:**
   ```php
   // Ensure correct path in functions.php
   require_once get_template_directory() . '/vendor/wordpress-routes/bootstrap.php';
   ```

2. **Verify file permissions:**
   ```bash
   ls -la wp-content/themes/your-theme/vendor/wordpress-routes/
   # Files should be readable (644) and directories executable (755)
   ```

3. **Check PHP version:**
   ```bash
   php -v
   # WP-Skin requires PHP 8.0 or higher
   ```

4. **Enable WordPress debugging:**
   ```php
   // In wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);

   // Check wp-content/debug.log for errors
   ```

### Auto-Scaffolding Not Working

**Problem:** Resources directory or theme files not automatically created.

**Solutions:**

1. **Check directory permissions:**
   ```bash
   # Theme directory must be writable
   chmod 755 wp-content/themes/your-theme/
   ```

2. **Verify theme activation:**
   ```bash
   wp theme list
   wp theme activate your-theme
   ```

3. **Force scaffolding:**
   ```bash
   wp skin:scaffold --force
   ```

4. **Manual scaffolding if auto-creation fails:**
   ```bash
   mkdir -p resources/src/{css,js}
   mkdir -p resources/components
   ```

## Build System Issues

### Vite Dev Server Not Starting

**Problem:** `npm run dev` fails or dev server not accessible.

**Solutions:**

1. **Check Node.js version:**
   ```bash
   node --version  # Should be 18+
   npm --version   # Should be 9+
   ```

2. **Clear npm cache and reinstall:**
   ```bash
   cd resources
   rm -rf node_modules package-lock.json
   npm cache clean --force
   npm install
   ```

3. **Check port availability:**
   ```bash
   # Kill process using port 5173
   lsof -ti:5173 | xargs kill -9

   # Or use different port
   npm run dev -- --port 3000
   ```

4. **CORS configuration:**
   ```javascript
   // resources/vite.config.mjs
   export default defineConfig({
     server: {
       host: '0.0.0.0',  // Allow external connections
       port: 5173,
       cors: true,
       hmr: {
         host: 'localhost'
       }
     }
   });
   ```

### Assets Not Loading in Development

**Problem:** CSS/JS changes not reflected, or assets return 404 errors.

**Solutions:**

1. **Verify dev server is running:**
   ```bash
   curl http://localhost:5173/@vite/client
   # Should return JavaScript, not 404
   ```

2. **Check WPSKIN_DEV constant:**
   ```php
   // In wp-config.php or functions.php
   define('WPSKIN_DEV', true);

   // Verify it's set
   wp eval 'var_dump(defined("WPSKIN_DEV") && WPSKIN_DEV);'
   ```

3. **Clear WordPress cache:**
   ```bash
   wp cache flush
   wp transient delete --all
   ```

4. **Check browser network tab:**
   - Assets should load from `localhost:5173`
   - Look for CORS errors or blocked requests

### Production Build Failures

**Problem:** `npm run build` fails with errors.

**Solutions:**

1. **Check for syntax errors:**
   ```bash
   cd resources
   npm run lint  # If available
   ```

2. **Clear build directory:**
   ```bash
   rm -rf resources/dist
   npm run build
   ```

3. **Increase Node memory limit:**
   ```bash
   NODE_OPTIONS="--max-old-space-size=4096" npm run build
   ```

4. **Check dependencies:**
   ```bash
   npm audit
   npm audit fix
   ```

## Tailwind CSS Issues

### Tailwind Classes Not Working

**Problem:** Tailwind utility classes have no effect on styling.

**Solutions:**

1. **Check CSS import:**
   ```css
   /* resources/src/css/app.css */
   @import "tailwindcss";  /* Must be present */
   ```

2. **Check CSS import and build process:**
   ```css
   /* resources/src/css/app.css */
   @import "tailwindcss";  /* Must be present */
   ```

3. **Rebuild assets:**
   ```bash
   cd resources
   rm -rf dist
   npm run build
   ```

4. **Check content paths:**
   ```bash
   # Verify files exist in content paths
   find ../. -name "*.php" | head -5
   ```

5. **Test with simple utility:**
   ```php
   <div class="bg-red-500 text-white p-4">
     This should be red with white text
   </div>
   ```

### Purging Issues

**Problem:** Required styles being purged from production build.

**Solutions:**

1. **Use CSS to preserve dynamic classes:**
   ```css
   /* resources/src/css/app.css */
   @import "tailwindcss";

   /* Ensure dynamic classes are preserved */
   .bg-red-500,
   .bg-blue-500,
   .bg-green-500 {
     /* Classes will be included automatically */
   }
   ```

2. **Use complete class names:**
   ```php
   <?php
   // Bad - may be purged
   $color = 'red';
   echo "bg-{$color}-500";

   // Good - full class name
   $classes = [
     'red' => 'bg-red-500',
     'blue' => 'bg-blue-500'
   ];
   echo $classes[$color];
   ?>
   ```

## Component Issues

### Components Not Found

**Problem:** `skin_component()` returns errors or nothing.

**Solutions:**

1. **Check function availability:**
   ```php
   if (function_exists('skin_component')) {
       skin_component('button');
   } else {
       echo 'skin_component not available';
   }
   ```

2. **Verify component file path:**
   ```bash
   ls -la resources/components/button.php
   ```

3. **Check file syntax:**
   ```bash
   php -l resources/components/button.php
   ```

4. **Enable WordPress debugging:**
   ```php
   // Check for PHP errors in component files
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

### Component Variables Not Available

**Problem:** Variables passed to components are undefined.

**Solutions:**

1. **Use null coalescing operator:**
   ```php
   <?php
   // In component file
   $title = $title ?? 'Default Title';
   $classes = $classes ?? 'default-class';
   ?>
   ```

2. **Debug passed variables:**
   ```php
   <?php
   // Temporary debug in component
   var_dump(get_defined_vars());
   ?>
   ```

3. **Check variable names:**
   ```php
   // Calling component
   skin_component('button', [
       'text' => 'Click me',  // Variable name must match
       'type' => 'submit'
   ]);

   // In component
   $text = $text ?? 'Default';  // Must use same name
   ```

## Database Issues

### WordPress ORM Integration

**Problem:** WP-Skin not working with WordPress ORM models.

**Solutions:**

1. **Check ORM installation:**
   ```bash
   wp eval 'var_dump(class_exists("WordpressORM\\Models\\Post"));'
   ```

2. **Verify include order:**
   ```php
   // Load ORM before WP-Skin
   require_once get_template_directory() . 'vendor/wordpress-orm/bootstrap.php';
   require_once get_template_directory() . '/vendor/wordpress-routes/bootstrap.php';
   ```

3. **Check database connection:**
   ```bash
   wp db check
   wp eval 'var_dump($wpdb->show_errors());'
   ```

## Performance Issues

### Slow Loading Times

**Problem:** Theme loads slowly in development or production.

**Solutions:**

1. **Optimize images:**
   ```bash
   # Install optimization tools
   npm install -D imagemin imagemin-webp
   ```

2. **Check asset sizes:**
   ```bash
   du -sh resources/dist/*
   # CSS/JS files should be reasonably sized
   ```

3. **Enable caching:**
   ```php
   // In wp-config.php
   define('WP_CACHE', true);
   ```

4. **Profile with browser dev tools:**
   - Check Network tab for slow assets
   - Use Lighthouse for performance audit

### Memory Issues

**Problem:** PHP memory limit exceeded.

**Solutions:**

1. **Increase PHP memory:**
   ```php
   // In wp-config.php
   ini_set('memory_limit', '256M');
   ```

2. **Check for memory leaks:**
   ```php
   // Add to component for debugging
   echo memory_get_peak_usage(true) / 1024 / 1024 . ' MB';
   ```

3. **Optimize component includes:**
   ```php
   // Use conditional loading
   if ($show_complex_component) {
       skin_component('complex-component');
   }
   ```

## WordPress Integration Issues

### Customizer Not Working

**Problem:** Customizer settings not saving or appearing.

**Solutions:**

1. **Check customizer registration:**
   ```php
   // In skin.php
   Skin::customizer()
       ->section('theme_options', 'Theme Options')
       ->setting('primary_color', 'color', 'Primary Color');
   ```

2. **Verify hook timing:**
   ```php
   // Should be in customize_register hook
   Skin::hooks()
       ->action('customize_register', function($wp_customize) {
           // Customizer settings here
       });
   ```

3. **Check PHP errors:**
   ```bash
   wp eval 'do_action("customize_register", $GLOBALS["wp_customize"]);'
   ```

### Menu Integration Issues

**Problem:** WordPress menus not working with components.

**Solutions:**

1. **Register menus properly:**
   ```php
   Skin::hooks()
       ->action('after_setup_theme', function() {
           register_nav_menus([
               'primary' => 'Primary Navigation',
               'footer' => 'Footer Menu'
           ]);
       });
   ```

2. **Check menu component:**
   ```php
   // In navigation component
   wp_nav_menu([
       'theme_location' => 'primary',
       'container' => false,
       'fallback_cb' => false
   ]);
   ```

3. **Verify menu assignment:**
   ```bash
   wp menu list
   wp menu location list
   ```

## Deployment Issues

### Deploy Command Fails

**Problem:** `wp skin:deploy` command errors or incomplete deployment.

**Solutions:**

1. **Check WP-CLI installation:**
   ```bash
   wp --info
   wp package list
   ```

2. **Verify file permissions:**
   ```bash
   # Output directory must be writable
   chmod 755 /path/to/deploy/output
   ```

3. **Check .gitignore patterns:**
   ```bash
   # Ensure .gitignore exists and has proper patterns
   cat .gitignore
   ```

4. **Manual deployment:**
   ```bash
   # If automated deployment fails
   cp -r . /path/to/production/
   rm -rf /path/to/production/node_modules
   rm -rf /path/to/production/resources/src
   ```

### Production Assets Missing

**Problem:** CSS/JS assets not loading in production.

**Solutions:**

1. **Build assets before deployment:**
   ```bash
   cd resources
   npm run build
   ```

2. **Check asset paths:**
   ```bash
   ls -la resources/dist/
   # Should contain CSS/JS files
   ```

3. **Verify asset loading:**
   ```php
   // Check if production assets exist
   $css_path = get_template_directory() . '/resources/dist/app.css';
   if (file_exists($css_path)) {
       echo "Asset exists: " . filesize($css_path) . " bytes";
   }
   ```

4. **Check server configuration:**
   ```bash
   # Ensure server can serve static files
   curl -I https://yoursite.com/wp-content/themes/your-theme/resources/dist/app.css
   ```

## Getting Help

### Debug Information

When reporting issues, include this information:

```bash
# System information
wp --info
wp skin:status --detailed

# Theme information
wp theme list
wp skin:info

# Server information
php -v
node --version
npm --version

# Error logs
tail -f wp-content/debug.log
```

### Log Files

Check these log files for errors:

- `wp-content/debug.log` - WordPress errors
- `resources/npm-debug.log` - NPM build errors
- Server error logs (location varies by host)

### Testing Isolation

Test in clean environment:

```bash
# Test with default theme
wp theme activate twentytwentythree

# Test with minimal plugins
wp plugin deactivate --all
wp plugin activate wp-skin-required-plugins-only
```

### Common File Locations

```
wp-content/themes/your-theme/
├── vendor/wordpress-routes/                    # Framework files
├── resources/                      # Build system
│   ├── dist/                      # Built assets
│   ├── src/                       # Source files
│   ├── components/                # PHP components
│   ├── package.json               # Dependencies
│   └── vite.config.mjs            # Build config
├── functions.php                   # Theme functions
├── skin.php                        # WP-Skin config
└── style.css                      # WordPress theme file
```

### Support Resources

- [WP-Skin Documentation](../README.md)
- [WordPress Debugging](https://wordpress.org/support/article/debugging-in-wordpress/)
- [Vite Documentation](https://vitejs.dev/guide/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [WP-CLI Documentation](https://wp-cli.org/)
