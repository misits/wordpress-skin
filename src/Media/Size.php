<?php

namespace WordPressSkin\Media;

// Prevent direct access.
defined("ABSPATH") or exit();

/**
 * Image Processing and Format Conversion Class
 *
 * Handles image resizing, format conversion (WebP, PNG, JPEG), and optimization
 * with modern WordPress standards. Preserves original dimensions while
 * providing format conversion capabilities for better web performance.
 *
 * @since 1.0.0
 */
class Size
{
    /**
     * Constants - Modern approach with better organization
     */
    private const CACHE_GROUP = "wordpress_skin_media_size";
    private const WEBP_QUALITY = 85;
    private const UNSUPPORTED_FORMATS = ["svg"];

    // Predefined responsive image sizes with device variants
    public const SIZES = [
        // Hero/Banner sizes (16:9 ratio)
        'hero' => [1920, 1080, true],
        'hero-s' => [480, 270, true],
        'hero-s2x' => [960, 540, true],
        'hero-m' => [800, 450, true],
        'hero-m2x' => [1600, 900, true],
        'hero-l' => [1200, 675, true],
        'hero-l2x' => [2400, 1350, true],

        // Featured content (3:2 ratio)
        'featured' => [1200, 800, true],
        'featured-s' => [400, 267, true],
        'featured-s2x' => [800, 534, true],
        'featured-m' => [600, 400, true],
        'featured-m2x' => [1200, 800, true],
        'featured-l' => [900, 600, true],
        'featured-l2x' => [1800, 1200, true],

        // Blog post images (4:3 ratio)
        'post' => [800, 600, true],
        'post-s' => [320, 240, true],
        'post-s2x' => [640, 480, true],
        'post-m' => [480, 360, true],
        'post-m2x' => [960, 720, true],
        'post-l' => [640, 480, true],
        'post-l2x' => [1280, 960, true],

        // Card/Grid items (3:2 ratio)
        'card' => [600, 400, true],
        'card-s' => [300, 200, true],
        'card-s2x' => [600, 400, true],
        'card-m' => [400, 267, true],
        'card-m2x' => [800, 534, true],
        'card-l' => [500, 333, true],
        'card-l2x' => [1000, 666, true],

        // Gallery thumbnails (4:3 ratio)
        'gallery' => [400, 300, true],
        'gallery-s' => [200, 150, true],
        'gallery-s2x' => [400, 300, true],
        'gallery-m' => [300, 225, true],
        'gallery-m2x' => [600, 450, true],

        // Testimonial/Profile images (4:5 ratio - portrait)
        'profile' => [400, 500, true],
        'profile-s' => [200, 250, true],
        'profile-s2x' => [400, 500, true],
        'profile-m' => [300, 375, true],
        'profile-m2x' => [600, 750, true],

        // Product images (1:1 square)
        'product' => [800, 800, true],
        'product-s' => [300, 300, true],
        'product-s2x' => [600, 600, true],
        'product-m' => [500, 500, true],
        'product-m2x' => [1000, 1000, true],
        'product-l' => [600, 600, true],
        'product-l2x' => [1200, 1200, true],

        // Avatar/Logo sizes (1:1 square)
        'avatar' => [300, 300, true],
        'avatar-s' => [100, 100, true],
        'avatar-s2x' => [200, 200, true],
        'avatar-m' => [150, 150, true],
        'avatar-m2x' => [300, 300, true],

        // Thumbnail sizes (3:2 ratio)
        'thumbnail' => [300, 200, true],
        'thumbnail-s' => [150, 100, true],
        'thumbnail-s2x' => [300, 200, true],
        'thumbnail-m' => [200, 133, true],
        'thumbnail-m2x' => [400, 266, true],

        // Cover/Banner wide (21:9 ultrawide)
        'banner' => [1680, 720, true],
        'banner-s' => [480, 206, true],
        'banner-s2x' => [960, 411, true],
        'banner-m' => [800, 343, true],
        'banner-m2x' => [1600, 686, true],

        // Social media optimized
        'social' => [1200, 630, true],     // Facebook/Twitter/LinkedIn card
        'social-s' => [400, 210, true],
        'social-s2x' => [800, 420, true],
        'social-m' => [600, 315, true],
        'social-m2x' => [1200, 630, true],

        // Instagram feed posts (4:5 portrait - 2025 standard)
        'instagram' => [1080, 1350, true],
        'instagram-s' => [400, 500, true],
        'instagram-s2x' => [800, 1000, true],
        'instagram-m' => [600, 750, true],
        'instagram-m2x' => [1200, 1500, true],

        // Instagram square (legacy 1:1 format)
        'ig-square' => [1080, 1080, true],
        'ig-square-s' => [320, 320, true],
        'ig-square-s2x' => [640, 640, true],
        'ig-square-m' => [540, 540, true],
        'ig-square-m2x' => [1080, 1080, true],

        // Instagram stories (9:16 vertical)
        'ig-story' => [1080, 1920, true],
        'ig-story-s' => [400, 711, true],
        'ig-story-s2x' => [800, 1422, true],
        'ig-story-m' => [600, 1067, true],
        'ig-story-m2x' => [1200, 2133, true],

        // Instagram reels (9:16 vertical)
        'ig-reel' => [1080, 1920, true],
        'ig-reel-s' => [400, 711, true],
        'ig-reel-s2x' => [800, 1422, true],
        'ig-reel-m' => [600, 1067, true],
        'ig-reel-m2x' => [1200, 2133, true],

        // Instagram landscape (16:9 in feed)
        'ig-landscape' => [1080, 566, true],
        'ig-landscape-s' => [400, 209, true],
        'ig-landscape-s2x' => [800, 418, true],
        'ig-landscape-m' => [600, 314, true],
        'ig-landscape-m2x' => [1200, 628, true],

        // Slider/Carousel images (16:10 ratio)
        'slider' => [1600, 1000, true],
        'slider-s' => [480, 300, true],
        'slider-s2x' => [960, 600, true],
        'slider-m' => [800, 500, true],
        'slider-m2x' => [1600, 1000, true],

        // Portfolio/Work showcase (16:10 ratio)
        'portfolio' => [1200, 750, true],
        'portfolio-s' => [400, 250, true],
        'portfolio-s2x' => [800, 500, true],
        'portfolio-m' => [600, 375, true],
        'portfolio-m2x' => [1200, 750, true],
    ];

    // Properties with modern typing (PHP 7.4+)
    private static ?self $_instance = null;
    private array $_image_sizes = [];
    private string $_wordpress_skin_dir = "";
    private string $_upload_url = "";
    private string $_capability = "manage_options";
    private bool $_webp_support = false;
    private bool $_png_support = false;
    private bool $_jpeg_support = false;

    /**
     * Singleton pattern with type safety
     *
     * Returns the single instance of the Size class, creating it if it doesn't exist.
     * Ensures only one instance handles image processing throughout the application.
     *
     * @return self The singleton instance of the Size class
     */
    public static function get_instance(): self
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Private constructor for singleton
     *
     * Initializes the Size class with WordPress upload directory settings
     * and checks for image format support (WebP, PNG, JPEG).
     */
    private function __construct()
    {
        // Initialize properties
        $upload_dir = wp_upload_dir();
        $this->_upload_url = $upload_dir["baseurl"];
        $this->_webp_support = function_exists("imagewebp");
        $this->_png_support =
            function_exists("imagecreatefrompng") &&
            function_exists("imagepng");
        $this->_jpeg_support =
            function_exists("imagecreatefromjpeg") &&
            function_exists("imagejpeg");
    }

    /**
     * Initialize with modern WordPress hooks
     *
     * Sets up WordPress integration, registers hooks, image sizes,
     * and enables modern image format features.
     *
     * @return void
     */
    public function init(): void
    {
        $this->_wordpress_skin_dir = apply_filters(
            "wordpress_skin_dir_path",
            $this->get_wordpress_skin_dir(),
        );
        $this->_capability = apply_filters(
            "wordpress_skin_images_user_capability",
            $this->_capability,
        );

        $this->check_wordpress_skin_dir();
        $this->register_hooks();
        $this->register_image_sizes();
        $this->setup_modern_features();
    }

    /**
     * Register WordPress hooks
     *
     * Registers all necessary WordPress action and filter hooks for
     * image processing, REST API integration, and performance optimization.
     *
     * @return void
     */
    private function register_hooks(): void
    {
        add_action("switch_blog", [$this, "blog_switched"]);
        add_action("delete_attachment", [$this, "cleanup_attachment_images"]);
        add_filter(
            "image_resize_dimensions",
            ["\\WordPressSkin\\Media\\Upscale", "resize"],
            10,
            6,
        );
        add_filter(
            "image_size_names_choose",
            fn($sizes) => array_merge($sizes, $this->get_size_names()),
        );

        // Modern REST API integration
        add_action("rest_api_init", [$this, "register_rest_routes"]);

        // Performance optimization
        add_action("wp_enqueue_scripts", [$this, "enqueue_modern_scripts"]);
    }

    /**
     * Register all image sizes
     *
     * Registers predefined image sizes with WordPress. Currently empty
     * as the focus is on format conversion rather than size modification.
     *
     * @return void
     */
    private function register_image_sizes(): void
    {
        foreach (self::SIZES as $name => $config) {
            $this->add_image_size($name, $config[0], $config[1], $config[2]);
        }
    }

    /**
     * Setup modern features (WebP, PNG, JPEG format conversion only)
     *
     * Enables image format generation (WebP, PNG, JPEG) on upload
     * and adds modern HTML attributes for better performance.
     *
     * @return void
     */
    private function setup_modern_features(): void
    {
        // Only do format conversion on upload, preserve original sizes
        if (
            $this->_webp_support ||
            $this->_png_support ||
            $this->_jpeg_support
        ) {
            add_filter(
                "wp_generate_attachment_metadata",
                [$this, "generate_modern_formats_only"],
                10,
                2,
            );
        }

        // Add modern image attributes
        add_filter(
            "wp_get_attachment_image_attributes",
            [$this, "add_modern_attributes"],
            10,
            3,
        );
    }

    /**
     * Static method to add image size (keeping original API)
     *
     * Provides a static interface for adding custom image sizes.
     * Maintains backward compatibility with existing implementations.
     *
     * @param string $size_name Name of the image size
     * @param int $width Maximum width in pixels
     * @param int $height Maximum height in pixels
     * @param bool $crop Whether to crop the image to exact dimensions
     * @return bool True if size was added successfully, false otherwise
     */
    public static function add(
        string $size_name = "",
        int $width = 0,
        int $height = 0,
        bool $crop = false,
    ): bool {
        return self::get_instance()->add_image_size(
            $size_name,
            $width,
            $height,
            $crop,
        );
    }

    /**
     * Static method to get image src (keeping original API)
     *
     * Provides a static interface for getting optimized image sources.
     * Returns the best available format (WebP > PNG > JPEG > Original).
     *
     * @param int $attachment_id WordPress attachment ID
     * @param mixed $size Image size (string name or array [width, height])
     * @param bool|null $crop Whether to crop the image
     * @return array Image data array with src, width, height, and format
     */
    public static function src(
        int $attachment_id = 0,
        $size = "",
        ?bool $crop = null,
    ): array {
        return self::get_instance()->get_attachment_image_src(
            $attachment_id,
            $size,
            $crop,
        );
    }

    /**
     * Get image src in specific format (for manual fallback control)
     *
     * Allows explicit format selection with automatic fallback handling.
     * Useful when you need a specific format rather than the "best" format.
     *
     * @param int $attachment_id WordPress attachment ID
     * @param mixed $size Image size (string name or array [width, height])
     * @param string $format Desired format ('webp', 'png', 'jpeg', 'original', 'best')
     * @param bool|null $crop Whether to crop the image
     * @return array Image data array with src, width, height, and format
     */
    public static function src_format(
        int $attachment_id = 0,
        $size = "",
        string $format = "best",
        ?bool $crop = null,
    ): array {
        return self::get_instance()->get_attachment_image_src_format(
            $attachment_id,
            $size,
            $format,
            $crop,
        );
    }

    /**
     * Get image source in specified format
     *
     * Internal method that handles format-specific image retrieval with
     * intelligent fallback logic when requested format is unavailable.
     *
     * @param int $attachment_id WordPress attachment ID
     * @param mixed $size Image size (string name or array [width, height])
     * @param string $format Desired format ('webp', 'png', 'jpeg', 'original', 'best')
     * @param bool|null $crop Whether to crop the image
     * @return array Image data array or empty array if not found
     */
    public function get_attachment_image_src_format(
        int $attachment_id,
        $size,
        string $format = "best",
        ?bool $crop = null,
    ): array {
        if ($format === "best") {
            return $this->get_attachment_image_src(
                $attachment_id,
                $size,
                $crop,
            );
        }

        $all_formats = $this->get_all_image_formats(
            $attachment_id,
            $size,
            $crop,
        );

        // Return specific format if available
        if (isset($all_formats[$format])) {
            return $all_formats[$format];
        }

        // Fallback logic
        $fallback_order = [
            "webp" => ["jpeg", "png", "original"],
            "png" => ["jpeg", "original"],
            "jpeg" => ["png", "original"],
            "original" => [],
        ];

        if (isset($fallback_order[$format])) {
            foreach ($fallback_order[$format] as $fallback) {
                if (isset($all_formats[$fallback])) {
                    return $all_formats[$fallback];
                }
            }
        }

        return [];
    }

    /**
     * Get optimized image source with modern format support
     *
     * Main method for retrieving optimized images. Automatically selects
     * the best available format and handles caching for performance.
     *
     * @param int $attachment_id WordPress attachment ID (must be > 0)
     * @param mixed $size Image size (string name or array [width, height])
     * @param bool|null $crop Whether to crop the image
     * @return array Image data array with src, width, height, and format
     */
    public function get_attachment_image_src(
        int $attachment_id = 0,
        $size = "",
        ?bool $crop = null,
    ): array {
        if ($attachment_id < 1 || empty($size)) {
            return [];
        }

        // Handle full size
        if ($size === "full") {
            return wp_get_attachment_image_src($attachment_id, "full") ?: [];
        }

        // Check cache first
        $cache_key = $this->get_cache_key($attachment_id, $size, $crop);
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        if ($cached !== false) {
            return $cached;
        }

        $result = $this->generate_optimized_image($attachment_id, $size, $crop);

        // Cache for 1 hour
        wp_cache_set($cache_key, $result, self::CACHE_GROUP, HOUR_IN_SECONDS);

        return $result;
    }

    /**
     * Generate optimized image with modern format support
     *
     * Core image processing method that handles format conversion,
     * optimization, and file generation with modern format support.
     *
     * @param int $attachment_id WordPress attachment ID
     * @param mixed $size Image size specification
     * @param bool|null $crop Whether to crop the image
     * @return array Image data array or empty array on failure
     */
    private function generate_optimized_image(
        int $attachment_id,
        $size,
        ?bool $crop,
    ): array {
        // Check if extension is supported
        $image_path = get_attached_file($attachment_id);
        if (!$image_path || !$this->is_supported_format($image_path)) {
            return wp_get_attachment_image_src($attachment_id, "full") ?: [];
        }

        // Get image metadata
        $image_meta = wp_get_attachment_metadata($attachment_id);
        if (!$image_meta) {
            return [];
        }

        // Parse dimensions
        [$width, $height, $crop] = $this->parse_size_params($size, $crop);
        if (!$width || !$height) {
            return [];
        }

        // Generate file paths for different formats
        $base_filename = $this->get_fly_file_name(
            basename($image_meta["file"]),
            $width,
            $height,
            $crop,
        );
        $paths = $this->get_format_paths($attachment_id, $base_filename);

        // Check existing files (prioritize modern formats, include PNG for source compatibility)
        foreach (["webp", "jpeg", "png", "original"] as $format) {
            if (
                isset($paths[$format]) &&
                file_exists($paths[$format]["path"])
            ) {
                $image_size = getimagesize($paths[$format]["path"]);
                if ($image_size) {
                    return [
                        "src" => $paths[$format]["url"],
                        "width" => $image_size[0],
                        "height" => $image_size[1],
                        "format" => $format,
                    ];
                }
            }
        }

        // Generate new images if none exist
        return $this->create_optimized_images(
            $attachment_id,
            $image_path,
            $paths,
            $width,
            $height,
            $crop,
        );
    }

    /**
     * Create optimized images in multiple formats
     *
     * Generates image files in multiple formats (original, WebP, PNG, JPEG)
     * using WordPress image editor with appropriate quality settings.
     *
     * @param int $attachment_id WordPress attachment ID
     * @param string $source_path Path to the original image file
     * @param array $paths Array of target paths for each format
     * @param int $width Target image width
     * @param int $height Target image height
     * @param bool $crop Whether to crop the image
     * @return array Image data for the successfully created image
     */
    private function create_optimized_images(
        int $attachment_id,
        string $source_path,
        array $paths,
        int $width,
        int $height,
        bool $crop,
    ): array {
        if (!$this->wordpress_skin_dir_writablee()) {
            return [];
        }

        try {
            // Ensure directory exists
            $this->check_wordpress_skin_dir();
            wp_mkdir_p(dirname($paths["original"]["path"]));

            // Get image editor
            $editor = wp_get_image_editor($source_path);
            if (is_wp_error($editor)) {
                return [];
            }

            // Resize
            $resize_result = $editor->resize($width, $height, $crop);
            if (is_wp_error($resize_result)) {
                return [];
            }

            $result = [];
            $formats_to_create = ["original"];

            // Add supported formats (WebP and JPEG only)
            if ($this->_webp_support) {
                $formats_to_create[] = "webp";
            }
            if ($this->_jpeg_support) {
                $formats_to_create[] = "jpeg";
            }

            // Create images in all supported formats
            foreach ($formats_to_create as $format) {
                if (!isset($paths[$format])) {
                    continue;
                }

                $save_args = [];
                if ($format === "webp") {
                    $save_args = ["quality" => self::WEBP_QUALITY];
                } elseif ($format === "jpeg") {
                    $save_args = ["quality" => 90];
                } elseif ($format === "png") {
                    $save_args = ["quality" => 90];
                }

                $mime_type = $format === "original" ? null : "image/{$format}";
                $save_result = $editor->save(
                    $paths[$format]["path"],
                    $mime_type,
                );

                if (
                    !is_wp_error($save_result) &&
                    file_exists($paths[$format]["path"])
                ) {
                    $image_dimensions = $editor->get_size();
                    $result = [
                        "src" => $paths[$format]["url"],
                        "width" => $image_dimensions["width"],
                        "height" => $image_dimensions["height"],
                        "format" => $format,
                    ];
                }
            }

            // Fire action for additional processing
            do_action(
                "wordpress_skin_image_created",
                $attachment_id,
                $paths,
                $result,
            );

            return $result;
        } catch (\Exception $e) {
            error_log("Size class error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse size parameters with better error handling
     *
     * Converts various size parameter formats into standardized width,
     * height, and crop values with proper fallback handling.
     *
     * @param mixed $size Size specification (string name or [width, height] array)
     * @param bool|null $crop Override crop setting
     * @return array Array containing [width, height, crop_boolean]
     */
    private function parse_size_params($size, ?bool $crop): array
    {
        if (is_string($size)) {
            $image_size = $this->get_image_size($size);
            if (empty($image_size)) {
                // Try WordPress native sizes
                $wp_sizes = wp_get_additional_image_sizes();
                if (isset($wp_sizes[$size])) {
                    $wp_size = $wp_sizes[$size];
                    return [
                        $wp_size["width"],
                        $wp_size["height"],
                        $crop ?? $wp_size["crop"],
                    ];
                }
                return [0, 0, false];
            }
            return [
                $image_size["size"][0],
                $image_size["size"][1],
                $crop ?? $image_size["crop"],
            ];
        }

        if (is_array($size) && count($size) >= 2) {
            return [$size[0], $size[1], $crop ?? false];
        }

        return [0, 0, false];
    }

    /**
     * Get paths for different image formats
     *
     * Generates file system paths and URLs for all supported image formats
     * (original, WebP, AVIF) based on the base filename.
     *
     * @param int $attachment_id WordPress attachment ID
     * @param string $base_filename Base filename for the resized image
     * @return array Associative array with 'webp', 'png', 'jpeg', 'original' => [path, url] mappings
     */
    private function get_format_paths(
        int $attachment_id,
        string $base_filename,
    ): array {
        $wordpress_skin_dir = $this->get_wordpress_skin_dir($attachment_id);
        $paths = [];

        // Original format
        $original_path = $wordpress_skin_dir . DIRECTORY_SEPARATOR . $base_filename;
        $paths["original"] = [
            "path" => $original_path,
            "url" => $this->get_fly_path($original_path),
        ];

        // WebP format
        if ($this->_webp_support) {
            $webp_filename = preg_replace(
                '/\.(jpg|jpeg|png|gif)$/i',
                ".webp",
                $base_filename,
            );
            $webp_path = $wordpress_skin_dir . DIRECTORY_SEPARATOR . $webp_filename;
            $paths["webp"] = [
                "path" => $webp_path,
                "url" => $this->get_fly_path($webp_path),
            ];
        }

        // PNG format (for source compatibility, but not generated for output)
        if ($this->_png_support) {
            $png_filename = preg_replace(
                '/\.(jpg|jpeg|gif|webp)$/i',
                ".png",
                $base_filename,
            );
            $png_path = $wordpress_skin_dir . DIRECTORY_SEPARATOR . $png_filename;
            $paths["png"] = [
                "path" => $png_path,
                "url" => $this->get_fly_path($png_path),
            ];
        }

        // JPEG format
        if ($this->_jpeg_support) {
            $jpeg_filename = preg_replace(
                '/\.(png|gif|webp)$/i',
                ".jpg",
                $base_filename,
            );
            $jpeg_path = $wordpress_skin_dir . DIRECTORY_SEPARATOR . $jpeg_filename;
            $paths["jpeg"] = [
                "path" => $jpeg_path,
                "url" => $this->get_fly_path($jpeg_path),
            ];
        }

        return $paths;
    }

    /**
     * Check if image format is supported
     *
     * Validates whether the image file format is supported for processing.
     * Currently excludes SVG files from processing.
     *
     * @param string $file_path Path to the image file
     * @return bool True if format is supported, false otherwise
     */
    private function is_supported_format(string $file_path): bool
    {
        $extension = strtolower($this->get_image_extension($file_path));
        return !in_array($extension, self::UNSUPPORTED_FORMATS);
    }

    /**
     * Generate cache key
     *
     * Creates a unique cache key for storing image processing results
     * based on attachment ID, size, and crop parameters.
     *
     * @param int $attachment_id WordPress attachment ID
     * @param mixed $size Size specification
     * @param bool|null $crop Crop setting
     * @return string Unique cache key string
     */
    private function get_cache_key(
        int $attachment_id,
        $size,
        ?bool $crop,
    ): string {
        $size_string = is_array($size) ? implode("x", $size) : $size;
        $crop_string = $crop ? "crop" : "no-crop";
        return "img_src_{$attachment_id}_{$size_string}_{$crop_string}";
    }

    /**
     * Get size names for WordPress admin
     *
     * Returns formatted size names for display in WordPress admin interfaces.
     * Converts technical size names into human-readable labels.
     *
     * @return array Associative array of size_name => display_label
     */
    private function get_size_names(): array
    {
        $names = [];
        foreach (array_keys(self::SIZES) as $size_name) {
            $names[$size_name] = ucwords(
                str_replace(["-", "_"], " ", $size_name),
            );
        }
        return $names;
    }

    /**
     * Add modern attributes to images (lazy loading, etc.)
     *
     * WordPress filter callback that adds modern HTML attributes
     * for improved performance (lazy loading, async decoding).
     *
     * @param array $attr Existing image attributes
     * @param \WP_Post $attachment WordPress post object for the attachment
     * @param mixed $size Image size specification
     * @return array Modified attributes array with modern performance attributes
     */
    public function add_modern_attributes(
        array $attr,
        \WP_Post $attachment,
        $size,
    ): array {
        // Add lazy loading for modern browsers
        if (!isset($attr["loading"])) {
            $attr["loading"] = "lazy";
        }

        // Add decoding hint
        if (!isset($attr["decoding"])) {
            $attr["decoding"] = "async";
        }

        return $attr;
    }

    /**
     * Generate modern format versions only (preserve original dimensions)
     *
     * WordPress filter callback that generates WebP, PNG, and JPEG versions
     * of uploaded images while preserving their original dimensions.
     *
     * @param array $metadata WordPress attachment metadata array
     * @param int $attachment_id WordPress attachment ID
     * @return array Unmodified metadata array (generation happens as side effect)
     */
    public function generate_modern_formats_only(
        array $metadata,
        int $attachment_id,
    ): array {
        if (empty($metadata["sizes"])) {
            return $metadata;
        }

        $original_path = get_attached_file($attachment_id);
        $upload_dir = dirname($original_path);

        foreach ($metadata["sizes"] as $size_name => $size_data) {
            $image_path = $upload_dir . "/" . $size_data["file"];

            // Generate WebP
            if ($this->_webp_support) {
                $webp_path = preg_replace(
                    '/\.(jpg|jpeg|png)$/i',
                    ".webp",
                    $image_path,
                );
                if (!file_exists($webp_path)) {
                    $this->convert_to_format($image_path, $webp_path, "webp");
                }
            }

            // Generate PNG
            if ($this->_png_support && !preg_match('/\.png$/i', $image_path)) {
                $png_path = preg_replace(
                    '/\.(jpg|jpeg|gif|webp)$/i',
                    ".png",
                    $image_path,
                );
                if (!file_exists($png_path)) {
                    $this->convert_to_format($image_path, $png_path, "png");
                }
            }

            // Generate JPEG
            if (
                $this->_jpeg_support &&
                !preg_match('/\.(jpg|jpeg)$/i', $image_path)
            ) {
                $jpeg_path = preg_replace(
                    '/\.(png|gif|webp)$/i',
                    ".jpg",
                    $image_path,
                );
                if (!file_exists($jpeg_path)) {
                    $this->convert_to_format($image_path, $jpeg_path, "jpeg");
                }
            }
        }

        return $metadata;
    }

    /**
     * Convert image to specified format
     *
     * Internal method for converting an existing image file to a different
     * format using WordPress image editor with appropriate quality settings.
     *
     * @param string $source_path Path to the source image file
     * @param string $target_path Path where converted image should be saved
     * @param string $format Target format ('webp', 'avif', etc.)
     * @return bool True on successful conversion, false on failure
     */
    private function convert_to_format(
        string $source_path,
        string $target_path,
        string $format,
    ): bool {
        if (!file_exists($source_path)) {
            return false;
        }

        $editor = wp_get_image_editor($source_path);
        if (
            is_wp_error($editor) ||
            !$editor->supports_mime_type("image/{$format}")
        ) {
            return false;
        }

        $quality_map = [
            "webp" => self::WEBP_QUALITY,
            "jpeg" => 90,
            "png" => 90,
        ];
        $quality = $quality_map[$format] ?? 90;
        $result = $editor->save($target_path, "image/{$format}");

        return !is_wp_error($result);
    }

    /**
     * Register REST API routes
     *
     * Sets up REST API endpoints for image resizing and format conversion.
     * Enables programmatic access to image processing functionality.
     *
     * @return void
     */
    public function register_rest_routes(): void
    {
        register_rest_route("wordpress_skin/v1", "/media/resize/(?P<id>\d+)", [
            "methods" => "GET",
            "callback" => [$this, "rest_get_resized_image"],
            "permission_callback" => "__return_true",
            "args" => [
                "size" => ["required" => true, "type" => "string"],
                "format" => [
                    "type" => "string",
                    "enum" => ["webp", "png", "jpeg", "original"],
                ],
            ],
        ]);
    }

    /**
     * REST endpoint for getting resized images
     *
     * REST API callback that processes image resize requests and returns
     * optimized image data in JSON format.
     *
     * @param \WP_REST_Request $request WordPress REST API request object
     * @return \WP_REST_Response JSON response with image data or error
     */
    public function rest_get_resized_image(
        \WP_REST_Request $request,
    ): \WP_REST_Response {
        $attachment_id = (int) $request->get_param("id");
        $size = $request->get_param("size");

        $image_data = $this->get_attachment_image_src($attachment_id, $size);

        if (empty($image_data)) {
            return new \WP_REST_Response(["error" => "Image not found"], 404);
        }

        return new \WP_REST_Response($image_data);
    }

    /**
     * Cleanup attachment images when deleted
     *
     * WordPress action callback that removes all generated image files
     * and clears caches when an attachment is deleted.
     *
     * @param int $attachment_id ID of the attachment being deleted
     * @return void
     */
    public function cleanup_attachment_images(int $attachment_id): void
    {
        $this->delete_attachment_fly_images($attachment_id);

        // Clear cache
        wp_cache_flush_group(self::CACHE_GROUP);
    }

    /**
     * Enqueue modern scripts for frontend optimization
     *
     * WordPress action callback that adds JavaScript for lazy loading
     * fallbacks and other performance optimizations.
     *
     * @return void
     */
    public function enqueue_modern_scripts(): void
    {
        // Add intersection observer for lazy loading fallback
        wp_add_inline_script("jquery", $this->get_lazy_loading_script());
    }

    /**
     * Get lazy loading fallback script
     *
     * Returns JavaScript code that provides lazy loading functionality
     * for browsers that don't support native lazy loading.
     *
     * @return string JavaScript code for lazy loading fallback
     */
    private function get_lazy_loading_script(): string
    {
        return "
        // Lazy loading fallback for older browsers
        if ('loading' in HTMLImageElement.prototype === false) {
            const images = document.querySelectorAll('img[loading=\"lazy\"]');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src || img.src;
                            img.removeAttribute('loading');
                            observer.unobserve(img);
                        }
                    });
                });
                images.forEach(img => observer.observe(img));
            }
        }";
    }

    // Keep all original methods for backward compatibility

    /**
     * Get the wordpress_skin images directory path
     *
     * Returns the absolute file system path to the wordpress_skin images directory,
     * optionally with a subdirectory path appended.
     *
     * @param string $path Optional subdirectory path to append
     * @return string Absolute path to wordpress_skin images directory
     */
    public function get_wordpress_skin_dir(string $path = ""): string
    {
        if (empty($this->_wordpress_skin_dir)) {
            $wp_upload_dir = wp_upload_dir();
            return $wp_upload_dir["basedir"] .
                DIRECTORY_SEPARATOR .
                "wordpress_skin-images" .
                ($path !== "" ? DIRECTORY_SEPARATOR . $path : "");
        }

        return $this->_wordpress_skin_dir .
            ($path !== "" ? DIRECTORY_SEPARATOR . $path : "");
    }

    /**
     * Ensure wordpress_skin directory exists
     *
     * Creates the wordpress_skin images directory if it doesn't exist.
     * Uses WordPress wp_mkdir_p for proper permissions handling.
     *
     * @return void
     */
    public function check_wordpress_skin_dir(): void
    {
        if (!is_dir($this->_wordpress_skin_dir)) {
            wp_mkdir_p($this->_wordpress_skin_dir);
        }
    }

    /**
     * Check if wordpress_skin directory is writable
     *
     * Verifies that the wordpress_skin directory exists and has write permissions
     * for creating and modifying image files.
     *
     * @return bool True if directory exists and is writable, false otherwise
     */
    public function wordpress_skin_dir_writablee(): bool
    {
        return is_dir($this->_wordpress_skin_dir) &&
            wp_is_writable($this->_wordpress_skin_dir);
    }

    /**
     * Delete all generated images for a specific attachment
     *
     * Removes all wordpress_skin-generated image files (all formats and sizes)
     * for the specified attachment ID.
     *
     * @param int $attachment_id WordPress attachment ID (0 for all)
     * @return bool True if deletion was successful, false otherwise
     */
    public function delete_attachment_fly_images(int $attachment_id = 0): bool
    {
        WP_Filesystem();
        global $wp_filesystem;
        return $wp_filesystem->rmdir(
            $this->get_wordpress_skin_dir($attachment_id),
            true,
        );
    }

    /**
     * Delete all wordpress_skin generated images
     *
     * Removes the entire wordpress_skin images directory and all its contents,
     * then recreates the empty directory structure.
     *
     * @return bool True if deletion and recreation was successful, false otherwise
     */
    public function delete_all_fly_images(): bool
    {
        WP_Filesystem();
        global $wp_filesystem;

        if ($wp_filesystem->rmdir($this->get_wordpress_skin_dir(), true)) {
            $this->check_wordpress_skin_dir();
            return true;
        }

        return false;
    }

    /**
     * Add a custom image size
     *
     * Registers a new image size configuration for use with the wordpress_skin
     * image processing system.
     *
     * @param string $size_name Unique name for the image size
     * @param int $width Maximum width in pixels
     * @param int $height Maximum height in pixels
     * @param bool $crop Whether to crop the image to exact dimensions
     * @return bool True if size was added successfully, false if invalid parameters
     */
    public function add_image_size(
        string $size_name,
        int $width = 0,
        int $height = 0,
        bool $crop = false,
    ): bool {
        if (empty($size_name) || !$width || !$height) {
            return false;
        }

        $this->_image_sizes[$size_name] = [
            "size" => [$width, $height],
            "crop" => $crop,
        ];

        return true;
    }

    /**
     * Get image size configuration
     *
     * Retrieves the configuration for a registered image size,
     * including dimensions and crop settings.
     *
     * @param string $size_name Name of the image size to retrieve
     * @return array Configuration array with 'size' and 'crop' keys, empty if not found
     */
    public function get_image_size(string $size_name = ""): array
    {
        if (empty($size_name) || !isset($this->_image_sizes[$size_name])) {
            return [];
        }

        return $this->_image_sizes[$size_name];
    }

    /**
     * Get file extension from filename
     *
     * Extracts and returns the file extension from a given filename.
     * Used for format detection and conversion operations.
     *
     * @param string $file_name Filename to extract extension from
     * @return string File extension without the dot (e.g., 'jpg', 'png')
     */
    public function get_image_extension(string $file_name = ""): string
    {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }

    /**
     * Get all registered image sizes
     *
     * Returns the complete array of all registered image sizes
     * and their configurations.
     *
     * @return array Associative array of size_name => configuration
     */
    public function get_all_image_sizes(): array
    {
        return $this->_image_sizes;
    }

    /**
     * Get complete HTML img or picture element for an attachment
     *
     * Generates a complete HTML image element with modern format support,
     * creating picture elements with multiple source formats when available.
     *
     * @param int $attachment_id WordPress attachment ID
     * @param mixed $size Image size specification (string name or [width, height])
     * @param bool|null $crop Whether to crop the image
     * @param array $attr Additional HTML attributes for the image element
     * @return string Complete HTML img or picture element
     */
    public function get_attachment_image(
        int $attachment_id = 0,
        $size = "",
        ?bool $crop = null,
        array $attr = [],
    ): string {
        if ($attachment_id < 1 || empty($size)) {
            return "";
        }

        if ($size === "full") {
            return wp_get_attachment_image($attachment_id, $size, false, $attr);
        }

        // Get all available formats for this image
        $all_formats = $this->get_all_image_formats(
            $attachment_id,
            $size,
            $crop,
        );
        if (empty($all_formats)) {
            return "";
        }

        $size_class = is_array($size) ? implode("x", $size) : $size;
        $attachment = get_post($attachment_id);

        // Get image dimensions from the best available format
        $primary_image = reset($all_formats);
        $hwstring = image_hwstring(
            $primary_image["width"],
            $primary_image["height"],
        );

        // Default attributes
        $default_attr = [
            "class" => "attachment-{$size_class}",
            "alt" => trim(
                strip_tags(
                    get_post_meta(
                        $attachment_id,
                        "_wp_attachment_image_alt",
                        true,
                    ),
                ),
            ),
            "loading" => "lazy",
            "decoding" => "async",
        ];

        // Fallback alt text
        if (empty($default_attr["alt"])) {
            $default_attr["alt"] = trim(strip_tags($attachment->post_excerpt));
        }
        if (empty($default_attr["alt"])) {
            $default_attr["alt"] = trim(strip_tags($attachment->post_title));
        }

        $attr = wp_parse_args($attr, $default_attr);
        $attr = apply_filters(
            "wordpress_skin_get_attachment_image_attributes",
            $attr,
            $attachment,
            $size,
        );

        // Generate modern <picture> element with WebP and JPEG fallbacks
        if (count($all_formats) > 1) {
            return $this->generate_picture_element(
                $all_formats,
                $attr,
                $hwstring,
            );
        }

        // Single format fallback (original <img> behavior)
        $primary_image = reset($all_formats);
        $attr["src"] = $primary_image["src"];
        $attr = array_map("esc_attr", $attr);

        $html = rtrim("<img {$hwstring}");
        foreach ($attr as $name => $value) {
            $html .= " {$name}=\"{$value}\"";
        }
        $html .= " />";

        return $html;
    }

    /**
     * Generate modern <picture> element with format fallbacks
     *
     * Creates a picture element with multiple source elements for different
     * formats, providing automatic format selection by the browser.
     *
     * @param array $formats Available image formats with their data
     * @param array $attr HTML attributes for the fallback img element
     * @param string $hwstring Width and height attributes string
     * @return string Complete HTML picture element with sources and fallback
     */
    private function generate_picture_element(
        array $formats,
        array $attr,
        string $hwstring,
    ): string {
        $html = "<picture>";

        // Add WebP source only (JPEG will be the fallback img)
        if (isset($formats["webp"])) {
            $html .= sprintf(
                '<source srcset="%s" type="image/webp">',
                esc_url($formats["webp"]["src"])
            );
        }

        // Fallback <img> with JPEG preferred, then original
        $fallback_format = $formats["jpeg"] ?? $formats["original"] ?? reset($formats);
        $attr["src"] = $fallback_format["src"];
        $attr = array_map("esc_attr", $attr);

        $html .= rtrim("<img {$hwstring}");
        foreach ($attr as $name => $value) {
            $html .= " {$name}=\"{$value}\"";
        }
        $html .= " />";

        $html .= "</picture>";

        return $html;
    }

    /**
     * Get all available image formats for an attachment
     *
     * Returns data for all available image formats (original, WebP, PNG, JPEG)
     * for a given attachment and size, generating missing formats if needed.
     *
     * @param int $attachment_id WordPress attachment ID
     * @param mixed $size Image size specification
     * @param bool|null $crop Whether to crop the image
     * @return array Associative array of format => image_data mappings
     */
    public function get_all_image_formats(
        int $attachment_id,
        $size,
        ?bool $crop,
    ): array {
        // Check if extension is supported
        $image_path = get_attached_file($attachment_id);
        if (!$image_path || !$this->is_supported_format($image_path)) {
            return [];
        }

        // Get image metadata
        $image_meta = wp_get_attachment_metadata($attachment_id);
        if (!$image_meta) {
            return [];
        }

        // Parse dimensions
        [$width, $height, $crop] = $this->parse_size_params($size, $crop);
        if (!$width || !$height) {
            return [];
        }

        // Generate file paths for different formats
        $base_filename = $this->get_fly_file_name(
            basename($image_meta["file"]),
            $width,
            $height,
            $crop,
        );
        $paths = $this->get_format_paths($attachment_id, $base_filename);

        $available_formats = [];

        // Check existing files and collect all available formats (include PNG for source compatibility)
        foreach (["webp", "jpeg", "png", "original"] as $format) {
            if (
                isset($paths[$format]) &&
                file_exists($paths[$format]["path"])
            ) {
                $image_size = getimagesize($paths[$format]["path"]);
                if ($image_size) {
                    $available_formats[$format] = [
                        "src" => $paths[$format]["url"],
                        "width" => $image_size[0],
                        "height" => $image_size[1],
                        "format" => $format,
                    ];
                }
            }
        }

        // Generate missing formats if none exist
        if (empty($available_formats)) {
            $generated = $this->create_optimized_images(
                $attachment_id,
                $image_path,
                $paths,
                $width,
                $height,
                $crop,
            );
            if ($generated) {
                // Re-check all formats after generation (include PNG for source compatibility)
                foreach (["webp", "jpeg", "png", "original"] as $format) {
                    if (
                        isset($paths[$format]) &&
                        file_exists($paths[$format]["path"])
                    ) {
                        $image_size = getimagesize($paths[$format]["path"]);
                        if ($image_size) {
                            $available_formats[$format] = [
                                "src" => $paths[$format]["url"],
                                "width" => $image_size[0],
                                "height" => $image_size[1],
                                "format" => $format,
                            ];
                        }
                    }
                }
            }
        }

        return $available_formats;
    }

    /**
     * Generate filename for resized/converted images
     *
     * Creates a standardized filename for processed images that includes
     * dimensions, crop settings, and format information.
     *
     * @param string $file_name Original filename
     * @param int $width Target width
     * @param int $height Target height
     * @param bool $crop Whether the image is cropped
     * @param bool $webp Whether this is a WebP conversion (deprecated parameter)
     * @return string Generated filename with processing parameters
     */
    public function get_fly_file_name(
        string $file_name,
        int $width,
        int $height,
        bool $crop,
        bool $webp = false,
    ): string {
        $file_name_only = pathinfo($file_name, PATHINFO_FILENAME);
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

        $crop_extension = $crop ? "-c" : "";
        if (is_array($crop)) {
            $crop_extension =
                "-" . implode("", array_map(fn($pos) => $pos[0], $crop));
        }

        if ($webp) {
            $file_extension = "webp";
        }

        return sprintf(
            "%s-%dx%d%s.%s",
            $file_name_only,
            $width,
            $height,
            $crop_extension,
            $file_extension,
        );
    }

    /**
     * Convert absolute file path to URL
     *
     * Converts an absolute file system path to a web-accessible URL
     * by replacing the uploads directory path with the uploads URL.
     *
     * @param string $absolute_path Absolute file system path
     * @return string Web-accessible URL for the file
     */
    public function get_fly_path(string $absolute_path = ""): string
    {
        $wp_upload_dir = wp_upload_dir();
        $path =
            $wp_upload_dir["baseurl"] .
            str_replace($wp_upload_dir["basedir"], "", $absolute_path);
        return str_replace(DIRECTORY_SEPARATOR, "/", $path);
    }

    /**
     * Convert URL to absolute file path
     *
     * Converts a web URL back to an absolute file system path
     * by replacing the uploads URL with the uploads directory path.
     *
     * @param string $path Web URL or relative path
     * @return string Absolute file system path
     */
    public function get_fly_absolute_path(string $path = ""): string
    {
        $wp_upload_dir = wp_upload_dir();
        return $wp_upload_dir["basedir"] .
            str_replace($wp_upload_dir["baseurl"], "", $path);
    }

    /**
     * Handle WordPress multisite blog switching
     *
     * WordPress action callback that resets the wordpress_skin directory
     * configuration when switching between sites in a multisite installation.
     *
     * @return void
     */
    public function blog_switched(): void
    {
        $this->_wordpress_skin_dir = "";
        $this->_wordpress_skin_dir = apply_filters(
            "wordpress_skin_dir_path",
            $this->get_wordpress_skin_dir(),
        );
    }
}
