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

### 3. Auto Logout Idle Users

**Note:** Login page hiding functionality has been removed from WP-Skin. Use dedicated plugins like **WPS Hide Login** for login protection instead.

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
    'auto_logout_idle_users' => true,
    'disable_xmlrpc' => false, // Default: enabled
    'idle_timeout' => 45 // minutes
]);
```

### Available Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `remove_wp_version` | boolean | `true` | Remove WordPress version info |
| `disable_directory_browsing` | boolean | `true` | Disable directory listing |
| `auto_logout_idle_users` | boolean | `false` | Enable auto logout |
| `disable_xmlrpc` | boolean | `false` | Disable XML-RPC (enable/disable dynamically) |
| `idle_timeout` | integer | `60` | Idle timeout in minutes |

## Production Setup

Recommended security configuration for production websites:

```php
use WordPressSkin\Core\Skin;

add_action('after_setup_theme', function() {
    $skin = Skin::getInstance();
    
    $skin->configureSecurity([
        'remove_wp_version' => true,           // Hide version info
        'disable_directory_browsing' => true, // Prevent directory listing
        'auto_logout_idle_users' => true,     // Auto logout after inactivity
        'disable_xmlrpc' => false,            // Enable XML-RPC (default)
        'idle_timeout' => 60                  // 60 minutes timeout
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

```

## Security Best Practices

### 1. Set Appropriate Timeout Values
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

### 2. Use Login Security Plugins
For advanced login protection, consider dedicated plugins like:
- **WPS Hide Login** - Hide login page URL
- **Wordfence** - Login attempt limiting and IP blocking  
- **Two-Factor Authentication** plugins

### 3. Regular Updates
Keep WordPress core, themes, and plugins updated alongside security configurations.

## Troubleshooting

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
- [ ] Login security plugin installed (e.g., WPS Hide Login)
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
if ($config['auto_logout_idle_users']) {
    // Show logout warning to users
    add_action('wp_footer', 'show_session_timeout_warning');
}
```