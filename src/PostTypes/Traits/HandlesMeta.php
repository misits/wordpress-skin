<?php
/**
 * HandlesMeta Trait
 *
 * Provides advanced meta field management for PostType classes
 * Includes ACF-style array/row management and type-safe meta handling
 *
 * @package WP-Skin
 */

namespace WordPressSkin\PostTypes\Traits;

defined("ABSPATH") or exit();

trait HandlesMeta
{
    /**
     * Post meta cache
     *
     * @var array
     */
    protected $metaCache = [];

    /**
     * Get post meta
     *
     * @param string $key Meta key
     * @param mixed $default Default value
     * @param bool $single Return single value
     * @return mixed
     */
    public function meta($key, $default = null, $single = true)
    {
        if (!$this->post) {
            return $default;
        }

        // Cache meta to avoid multiple database queries
        $cacheKey = $key . "_" . ($single ? "single" : "array");

        if (!isset($this->metaCache[$cacheKey])) {
            $value = get_post_meta($this->id, $key, $single);
            $this->metaCache[$cacheKey] = $value;
        }

        $value = $this->metaCache[$cacheKey];

        return $value === "" || $value === false || $value === []
            ? $default
            : $value;
    }

    /**
     * Update post meta
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function updateMeta($key, $value)
    {
        if (!$this->post) {
            return false;
        }

        // Clear cache
        $this->clearMetaCache($key);

        return update_post_meta($this->id, $key, $value);
    }

    /**
     * Add post meta (doesn't replace existing)
     *
     * @param string $key
     * @param mixed $value
     * @param bool $unique
     * @return bool|int
     */
    public function addMeta($key, $value, $unique = false)
    {
        if (!$this->post) {
            return false;
        }

        // Clear cache
        $this->clearMetaCache($key);

        return add_post_meta($this->id, $key, $value, $unique);
    }

    /**
     * Delete post meta
     *
     * @param string $key
     * @param mixed $value Optional specific value to delete
     * @return bool
     */
    public function deleteMeta($key, $value = "")
    {
        if (!$this->post) {
            return false;
        }

        // Clear cache
        $this->clearMetaCache($key);

        return delete_post_meta($this->id, $key, $value);
    }

    /**
     * Get all post meta
     *
     * @return array
     */
    public function allMeta()
    {
        if (!$this->post) {
            return [];
        }

        return get_post_meta($this->id);
    }

    /**
     * Check if meta key exists
     *
     * @param string $key
     * @return bool
     */
    public function hasMeta($key)
    {
        if (!$this->post) {
            return false;
        }

        return metadata_exists("post", $this->id, $key);
    }

    /**
     * Clear meta cache for a specific key
     *
     * @param string $key
     * @return void
     */
    protected function clearMetaCache($key)
    {
        unset($this->metaCache[$key . "_single"]);
        unset($this->metaCache[$key . "_array"]);

        // Also clear WordPress meta cache
        wp_cache_delete($this->id, "post_meta");
    }

    /**
     * Clear all meta cache
     *
     * @return void
     */
    public function clearAllMetaCache()
    {
        $this->metaCache = [];
        wp_cache_delete($this->id, "post_meta");
    }

    // ==========================================
    // Type-Safe Meta Methods
    // ==========================================

    /**
     * Get meta as string
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public function getStringMeta($key, $default = "")
    {
        $value = $this->meta($key, $default);
        return (string) $value;
    }

    /**
     * Get meta as integer
     *
     * @param string $key
     * @param int $default
     * @return int
     */
    public function getIntegerMeta($key, $default = 0)
    {
        $value = $this->meta($key, $default);
        return (int) $value;
    }

    /**
     * Get meta as float
     *
     * @param string $key
     * @param float $default
     * @return float
     */
    public function getFloatMeta($key, $default = 0.0)
    {
        $value = $this->meta($key, $default);
        return (float) $value;
    }

    /**
     * Get meta as boolean
     *
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public function getBooleanMeta($key, $default = false)
    {
        $value = $this->meta($key, $default);

        // Handle various truthy/falsy values
        if (is_string($value)) {
            return !in_array(strtolower($value), [
                "",
                "0",
                "false",
                "no",
                "off",
                "null",
            ]);
        }

        return (bool) $value;
    }

    /**
     * Get meta as array (always returns array, even for single values)
     *
     * @param string $key
     * @param array $default
     * @return array
     */
    public function getArrayMeta($key, $default = [])
    {
        $value = $this->meta($key, $default, false);

        if (!is_array($value)) {
            return $value !== null && $value !== "" ? [$value] : $default;
        }

        return $value ?: $default;
    }

    /**
     * Get meta as JSON decoded array
     *
     * @param string $key
     * @param array $default
     * @return array
     */
    public function getJsonMeta($key, $default = [])
    {
        $value = $this->meta($key, "");

        if (empty($value)) {
            return $default;
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $default;
    }

    /**
     * Set meta as JSON
     *
     * @param string $key
     * @param array $value
     * @return bool
     */
    public function setJsonMeta($key, $value)
    {
        $json = json_encode($value);
        return $this->updateMeta($key, $json);
    }

    /**
     * Get meta as DateTime object
     *
     * @param string $key
     * @param \DateTime|null $default
     * @return \DateTime|null
     */
    public function getDateMeta($key, $default = null)
    {
        $value = $this->meta($key);

        if (empty($value)) {
            return $default;
        }

        try {
            return new \DateTime($value);
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Set meta as date
     *
     * @param string $key
     * @param \DateTime|string $value
     * @param string $format
     * @return bool
     */
    public function setDateMeta($key, $value, $format = "Y-m-d H:i:s")
    {
        if ($value instanceof \DateTime) {
            $dateString = $value->format($format);
        } elseif (is_string($value)) {
            $dateString = $value;
        } else {
            return false;
        }

        return $this->updateMeta($key, $dateString);
    }

    // ==========================================
    // Array/Row Management
    // ==========================================

    /**
     * Get row count
     *
     * @param string $key
     * @return int
     */
    public function getCount($key)
    {
        return $this->getIntegerMeta($key, 0);
    }

    /**
     * Get rows
     *
     * @param string $key Base field name
     * @param array $subFields Sub field names to retrieve
     * @return array Array of rows with sub field data
     */
    public function getRows($key, $subFields = [])
    {
        $count = $this->getCount($key);
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $row = [];

            if (empty($subFields)) {
                // Get all meta keys that match the pattern
                $allMeta = $this->allMeta();
                $pattern = "/^{$key}_{$i}_(.+)$/";

                foreach ($allMeta as $metaKey => $metaValue) {
                    if (preg_match($pattern, $metaKey, $matches)) {
                        $subField = $matches[1];
                        $row[$subField] = is_array($metaValue)
                            ? $metaValue[0]
                            : $metaValue;
                    }
                }
            } else {
                // Get specific sub fields
                foreach ($subFields as $subField) {
                    $metaKey = "{$key}_{$i}_{$subField}";
                    $row[$subField] = $this->meta($metaKey);
                }
            }

            if (!empty($row)) {
                $row["_index"] = $i;
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Update rows
     *
     * @param string $key Base field name
     * @param array $rows Array of row data
     * @return bool
     */
    public function updateRows($key, $rows)
    {
        if (!$this->post) {
            return false;
        }

        // Clear existing rows first
        $oldCount = $this->getCount($key);
        for ($i = 0; $i < $oldCount; $i++) {
            $this->deleteRowMeta($key, $i);
        }

        // Update count
        $this->updateMeta($key, count($rows));

        // Add new rows
        foreach ($rows as $index => $row) {
            foreach ($row as $subField => $value) {
                if ($subField !== "_index") {
                    $metaKey = "{$key}_{$index}_{$subField}";
                    $this->updateMeta($metaKey, $value);
                }
            }
        }

        return true;
    }

    /**
     * Add row
     *
     * @param string $key Base field name
     * @param array $rowData Row data
     * @return int New row index
     */
    public function addRow($key, $rowData)
    {
        $count = $this->getCount($key);
        $newIndex = $count;

        // Add row data
        foreach ($rowData as $subField => $value) {
            $metaKey = "{$key}_{$newIndex}_{$subField}";
            $this->updateMeta($metaKey, $value);
        }

        // Update count
        $this->updateMeta($key, $count + 1);

        return $newIndex;
    }

    /**
     * Delete row
     *
     * @param string $key Base field name
     * @param int $index Row index to delete
     * @return bool
     */
    public function deleteRow($key, $index)
    {
        $count = $this->getCount($key);

        if ($index >= $count || $index < 0) {
            return false;
        }

        // Delete row meta
        $this->deleteRowMeta($key, $index);

        // Shift remaining rows up
        for ($i = $index + 1; $i < $count; $i++) {
            $this->moveRow($key, $i, $i - 1);
        }

        // Update count
        $this->updateMeta($key, $count - 1);

        return true;
    }

    /**
     * Get single row
     *
     * @param string $key Base field name
     * @param int $index Row index
     * @param array $subFields Sub field names to retrieve
     * @return array|null
     */
    public function getRow($key, $index, $subFields = [])
    {
        $count = $this->getCount($key);

        if ($index >= $count || $index < 0) {
            return null;
        }

        $row = [];

        if (empty($subFields)) {
            // Get all sub fields for this row
            $allMeta = $this->allMeta();
            $pattern = "/^{$key}_{$index}_(.+)$/";

            foreach ($allMeta as $metaKey => $metaValue) {
                if (preg_match($pattern, $metaKey, $matches)) {
                    $subField = $matches[1];
                    $row[$subField] = is_array($metaValue)
                        ? $metaValue[0]
                        : $metaValue;
                }
            }
        } else {
            // Get specific sub fields
            foreach ($subFields as $subField) {
                $metaKey = "{$key}_{$index}_{$subField}";
                $row[$subField] = $this->meta($metaKey);
            }
        }

        $row["_index"] = $index;
        return $row;
    }

    /**
     * Update single row
     *
     * @param string $key Base field name
     * @param int $index Row index
     * @param array $rowData Row data
     * @return bool
     */
    public function updateRow($key, $index, $rowData)
    {
        $count = $this->getCount($key);

        if ($index >= $count || $index < 0) {
            return false;
        }

        foreach ($rowData as $subField => $value) {
            if ($subField !== "_index") {
                $metaKey = "{$key}_{$index}_{$subField}";
                $this->updateMeta($metaKey, $value);
            }
        }

        return true;
    }

    /**
     * Delete all meta for a specific row
     *
     * @param string $key Base field name
     * @param int $index Row index
     * @return void
     */
    protected function deleteRowMeta($key, $index)
    {
        $allMeta = $this->allMeta();
        $pattern = "/^{$key}_{$index}_(.+)$/";

        foreach ($allMeta as $metaKey => $metaValue) {
            if (preg_match($pattern, $metaKey)) {
                $this->deleteMeta($metaKey);
            }
        }
    }

    /**
     * Move row data from one index to another
     *
     * @param string $key Base field name
     * @param int $fromIndex Source index
     * @param int $toIndex Destination index
     * @return void
     */
    protected function moveRow($key, $fromIndex, $toIndex)
    {
        $allMeta = $this->allMeta();
        $pattern = "/^{$key}_{$fromIndex}_(.+)$/";

        foreach ($allMeta as $metaKey => $metaValue) {
            if (preg_match($pattern, $metaKey, $matches)) {
                $subField = $matches[1];
                $newKey = "{$key}_{$toIndex}_{$subField}";

                // Copy to new location
                $this->updateMeta(
                    $newKey,
                    is_array($metaValue) ? $metaValue[0] : $metaValue,
                );

                // Delete old location
                $this->deleteMeta($metaKey);
            }
        }
    }

    // ==========================================
    // Group/Object Field Management
    // ==========================================

    /**
     * Get group field data
     *
     * @param string $key Base field name
     * @param array $subFields Sub field names to retrieve
     * @return array
     */
    public function getGroupField($key, $subFields = [])
    {
        $group = [];

        if (empty($subFields)) {
            // Auto-discover sub fields
            $allMeta = $this->allMeta();
            $pattern = "/^{$key}_(.+)$/";

            foreach ($allMeta as $metaKey => $metaValue) {
                if (preg_match($pattern, $metaKey, $matches)) {
                    $subField = $matches[1];
                    // Skip if this looks like a repeater row (contains numbers)
                    if (!preg_match("/^\d+_/", $subField)) {
                        $group[$subField] = is_array($metaValue)
                            ? $metaValue[0]
                            : $metaValue;
                    }
                }
            }
        } else {
            // Get specific sub fields
            foreach ($subFields as $subField) {
                $metaKey = "{$key}_{$subField}";
                $group[$subField] = $this->meta($metaKey);
            }
        }

        return $group;
    }

    /**
     * Update group field data
     *
     * @param string $key Base field name
     * @param array $groupData Group field data
     * @return bool
     */
    public function updateGroupField($key, $groupData)
    {
        if (!$this->post) {
            return false;
        }

        foreach ($groupData as $subField => $value) {
            $metaKey = "{$key}_{$subField}";
            $this->updateMeta($metaKey, $value);
        }

        return true;
    }

    /**
     * Delete group field data
     *
     * @param string $key Base field name
     * @return bool
     */
    public function deleteGroupField($key)
    {
        if (!$this->post) {
            return false;
        }

        $allMeta = $this->allMeta();
        $pattern = "/^{$key}_(.+)$/";

        foreach ($allMeta as $metaKey => $metaValue) {
            if (preg_match($pattern, $metaKey)) {
                $this->deleteMeta($metaKey);
            }
        }

        return true;
    }

    // ==========================================
    // Legacy Type-Safe Methods (for compatibility)
    // ==========================================

    /**
     * Helper method for meta with typed return
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getTypedMeta($key, $default = null)
    {
        return $this->meta($key, $default);
    }

    /**
     * Helper method for boolean meta (protected for backward compatibility)
     *
     * @param string $key
     * @param bool $default
     * @return bool
     */
    protected function getBooleanMeta_protected($key, $default = false)
    {
        return $this->getBooleanMeta($key, $default);
    }

    /**
     * Helper method for integer meta (protected for backward compatibility)
     *
     * @param string $key
     * @param int $default
     * @return int
     */
    protected function getIntegerMeta_protected($key, $default = 0)
    {
        return $this->getIntegerMeta($key, $default);
    }

    /**
     * Helper method for float meta (protected for backward compatibility)
     *
     * @param string $key
     * @param float $default
     * @return float
     */
    protected function getFloatMeta_protected($key, $default = 0.0)
    {
        return $this->getFloatMeta($key, $default);
    }

    /**
     * Helper method for array meta (protected for backward compatibility)
     *
     * @param string $key
     * @param array $default
     * @return array
     */
    protected function getArrayMeta_protected($key, $default = [])
    {
        return $this->getArrayMeta($key, $default);
    }
}
