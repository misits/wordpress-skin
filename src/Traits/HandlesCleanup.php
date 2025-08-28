<?php
/**
 * Handles Cleanup functionality
 *
 * @package WP-Skin
 */

namespace WordPressSkin\Traits;

use WordPressSkin\Cleanup\CleanupManager;

defined("ABSPATH") or exit();

trait HandlesCleanup
{
    /**
     * Cleanup manager instance
     *
     * @var CleanupManager|null
     */
    private $cleanupManager = null;

    /**
     * Initialize cleanup functionality
     *
     * @return void
     */
    protected function initializeCleanup(): void
    {
        // Cleanup manager will be initialized when first accessed
    }

    /**
     * Get cleanup manager instance
     *
     * @return CleanupManager
     */
    public function getCleanupManager(): CleanupManager
    {
        if ($this->cleanupManager === null) {
            $this->cleanupManager = new CleanupManager();
        }

        return $this->cleanupManager;
    }

    /**
     * Configure WordPress cleanup
     *
     * @param array $config Cleanup configuration
     * @return void
     */
    public function configureCleanup(array $config): void
    {
        $this->getCleanupManager()->updateConfig($config);
    }
}