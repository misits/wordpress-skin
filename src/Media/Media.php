<?php
/**
 * Media Wrapper
 *
 * Provides a clean, object-oriented interface for working with WordPress media attachments
 * with built-in image resizing capabilities using Size and Upscale classes
 *
 * @package WP-Skin
 */

namespace WordPressSkin\Media;

use WordPressSkin\PostTypes\Traits\HandlesMeta;
use WordPressSkin\PostTypes\Traits\HandlesAcf;
use WordPressSkin\Media\Size;
use WordPressSkin\Media\Upscale;

defined('ABSPATH') or exit;

class Media {
    use HandlesMeta;
    use HandlesAcf;

    /**
     * The WordPress attachment post object
     *
     * @var \WP_Post|null
     */
    protected $attachment;

    /**
     * Attachment ID
     *
     * @var int
     */
    protected $id;

    /**
     * Cached metadata
     *
     * @var array|null
     */
    protected $metadata;

    /**
     * Image editor instance
     *
     * @var \WP_Image_Editor|null
     */
    protected $editor;

    /**
     * Constructor
     *
     * @param int|\WP_Post|null $attachment Attachment ID, WP_Post object, or null
     */
    public function __construct($attachment = null) {
        if ($attachment === null) {
            $this->attachment = null;
        } elseif (is_numeric($attachment)) {
            // Get attachment by ID
            $this->attachment = get_post($attachment);
        } elseif ($attachment instanceof \WP_Post) {
            $this->attachment = $attachment;
        } else {
            $this->attachment = null;
        }

        if ($this->attachment && $this->attachment->post_type === 'attachment') {
            $this->id = $this->attachment->ID;
        } else {
            $this->attachment = null;
            $this->id = null;
        }

        // Call initialization hook for child classes
        $this->initialize();
    }

    /**
     * Initialize hook for child classes
     * Override this method to add custom initialization logic
     *
     * @return void
     */
    protected function initialize() {
        // Override in child classes
    }

    /**
     * Get attachment ID
     *
     * @return int|null
     */
    public function id() {
        return $this->id;
    }

    /**
     * Get attachment title
     *
     * @param bool $filtered Apply filters
     * @return string
     */
    public function title($filtered = true) {
        if (!$this->attachment) return '';

        return $filtered
            ? get_the_title($this->attachment)
            : $this->attachment->post_title;
    }

    /**
     * Get attachment caption
     *
     * @return string
     */
    public function caption() {
        if (!$this->attachment) return '';

        return $this->attachment->post_excerpt;
    }

    /**
     * Get attachment description
     *
     * @return string
     */
    public function description() {
        if (!$this->attachment) return '';

        return $this->attachment->post_content;
    }

    /**
     * Get attachment alt text
     *
     * @return string
     */
    public function alt() {
        if (!$this->attachment) return '';

        return get_post_meta($this->id, '_wp_attachment_image_alt', true) ?: '';
    }

    /**
     * Get attachment URL using Size class for optimized images
     *
     * @param string|array $size Image size
     * @param bool|null $crop Whether to crop the image
     * @return string
     */
    public function url($size = 'full', $crop = null) {
        if (!$this->attachment) return '';

        if ($this->isImage() && $size !== 'full') {
            $image_data = Size::src($this->id, $size, $crop);
            return $image_data['src'] ?? '';
        }

        return wp_get_attachment_url($this->id) ?: '';
    }

    /**
     * Get image source data using Size class
     *
     * @param string|array $size Image size
     * @param bool|null $crop Whether to crop the image
     * @return array Array with src, width, height, format
     */
    public function src($size = 'full', $crop = null) {
        if (!$this->attachment || !$this->isImage()) return [];

        return Size::src($this->id, $size, $crop);
    }

    /**
     * Get image source in specific format
     *
     * @param string|array $size Image size
     * @param string $format Desired format (webp, png, jpeg, original, best)
     * @param bool|null $crop Whether to crop the image
     * @return array Array with src, width, height, format
     */
    public function srcFormat($size = 'full', $format = 'best', $crop = null) {
        if (!$this->attachment || !$this->isImage()) return [];

        return Size::src_format($this->id, $size, $format, $crop);
    }

    /**
     * Get attachment file path
     *
     * @return string
     */
    public function path() {
        if (!$this->attachment) return '';

        return get_attached_file($this->id) ?: '';
    }

    /**
     * Get file name
     *
     * @param bool $withExtension Include file extension
     * @return string
     */
    public function filename($withExtension = true) {
        $path = $this->path();
        if (!$path) return '';

        $filename = basename($path);

        if (!$withExtension) {
            $filename = pathinfo($filename, PATHINFO_FILENAME);
        }

        return $filename;
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function extension() {
        $path = $this->path();
        if (!$path) return '';

        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Get file size
     *
     * @param bool $formatted Format size for display
     * @return int|string
     */
    public function filesize($formatted = false) {
        $path = $this->path();
        if (!$path || !file_exists($path)) return $formatted ? '0 B' : 0;

        $size = filesize($path);

        if ($formatted) {
            return size_format($size);
        }

        return $size;
    }

    /**
     * Get MIME type
     *
     * @return string
     */
    public function mimeType() {
        if (!$this->attachment) return '';

        return $this->attachment->post_mime_type;
    }

    /**
     * Check if attachment is an image
     *
     * @return bool
     */
    public function isImage() {
        return wp_attachment_is_image($this->id);
    }

    /**
     * Check if attachment is a video
     *
     * @return bool
     */
    public function isVideo() {
        return wp_attachment_is('video', $this->id);
    }

    /**
     * Check if attachment is audio
     *
     * @return bool
     */
    public function isAudio() {
        return wp_attachment_is('audio', $this->id);
    }

    /**
     * Check if attachment is an SVG
     *
     * @return bool
     */
    public function isSvg() {
        $mime_type = $this->mimeType();
        return $mime_type === 'image/svg+xml' || $this->extension() === 'svg';
    }

    /**
     * Get SVG content as inline HTML
     *
     * @param array $attr Additional HTML attributes for the SVG element
     * @return string SVG HTML content
     */
    public function getSvgContent($attr = []) {
        if (!$this->isSvg()) return '';

        $path = $this->path();
        if (!$path || !file_exists($path)) {
            // Fallback to URL if local path doesn't exist
            $url = $this->url();
            return sprintf('<img src="%s" %s />', esc_url($url), $this->buildAttributes($attr));
        }

        // Read SVG content
        $svg_content = file_get_contents($path);
        if (!$svg_content) {
            return '';
        }

        // Add custom attributes to the SVG element
        if (!empty($attr)) {
            $svg_content = $this->addSvgAttributes($svg_content, $attr);
        }

        return $svg_content;
    }

    /**
     * Add attributes to SVG element
     *
     * @param string $svg_content SVG content
     * @param array $attr Attributes to add
     * @return string Modified SVG content
     */
    private function addSvgAttributes($svg_content, $attr) {
        // Find the opening <svg tag
        if (preg_match('/<svg([^>]*)>/', $svg_content, $matches)) {
            $existing_attrs = $matches[1];
            $new_attrs = $this->buildAttributes($attr);

            // Merge attributes
            $replacement = '<svg' . $existing_attrs . ' ' . $new_attrs . '>';
            $svg_content = preg_replace('/<svg([^>]*)>/', $replacement, $svg_content, 1);
        }

        return $svg_content;
    }

    /**
     * Build HTML attributes string from array
     *
     * @param array $attr Attributes array
     * @return string HTML attributes string
     */
    private function buildAttributes($attr) {
        $attributes = [];
        foreach ($attr as $key => $value) {
            if ($value !== null && $value !== false) {
                $attributes[] = sprintf('%s="%s"', esc_attr($key), esc_attr($value));
            }
        }
        return implode(' ', $attributes);
    }

    /**
     * Get attachment metadata
     *
     * @param bool $refresh Force refresh metadata
     * @return array
     */
    public function metadata($refresh = false) {
        if (!$this->attachment) return [];

        if ($this->metadata === null || $refresh) {
            $this->metadata = wp_get_attachment_metadata($this->id) ?: [];
        }

        return $this->metadata;
    }

    /**
     * Get image dimensions
     *
     * @param string $size Image size
     * @return array ['width' => int, 'height' => int]
     */
    public function dimensions($size = 'full') {
        if (!$this->isImage()) {
            return ['width' => 0, 'height' => 0];
        }

        if ($size === 'full') {
            $metadata = $this->metadata();
            return [
                'width' => $metadata['width'] ?? 0,
                'height' => $metadata['height'] ?? 0
            ];
        }

        $image = wp_get_attachment_image_src($this->id, $size);
        if ($image) {
            return [
                'width' => $image[1],
                'height' => $image[2]
            ];
        }

        return ['width' => 0, 'height' => 0];
    }

    /**
     * Get image width
     *
     * @param string $size Image size
     * @return int
     */
    public function width($size = 'full') {
        $dimensions = $this->dimensions($size);
        return $dimensions['width'];
    }

    /**
     * Get image height
     *
     * @param string $size Image size
     * @return int
     */
    public function height($size = 'full') {
        $dimensions = $this->dimensions($size);
        return $dimensions['height'];
    }

    /**
     * Generate responsive image srcset using Size class
     *
     * @param array $sizes Array of sizes ['320w' => 'size-name', '640w' => 'size-name-2x']
     * @param bool $crop Crop images to exact dimensions
     * @return string Srcset attribute value
     */
    public function srcset($sizes = [], $crop = null) {
        if (!$this->isImage()) return '';

        $srcset = [];

        foreach ($sizes as $descriptor => $size_name) {
            $image_data = $this->src($size_name, $crop);
            if (!empty($image_data['src'])) {
                $srcset[] = "{$image_data['src']} {$descriptor}";
            }
        }

        return implode(', ', $srcset);
    }

    /**
     * Generate responsive picture element with multiple formats
     *
     * @param array $breakpoints Array of breakpoints with media queries and sizes
     * @param array $attr Additional attributes for the img element
     * @return string Complete picture element HTML
     */
    public function picture($breakpoints = [], $attr = []) {
        if (!$this->isImage()) return '';

        $size_instance = Size::get_instance();
        return $size_instance->get_attachment_image($this->id, $breakpoints['fallback'] ?? 'full', null, $attr);
    }

    /**
     * Generate picture element HTML for responsive images
     *
     * @param array $config Configuration array with breakpoints and sizes
     * @return string Complete picture element HTML
     */
    public function responsivePicture($config = []) {
        if (!$this->isImage()) return '';

        $default_config = [
            'breakpoints' => [
                '(min-width: 1920px)' => ['size' => 'image-4k', 'density' => ['1x']],
                '(min-width: 1600px)' => ['size' => 'image-xl', 'density' => ['1x', '2x']],
                '(min-width: 1024px)' => ['size' => 'image-l', 'density' => ['1x', '2x']],
                '(min-width: 600px)' => ['size' => 'image-m', 'density' => ['1x', '2x']],
                '(max-width: 599px)' => ['size' => 'image-s', 'density' => ['1x', '2x']]
            ],
            'fallback' => 'image-l',
            'sizes' => '100vw',
            'srcset_sizes' => [
                'image-s' => '400w',
                'image-m' => '860w',
                'image-l' => '1280w',
                'image-xl' => '1920w',
                'image-4k' => '3840w'
            ]
        ];

        $config = array_merge($default_config, $config);

        $html = '<picture>';

        // Generate source elements for each breakpoint
        foreach ($config['breakpoints'] as $media => $settings) {
            $html .= $this->generateSourceElement($media, $settings);
        }

        // Generate fallback img element
        $html .= $this->generateFallbackImg($config);
        $html .= '</picture>';

        return $html;
    }

    /**
     * Generate source element for picture
     *
     * @param string $media Media query
     * @param array $settings Size and density settings
     * @return string Source element HTML
     */
    private function generateSourceElement($media, $settings) {
        $srcset_parts = [];

        foreach ($settings['density'] as $density) {
            $size_name = $settings['size'];
            if ($density === '2x') {
                $size_name .= '-2x';
            }

            $image_data = $this->src($size_name);
            if (!empty($image_data['src'])) {
                $srcset_parts[] = "{$image_data['src']} {$density}";
            }
        }

        if (empty($srcset_parts)) return '';

        $srcset = implode(', ', $srcset_parts);
        return sprintf('<source media="%s" srcset="%s">', esc_attr($media), esc_attr($srcset));
    }

    /**
     * Generate fallback img element
     *
     * @param array $config Configuration array
     * @return string Img element HTML
     */
    private function generateFallbackImg($config) {
        $fallback_data = $this->src($config['fallback']);
        if (empty($fallback_data['src'])) return '';

        // Build srcset for fallback img
        $srcset_parts = [];
        foreach ($config['srcset_sizes'] as $size_name => $width) {
            $image_data = $this->src($size_name);
            if (!empty($image_data['src'])) {
                $srcset_parts[] = "{$image_data['src']} {$width}";
            }
        }

        $srcset = implode(', ', $srcset_parts);

        return sprintf(
            '<img src="%s" srcset="%s" sizes="%s" alt="%s" loading="lazy" decoding="async">',
            esc_url($fallback_data['src']),
            esc_attr($srcset),
            esc_attr($config['sizes']),
            esc_attr($this->alt())
        );
    }

    /**
     * Generate complete figure element with responsive picture
     *
     * @param array $config Configuration for responsive breakpoints
     * @param array $figure_attr Attributes for the figure element
     * @return string Complete figure element HTML
     */
    public function figure($config = [], $figure_attr = []) {
        if (!$this->isImage()) return '';

        $figure_class = $figure_attr['class'] ?? '';
        $figure_attrs = '';
        foreach ($figure_attr as $attr => $value) {
            if ($attr !== 'class') {
                $figure_attrs .= sprintf(' %s="%s"', esc_attr($attr), esc_attr($value));
            }
        }

        $html = sprintf('<figure class="%s"%s>', esc_attr($figure_class), $figure_attrs);
        $html .= $this->responsivePicture($config);

        $caption = $this->caption();
        if ($caption) {
            $html .= sprintf('<figcaption>%s</figcaption>', wp_kses_post($caption));
        }

        $html .= '</figure>';

        return $html;
    }

    /**
     * Generate picture element with multiple formats and breakpoints
     *
     * @param array $breakpoints Array of breakpoints with media queries and sizes
     * @param array $formats Array of formats to include (default: ['webp', 'png'])
     * @param bool $crop Whether to crop images
     * @param array $attr Additional attributes for img element
     * @return string HTML picture element
     */
    public function pictureElement($breakpoints = [], $formats = ['webp', 'png'], $crop = true, $attr = []) {
        if (!$this->isImage() || empty($breakpoints)) return '';

        $html = '<picture>';

        // Generate sources for each breakpoint and format
        foreach ($breakpoints as $breakpoint) {
            $media = $breakpoint['media'] ?? '';
            $size = $breakpoint['size'] ?? [400, 300];

            foreach ($formats as $format) {
                $imageData = $this->srcFormat($size, $format, $crop);
                if (!empty($imageData['src'])) {
                    $mimeType = $format === 'jpeg' ? 'image/jpeg' : "image/{$format}";
                    if (!empty($media)) {
                        $html .= sprintf(
                            '<source media="%s" srcset="%s" type="%s">',
                            esc_attr($media),
                            esc_url($imageData['src']),
                            esc_attr($mimeType)
                        );
                    } else {
                        $html .= sprintf(
                            '<source srcset="%s" type="%s">',
                            esc_url($imageData['src']),
                            esc_attr($mimeType)
                        );
                    }
                }
            }
        }

        // Fallback img element (use smallest size)
        $fallbackSize = end($breakpoints)['size'] ?? [400, 300];
        $fallbackData = $this->src($fallbackSize, $crop);

        $defaultAttr = [
            'src' => $fallbackData['src'] ?? '',
            'alt' => $this->alt(),
            'loading' => 'lazy',
            'decoding' => 'async'
        ];

        $attr = array_merge($defaultAttr, $attr);

        $html .= '<img';
        foreach ($attr as $name => $value) {
            $html .= sprintf(' %s="%s"', esc_attr($name), esc_attr($value));
        }
        $html .= '>';

        $html .= '</picture>';

        return $html;
    }

    /**
     * Generate the exact picture element structure from the user's example
     *
     * @return string Picture element HTML matching the user's example
     */
    public function examplePicture() {
        if (!$this->isImage()) return '';

        $breakpoints = [
            [
                'media' => '(min-width: 1024px)',
                'size' => [1200, 800]
            ],
            [
                'media' => '(min-width: 768px)',
                'size' => [800, 600]
            ],
            [
                'size' => [400, 300]
            ]
        ];

        return $this->pictureElement($breakpoints, ['webp', 'png'], true);
    }

    /**
     * Generate complete figure element matching your example
     *
     * @return string Complete figure element with picture and caption
     */
    public function exampleFigure() {
        if (!$this->isImage()) return '';

        $html = '<figure>';
        $html .= $this->examplePicture();

        $caption = $this->caption();
        if ($caption) {
            $html .= sprintf('<figcaption>%s</figcaption>', wp_kses_post($caption));
        }

        $html .= '</figure>';

        return $html;
    }

    /**
     * Get complete HTML img or picture element using Size class
     *
     * @param string $size Image size name or [width, height] array
     * @param bool|null $crop Whether to crop the image
     * @param array $attr Additional HTML attributes
     * @param bool $responsive Whether to generate responsive srcset
     * @return string Complete HTML img or picture element
     */
    public function html($size = 'full', $crop = null, $attr = [], $responsive = true) {
        if (!$this->attachment) return '';

        // Handle SVG files specially
        if ($this->isSvg()) {
            return $this->getSvgContent($attr);
        }

        if ($this->isImage()) {
            if ($responsive && is_string($size) && $this->hasResponsiveVariants($size)) {
                return $this->generateResponsiveImage($size, $crop, $attr);
            }
            return Size::get_instance()->get_attachment_image($this->id, $size, $crop, $attr);
        }

        // For non-images, return a link
        $url = $this->url();
        $title = $this->title();

        return sprintf(
            '<a href="%s" title="%s">%s</a>',
            esc_url($url),
            esc_attr($title),
            esc_html($title)
        );
    }

    /**
     * Check if a size has responsive variants
     *
     * @param string $size Base size name
     * @return bool
     */
    private function hasResponsiveVariants($size) {
        $variants = ["{$size}-s", "{$size}-m", "{$size}-l"];
        foreach ($variants as $variant) {
            if (Size::get_instance()->get_image_size($variant)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate responsive image with srcset
     *
     * @param string $size Base size name
     * @param bool|null $crop Whether to crop
     * @param array $attr Additional attributes
     * @return string HTML picture element or img with srcset
     */
    private function generateResponsiveImage($size, $crop, $attr) {
        // Get all available formats for the base size
        $baseImage = $this->src($size, $crop);
        if (empty($baseImage['src'])) {
            return '';
        }

        // Build srcset for different device sizes
        $srcsetItems = [];
        $variants = [
            ["{$size}-s", '480w'],
            ["{$size}-s2x", '960w'],
            ["{$size}-m", '800w'],
            ["{$size}-m2x", '1600w'],
            ["{$size}-l", '1200w'],
            ["{$size}-l2x", '2400w'],
            [$size, '1920w'], // Default size
        ];

        foreach ($variants as [$variantSize, $descriptor]) {
            $image = $this->src($variantSize, $crop);
            if (!empty($image['src'])) {
                $srcsetItems[] = $image['src'] . ' ' . $descriptor;
            }
        }

        $srcset = implode(', ', $srcsetItems);
        $sizes = '(max-width: 480px) 480px, (max-width: 800px) 800px, (max-width: 1200px) 1200px, 1920px';

        // Check if we have WebP variant
        $webpImage = $this->srcFormat($size, 'webp', $crop);

        if (!empty($webpImage['src'])) {
            // Generate picture element with WebP source
            $html = '<picture>';

            // WebP srcset
            $webpSrcsetItems = [];
            foreach ($variants as [$variantSize, $descriptor]) {
                $webpVariant = $this->srcFormat($variantSize, 'webp', $crop);
                if (!empty($webpVariant['src'])) {
                    $webpSrcsetItems[] = $webpVariant['src'] . ' ' . $descriptor;
                }
            }

            if (!empty($webpSrcsetItems)) {
                $webpSrcset = implode(', ', $webpSrcsetItems);
                $html .= sprintf('<source srcset="%s" sizes="%s" type="image/webp">',
                    esc_attr($webpSrcset), esc_attr($sizes));
            }

            // Fallback img with JPEG srcset
            $defaultAttr = [
                'src' => $baseImage['src'],
                'srcset' => $srcset,
                'sizes' => $sizes,
                'width' => $baseImage['width'] ?? '',
                'height' => $baseImage['height'] ?? '',
                'alt' => $this->alt(),
                'loading' => 'lazy',
                'decoding' => 'async'
            ];

            $attr = array_merge($defaultAttr, $attr);

            $html .= '<img';
            foreach ($attr as $name => $value) {
                if (!empty($value)) {
                    $html .= sprintf(' %s="%s"', esc_attr($name), esc_attr($value));
                }
            }
            $html .= '>';

            $html .= '</picture>';
            return $html;
        } else {
            // Simple img with srcset
            $defaultAttr = [
                'src' => $baseImage['src'],
                'srcset' => $srcset,
                'sizes' => $sizes,
                'width' => $baseImage['width'] ?? '',
                'height' => $baseImage['height'] ?? '',
                'alt' => $this->alt(),
                'loading' => 'lazy',
                'decoding' => 'async'
            ];

            $attr = array_merge($defaultAttr, $attr);

            $html = '<img';
            foreach ($attr as $name => $value) {
                if (!empty($value)) {
                    $html .= sprintf(' %s="%s"', esc_attr($name), esc_attr($value));
                }
            }
            $html .= '>';

            return $html;
        }
    }

    /**
     * Get all available image formats for a size
     *
     * @param string $size Image size name or [width, height] array
     * @param bool|null $crop Whether to crop the image
     * @return array Associative array of format => image_data mappings
     */
    public function allFormats($size = 'full', $crop = null) {
        if (!$this->attachment || !$this->isImage()) return [];

        return Size::get_instance()->get_all_image_formats($this->id, $size, $crop);
    }

    /**
     * Get parent post
     *
     * @return \WP_Post|null
     */
    public function parent() {
        if (!$this->attachment || !$this->attachment->post_parent) {
            return null;
        }

        return get_post($this->attachment->post_parent);
    }

    /**
     * Get parent post ID
     *
     * @return int
     */
    public function parentId() {
        return $this->attachment ? (int) $this->attachment->post_parent : 0;
    }

    /**
     * Check if has parent
     *
     * @return bool
     */
    public function hasParent() {
        return $this->parentId() > 0;
    }

    /**
     * Get edit link
     *
     * @return string
     */
    public function editLink() {
        if (!$this->attachment) return '';

        return get_edit_post_link($this->id);
    }

    /**
     * Update attachment data
     *
     * @param array $data Data to update
     * @return bool
     */
    public function update($data = []) {
        if (!$this->attachment) return false;

        $data['ID'] = $this->id;
        $result = wp_update_post($data);

        if ($result && !is_wp_error($result)) {
            // Refresh the attachment object
            $this->attachment = get_post($this->id);
            return true;
        }

        return false;
    }

    /**
     * Delete attachment
     *
     * @param bool $force Force delete
     * @return bool
     */
    public function delete($force = false) {
        if (!$this->attachment) return false;

        $result = wp_delete_attachment($this->id, $force);

        if ($result) {
            $this->attachment = null;
            $this->id = null;
            return true;
        }

        return false;
    }

    /**
     * Get the raw WP_Post object
     *
     * @return \WP_Post|null
     */
    public function raw() {
        return $this->attachment;
    }

    /**
     * Check if attachment exists
     *
     * @return bool
     */
    public function exists() {
        return $this->attachment !== null;
    }

    /**
     * Convert to array
     *
     * @param array $fields Fields to include
     * @return array
     */
    public function toArray($fields = null) {
        if (!$this->attachment) return [];

        $defaultFields = ['id', 'title', 'url', 'alt', 'caption', 'description'];
        $fields = $fields ?: $defaultFields;

        $array = [];
        foreach ($fields as $field) {
            if (method_exists($this, $field)) {
                $array[$field] = $this->$field();
            } elseif (property_exists($this->attachment, $field)) {
                $array[$field] = $this->attachment->$field;
            }
        }

        return $array;
    }

    /**
     * Convert to JSON
     *
     * @param array $fields Fields to include
     * @return string
     */
    public function toJson($fields = null) {
        return json_encode($this->toArray($fields));
    }

    /**
     * Magic method to get attachment properties
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        if ($this->attachment && property_exists($this->attachment, $name)) {
            return $this->attachment->$name;
        }

        return null;
    }

    /**
     * Magic method to check if property is set
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        return $this->attachment && property_exists($this->attachment, $name);
    }

    /**
     * String representation
     *
     * @return string
     */
    public function __toString() {
        return $this->url();
    }

    // ==========================================
    // Static Methods for Media Extensions
    // ==========================================

    /**
     * Find attachment by ID
     *
     * @param int $id Attachment ID
     * @return static|null
     */
    public static function find($id) {
        $attachment = get_post($id);

        if (!$attachment || $attachment->post_type !== 'attachment') {
            return null;
        }

        return new static($attachment);
    }

    /**
     * Find attachment by URL
     *
     * @param string $url Attachment URL
     * @return static|null
     */
    public static function findByUrl($url) {
        $attachment_id = attachment_url_to_postid($url);

        if (!$attachment_id) {
            return null;
        }

        return new static($attachment_id);
    }

    /**
     * Get all attachments
     *
     * @param array $args Query arguments
     * @return array Array of Media objects
     */
    public static function all($args = []) {
        $defaults = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1
        ];

        $args = wp_parse_args($args, $defaults);
        $attachments = get_posts($args);

        return array_map(function($attachment) {
            return new static($attachment);
        }, $attachments);
    }

    /**
     * Get attachments by parent post
     *
     * @param int $parent_id Parent post ID
     * @param string $mime_type Optional MIME type filter
     * @return array Array of Media objects
     */
    public static function byParent($parent_id, $mime_type = '') {
        $args = [
            'post_parent' => $parent_id,
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1
        ];

        if ($mime_type) {
            $args['post_mime_type'] = $mime_type;
        }

        $attachments = get_children($args);

        return array_map(function($attachment) {
            return new static($attachment);
        }, $attachments);
    }

    /**
     * Get images only
     *
     * @param array $args Additional query arguments
     * @return array Array of Media objects
     */
    public static function images($args = []) {
        $defaults = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => -1
        ];

        $args = wp_parse_args($args, $defaults);
        $attachments = get_posts($args);

        return array_map(function($attachment) {
            return new static($attachment);
        }, $attachments);
    }

    /**
     * Upload file and create attachment
     *
     * @param array $file File array from $_FILES
     * @param int $parent_id Optional parent post ID
     * @param array $metadata Optional attachment metadata
     * @return static|null
     */
    public static function upload($file, $parent_id = 0, $metadata = []) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_overrides = ['test_form' => false];
        $movefile = wp_handle_upload($file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $filename = $movefile['file'];
            $filetype = wp_check_filetype(basename($filename), null);
            $wp_upload_dir = wp_upload_dir();

            // Prepare attachment data
            $attachment = [
                'guid' => $wp_upload_dir['url'] . '/' . basename($filename),
                'post_mime_type' => $filetype['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                'post_content' => '',
                'post_status' => 'inherit'
            ];

            // Merge with provided metadata
            $attachment = array_merge($attachment, $metadata);

            // Insert the attachment
            $attach_id = wp_insert_attachment($attachment, $filename, $parent_id);

            if (!is_wp_error($attach_id)) {
                // Generate attachment metadata
                if (!function_exists('wp_generate_attachment_metadata')) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                }

                $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
                wp_update_attachment_metadata($attach_id, $attach_data);

                return new static($attach_id);
            }
        }

        return null;
    }

    /**
     * Create attachment from URL
     *
     * @param string $url External image URL
     * @param int $parent_id Optional parent post ID
     * @param array $metadata Optional attachment metadata
     * @return static|null
     */
    public static function createFromUrl($url, $parent_id = 0, $metadata = []) {
        if (!function_exists('media_sideload_image')) {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }

        // Download and attach the image
        $attachment_id = media_sideload_image($url, $parent_id, null, 'id');

        if (!is_wp_error($attachment_id)) {
            // Update metadata if provided
            if (!empty($metadata)) {
                $attachment_data = $metadata;
                $attachment_data['ID'] = $attachment_id;
                wp_update_post($attachment_data);
            }

            return new static($attachment_id);
        }

        return null;
    }
}