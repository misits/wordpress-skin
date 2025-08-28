<?php
/**
 * WP-Skin Bootstrap
 *
 * Unified theme management system for WordPress
 *
 * @package WP-Skin
 * @version 1.0.0
 */

defined("ABSPATH") or exit();

// Define WP-Skin constants
if (!defined("WPSKIN_VERSION")) {
    define("WPSKIN_VERSION", "1.0.0");
}

if (!defined("WPSKIN_PATH")) {
    define("WPSKIN_PATH", __DIR__);
}

if (!defined("WPSKIN_URL")) {
    $theme_dir = get_template_directory_uri();
    $relative_path = str_replace(get_template_directory(), "", WPSKIN_PATH);
    define("WPSKIN_URL", $theme_dir . $relative_path);
}

// Theme mode only - no plugin mode
if (!defined("WPSKIN_MODE")) {
    define("WPSKIN_MODE", "theme");
}

/**
 * Autoloader for WP-Skin classes and App namespace
 */
function wpskin_autoload($class)
{
    // Handle WordPressSkin namespace
    $wpskin_prefix = "WordPressSkin\\";
    $wpskin_base_dir = WPSKIN_PATH . "/src/";

    $wpskin_len = strlen($wpskin_prefix);
    if (strncmp($wpskin_prefix, $class, $wpskin_len) === 0) {
        $relative_class = substr($class, $wpskin_len);
        $file =
            $wpskin_base_dir . str_replace("\\", "/", $relative_class) . ".php";

        if (file_exists($file)) {
            require $file;
            return;
        }
    }

    // Handle App namespace for PhytoPass
    $app_prefix = "App\\";
    $app_base_dir = get_template_directory() . "/app/";

    $app_len = strlen($app_prefix);
    if (strncmp($app_prefix, $class, $app_len) === 0) {
        $relative_class = substr($class, $app_len);
        $file =
            $app_base_dir . str_replace("\\", "/", $relative_class) . ".php";

        if (file_exists($file)) {
            require $file;
            return;
        }
    }
}

spl_autoload_register("wpskin_autoload");

// Load helper functions
require_once __DIR__ . "/src/helpers.php";

/**
 * Auto-load theme skin configuration
 */
function wpskin_auto_load_config()
{
    $theme_root = get_template_directory();
    $config_files = [
        $theme_root . "/skin.php",
        $theme_root . "/config/skin.php",
        $theme_root . "/theme/skin.php",
    ];

    $config_found = false;
    foreach ($config_files as $config_file) {
        if (file_exists($config_file)) {
            require_once $config_file;
            $config_found = true;
            break;
        }
    }

    // Auto-create skin.php if none found
    if (!$config_found) {
        wpskin_create_default_config();
    }
}

/**
 * Create default skin.php configuration file
 */
function wpskin_create_default_config()
{
    $theme_root = get_template_directory();
    $skin_file = $theme_root . "/skin.php";

    // Only create if we have write permissions and file doesn't exist
    if (!file_exists($skin_file) && is_writable($theme_root)) {
        $theme_name = wp_get_theme()->get("Name");
        $theme_slug = get_template();

        $default_config = wpskin_get_default_config_template(
            $theme_name,
            $theme_slug,
        );

        if (file_put_contents($skin_file, $default_config)) {
            // Log success in debug mode
            if (WP_DEBUG) {
                error_log(
                    "WP-Skin: Created default skin.php configuration file at {$skin_file}",
                );
            }

            // Include the newly created file
            require_once $skin_file;
        }
    }
}

/**
 * Load template from file
 */
function wpskin_load_template($template_name, $replacements = [])
{
    $template_path = __DIR__ . "/templates/" . $template_name . ".template";

    if (!file_exists($template_path)) {
        return "";
    }

    $content = file_get_contents($template_path);

    // Replace placeholders
    foreach ($replacements as $key => $value) {
        $content = str_replace("{{" . $key . "}}", $value, $content);
    }

    return $content;
}

/**
 * Get default skin.php configuration template
 */
function wpskin_get_default_config_template($theme_name, $theme_slug)
{
    return wpskin_load_template("skin.php", [
        "THEME_NAME" => $theme_name,
        "THEME_SLUG" => $theme_slug,
    ]);
}

/**
 * Get starter CSS template
 */
function wpskin_get_starter_css()
{
    return wpskin_load_template("style.css", [
        "THEME_NAME" => wp_get_theme()->get("Name"),
    ]);
}

/**
 * Get starter app.css template
 */
function wpskin_get_starter_app_css()
{
    return wpskin_load_template("app.css", [
        "THEME_NAME" => wp_get_theme()->get("Name"),
    ]);
}

/**
 * Get starter JS template
 */
function wpskin_get_starter_js()
{
    return wpskin_load_template("main.js", [
        "THEME_NAME" => wp_get_theme()->get("Name"),
    ]);
}

/**
 * Get starter header component template
 */
function wpskin_get_starter_header_component()
{
    return wpskin_load_template("header.php");
}

/**
 * Get starter footer component template
 */
function wpskin_get_starter_footer_component()
{
    return wpskin_load_template("footer.php");
}

/**
 * Get starter layout template
 */
function wpskin_get_starter_layout()
{
    return wpskin_load_template("layout.php");
}

/**
 * Get starter page view template
 */
function wpskin_get_starter_page_view()
{
    return wpskin_load_template("page.php");
}

/**
 * Get starter modern JS template
 */
function wpskin_get_starter_modern_js()
{
    return wpskin_load_template("app.js", [
        "THEME_NAME" => wp_get_theme()->get("Name"),
    ]);
}

/**
 * Get starter SCSS template
 */
function wpskin_get_starter_scss()
{
    return wpskin_load_template("style.scss", [
        "THEME_NAME" => wp_get_theme()->get("Name"),
    ]);
}

/**
 * Get starter SCSS variables template
 */
function wpskin_get_starter_scss_variables()
{
    return wpskin_load_template("_variables.scss", [
        "THEME_NAME" => wp_get_theme()->get("Name"),
    ]);
}

/**
 * Get starter package.json template
 */
function wpskin_get_starter_package_json()
{
    return wpskin_load_template("package.json", [
        "THEME_NAME" => wp_get_theme()->get("Name"),
        "THEME_SLUG" => get_template(),
    ]);
}

/**
 * Get starter vite config template
 */
function wpskin_get_starter_vite_config()
{
    return wpskin_load_template("vite.config.mjs");
}

/**
 * Get resources README template
 */
function wpskin_get_resources_readme()
{
    return wpskin_load_template("README.md", [
        "THEME_NAME" => wp_get_theme()->get("Name"),
    ]);
}

/**
 * Get gitignore template
 */
function wpskin_get_gitignore_template()
{
    return wpskin_load_template(".gitignore");
}

/**
 * Get WordPress theme root header.php template
 */
function wpskin_get_root_header_template()
{
    return wpskin_load_template("header-root.php");
}

/**
 * Get WordPress theme root footer.php template
 */
function wpskin_get_root_footer_template()
{
    return wpskin_load_template("footer-root.php");
}

/**
 * Get WordPress theme root index.php template
 */
function wpskin_get_root_index_template()
{
    return wpskin_load_template("index.php", [
        "THEME_NAME" => wp_get_theme()->get("Name"),
    ]);
}

/**
 * Get WordPress theme front-page.php template
 */
function wpskin_get_front_page_template()
{
    return wpskin_load_template("front-page.php", [
        "THEME_NAME" => wp_get_theme()->get("Name"),
    ]);
}

/**
 * Initialize WP-Skin
 */
function wpskin_boot()
{
    // Initialize the main Skin class
    \WordPressSkin\Core\Skin::getInstance();

    // Auto-scaffold resources directory structure
    wpskin_scaffold_resources();

    // Auto-load theme configuration
    wpskin_auto_load_config();

    // Fire initialization hook
    do_action("wpskin_loaded");
}

/**
 * Create essential WordPress theme files in theme root
 */
function wpskin_create_theme_root_files()
{
    $theme_root = get_template_directory();

    $theme_root_files = [
        "/style.css" => wpskin_get_starter_css(),
        "/header.php" => wpskin_get_root_header_template(),
        "/footer.php" => wpskin_get_root_footer_template(),
        "/index.php" => wpskin_get_root_index_template(),
        "/front-page.php" => wpskin_get_front_page_template(),
    ];

    foreach ($theme_root_files as $file_path => $content) {
        $full_path = $theme_root . $file_path;
        if (!file_exists($full_path) && $content) {
            file_put_contents($full_path, $content);
        }
    }
}

/**
 * Auto-scaffold resources directory structure
 */
function wpskin_scaffold_resources()
{
    $theme_root = get_template_directory();
    $resources_path = $theme_root . "/resources";

    // Only scaffold if theme directory is writable and resources doesn't exist
    if (!is_dir($resources_path) && is_writable($theme_root)) {
        $directories = [
            $resources_path,
            $resources_path . "/assets",
            $resources_path . "/assets/css",
            $resources_path . "/assets/js",
            $resources_path . "/assets/fonts",
            $resources_path . "/assets/images",
            $resources_path . "/components",
            $resources_path . "/layouts",
            $resources_path . "/views",
            $resources_path . "/src", // For modern JS build tools
            $resources_path . "/src/js",
            $resources_path . "/src/scss", // SCSS source files
            $resources_path . "/src/css", // Compiled CSS output
            $resources_path . "/dist", // For built assets
        ];

        $created_directories = [];
        foreach ($directories as $dir) {
            if (wp_mkdir_p($dir)) {
                $created_directories[] = basename(
                    str_replace($theme_root, "", $dir),
                );
            }
        }

        if (!empty($created_directories)) {
            // Create starter files
            wpskin_create_starter_files($resources_path);

            // Log success in debug mode
            if (WP_DEBUG) {
                error_log(
                    "WP-Skin: Scaffolded resources directory structure at {$resources_path}",
                );
            }
        }
    }

    // Always check and create essential theme root files
    wpskin_create_theme_root_files();

    // Always check and create .gitignore
    wpskin_create_gitignore(get_template_directory());
}

/**
 * Create starter files in resources directory
 */
function wpskin_create_starter_files($resources_path)
{
    // First check if the resources directory exists
    if (!is_dir($resources_path)) {
        return;
    }

    $starter_files = [
        // JS starter file
        "/assets/js/main.js" => wpskin_get_starter_js(),

        // Component starter files - defined later in file
        "/components/header.php" => null, // Will be set later
        "/components/footer.php" => null,

        // Layout starter files - defined later in file
        "/layouts/default.php" => null,

        // View starter files - defined later in file
        "/views/page-content.php" => null,

        // Modern JS starter files - defined later in file
        "/src/js/app.js" => null,

        // Build tool configs - defined later in file
        "/package.json" => null,
        "/vite.config.mjs" => null,

        // README for guidance - defined later in file
        "/README.md" => null,
    ];

    // Create basic files that are defined above
    foreach ($starter_files as $file_path => $content) {
        if ($content === null) {
            continue;
        } // Skip files with content defined later

        $full_path = $resources_path . $file_path;
        $dir = dirname($full_path);

        // Ensure directory exists
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }

        // Create file if it doesn't exist
        if (!file_exists($full_path) && $content) {
            file_put_contents($full_path, $content);
        }
    }

    // Use a deferred approach for files with content defined later
    add_action(
        "after_setup_theme",
        function () use ($resources_path) {
            wpskin_create_remaining_starter_files($resources_path);
        },
        15,
    );
}

/**
 * Create remaining starter files after all functions are defined
 */
function wpskin_create_remaining_starter_files($resources_path)
{
    $remaining_files = [
        // Component starter files (minimal)
        "/components/header.php" => wpskin_get_starter_header_component(),
        "/components/footer.php" => wpskin_get_starter_footer_component(),

        // Layout starter files
        "/layouts/default.php" => wpskin_get_starter_layout(),

        // View starter files
        "/views/page-content.php" => wpskin_get_starter_page_view(),

        // Modern JS starter files
        "/src/js/app.js" => wpskin_get_starter_modern_js(),

        // SCSS starter files (minimal)
        "/src/scss/style.scss" => wpskin_get_starter_scss(),
        "/src/scss/_variables.scss" => wpskin_get_starter_scss_variables(),

        // CSS output (compiled from SCSS)
        "/src/css/app.css" => wpskin_get_starter_app_css(),

        // Build tool configs
        "/package.json" => wpskin_get_starter_package_json(),
        "/vite.config.mjs" => wpskin_get_starter_vite_config(),

        // README for guidance
        "/README.md" => wpskin_get_resources_readme(),
    ];

    foreach ($remaining_files as $file_path => $content) {
        $full_path = $resources_path . $file_path;
        $dir = dirname($full_path);

        // Ensure directory exists
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }

        // Create file if it doesn't exist
        if (!file_exists($full_path) && $content) {
            file_put_contents($full_path, $content);
        }
    }
}

/**
 * Create .gitignore file for production deployment
 */
function wpskin_create_gitignore($theme_root)
{
    $gitignore_path = $theme_root . "/.gitignore";

    if (!file_exists($gitignore_path)) {
        $gitignore_content = wpskin_get_gitignore_template();
        file_put_contents($gitignore_path, $gitignore_content);
    }
}

// Register WP-CLI commands after WordPress is initialized
if (defined("WP_CLI") && WP_CLI) {
    add_action(
        "init",
        function () {
            require_once __DIR__ . "/cli/WP/CommandRegistrar.php";
            \WordPressSkin\CLI\WP\CommandRegistrar::register();
        },
        10,
    );
}
// Initialize WP-Skin when theme is loaded
add_action("after_setup_theme", "wpskin_boot", 10);
