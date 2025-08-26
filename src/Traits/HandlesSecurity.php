<?php
/**
 * Handles Security Functionality
 * 
 * Trait for managing WordPress security features
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Traits;

use WordPressSkin\Security\SecurityManager;

defined('ABSPATH') or exit;

trait HandlesSecurity {
    
    /**
     * Security manager instance
     * 
     * @var SecurityManager|null
     */
    private $security_manager = null;
    
    /**
     * Initialize security functionality
     * 
     * @param array $config Optional security configuration
     * @return void
     */
    protected function initializeSecurity(array $config = []): void {
        $this->security_manager = new SecurityManager($config);
    }
    
    /**
     * Get security manager instance
     * 
     * @return SecurityManager
     */
    public function getSecurityManager(): SecurityManager {
        if ($this->security_manager === null) {
            $this->initializeSecurity();
        }
        
        return $this->security_manager;
    }
    
    /**
     * Configure security settings
     * 
     * @param array $config Security configuration
     * @return self
     */
    public function configureSecurity(array $config): self {
        $this->getSecurityManager()->updateConfig($config);
        return $this;
    }
    
    /**
     * Enable WordPress version removal
     * 
     * @return self
     */
    public function removeWpVersion(): self {
        return $this->configureSecurity(['remove_wp_version' => true]);
    }
    
    /**
     * Disable directory browsing
     * 
     * @return self
     */
    public function disableDirectoryBrowsing(): self {
        return $this->configureSecurity(['disable_directory_browsing' => true]);
    }
    
    /**
     * Hide login page with custom slug
     * 
     * @param string|null $slug Custom login slug
     * @return self
     */
    public function hideLoginPage(string $slug = null): self {
        $config = ['hide_login_page' => true];
        if ($slug !== null) {
            $config['custom_login_slug'] = $slug;
        }
        return $this->configureSecurity($config);
    }
    
    /**
     * Enable automatic logout for idle users
     * 
     * @param int $timeout Timeout in minutes (default: 30)
     * @return self
     */
    public function autoLogoutIdleUsers(int $timeout = 30): self {
        return $this->configureSecurity([
            'auto_logout_idle_users' => true,
            'idle_timeout' => $timeout
        ]);
    }
    
    /**
     * Disable XML-RPC functionality
     * 
     * @return self
     */
    public function disableXmlRpc(): self {
        return $this->configureSecurity(['disable_xmlrpc' => true]);
    }
    
    /**
     * Get custom login URL
     * 
     * @return string
     */
    public function getCustomLoginUrl(): string {
        return $this->getSecurityManager()->getCustomLoginUrl();
    }
    
    /**
     * Enable all security features with default settings
     * 
     * @return self
     */
    public function enableAllSecurity(): self {
        return $this->configureSecurity([
            'remove_wp_version' => true,
            'disable_directory_browsing' => true,
            'hide_login_page' => true,
            'auto_logout_idle_users' => true,
            'disable_xmlrpc' => true,
            'idle_timeout' => 30
        ]);
    }
    
    /**
     * Get current security configuration
     * 
     * @return array
     */
    public function getSecurityConfig(): array {
        return $this->getSecurityManager()->getConfig();
    }
}