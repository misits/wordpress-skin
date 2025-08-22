<?php
/**
 * WP-Skin CLI Command Registrar
 * 
 * Registers all WP-Skin CLI commands with WP-CLI
 * 
 * @package WP-Skin
 */

namespace WPSkin\CLI\WP;

/**
 * WP-CLI Command Registrar for WP-Skin
 */
class CommandRegistrar extends \WP_CLI_Command
{
    /**
     * Register all WP-CLI commands
     */
    public static function register()
    {
        if (!class_exists('WP_CLI')) {
            return;
        }

        // Load command classes
        require_once __DIR__ . '/DeployCommand.php';
        require_once __DIR__ . '/HelpCommand.php';

        // Register main help command
        \WP_CLI::add_command('wpskin help', HelpCommand::class);

        // Register deployment commands
        \WP_CLI::add_command('wpskin:deploy', DeployCommand::class);

        // Register asset commands
        \WP_CLI::add_command('wpskin:build', [__CLASS__, 'build']);
        \WP_CLI::add_command('wpskin:watch', [__CLASS__, 'watch']);

        // Register scaffold commands
        \WP_CLI::add_command('wpskin:component', [__CLASS__, 'component']);
        \WP_CLI::add_command('wpskin:layout', [__CLASS__, 'layout']);

        // Register info commands
        \WP_CLI::add_command('wpskin:info', [__CLASS__, 'info']);
        \WP_CLI::add_command('wpskin:status', [__CLASS__, 'status']);
    }

    /**
     * Build theme assets
     *
     * ## OPTIONS
     *
     * [--production]
     * : Build for production (minified, no source maps)
     *
     * ## EXAMPLES
     *
     *     wp wpskin build
     *     wp wpskin build --production
     *
     * @when after_wp_load
     */
    public function build($args, $assoc_args) {
        $theme_root = get_template_directory();
        $resources_dir = $theme_root . '/resources';
        
        if (!is_dir($resources_dir)) {
            \WP_CLI::error('Resources directory not found. Make sure WP-Skin is properly set up.');
        }
        
        if (!file_exists($resources_dir . '/package.json')) {
            \WP_CLI::error('package.json not found in resources directory.');
        }
        
        $production = isset($assoc_args['production']);
        
        \WP_CLI::line('Building theme assets...');
        
        $old_dir = getcwd();
        chdir($resources_dir);
        
        try {
            // Install dependencies if needed
            if (!is_dir('node_modules')) {
                \WP_CLI::line('Installing dependencies...');
                $result = $this->runCommand('npm install');
                if ($result !== 0) {
                    throw new \Exception('Failed to install npm dependencies');
                }
            }
            
            // Choose build command and set environment
            if ($production) {
                \WP_CLI::line('Building for production...');
                // Force production mode for Vite
                $command = 'NODE_ENV=production npm run build';
            } else {
                \WP_CLI::line('Building for development...');
                $command = 'NODE_ENV=development npm run build';
            }
            
            $result = $this->runCommand($command);
            if ($result !== 0) {
                throw new \Exception('Build failed');
            }
            
            \WP_CLI::success('Assets built successfully!');
            
        } catch (\Exception $e) {
            \WP_CLI::error('Asset build failed: ' . $e->getMessage());
        } finally {
            chdir($old_dir);
        }
    }

    /**
     * Watch assets for changes
     *
     * ## EXAMPLES
     *
     *     wp wpskin watch
     *
     * @when after_wp_load
     */
    public function watch($args, $assoc_args) {
        $theme_root = get_template_directory();
        $resources_dir = $theme_root . '/resources';
        
        if (!is_dir($resources_dir)) {
            \WP_CLI::error('Resources directory not found.');
        }
        
        \WP_CLI::line('Watching for changes... (Press Ctrl+C to stop)');
        
        $old_dir = getcwd();
        chdir($resources_dir);
        
        try {
            $this->runCommand('npm run dev');
        } finally {
            chdir($old_dir);
        }
    }

    /**
     * Scaffold a new component
     *
     * ## OPTIONS
     *
     * <name>
     * : Component name
     *
     * [--with-scss]
     * : Create SCSS file for component
     *
     * [--with-js]
     * : Create JS file for component
     *
     * ## EXAMPLES
     *
     *     wp wpskin component navbar
     *     wp wpskin component hero --with-scss --with-js
     *
     * @when after_wp_load
     */
    public function component($args, $assoc_args) {
        if (empty($args[0])) {
            \WP_CLI::error('Component name is required.');
        }
        
        $name = sanitize_file_name($args[0]);
        $theme_root = get_template_directory();
        $components_dir = $theme_root . '/resources/components';
        
        if (!is_dir($components_dir)) {
            wp_mkdir_p($components_dir);
        }
        
        $component_file = $components_dir . '/' . $name . '.php';
        
        if (file_exists($component_file)) {
            \WP_CLI::error("Component '{$name}' already exists.");
        }
        
        // Create component PHP file
        $component_content = $this->getComponentTemplate($name);
        file_put_contents($component_file, $component_content);
        
        \WP_CLI::success("Created component: {$component_file}");
        
        // Create SCSS file if requested
        if (isset($assoc_args['with-scss'])) {
            $scss_dir = $theme_root . '/resources/src/scss/components';
            if (!is_dir($scss_dir)) {
                wp_mkdir_p($scss_dir);
            }
            
            $scss_file = $scss_dir . '/_' . $name . '.scss';
            $scss_content = $this->getComponentScssTemplate($name);
            file_put_contents($scss_file, $scss_content);
            
            \WP_CLI::success("Created SCSS file: {$scss_file}");
        }
        
        // Create JS file if requested
        if (isset($assoc_args['with-js'])) {
            $js_dir = $theme_root . '/resources/src/js/components';
            if (!is_dir($js_dir)) {
                wp_mkdir_p($js_dir);
            }
            
            $js_file = $js_dir . '/' . $name . '.js';
            $js_content = $this->getComponentJsTemplate($name);
            file_put_contents($js_file, $js_content);
            
            \WP_CLI::success("Created JS file: {$js_file}");
        }
        
        \WP_CLI::line('');
        \WP_CLI::line("Usage in templates: skin_component('{$name}', []);");
    }

    /**
     * Scaffold a new layout
     *
     * ## OPTIONS
     *
     * <name>
     * : Layout name
     *
     * ## EXAMPLES
     *
     *     wp wpskin layout single-post
     *     wp wpskin layout homepage
     *
     * @when after_wp_load
     */
    public function layout($args, $assoc_args) {
        if (empty($args[0])) {
            \WP_CLI::error('Layout name is required.');
        }
        
        $name = sanitize_file_name($args[0]);
        $theme_root = get_template_directory();
        $layouts_dir = $theme_root . '/resources/layouts';
        
        if (!is_dir($layouts_dir)) {
            wp_mkdir_p($layouts_dir);
        }
        
        $layout_file = $layouts_dir . '/' . $name . '.php';
        
        if (file_exists($layout_file)) {
            \WP_CLI::error("Layout '{$name}' already exists.");
        }
        
        $layout_content = $this->getLayoutTemplate($name);
        file_put_contents($layout_file, $layout_content);
        
        \WP_CLI::success("Created layout: {$layout_file}");
        \WP_CLI::line("Usage in templates: skin_layout('{$name}', []);");
    }

    /**
     * Show WP-Skin information
     *
     * @when after_wp_load
     */
    public function info($args, $assoc_args) {
        $theme_root = get_template_directory();
        $theme_name = wp_get_theme()->get('Name');
        
        \WP_CLI::line('WP-Skin Information');
        \WP_CLI::line('');
        \WP_CLI::line("Theme: {$theme_name}");
        \WP_CLI::line("Version: " . (defined('WPSKIN_VERSION') ? WPSKIN_VERSION : 'Unknown'));
        \WP_CLI::line("Theme Path: {$theme_root}");
        \WP_CLI::line("WP-Skin Path: " . (defined('WPSKIN_PATH') ? WPSKIN_PATH : 'Not defined'));
        \WP_CLI::line("Mode: " . (defined('WPSKIN_MODE') ? WPSKIN_MODE : 'theme'));
        \WP_CLI::line('');
        
        // Check directories
        $dirs = [
            'resources' => '/resources',
            'components' => '/resources/components',
            'layouts' => '/resources/layouts',
            'views' => '/resources/views',
            'assets' => '/resources/assets',
            'src' => '/resources/src',
        ];
        
        \WP_CLI::line('Directory Structure:');
        foreach ($dirs as $name => $path) {
            $full_path = $theme_root . $path;
            $status = is_dir($full_path) ? '[OK]' : '[MISSING]';
            \WP_CLI::line("   {$status} {$name}: {$path}");
        }
        
        // Check config file
        \WP_CLI::line('');
        \WP_CLI::line('Configuration:');
        $config_file = $theme_root . '/skin.php';
        $config_status = file_exists($config_file) ? '[FOUND]' : '[NOT FOUND]';
        \WP_CLI::line("   skin.php: {$config_status}");
    }

    /**
     * Check theme status
     *
     * @when after_wp_load
     */
    public function status($args, $assoc_args) {
        $theme_root = get_template_directory();
        $resources_dir = $theme_root . '/resources';
        
        \WP_CLI::line('WP-Skin Theme Status');
        \WP_CLI::line('');
        
        // Check if resources exist
        if (is_dir($resources_dir)) {
            \WP_CLI::line('[OK] Resources directory exists');
            
            // Check package.json
            if (file_exists($resources_dir . '/package.json')) {
                \WP_CLI::line('[OK] package.json found');
                
                // Check node_modules
                if (is_dir($resources_dir . '/node_modules')) {
                    \WP_CLI::line('[OK] Dependencies installed');
                } else {
                    \WP_CLI::line('[WARNING] Dependencies not installed (run: npm install)');
                }
            } else {
                \WP_CLI::line('[ERROR] package.json not found');
            }
            
            // Check built assets
            $built_css = glob($resources_dir . '/assets/css/*.css');
            $built_js = glob($resources_dir . '/assets/js/*.js');
            
            if (!empty($built_css)) {
                \WP_CLI::line('[OK] CSS assets found (' . count($built_css) . ' files)');
            } else {
                \WP_CLI::line('[WARNING] No CSS assets found');
            }
            
            if (!empty($built_js)) {
                \WP_CLI::line('[OK] JS assets found (' . count($built_js) . ' files)');
            } else {
                \WP_CLI::line('[WARNING] No JS assets found');
            }
            
        } else {
            \WP_CLI::line('[ERROR] Resources directory not found');
        }
        
        // Check skin.php
        if (file_exists($theme_root . '/skin.php')) {
            \WP_CLI::line('[OK] skin.php configuration found');
        } else {
            \WP_CLI::line('[ERROR] skin.php configuration not found');
        }
        
        \WP_CLI::line('');
        \WP_CLI::line('Ready for development: ' . (is_dir($resources_dir) ? '[YES]' : '[NO]'));
        \WP_CLI::line('Ready for production: ' . (!empty($built_css) || !empty($built_js) ? '[YES]' : '[NO]'));
    }

    /**
     * Helper methods for templates
     */
    private function getComponentTemplate($name) {
        $title = ucwords(str_replace(['-', '_'], ' ', $name));
        return "<?php
/**
 * {$title} Component
 * 
 * Usage: skin_component('{$name}', ['title' => 'My Title']);
 */

\$title = \$title ?? '{$title}';
\$content = \$content ?? '';
?>

<div class=\"{$name}\">
    <?php if (\$title): ?>
        <h2 class=\"{$name}__title\"><?php echo esc_html(\$title); ?></h2>
    <?php endif; ?>
    
    <?php if (\$content): ?>
        <div class=\"{$name}__content\">
            <?php echo wp_kses_post(\$content); ?>
        </div>
    <?php endif; ?>
</div>
";
    }
    
    private function getComponentScssTemplate($name) {
        return "//
// {$name} Component Styles
//

.{$name} {
    // Component styles here
    
    &__title {
        // Title styles
    }
    
    &__content {
        // Content styles
    }
    
    // Modifiers
    &--variant {
        // Variant styles
    }
    
    // States
    &.is-active {
        // Active state
    }
}
";
    }
    
    private function getComponentJsTemplate($name) {
        return "/**
 * {$name} Component JavaScript
 */

class " . ucfirst($name) . " {
    constructor(element) {
        this.element = element;
        this.init();
    }
    
    init() {
        // Component initialization
        this.bindEvents();
    }
    
    bindEvents() {
        // Event listeners
    }
}

// Auto-initialize components
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.{$name}').forEach(element => {
        new " . ucfirst($name) . "(element);
    });
});

export default " . ucfirst($name) . ";
";
    }
    
    private function getLayoutTemplate($name) {
        $title = ucwords(str_replace(['-', '_'], ' ', $name));
        return "<?php
/**
 * {$title} Layout
 * 
 * Usage: skin_layout('{$name}', ['content' => \$content]);
 */

get_header();
?>

<div class=\"layout-{$name}\">
    <main class=\"main-content\">
        <div class=\"container\">
            <?php echo \$content ?? ''; ?>
        </div>
    </main>
</div>

<?php get_footer(); ?>
";
    }
    
    private function runCommand($command) {
        $output = [];
        $return_var = 0;
        exec($command . ' 2>&1', $output, $return_var);
        
        if ($return_var !== 0) {
            \WP_CLI::line('Command output: ' . implode("\n", $output));
        }
        
        return $return_var;
    }
}