<?php
/**
 * Asset Manager
 * 
 * Handles CSS, JavaScript, and font loading with smart conventions
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Assets;

use WordPressSkin\Core\Skin;

defined('ABSPATH') or exit;

class AssetManager {
    /**
     * Skin instance
     * 
     * @var Skin
     */
    private $skin;
    
    /**
     * Registered assets
     * 
     * @var array
     */
    private $assets = [
        'css' => [],
        'js' => [],
        'fonts' => []
    ];
    
    /**
     * Auto-enqueue enabled
     * 
     * @var bool
     */
    private $autoEnqueue = true;
    
    /**
     * Vite dev server URL
     * 
     * @var string
     */
    private $viteDevServer = 'http://localhost:5173';
    
    /**
     * Vite manifest data
     * 
     * @var array|null
     */
    private $viteManifest = null;
    
    /**
     * Constructor
     * 
     * @param Skin $skin
     */
    public function __construct(Skin $skin) {
        $this->skin = $skin;
        
        // Hook into WordPress asset enqueuing
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_head', [$this, 'outputFonts'], 1);
        add_action('wp_head', [$this, 'enqueueViteAssets'], 5);
        
        // Load Vite manifest if in production
        $this->loadViteManifest();
    }
    
    /**
     * Register CSS file
     * 
     * @param string $handle Handle name
     * @param string|null $src Source path
     * @param array $deps Dependencies
     * @param string|bool|null $ver Version
     * @param string $media Media type
     * @return self
     */
    public function css($handle, $src = null, $deps = [], $ver = null, $media = 'all') {
        // Auto-detect source if not provided
        if ($src === null) {
            $src = $this->autoDetectCssPath($handle);
        }
        
        // Convert relative path to full URL
        if ($src && !$this->isUrl($src)) {
            $src = $this->getAssetUrl($src);
        }
        
        $this->assets['css'][$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver ?: $this->getAssetVersion($src),
            'media' => $media
        ];
        
        return $this;
    }
    
    /**
     * Register JavaScript file
     * 
     * @param string $handle Handle name
     * @param string|null $src Source path
     * @param array $deps Dependencies
     * @param string|bool|null $ver Version
     * @param bool $in_footer Load in footer
     * @return self
     */
    public function js($handle, $src = null, $deps = [], $ver = null, $in_footer = true) {
        // Auto-detect source if not provided
        if ($src === null) {
            $src = $this->autoDetectJsPath($handle);
        }
        
        // Convert relative path to full URL
        if ($src && !$this->isUrl($src)) {
            $src = $this->getAssetUrl($src);
        }
        
        $this->assets['js'][$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver ?: $this->getAssetVersion($src),
            'in_footer' => $in_footer
        ];
        
        return $this;
    }
    
    /**
     * Register font
     * 
     * @param string $family Font family name
     * @param array $variants Font variants
     * @param string $display Font display property
     * @return self
     */
    public function font($family, $variants = ['400'], $display = 'swap') {
        $this->assets['fonts'][$family] = [
            'family' => $family,
            'variants' => $variants,
            'display' => $display
        ];
        
        return $this;
    }
    
    /**
     * Get asset URL
     * 
     * @param string $path Asset path
     * @return string Asset URL
     */
    public function url($path) {
        if ($this->isUrl($path)) {
            return $path;
        }
        
        // Remove leading slash if present
        $path = ltrim($path, '/');
        
        // If path doesn't start with resources, prepend it
        if (!str_starts_with($path, 'resources/')) {
            $path = 'resources/assets/' . $path;
        }
        
        return get_template_directory_uri() . '/' . $path;
    }
    
    /**
     * Enqueue all registered assets
     * 
     * @return void
     */
    public function enqueueAssets(): void {
        if (!$this->autoEnqueue) {
            return;
        }
        
        // Enqueue CSS files
        foreach ($this->assets['css'] as $handle => $asset) {
            if ($asset['src']) {
                wp_enqueue_style($handle, $asset['src'], $asset['deps'], $asset['ver'], $asset['media']);
            }
        }
        
        // Enqueue JavaScript files
        foreach ($this->assets['js'] as $handle => $asset) {
            if ($asset['src']) {
                wp_enqueue_script($handle, $asset['src'], $asset['deps'], $asset['ver'], $asset['in_footer']);
            }
        }
    }
    
    /**
     * Output font preloads and Google Fonts
     * 
     * @return void
     */
    public function outputFonts(): void {
        if (empty($this->assets['fonts'])) {
            return;
        }
        
        $googleFonts = [];
        
        foreach ($this->assets['fonts'] as $family => $font) {
            // Check if it's a Google Font (simple heuristic)
            if ($this->isGoogleFont($font['family'])) {
                $googleFonts[] = $font;
            }
        }
        
        if (!empty($googleFonts)) {
            $this->outputGoogleFonts($googleFonts);
        }
    }
    
    /**
     * Auto-detect CSS file path
     * 
     * @param string $handle
     * @return string|null
     */
    private function autoDetectCssPath($handle) {
        $possiblePaths = [
            "resources/assets/css/{$handle}.css",
            "resources/assets/css/{$handle}.min.css",
            "resources/assets/{$handle}.css",
            "assets/css/{$handle}.css"
        ];
        
        foreach ($possiblePaths as $path) {
            $fullPath = $this->skin->getThemeRoot() . '/' . $path;
            if (file_exists($fullPath)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Auto-detect JavaScript file path
     * 
     * @param string $handle
     * @return string|null
     */
    private function autoDetectJsPath($handle) {
        $possiblePaths = [
            "resources/assets/js/{$handle}.js",
            "resources/assets/js/{$handle}.min.js",
            "resources/assets/{$handle}.js",
            "assets/js/{$handle}.js"
        ];
        
        foreach ($possiblePaths as $path) {
            $fullPath = $this->skin->getThemeRoot() . '/' . $path;
            if (file_exists($fullPath)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Get asset URL from path
     * 
     * @param string $path
     * @return string
     */
    private function getAssetUrl($path) {
        return get_template_directory_uri() . '/' . ltrim($path, '/');
    }
    
    /**
     * Get asset version based on file modification time
     * 
     * @param string $src
     * @return string
     */
    private function getAssetVersion($src) {
        if (!$src || $this->isUrl($src)) {
            return WPSKIN_VERSION;
        }
        
        $filePath = $this->skin->getThemeRoot() . '/' . ltrim($src, '/');
        
        if (file_exists($filePath)) {
            return filemtime($filePath);
        }
        
        return WPSKIN_VERSION;
    }
    
    /**
     * Check if string is a URL
     * 
     * @param string $str
     * @return bool
     */
    private function isUrl($str) {
        return filter_var($str, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Check if font is a Google Font
     * 
     * @param string $family
     * @return bool
     */
    private function isGoogleFont($family) {
        // Simple check - could be enhanced with a list of Google Fonts
        $googleFonts = [
            'Inter', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Source Sans Pro',
            'Oswald', 'Raleway', 'Slabo 27px', 'Poppins', 'Merriweather', 'PT Sans'
        ];
        
        return in_array($family, $googleFonts);
    }
    
    /**
     * Output Google Fonts link
     * 
     * @param array $fonts
     * @return void
     */
    private function outputGoogleFonts($fonts) {
        $families = [];
        
        foreach ($fonts as $font) {
            $family = str_replace(' ', '+', $font['family']);
            if (!empty($font['variants'])) {
                $family .= ':' . implode(',', $font['variants']);
            }
            $families[] = $family;
        }
        
        $url = 'https://fonts.googleapis.com/css2?family=' . implode('&family=', $families) . '&display=swap';
        
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link href="' . esc_url($url) . '" rel="stylesheet">' . "\n";
    }
    
    /**
     * Disable auto-enqueue
     * 
     * @return self
     */
    public function disableAutoEnqueue() {
        $this->autoEnqueue = false;
        return $this;
    }
    
    /**
     * Enable auto-enqueue
     * 
     * @return self
     */
    public function enableAutoEnqueue() {
        $this->autoEnqueue = true;
        return $this;
    }
    
    /**
     * Load Vite manifest file
     * 
     * @return void
     */
    private function loadViteManifest() {
        $manifestPaths = [
            $this->skin->getThemeRoot() . '/resources/dist/.vite/manifest.json',
            $this->skin->getThemeRoot() . '/resources/dist/manifest.json'
        ];
        
        foreach ($manifestPaths as $manifestPath) {
            if (file_exists($manifestPath)) {
                $this->viteManifest = json_decode(file_get_contents($manifestPath), true);
                break;
            }
        }
    }
    
    /**
     * Enqueue Vite assets automatically
     * 
     * @return void
     */
    public function enqueueViteAssets() {
        // In development mode (requires vite.config.mjs)
        if ($this->isDevMode()) {
            $viteConfigPath = $this->skin->getThemeRoot() . '/resources/vite.config.mjs';
            if (!file_exists($viteConfigPath)) {
                return;
            }
            $this->enqueueViteDev();
        } 
        // In production mode (only needs manifest)
        else if ($this->viteManifest) {
            $this->enqueueViteProd();
        }
    }
    
    /**
     * Enqueue Vite dev server assets
     * 
     * @return void
     */
    private function enqueueViteDev() {
        ?>
        <!-- Vite Dev Server -->
        <script type="module">
            import RefreshRuntime from '<?php echo $this->viteDevServer; ?>/@react-refresh';
            RefreshRuntime.injectIntoGlobalHook(window);
            window.$RefreshReg$ = () => {};
            window.$RefreshSig$ = () => (type) => type;
            window.__vite_plugin_react_preamble_installed__ = true;
        </script>
        <script type="module" src="<?php echo $this->viteDevServer; ?>/@vite/client"></script>
        <script type="module" src="<?php echo $this->viteDevServer; ?>/src/js/app.js"></script>
        <?php
    }
    
    /**
     * Enqueue Vite production assets
     * 
     * @return void
     */
    private function enqueueViteProd() {
        if (!isset($this->viteManifest['src/js/app.js'])) {
            return;
        }
        
        $entry = $this->viteManifest['src/js/app.js'];
        $baseUrl = get_template_directory_uri() . '/resources/dist/';
        
        // Enqueue CSS if exists
        if (isset($entry['css'])) {
            foreach ($entry['css'] as $cssFile) {
                ?>
                <link rel="stylesheet" href="<?php echo esc_url($baseUrl . $cssFile); ?>">
                <?php
            }
        }
        
        // Enqueue JS
        if (isset($entry['file'])) {
            ?>
            <script type="module" src="<?php echo esc_url($baseUrl . $entry['file']); ?>"></script>
            <?php
        }
    }
    
    /**
     * Check if in development mode
     * 
     * @return bool
     */
    private function isDevMode() {
        // Never use dev mode if we're in WP-CLI context (building assets)
        if (defined('WP_CLI') && WP_CLI) {
            return false;
        }
        
        // Explicitly check for WPSKIN_DEV first
        if (defined('WPSKIN_DEV') && WPSKIN_DEV) {
            return true;
        }
        
        // Only use dev mode if debug is enabled AND dev server is accessible
        $debugEnabled = (
            (defined('WP_DEBUG') && WP_DEBUG) ||
            (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)
        );
        
        if ($debugEnabled) {
            // Check if Vite dev server is actually running
            $devServerResponse = @wp_remote_get($this->viteDevServer . '/@vite/client', [
                'timeout' => 1,
                'sslverify' => false
            ]);
            
            return !is_wp_error($devServerResponse);
        }
        
        return false;
    }
    
    /**
     * Set Vite dev server URL
     * 
     * @param string $url
     * @return self
     */
    public function setViteDevServer($url) {
        $this->viteDevServer = rtrim($url, '/');
        return $this;
    }
}