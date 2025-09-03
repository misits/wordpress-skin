<?php

/**
 * Main Skin Class
 *
 * Unified interface for all theme management features
 *
 * @package WP-Skin
 */

namespace WordPressSkin\Core;

use WordPressSkin\Traits\HandlesCustomizer;
use WordPressSkin\Traits\HandlesAssets;
use WordPressSkin\Traits\HandlesHooks;
use WordPressSkin\Traits\HandlesLayouts;
use WordPressSkin\Traits\HandlesComponents;
use WordPressSkin\Traits\HandlesPostTypes;
use WordPressSkin\Traits\HandlesTaxonomies;
use WordPressSkin\Traits\HandlesSecurity;
use WordPressSkin\Traits\HandlesCleanup;

defined("ABSPATH") or exit();

class Skin
{
    use HandlesCustomizer;
    use HandlesAssets;
    use HandlesHooks;
    use HandlesLayouts;
    use HandlesComponents;
    use HandlesPostTypes;
    use HandlesTaxonomies;
    use HandlesSecurity;
    use HandlesCleanup;

    /**
     * Singleton instance
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Theme root directory
     *
     * @var string
     */
    private $theme_root;

    /**
     * Resources directory
     *
     * @var string
     */
    private $resources_path;

    /**
     * Resources URL
     *
     * @var string
     */
    private $resources_url;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->theme_root = get_template_directory();
        $this->resources_path = $this->theme_root . "/resources";
        $this->resources_url = get_template_directory_uri() . "/resources";

        $this->initializeTraits();
        $this->initializeMaintenanceMode();
    }

    /**
     * Get singleton instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Static method to access customizer functionality
     *
     * @param string|null $section Section name
     * @param callable|null $callback Configuration callback
     * @return \WordPressSkin\Customizer\CustomizerManager|\WordPressSkin\Customizer\Section
     */
    public static function customizer($section = null, $callback = null)
    {
        $instance = self::getInstance();

        if ($section === null) {
            return $instance->getCustomizerManager();
        }

        return $instance->addCustomizerSection($section, $callback);
    }

    /**
     * Static method to access assets functionality
     *
     * @return \WordPressSkin\Assets\AssetManager
     */
    public static function assets()
    {
        return self::getInstance()->getAssetManager();
    }

    /**
     * Static method to access hooks functionality
     *
     * @return \WordPressSkin\Hooks\HookManager
     */
    public static function hooks()
    {
        return self::getInstance()->getHookManager();
    }

    /**
     * Static method to access layouts functionality
     *
     * @return \WordPressSkin\Core\LayoutManager
     */
    public static function layouts()
    {
        return self::getInstance()->getLayoutManager();
    }

    /**
     * Static method to access components functionality
     *
     * @return \WordPressSkin\Core\ComponentManager
     */
    public static function components()
    {
        return self::getInstance()->getComponentManager();
    }

    /**
     * Static method to access post types functionality
     *
     * @return \WordPressSkin\PostTypes\PostTypeManager
     */
    public static function postTypes()
    {
        return self::getInstance()->getPostTypeManager();
    }

    /**
     * Static method to access taxonomies functionality
     *
     * @return \WordPressSkin\Taxonomies\TaxonomyManager
     */
    public static function taxonomies()
    {
        return self::getInstance()->getTaxonomyManager();
    }

    /**
     * Static method to access security functionality
     *
     * @return \WordPressSkin\Security\SecurityManager
     */
    public static function security()
    {
        return self::getInstance()->getSecurityManager();
    }

    /**
     * Static method to access cleanup functionality
     *
     * @return \WordPressSkin\Cleanup\CleanupManager
     */
    public static function cleanup()
    {
        return self::getInstance()->getCleanupManager();
    }

    /**
     * Get theme root directory
     *
     * @return string
     */
    public function getThemeRoot(): string
    {
        return $this->theme_root;
    }

    /**
     * Get resources directory path
     *
     * @return string
     */
    public function getResourcesPath(): string
    {
        return $this->resources_path;
    }

    /**
     * Get resources directory URL
     *
     * @return string
     */
    public function getResourcesUrl(): string
    {
        return $this->resources_url;
    }

    /**
     * Initialize all traits
     *
     * @return void
     */
    private function initializeTraits(): void
    {
        $this->initializeCustomizer();
        $this->initializeAssets();
        $this->initializeHooks();
        $this->initializeLayouts();
        $this->initializeComponents();
        $this->initializePostTypes();
        $this->initializeTaxonomies();
        $this->initializeSecurity();
        $this->initializeCleanup();
    }

    /**
     * Check if resources directory exists
     *
     * @return bool
     */
    public function hasResourcesDirectory(): bool
    {
        return is_dir($this->resources_path);
    }

    /**
     * Create resources directory structure if it doesn't exist
     *
     * @return bool
     */
    public function createResourcesDirectory(): bool
    {
        if ($this->hasResourcesDirectory()) {
            return true;
        }

        $directories = [
            $this->resources_path,
            $this->resources_path . "/assets",
            $this->resources_path . "/assets/css",
            $this->resources_path . "/assets/js",
            $this->resources_path . "/assets/fonts",
            $this->resources_path . "/assets/images",
            $this->resources_path . "/components",
            $this->resources_path . "/layouts",
            $this->resources_path . "/views",
        ];

        foreach ($directories as $dir) {
            if (!wp_mkdir_p($dir)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a new query builder for posts
     *
     * @return \WordPressSkin\Query\QueryBuilder
     */
    public static function query()
    {
        return new \WordPressSkin\Query\QueryBuilder();
    }

    /**
     * Create a new query builder for users
     *
     * @return \WordPressSkin\Query\UserQueryBuilder
     */
    public static function users()
    {
        return new \WordPressSkin\Query\UserQueryBuilder();
    }

    /**
     * Get current post/page context
     *
     * @param callable|null $callback Optional callback to transform the post
     * @return \WordPressSkin\PostTypes\PostType|mixed|null
     */
    public static function current(?callable $callback = null)
    {
        wp_reset_query();

        // Get the queried object (post, page, etc.)
        $queriedObject = get_queried_object();

        if (!$queriedObject || !($queriedObject instanceof \WP_Post)) {
            return null;
        }

        $postType = new \WordPressSkin\PostTypes\PostType($queriedObject);

        return $callback ? $callback($postType) : $postType;
    }

    /**
     * Create a PostType instance
     *
     * @param int|\WP_Post|null $post Post ID, WP_Post object, or null for current
     * @return \WordPressSkin\PostTypes\PostType
     */
    public static function post($post = null)
    {
        return new \WordPressSkin\PostTypes\PostType($post);
    }

    /**
     * Enable or disable maintenance mode
     *
     * @param bool $enable True to enable, false to disable
     * @param string|null $message Optional custom maintenance message
     * @param string|null $title Optional custom maintenance title
     * @return bool Success status
     */
    public static function maintenance(bool $enable = true, ?string $message = null, ?string $title = null): bool
    {
        if ($enable) {
            $maintenance_data = [
                'enabled' => true,
                'enabled_at' => current_time('mysql'),
                'message' => $message ?: 'We are currently performing scheduled maintenance. Please check back soon.',
                'title' => $title ?: 'Maintenance'
            ];

            $result = update_option('wordpress_skin_maintenance_mode', $maintenance_data);

            if ($result) {
                add_action('template_redirect', [self::class, 'handleMaintenanceMode'], 1);
            }

            return $result;
        } else {
            return delete_option('wordpress_skin_maintenance_mode');
        }
    }

    /**
     * Check if maintenance mode is enabled
     *
     * @return bool
     */
    public static function isMaintenanceMode(): bool
    {
        $maintenance_data = get_option('wordpress_skin_maintenance_mode', false);
        return is_array($maintenance_data) && isset($maintenance_data['enabled']) && $maintenance_data['enabled'];
    }

    /**
     * Get maintenance mode data
     *
     * @return array|false
     */
    public static function getMaintenanceData()
    {
        return get_option('wordpress_skin_maintenance_mode', false);
    }

    /**
     * Handle maintenance mode display
     *
     * @return void
     */
    public static function handleMaintenanceMode(): void
    {
        if (!self::isMaintenanceMode()) {
            return;
        }

        if (current_user_can('administrator')) {
            return;
        }

        $maintenance_data = self::getMaintenanceData();
        $message = $maintenance_data['message'] ?? 'We are currently performing scheduled maintenance. Please check back soon.';
        $title = $maintenance_data['title'] ?? 'Maintenance';

        // Clean up empty paragraph tags
        $message = preg_replace('/<p>\s*<\/p>/', '', $message);

        wp_die(
            self::getMaintenanceTemplate($message, $title),
            'Site Maintenance',
            [
                'response' => 503,
                'back_link' => false,
                'text_direction' => 'ltr'
            ]
        );
    }

    /**
     * Get maintenance mode template
     *
     * @param string $message
     * @param string $title
     * @return string
     */
    private static function getMaintenanceTemplate(string $message, string $title = 'Maintenance'): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Site Maintenance</title>
            <style>
            html {
                background-color: #fff;
            }
                body {
                    border: none;
                    box-shadow: none;
                    height: 100vh;
                    margin: 0 auto !important;
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                    h1 {
                    color: #000;
                    font-size: 45px;
                    margin-top: 0;
                    border-bottom: 0;
                    }

                    .wp-die-message,
                    .maintenance-container {
                                    width: 100%;
                    }

                    #error-page p {
                    margin: 0 0 20px;
                    font-size: 16px;
                    line-height: 1.5;
                    max-width: 600px;
                }
            </style>
        </head>
        <body>
            <div class="maintenance-container">
                <h1>' . $title . '</h1>
                ' . $message . '
            </div>
        </body>
        </html>
        ';
    }

    /**
     * Initialize maintenance mode hook
     *
     * @return void
     */
    private function initializeMaintenanceMode(): void
    {
        if (self::isMaintenanceMode()) {
            add_action('template_redirect', [self::class, 'handleMaintenanceMode'], 1);
        }
    }
}
