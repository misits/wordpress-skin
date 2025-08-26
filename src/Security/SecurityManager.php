<?php
/**
 * WordPress Security Manager
 * 
 * Handles WordPress security hardening features
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Security;

defined('ABSPATH') or exit;

class SecurityManager {
    
    /**
     * Security configuration
     * 
     * @var array
     */
    private $config = [
        'remove_wp_version' => true,
        'disable_directory_browsing' => true,
        'hide_login_page' => false,
        'auto_logout_idle_users' => false,
        'disable_xmlrpc' => true,
        'idle_timeout' => 30, // minutes
        'custom_login_slug' => 'secure-login'
    ];
    
    /**
     * Constructor
     * 
     * @param array $config Optional security configuration
     */
    public function __construct(array $config = []) {
        $this->config = array_merge($this->config, $config);
        $this->init();
    }
    
    /**
     * Initialize security features
     * 
     * @return void
     */
    private function init(): void {
        if ($this->config['remove_wp_version']) {
            $this->removeWpVersion();
        }
        
        if ($this->config['disable_directory_browsing']) {
            $this->disableDirectoryBrowsing();
        }
        
        if ($this->config['hide_login_page']) {
            $this->hideLoginPage();
        }
        
        if ($this->config['auto_logout_idle_users']) {
            $this->autoLogoutIdleUsers();
        }
        
        if ($this->config['disable_xmlrpc']) {
            $this->disableXmlRpc();
        }
    }
    
    /**
     * Remove WordPress version from frontend
     * 
     * @return void
     */
    private function removeWpVersion(): void {
        // Remove version from head
        remove_action('wp_head', 'wp_generator');
        
        // Remove version from RSS feeds
        add_filter('the_generator', '__return_empty_string');
        
        // Remove version from scripts and styles
        add_filter('style_loader_src', [$this, 'removeVersionFromAssets']);
        add_filter('script_loader_src', [$this, 'removeVersionFromAssets']);
        
        // Remove from admin footer
        add_filter('update_footer', '__return_empty_string', 11);
    }
    
    /**
     * Remove version parameter from assets
     * 
     * @param string $src Asset source URL
     * @return string
     */
    public function removeVersionFromAssets(string $src): string {
        if (strpos($src, 'ver=') !== false) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }
    
    /**
     * Disable directory browsing
     * 
     * @return void
     */
    private function disableDirectoryBrowsing(): void {
        add_action('init', function() {
            $htaccess_content = "Options -Indexes\n";
            $htaccess_file = ABSPATH . '.htaccess';
            
            if (is_writable($htaccess_file)) {
                $current_content = file_get_contents($htaccess_file);
                if (strpos($current_content, 'Options -Indexes') === false) {
                    $new_content = $htaccess_content . $current_content;
                    file_put_contents($htaccess_file, $new_content);
                }
            }
        });
    }
    
    /**
     * Hide login page from unauthorized access
     * 
     * @return void
     */
    private function hideLoginPage(): void {
        // Login hiding functionality is currently disabled
        // This method is kept for future use when login hiding is re-enabled
    }
    
    /**
     * Automatically logout idle users
     * 
     * @return void
     */
    private function autoLogoutIdleUsers(): void {
        add_action('init', [$this, 'checkUserIdleTime']);
        add_action('wp_login', [$this, 'setUserLastActivity']);
        add_action('wp_loaded', [$this, 'updateUserActivity']);
    }
    
    /**
     * Check if user has been idle too long
     * 
     * @return void
     */
    public function checkUserIdleTime(): void {
        if (!is_user_logged_in()) {
            return;
        }
        
        $user_id = get_current_user_id();
        $last_activity = get_user_meta($user_id, '_last_activity', true);
        
        if ($last_activity) {
            $idle_time = time() - $last_activity;
            $timeout = $this->config['idle_timeout'] * 60; // Convert to seconds
            
            if ($idle_time > $timeout) {
                wp_logout();
                wp_redirect(home_url());
                exit;
            }
        }
    }
    
    /**
     * Set user last activity on login
     * 
     * @param string $user_login Username
     * @return void
     */
    public function setUserLastActivity(string $user_login): void {
        $user = get_user_by('login', $user_login);
        if ($user) {
            update_user_meta($user->ID, '_last_activity', time());
        }
    }
    
    /**
     * Update user activity timestamp
     * 
     * @return void
     */
    public function updateUserActivity(): void {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            update_user_meta($user_id, '_last_activity', time());
        }
    }
    
    /**
     * Disable XML-RPC functionality
     * 
     * @return void
     */
    private function disableXmlRpc(): void {
        // Disable XML-RPC
        add_filter('xmlrpc_enabled', '__return_false');
        
        // Remove RSD link
        remove_action('wp_head', 'rsd_link');
        
        // Remove Windows Live Writer manifest link
        remove_action('wp_head', 'wlwmanifest_link');
        
        // Block XML-RPC requests
        add_action('init', function() {
            if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'xmlrpc.php') !== false) {
                http_response_code(403);
                exit('XML-RPC is disabled for security reasons.');
            }
        });
        
        // Add X-Pingback header removal
        add_filter('wp_headers', [$this, 'removePingbackHeader']);
        
        // Block pingback requests
        add_filter('xmlrpc_methods', [$this, 'removeXmlrpcMethods']);
    }
    
    /**
     * Remove pingback header
     * 
     * @param array $headers HTTP headers
     * @return array
     */
    public function removePingbackHeader(array $headers): array {
        unset($headers['X-Pingback']);
        return $headers;
    }
    
    /**
     * Remove XML-RPC methods
     * 
     * @param array $methods XML-RPC methods
     * @return array
     */
    public function removeXmlrpcMethods(array $methods): array {
        unset($methods['pingback.ping']);
        unset($methods['pingback.extensions.getPingbacks']);
        return $methods;
    }
    
    /**
     * Get custom login URL
     * 
     * @return string
     */
    public function getCustomLoginUrl(): string {
        if ($this->config['hide_login_page']) {
            return home_url('?' . $this->config['custom_login_slug'] . '=1');
        }
        return wp_login_url();
    }
    
    /**
     * Update security configuration
     * 
     * @param array $config New configuration
     * @return void
     */
    public function updateConfig(array $config): void {
        $this->config = array_merge($this->config, $config);
        $this->init();
    }
    
    /**
     * Get current security configuration
     * 
     * @return array
     */
    public function getConfig(): array {
        return $this->config;
    }
}