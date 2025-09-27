<?php

namespace WordPressSkin\Media;

// Prevent direct access.
defined("ABSPATH") or exit();

/**
 * Image Upscaling and Resizing Utilities
 *
 * Provides advanced image resizing algorithms with better quality preservation
 * than WordPress default methods. Handles both cropping and aspect ratio preservation.
 */
class Upscale
{
    /**
     * Resize image with center cropping
     *
     * Calculates resize dimensions to fill the target size completely,
     * cropping from the center to maintain aspect ratio.
     *
     * @param int|float $orig_w Original image width
     * @param int|float $orig_h Original image height
     * @param int|float $new_w Target width
     * @param int|float $new_h Target height
     * @return array Array of resize parameters [dst_x, dst_y, src_x, src_y, dst_w, dst_h, src_w, src_h]
     */
    public static function resize_crop($orig_w, $orig_h, $new_w, $new_h): array
    {
        $size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

        $crop_w = round($new_w / $size_ratio);
        $crop_h = round($new_h / $size_ratio);

        $s_x = floor(($orig_w - $crop_w) / 2);
        $s_y = floor(($orig_h - $crop_h) / 2);

        return [
            0,
            0,
            (int) $s_x,
            (int) $s_y,
            (int) $new_w,
            (int) $new_h,
            (int) $crop_w,
            (int) $crop_h,
        ];
    }

    /**
     * Resize image while maintaining aspect ratio
     *
     * Calculates resize dimensions to fit within the target size
     * without cropping, maintaining the original aspect ratio.
     *
     * @param int|float $orig_w Original image width
     * @param int|float $orig_h Original image height
     * @param int|float $new_w Target width
     * @param int|float $new_h Target height
     * @return array|null Array of resize parameters or null if ratio is invalid
     */
    public static function resize_keep_ratio(
        $orig_w,
        $orig_h,
        $new_w,
        $new_h,
    ): ?array {
        $size_ratio = min($new_w / $orig_w, $new_h / $orig_h);

        if ($size_ratio === 0) {
            return null;
        }

        $new_w = $orig_w * $size_ratio;
        $new_h = $orig_h * $size_ratio;

        return [
            0,
            0,
            0,
            0,
            (int) $new_w,
            (int) $new_h,
            (int) $orig_w,
            (int) $orig_h,
        ];
    }

    /**
     * Main resize function that chooses between crop and ratio preservation
     *
     * WordPress filter callback that determines whether to crop or preserve
     * aspect ratio based on the crop parameter.
     *
     * @param mixed $default WordPress default resize parameters (unused)
     * @param int|float $orig_w Original image width
     * @param int|float $orig_h Original image height
     * @param int|float $new_w Target width
     * @param int|float $new_h Target height
     * @param bool $crop Whether to crop the image
     * @return array|null Array of resize parameters or null
     */
    public static function resize(
        $default,
        $orig_w,
        $orig_h,
        $new_w,
        $new_h,
        $crop,
    ): ?array {
        if ($crop) {
            return self::resize_crop($orig_w, $orig_h, $new_w, $new_h);
        }

        return self::resize_keep_ratio($orig_w, $orig_h, $new_w, $new_h);
    }
}
