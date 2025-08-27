<?php
/**
 * HandlesAcf Trait
 *
 * Provides ACF field integration for PostType classes
 * Gracefully handles ACF availability and version compatibility
 *
 * @package WP-Skin
 */

namespace WordPressSkin\PostTypes\Traits;

defined("ABSPATH") or exit();

trait HandlesAcf
{
    /**
     * Get ACF field value (if ACF plugin is active)
     *
     * @param string $selector Field name or field key
     * @param bool $formatValue Whether to apply formatting logic (default: true)
     * @param bool $escapeHtml Whether to return HTML-safe version (default: false)
     * @return mixed ACF field value or null if ACF not active
     */
    public function acf($selector, $formatValue = true, $escapeHtml = false)
    {
        if (!$this->post) {
            return null;
        }

        // Check if ACF plugin is active
        if (!function_exists("get_field")) {
            return null;
        }

        // Use get_field with all parameters
        // Note: $escapeHtml parameter was added in ACF 6.0+
        $reflection = new \ReflectionFunction("get_field");
        $paramCount = $reflection->getNumberOfParameters();

        if ($paramCount >= 4) {
            // ACF 6.0+ with escape_html parameter
            return get_field($selector, $this->id, $formatValue, $escapeHtml);
        } else {
            // Older ACF versions
            return get_field($selector, $this->id, $formatValue);
        }
    }

    /**
     * Check if ACF field exists and has value
     *
     * @param string $selector Field name or field key
     * @return bool
     */
    public function hasAcf($selector)
    {
        if (!$this->post || !function_exists("get_field")) {
            return false;
        }

        $value = get_field($selector, $this->id, false);

        // ACF returns false for non-existent fields, empty string for empty fields
        return $value !== false && $value !== null && $value !== "";
    }

    /**
     * Get ACF field object (if ACF plugin is active)
     *
     * @param string $selector Field name or field key
     * @return array|null ACF field object or null if ACF not active
     */
    public function acfField($selector)
    {
        if (!$this->post) {
            return null;
        }

        // Check if ACF plugin is active
        if (!function_exists("get_field_object")) {
            return null;
        }

        return get_field_object($selector, $this->id);
    }

    /**
     * Update ACF field value (if ACF plugin is active)
     *
     * @param string $selector Field name or field key
     * @param mixed $value Field value
     * @return bool Success status or false if ACF not active
     */
    public function updateAcf($selector, $value)
    {
        if (!$this->post) {
            return false;
        }

        // Check if ACF plugin is active
        if (!function_exists("update_field")) {
            return false;
        }

        return update_field($selector, $value, $this->id);
    }

    /**
     * Delete ACF field value (if ACF plugin is active)
     *
     * @param string $selector Field name or field key
     * @return bool Success status or false if ACF not active
     */
    public function deleteAcf($selector)
    {
        if (!$this->post) {
            return false;
        }

        // Check if ACF plugin is active
        if (!function_exists("delete_field")) {
            return false;
        }

        return delete_field($selector, $this->id);
    }

    /**
     * Get ACF sub field value (for use within ACF loops)
     *
     * @param string $selector Field name or field key
     * @param bool $formatValue Whether to apply formatting logic
     * @return mixed ACF sub field value or null if ACF not active
     */
    public function acfSub($selector, $formatValue = true)
    {
        if (!function_exists("get_sub_field")) {
            return null;
        }

        return get_sub_field($selector, $formatValue);
    }

    /**
     * Check if ACF sub field exists and has value (for use within ACF loops)
     *
     * @param string $selector Field name or field key
     * @return bool
     */
    public function hasAcfSub($selector)
    {
        if (!function_exists("get_sub_field")) {
            return false;
        }

        $value = get_sub_field($selector, false);
        return $value !== false && $value !== null && $value !== "";
    }

    /**
     * Get ACF sub field object (for use within ACF loops)
     *
     * @param string $selector Field name or field key
     * @return array|null ACF sub field object or null if ACF not active
     */
    public function acfSubField($selector)
    {
        if (!function_exists("get_sub_field_object")) {
            return null;
        }

        return get_sub_field_object($selector);
    }

    /**
     * Check if ACF plugin is active
     *
     * @return bool
     */
    public function isAcfActive()
    {
        return function_exists("get_field");
    }

    /**
     * Get ACF version if available
     *
     * @return string|null ACF version or null if not available
     */
    public function getAcfVersion()
    {
        if (!$this->isAcfActive()) {
            return null;
        }

        if (defined("ACF_VERSION")) {
            return ACF_VERSION;
        }

        return null;
    }

    /**
     * Get ACF field with fallback to meta
     * Useful for fields that might exist in either ACF or regular meta
     *
     * @param string $selector ACF field selector
     * @param string $metaKey Meta key for fallback
     * @param mixed $default Default value if neither exists
     * @param bool $formatValue Whether to format ACF value
     * @return mixed
     */
    public function acfOrMeta($selector, $metaKey = null, $default = null, $formatValue = true)
    {
        // Try ACF first
        if ($this->isAcfActive()) {
            $acfValue = $this->acf($selector, $formatValue);
            if ($acfValue !== null && $acfValue !== false && $acfValue !== "") {
                return $acfValue;
            }
        }

        // Fallback to meta
        $metaKey = $metaKey ?: $selector; // Use selector as meta key if not specified
        return $this->meta($metaKey, $default);
    }

    /**
     * Get multiple ACF fields at once
     *
     * @param array $selectors Array of field selectors
     * @param bool $formatValue Whether to format values
     * @return array Associative array of field values
     */
    public function acfMultiple(array $selectors, $formatValue = true)
    {
        $values = [];

        foreach ($selectors as $key => $selector) {
            // Allow both indexed and associative arrays
            $fieldKey = is_string($key) ? $key : $selector;
            $values[$fieldKey] = $this->acf($selector, $formatValue);
        }

        return $values;
    }

    /**
     * Get ACF repeater field data as array
     *
     * @param string $selector Repeater field selector
     * @param array $subFields Sub field selectors to retrieve
     * @return array Array of repeater rows
     */
    public function acfRepeater($selector, array $subFields = [])
    {
        if (!$this->isAcfActive() || !function_exists("have_rows")) {
            return [];
        }

        $rows = [];

        if (have_rows($selector, $this->id)) {
            while (have_rows($selector, $this->id)) {
                the_row();
                $row = [];

                if (empty($subFields)) {
                    // Get all available sub fields (this would require additional ACF functions)
                    // For now, return empty array if no sub fields specified
                    $row = [];
                } else {
                    // Get specified sub fields
                    foreach ($subFields as $subField) {
                        $row[$subField] = $this->acfSub($subField);
                    }
                }

                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Get ACF flexible content field data
     *
     * @param string $selector Flexible content field selector
     * @return array Array of layout data
     */
    public function acfFlexible($selector)
    {
        if (!$this->isAcfActive() || !function_exists("have_rows")) {
            return [];
        }

        $layouts = [];

        if (have_rows($selector, $this->id)) {
            while (have_rows($selector, $this->id)) {
                the_row();
                $layout = [
                    "acf_fc_layout" => get_row_layout(),
                    "data" => [], // Sub fields would need to be specified per layout
                ];

                $layouts[] = $layout;
            }
        }

        return $layouts;
    }
}