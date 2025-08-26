# WordPress Security

WP-Skin provides comprehensive WordPress security hardening features to protect your website from common vulnerabilities and attacks.

## Quick Start

Enable all security features with default settings:

```php
use WordPressSkin\Core\Skin;

// Enable all security features
$skin = Skin::getInstance();
$skin->enableAllSecurity();
```

## Security Features

### 1. Remove WordPress Version

Hides WordPress version information from various locations to prevent version-based attacks.

**What it does:**
- Removes version from HTML head (`<meta name="generator">`)
- Removes version from RSS feeds
- Strips version parameters from CSS/JS files
- Cleans admin footer version display

**Usage:**
```php
$skin->removeWpVersion();
```

### 2. Disable Directory Browsing

Prevents unauthorized directory listing when no index file is present.

**What it does:**
- Adds `Options -Indexes` to .htaccess file
- Blocks directory enumeration attacks

**Usage:**
```php
$skin->disableDirectoryBrowsing();
```

### 3. Hide Login Page

Obscures the default WordPress login page to prevent brute force attacks.

**What it does:**
- Creates custom login URL with secret slug
- Blocks access to `/wp-login.php`
- Redirects unauthorized login attempts to 404

**Usage:**
```php
// Default custom slug
$skin->hideLoginPage();

// Custom slug
$skin->hideLoginPage('my-secret-admin');

// Get the custom login URL
$login_url = $skin->getCustomLoginUrl();
// Result: https://yoursite.com/?my-secret-admin=1
```

### 4. Auto Logout Idle Users

Automatically logs out users after a period of inactivity.

**What it does:**
- Tracks user activity timestamps
- Logs out users after configured timeout
- Prevents session hijacking from abandoned sessions

**Usage:**
```php
// 30 minutes (default)
$skin->autoLogoutIdleUsers();

// Custom timeout (15 minutes)
$skin->autoLogoutIdleUsers(15);
```

### 5. Disable XML-RPC

Disables XML-RPC functionality to prevent related attacks.

**What it does:**
- Disables XML-RPC endpoint entirely
- Removes pingback headers and methods
- Blocks XML-RPC brute force attempts
- Removes RSD and Windows Live Writer links

**Usage:**
```php
$skin->disableXmlRpc();
```

## Configuration Options

### Manual Configuration

For fine-grained control, use the `configureSecurity()` method:

```php
$skin->configureSecurity([
    'remove_wp_version' => true,
    'disable_directory_browsing' => true,
    'hide_login_page' => true,
    'auto_logout_idle_users' => true,
    'disable_xmlrpc' => true,
    'idle_timeout' => 45, // minutes
    'custom_login_slug' => 'admin-portal'
]);
```

### Available Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `remove_wp_version` | boolean | `true` | Remove WordPress version info |
| `disable_directory_browsing` | boolean | `true` | Disable directory listing |
| `hide_login_page` | boolean | `false` | Hide default login page |
| `auto_logout_idle_users` | boolean | `false` | Enable auto logout |
| `disable_xmlrpc` | boolean | `true` | Disable XML-RPC |
| `idle_timeout` | integer | `30` | Idle timeout in minutes |
| `custom_login_slug` | string | `'secure-login'` | Custom login URL slug |

## Production Setup

Recommended security configuration for production websites:

```php
use WordPressSkin\Core\Skin;

add_action('after_setup_theme', function() {
    $skin = Skin::getInstance();
    
    $skin->configureSecurity([
        'remove_wp_version' => true,           // Hide version info
        'disable_directory_browsing' => true, // Prevent directory listing
        'hide_login_page' => true,            // Hide wp-login.php
        'auto_logout_idle_users' => true,     // Auto logout after inactivity
        'disable_xmlrpc' => true,             // Disable XML-RPC
        'idle_timeout' => 30,                 // 30 minutes timeout
        'custom_login_slug' => 'secure-admin' // Custom admin URL
    ]);
});
```

## Static Access

Access security features through the static interface:

```php
// Get security manager
$security = Skin::security();

// Update configuration
$security->updateConfig(['idle_timeout' => 60]);

// Get current config
$config = $security->getConfig();

// Get custom login URL
$login_url = $security->getCustomLoginUrl();
```

## Security Best Practices

### 1. Use Strong Custom Login Slugs
- Avoid predictable slugs like 'admin', 'login', 'secure'
- Use a combination of letters and numbers
- Keep it memorable but not guessable

```php
$skin->hideLoginPage('x9k2m7admin2024');
```

### 2. Set Appropriate Timeout Values
- Balance security with user experience
- Consider your users' typical session lengths
- Shorter timeouts for high-security sites

```php
// High security: 15 minutes
$skin->autoLogoutIdleUsers(15);

// Standard: 30 minutes
$skin->autoLogoutIdleUsers(30);

// Extended: 60 minutes
$skin->autoLogoutIdleUsers(60);
```

### 3. Monitor Failed Login Attempts
While WP-Skin handles basic login security, consider additional plugins for:
- Login attempt limiting
- IP blocking
- Two-factor authentication

### 4. Regular Updates
Keep WordPress core, themes, and plugins updated alongside security configurations.

## Troubleshooting

### Login Page Issues

**Problem:** Can't access login page after hiding it.

**Solution:** Access your custom URL or disable the feature:
```php
// Temporarily disable
$skin->configureSecurity(['hide_login_page' => false]);
```

### Auto Logout Too Aggressive

**Problem:** Users getting logged out too frequently.

**Solution:** Increase timeout or disable feature:
```php
// Increase timeout
$skin->autoLogoutIdleUsers(60); // 1 hour

// Or disable
$skin->configureSecurity(['auto_logout_idle_users' => false]);
```

### .htaccess Permission Issues

**Problem:** Directory browsing protection not working.

**Solution:** Ensure .htaccess is writable:
```bash
chmod 644 .htaccess
```

## Security Checklist

Use this checklist to ensure comprehensive security:

- [ ] WordPress version hidden
- [ ] Directory browsing disabled
- [ ] Custom login URL configured
- [ ] Auto logout enabled with appropriate timeout
- [ ] XML-RPC disabled
- [ ] Strong passwords for all users
- [ ] Regular WordPress updates
- [ ] SSL certificate installed
- [ ] Regular backups scheduled

## Advanced Usage

### Conditional Security

Enable different security levels based on environment:

```php
if (WP_ENV === 'production') {
    $skin->enableAllSecurity();
} else {
    // Development - minimal security
    $skin->removeWpVersion()
         ->disableXmlRpc();
}
```

### Custom Implementation

Extend security features by accessing the SecurityManager directly:

```php
$security = Skin::security();
$config = $security->getConfig();

// Add custom logic based on current config
if ($config['hide_login_page']) {
    // Custom login page styling
    add_action('login_head', 'custom_login_styles');
}
```