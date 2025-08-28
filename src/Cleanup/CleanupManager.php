<?php
/**
 * WordPress Cleanup Manager
 *
 * Handles WordPress cleanup and feature removal
 *
 * @package WP-Skin
 */

namespace WordPressSkin\Cleanup;

defined("ABSPATH") or exit();

class CleanupManager
{
    /**
     * Cleanup configuration
     *
     * @var array
     */
    private $config = [
        "remove_comments" => true,
        "remove_block_styles" => true,
        "remove_emoji" => true,
        "remove_rest_api" => true,
        "remove_oembed" => true,
        "remove_admin_bar" => true,
        "remove_file_editors" => true,
        "remove_shortlink" => true,
    ];

    /**
     * Track which cleanup features are currently active
     *
     * @var array
     */
    private $activeFeatures = [];

    /**
     * Constructor
     *
     * @param array $config Optional cleanup configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->init();
    }

    /**
     * Initialize cleanup features
     *
     * @return void
     */
    private function init(): void
    {
        // Handle comments removal
        if (
            $this->config["remove_comments"] &&
            !in_array("remove_comments", $this->activeFeatures)
        ) {
            $this->removeComments();
            $this->activeFeatures[] = "remove_comments";
        } elseif (
            !$this->config["remove_comments"] &&
            in_array("remove_comments", $this->activeFeatures)
        ) {
            $this->restoreComments();
            $this->activeFeatures = array_diff($this->activeFeatures, [
                "remove_comments",
            ]);
        }

        // Handle block styles removal
        if (
            $this->config["remove_block_styles"] &&
            !in_array("remove_block_styles", $this->activeFeatures)
        ) {
            $this->removeBlockStyles();
            $this->activeFeatures[] = "remove_block_styles";
        } elseif (
            !$this->config["remove_block_styles"] &&
            in_array("remove_block_styles", $this->activeFeatures)
        ) {
            $this->restoreBlockStyles();
            $this->activeFeatures = array_diff($this->activeFeatures, [
                "remove_block_styles",
            ]);
        }

        // Handle emoji removal
        if (
            $this->config["remove_emoji"] &&
            !in_array("remove_emoji", $this->activeFeatures)
        ) {
            $this->removeEmoji();
            $this->activeFeatures[] = "remove_emoji";
        } elseif (
            !$this->config["remove_emoji"] &&
            in_array("remove_emoji", $this->activeFeatures)
        ) {
            $this->restoreEmoji();
            $this->activeFeatures = array_diff($this->activeFeatures, [
                "remove_emoji",
            ]);
        }

        // Handle REST API removal
        if (
            $this->config["remove_rest_api"] &&
            !in_array("remove_rest_api", $this->activeFeatures)
        ) {
            $this->removeRestApi();
            $this->activeFeatures[] = "remove_rest_api";
        } elseif (
            !$this->config["remove_rest_api"] &&
            in_array("remove_rest_api", $this->activeFeatures)
        ) {
            $this->restoreRestApi();
            $this->activeFeatures = array_diff($this->activeFeatures, [
                "remove_rest_api",
            ]);
        }

        // Handle oEmbed removal
        if (
            $this->config["remove_oembed"] &&
            !in_array("remove_oembed", $this->activeFeatures)
        ) {
            $this->removeOembed();
            $this->activeFeatures[] = "remove_oembed";
        } elseif (
            !$this->config["remove_oembed"] &&
            in_array("remove_oembed", $this->activeFeatures)
        ) {
            $this->restoreOembed();
            $this->activeFeatures = array_diff($this->activeFeatures, [
                "remove_oembed",
            ]);
        }

        // Handle admin bar removal
        if (
            $this->config["remove_admin_bar"] &&
            !in_array("remove_admin_bar", $this->activeFeatures)
        ) {
            $this->removeAdminBar();
            $this->activeFeatures[] = "remove_admin_bar";
        } elseif (
            !$this->config["remove_admin_bar"] &&
            in_array("remove_admin_bar", $this->activeFeatures)
        ) {
            $this->restoreAdminBar();
            $this->activeFeatures = array_diff($this->activeFeatures, [
                "remove_admin_bar",
            ]);
        }

        // Handle file editors removal
        if (
            $this->config["remove_file_editors"] &&
            !in_array("remove_file_editors", $this->activeFeatures)
        ) {
            $this->removeFileEditors();
            $this->activeFeatures[] = "remove_file_editors";
        } elseif (
            !$this->config["remove_file_editors"] &&
            in_array("remove_file_editors", $this->activeFeatures)
        ) {
            $this->restoreFileEditors();
            $this->activeFeatures = array_diff($this->activeFeatures, [
                "remove_file_editors",
            ]);
        }

        // Handle shortlink removal
        if (
            $this->config["remove_shortlink"] &&
            !in_array("remove_shortlink", $this->activeFeatures)
        ) {
            $this->removeShortlink();
            $this->activeFeatures[] = "remove_shortlink";
        } elseif (
            !$this->config["remove_shortlink"] &&
            in_array("remove_shortlink", $this->activeFeatures)
        ) {
            $this->restoreShortlink();
            $this->activeFeatures = array_diff($this->activeFeatures, [
                "remove_shortlink",
            ]);
        }
    }

    /**
     * Remove WordPress block styles
     *
     * @return void
     */
    private function removeBlockStyles(): void
    {
        add_action(
            "wp_enqueue_scripts",
            function () {
                wp_dequeue_style("wp-block-library");
                wp_dequeue_style("wp-block-library-theme");
                wp_dequeue_style("classic-theme-styles");
                wp_dequeue_style("global-styles");
            },
            100
        );

        // Disable WordPress Global Styles
        remove_action("wp_enqueue_scripts", "wp_enqueue_global_styles");
    }

    /**
     * Remove emoji detection scripts
     *
     * @return void
     */
    private function removeEmoji(): void
    {
        remove_action("wp_head", "print_emoji_detection_script", 7);
        remove_action("wp_print_styles", "print_emoji_styles");
        remove_action("admin_print_scripts", "print_emoji_detection_script");
        remove_action("admin_print_styles", "print_emoji_styles");
    }

    /**
     * Remove REST API links and headers
     *
     * @return void
     */
    private function removeRestApi(): void
    {
        remove_action("wp_head", "rest_output_link_wp_head");
        remove_action("template_redirect", "rest_output_link_header");
        remove_action("wp_head", "rsd_link");
    }

    /**
     * Remove oEmbed discovery links
     *
     * @return void
     */
    private function removeOembed(): void
    {
        remove_action("wp_head", "wp_oembed_add_discovery_links");
    }

    /**
     * Disable WordPress admin bar
     *
     * @return void
     */
    private function removeAdminBar(): void
    {
        add_filter("show_admin_bar", "__return_false");
    }

    /**
     * Remove comments system completely
     *
     * @return void
     */
    private function removeComments(): void
    {
        // Admin initialization
        add_action("admin_init", [$this, "handleAdminComments"]);

        // Front-end comment blocking
        add_filter("comments_open", "__return_false", 20, 2);
        add_filter("pings_open", "__return_false", 20, 2);
        add_filter("comments_array", "__return_empty_array", 10, 2);

        // Remove comments from admin menu (only if comments are disabled)
        add_action("admin_menu", [$this, "removeCommentsMenu"]);

        // Remove comments from admin bar
        add_action("init", [$this, "removeCommentsFromAdminBar"]);
        add_action("wp_before_admin_bar_render", [$this, "removeAdminBarComments"]);

        // Remove comment-related admin pages (only if comments are disabled)
        add_action("admin_head", [$this, "removeCommentAdminPages"]);

        // Disable comment feeds
        add_filter("feed_links_show_comments_feed", "__return_false");

        // Remove comment reply script
        add_action(
            "wp_enqueue_scripts",
            function () {
                wp_dequeue_script("comment-reply");
            },
            100
        );
    }

    /**
     * Handle comments in admin area
     *
     * @return void
     */
    public function handleAdminComments(): void
    {
        global $pagenow;

        // Redirect any user trying to access comments page
        if ($pagenow === "edit-comments.php") {
            wp_safe_redirect(admin_url());
            exit();
        }

        // Remove comments metabox from dashboard
        remove_meta_box("dashboard_recent_comments", "dashboard", "normal");

        // Disable support for comments and trackbacks in post types
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, "comments")) {
                remove_post_type_support($post_type, "comments");
                remove_post_type_support($post_type, "trackbacks");
            }
        }
    }

    /**
     * Remove comments links from admin bar
     *
     * @return void
     */
    public function removeCommentsFromAdminBar(): void
    {
        // Only remove if comments are disabled
        if ($this->config["remove_comments"] && is_admin_bar_showing()) {
            remove_action("admin_bar_menu", "wp_admin_bar_comments_menu", 60);
        }
    }

    /**
     * Remove comments menu from admin bar
     *
     * @return void
     */
    public function removeAdminBarComments(): void
    {
        // Only remove if comments are disabled
        if ($this->config["remove_comments"]) {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu("comments");
        }
    }

    /**
     * Remove comments menu from admin
     *
     * @return void
     */
    public function removeCommentsMenu(): void
    {
        // Only remove if comments are disabled
        if ($this->config["remove_comments"]) {
            remove_menu_page("edit-comments.php");
        }
    }

    /**
     * Remove comment-related admin pages
     *
     * @return void
     */
    public function removeCommentAdminPages(): void
    {
        // Only remove if comments are disabled
        if ($this->config["remove_comments"]) {
            remove_submenu_page("options-general.php", "options-discussion.php");
        }
    }

    /**
     * Update cleanup configuration
     *
     * @param array $config New configuration
     * @return void
     */
    public function updateConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        $this->init();
    }

    /**
     * Restore WordPress block styles
     *
     * @return void
     */
    private function restoreBlockStyles(): void
    {
        // Note: Block styles cannot be easily restored once removed
        // This would require re-enqueuing styles during wp_enqueue_scripts
        // which may not work reliably after they've been dequeued
    }

    /**
     * Restore emoji detection scripts
     *
     * @return void
     */
    private function restoreEmoji(): void
    {
        // Re-add emoji scripts (only if not already added)
        if (!has_action("wp_head", "print_emoji_detection_script")) {
            add_action("wp_head", "print_emoji_detection_script", 7);
        }
        if (!has_action("wp_print_styles", "print_emoji_styles")) {
            add_action("wp_print_styles", "print_emoji_styles");
        }
        if (!has_action("admin_print_scripts", "print_emoji_detection_script")) {
            add_action("admin_print_scripts", "print_emoji_detection_script");
        }
        if (!has_action("admin_print_styles", "print_emoji_styles")) {
            add_action("admin_print_styles", "print_emoji_styles");
        }
    }

    /**
     * Restore REST API links and headers
     *
     * @return void
     */
    private function restoreRestApi(): void
    {
        // Re-add REST API links (only if not already added)
        if (!has_action("wp_head", "rest_output_link_wp_head")) {
            add_action("wp_head", "rest_output_link_wp_head");
        }
        if (!has_action("template_redirect", "rest_output_link_header")) {
            add_action("template_redirect", "rest_output_link_header");
        }
        if (!has_action("wp_head", "rsd_link")) {
            add_action("wp_head", "rsd_link");
        }
    }

    /**
     * Restore oEmbed discovery links
     *
     * @return void
     */
    private function restoreOembed(): void
    {
        // Re-add oEmbed links (only if not already added)
        if (!has_action("wp_head", "wp_oembed_add_discovery_links")) {
            add_action("wp_head", "wp_oembed_add_discovery_links");
        }
    }

    /**
     * Enable WordPress admin bar
     *
     * @return void
     */
    private function restoreAdminBar(): void
    {
        // Remove the filter that disables admin bar
        remove_filter("show_admin_bar", "__return_false");
    }

    /**
     * Remove file editors from admin
     *
     * @return void
     */
    private function removeFileEditors(): void
    {
        // Remove plugin editor menus
        add_action("admin_menu", [$this, "removeFileEditorMenus"], 999);
        
        // Block direct access to editor pages
        add_action("admin_init", [$this, "blockFileEditorAccess"]);
    }

    /**
     * Remove file editor menu items
     *
     * @return void
     */
    public function removeFileEditorMenus(): void
    {
        // Only remove if file editors are disabled
        if ($this->config["remove_file_editors"]) {
            remove_submenu_page("plugins.php", "plugin-editor.php");
            remove_submenu_page("themes.php", "theme-editor.php");
            
            // Remove site editor - try multiple possible menu locations
            remove_submenu_page("themes.php", "site-editor.php");
            remove_submenu_page("themes.php", "site-editor.php?p=%2F");
            
            // Also try removing by menu title (Full Site Editing/Editor)
            global $submenu;
            if (isset($submenu['themes.php'])) {
                foreach ($submenu['themes.php'] as $key => $menu_item) {
                    if (strpos($menu_item[2], 'site-editor.php') !== false) {
                        unset($submenu['themes.php'][$key]);
                    }
                }
            }
        }
    }

    /**
     * Block direct access to file editor pages
     *
     * @return void
     */
    public function blockFileEditorAccess(): void
    {
        // Only block if file editors are disabled
        if ($this->config["remove_file_editors"]) {
            global $pagenow;
            
            if ($pagenow === "plugin-editor.php" || $pagenow === "theme-editor.php" || $pagenow === "site-editor.php") {
                wp_safe_redirect(admin_url());
                exit();
            }
        }
    }

    /**
     * Restore file editors
     *
     * @return void
     */
    private function restoreFileEditors(): void
    {
        // Remove the hook that removes file editor menus
        remove_action("admin_menu", [$this, "removeFileEditorMenus"]);
        
        // Remove the hook that blocks direct access
        remove_action("admin_init", [$this, "blockFileEditorAccess"]);
    }

    /**
     * Remove shortlink from head
     *
     * @return void
     */
    private function removeShortlink(): void
    {
        // Remove shortlink from head
        remove_action("wp_head", "wp_shortlink_wp_head");
        
        // Remove shortlink HTTP header
        remove_action("template_redirect", "wp_shortlink_header");
    }

    /**
     * Restore shortlink
     *
     * @return void
     */
    private function restoreShortlink(): void
    {
        // Re-add shortlink to head (only if not already added)
        if (!has_action("wp_head", "wp_shortlink_wp_head")) {
            add_action("wp_head", "wp_shortlink_wp_head");
        }
        
        // Re-add shortlink HTTP header (only if not already added)
        if (!has_action("template_redirect", "wp_shortlink_header")) {
            add_action("template_redirect", "wp_shortlink_header");
        }
    }

    /**
     * Restore comments system
     *
     * @return void
     */
    private function restoreComments(): void
    {
        // Remove admin comment handlers
        remove_action("admin_init", [$this, "handleAdminComments"]);

        // Remove front-end comment blocking
        remove_filter("comments_open", "__return_false", 20);
        remove_filter("pings_open", "__return_false", 20);
        remove_filter("comments_array", "__return_empty_array", 10);

        // Remove admin bar comment removal
        remove_action("init", [$this, "removeCommentsFromAdminBar"]);
        remove_action("wp_before_admin_bar_render", [$this, "removeAdminBarComments"]);

        // Remove admin menu hooks
        remove_action("admin_menu", [$this, "removeCommentsMenu"]);
        remove_action("admin_head", [$this, "removeCommentAdminPages"]);

        // Remove comment feed filter
        remove_filter("feed_links_show_comments_feed", "__return_false");

        // Note: Admin menu changes already applied cannot be reversed in same request
        // A page refresh is needed to see the Comments menu restored
    }

    /**
     * Get current cleanup configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}