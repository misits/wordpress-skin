<?php
/**
 * WordPress Security Usage Examples
 * 
 * This file demonstrates how to use the WP-Skin security features
 * 
 * @package WP-Skin
 */

// Prevent direct access
defined('ABSPATH') or exit;

use WordPressSkin\Core\Skin;

// Example 1: Enable all security features with defaults
function example_enable_all_security() {
    $skin = Skin::getInstance();
    $skin->enableAllSecurity();
}

// Example 2: Enable specific security features
function example_selective_security() {
    $skin = Skin::getInstance();
    
    // Remove WordPress version from frontend and feeds
    $skin->removeWpVersion();
    
    // Disable directory browsing
    $skin->disableDirectoryBrowsing();
    
    // Disable XML-RPC (prevents brute force attacks)
    $skin->disableXmlRpc();
}

// Example 3: Hide login page with custom slug
function example_hide_login() {
    $skin = Skin::getInstance();
    
    // Hide login page with custom slug
    $skin->hideLoginPage('my-secret-login');
    
    // Get the custom login URL
    $login_url = $skin->getCustomLoginUrl();
    // URL will be: yoursite.com/?my-secret-login=1
}

// Example 4: Auto logout idle users
function example_auto_logout() {
    $skin = Skin::getInstance();
    
    // Auto logout users after 15 minutes of inactivity
    $skin->autoLogoutIdleUsers(15);
}

// Example 5: Custom security configuration
function example_custom_security_config() {
    $skin = Skin::getInstance();
    
    $skin->configureSecurity([
        'remove_wp_version' => true,
        'disable_directory_browsing' => true,
        'hide_login_page' => true,
        'auto_logout_idle_users' => true,
        'disable_xmlrpc' => true,
        'idle_timeout' => 45, // 45 minutes
        'custom_login_slug' => 'admin-portal'
    ]);
}

// Example 6: Using static security method
function example_static_access() {
    // Access security manager directly
    $security = Skin::security();
    
    // Get current configuration
    $config = $security->getConfig();
    
    // Update configuration
    $security->updateConfig([
        'idle_timeout' => 20
    ]);
}

// Example 7: Production security setup
function example_production_security() {
    $skin = Skin::getInstance();
    
    $skin->configureSecurity([
        'remove_wp_version' => true,           // Hide WP version
        'disable_directory_browsing' => true, // Prevent directory listing
        'hide_login_page' => true,            // Hide wp-login.php
        'auto_logout_idle_users' => true,     // Auto logout after inactivity
        'disable_xmlrpc' => true,             // Disable XML-RPC
        'idle_timeout' => 30,                 // 30 minutes timeout
        'custom_login_slug' => 'secure-admin' // Custom login URL
    ]);
    
    // The login URL will be: yoursite.com/?secure-admin=1
}

// Hook examples - add these to your theme's functions.php
add_action('after_setup_theme', 'example_enable_all_security');

// Or for more control:
// add_action('after_setup_theme', 'example_production_security');