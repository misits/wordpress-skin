<?php
/**
 * WP-Skin Deploy Command
 *
 * Handles theme deployment and production builds
 *
 * @package WP-Skin
 */

namespace WordPressSkin\CLI\WP;

if (!class_exists("WP_CLI")) {
    return;
}

class DeployCommand
{
    /**
     * Build theme for production deployment
     *
     * ## OPTIONS
     *
     * [--output=<directory>]
     * : Output directory for production build. Default: ../theme-name-production
     *
     * [--skip-build]
     * : Skip asset building (use existing built assets)
     *
     * [--no-clean]
     * : Skip cleaning output directory (output directory is cleaned by default)
     *
     * ## EXAMPLES
     *
     *     wp skin:deploy
     *     wp skin:deploy --output=../production-build
     *     wp skin:deploy --skip-build --no-clean
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assoc_args)
    {
        $theme_root = get_template_directory();
        $theme_name = get_template();

        // Validate we're in a theme with WP-Skin
        if (
            !file_exists($theme_root . "/vendor/wordpress-routes/bootstrap.php")
        ) {
            \WP_CLI::error(
                'WP-Skin not found in current theme. Make sure you\'re running this from a theme with WP-Skin installed.',
            );
        }

        // Set output directory
        $output_dir = isset($assoc_args["output"])
            ? $assoc_args["output"]
            : dirname($theme_root) . "/" . $theme_name . "-production";

        $skip_build = isset($assoc_args["skip-build"]);
        $no_clean = isset($assoc_args["no-clean"]);

        \WP_CLI::line("Starting WP-Skin production deployment...");
        \WP_CLI::line("Theme: {$theme_name}");
        \WP_CLI::line("Output: {$output_dir}");

        try {
            $this->buildForProduction(
                $theme_root,
                $output_dir,
                $skip_build,
                $no_clean,
            );
            \WP_CLI::success("Production build completed successfully!");
            $this->showDeploymentInfo($output_dir);
        } catch (Exception $e) {
            \WP_CLI::error("Deployment failed: " . $e->getMessage());
        }
    }

    /**
     * Build theme for production
     */
    private function buildForProduction(
        $theme_root,
        $output_dir,
        $skip_build,
        $no_clean,
    ) {
        $resources_dir = $theme_root . "/resources";

        // Clean output directory by default (unless --no-clean is specified)
        if (!$no_clean && is_dir($output_dir)) {
            \WP_CLI::line("Cleaning output directory...");
            $this->removeDirectory($output_dir);
        }

        // Create output directory
        if (!is_dir($output_dir)) {
            wp_mkdir_p($output_dir);
        }

        // Build assets if not skipping
        if (!$skip_build && is_dir($resources_dir)) {
            $this->buildAssets($resources_dir);
        }

        // Copy all theme files except .gitignore patterns
        \WP_CLI::line("Copying theme files...");
        $this->copyThemeFiles($theme_root, $output_dir);

        // Set permissions
        \WP_CLI::line("Setting file permissions...");
        $this->setPermissions($output_dir);

        // Show size comparison
        $this->showSizeComparison($theme_root, $output_dir);
    }

    /**
     * Build assets using npm
     */
    private function buildAssets($resources_dir)
    {
        if (!file_exists($resources_dir . "/package.json")) {
            \WP_CLI::line(
                "[WARNING] No package.json found, skipping asset build",
            );
            return;
        }

        \WP_CLI::line("Building production assets...");

        $old_dir = getcwd();
        chdir($resources_dir);

        // Install dependencies if needed
        if (!is_dir("node_modules")) {
            \WP_CLI::line("Installing dependencies...");
            $result = $this->runCommand("npm install");
            if ($result !== 0) {
                chdir($old_dir);
                throw new \Exception("Failed to install npm dependencies");
            }
        }

        // Build for production (force production mode)
        \WP_CLI::line("Building CSS and JS for production...");
        $result = $this->runCommand("NODE_ENV=production npm run build");
        if ($result !== 0) {
            chdir($old_dir);
            throw new \Exception("Failed to build assets");
        }

        chdir($old_dir);
    }

    /**
     * Copy theme files (exclude only .gitignore patterns)
     */
    private function copyThemeFiles($theme_root, $output_dir)
    {
        // Create .gitignore if it doesn't exist
        $this->ensureGitignoreExists($theme_root);

        // Read .gitignore patterns
        $gitignore_patterns = $this->getGitignorePatterns($theme_root);

        // Copy all files/directories except those matching .gitignore patterns
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $theme_root,
                \RecursiveDirectoryIterator::SKIP_DOTS,
            ),
            \RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace(
                $theme_root . "/",
                "",
                $item->getPathname(),
            );

            // Skip if matches gitignore patterns
            if (
                $this->matchesGitignorePattern(
                    $relativePath,
                    $gitignore_patterns,
                )
            ) {
                continue;
            }

            $destPath = $output_dir . "/" . $relativePath;

            if ($item->isDir()) {
                wp_mkdir_p($destPath);
            } else {
                // Ensure parent directory exists
                $parentDir = dirname($destPath);
                if (!is_dir($parentDir)) {
                    wp_mkdir_p($parentDir);
                }
                copy($item->getPathname(), $destPath);
            }
        }
    }

    /**
     * Set proper file permissions
     */
    private function setPermissions($output_dir)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $output_dir,
                \RecursiveDirectoryIterator::SKIP_DOTS,
            ),
            \RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                chmod($item->getPathname(), 0644);
            } elseif ($item->isDir()) {
                chmod($item->getPathname(), 0755);
            }
        }
    }

    /**
     * Show size comparison
     */
    private function showSizeComparison($theme_root, $output_dir)
    {
        $dev_size = $this->getDirectorySize($theme_root);
        $prod_size = $this->getDirectorySize($output_dir);

        \WP_CLI::line("Size comparison:");
        \WP_CLI::line("   Development: " . $this->formatBytes($dev_size));
        \WP_CLI::line("   Production:  " . $this->formatBytes($prod_size));

        $reduction = round((($dev_size - $prod_size) / $dev_size) * 100, 1);
        \WP_CLI::line("   Reduction:   {$reduction}%");
    }

    /**
     * Show deployment information
     */
    private function showDeploymentInfo($output_dir)
    {
        \WP_CLI::line("");
        \WP_CLI::success("Production build ready!");
        \WP_CLI::line("Build location: {$output_dir}");
        \WP_CLI::line("");
        \WP_CLI::line("Upload these directories to your production server:");
        \WP_CLI::line("   - vendor/wordpress-routes/ (WP-Skin core)");
        \WP_CLI::line("   - resources/assets/ (compiled CSS/JS)");
        \WP_CLI::line("   - resources/components/ (PHP components)");
        \WP_CLI::line("   - resources/layouts/ (PHP layouts)");
        \WP_CLI::line("   - resources/views/ (PHP views)");
        \WP_CLI::line("   - resources/dist/ (built modern JS assets)");
        \WP_CLI::line("   - *.php files (theme files)");
        \WP_CLI::line("");
        \WP_CLI::line("The following were excluded (development only):");
        \WP_CLI::line("   - resources/src/ (source files)");
        \WP_CLI::line("   - resources/node_modules/ (dependencies)");
        \WP_CLI::line("   - resources/package*.json (build configs)");
        \WP_CLI::line("   - resources/vite.config.mjs (build tools)");
    }

    /**
     * Helper methods
     */
    private function runCommand($command)
    {
        $output = [];
        $return_var = 0;
        exec($command . " 2>&1", $output, $return_var);

        if ($return_var !== 0) {
            \WP_CLI::line("Command output: " . implode("\n", $output));
        }

        return $return_var;
    }

    private function copyDirectory($source, $dest)
    {
        if (!is_dir($dest)) {
            wp_mkdir_p($dest);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $source,
                \RecursiveDirectoryIterator::SKIP_DOTS,
            ),
            \RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $item) {
            $destPath =
                $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                wp_mkdir_p($destPath);
            } else {
                copy($item, $destPath);
            }
        }
    }

    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \RecursiveDirectoryIterator::SKIP_DOTS,
            ),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }

    private function getDirectorySize($directory)
    {
        $size = 0;
        if (is_dir($directory)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $directory,
                    \RecursiveDirectoryIterator::SKIP_DOTS,
                ),
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }

        return $size;
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ["B", "KB", "MB", "GB", "TB"];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . " " . $units[$i];
    }

    /**
     * Ensure .gitignore exists in theme root
     */
    private function ensureGitignoreExists($theme_root)
    {
        $gitignore_file = $theme_root . "/.gitignore";

        if (!file_exists($gitignore_file)) {
            \WP_CLI::line("Creating .gitignore file...");

            $gitignore_content = '# WP-Skin Development Files
# Exclude development and build files that shouldn\'t be in production

# Node modules and build artifacts
resources/node_modules/
resources/.npm/
resources/.yarn/
resources/yarn-error.log
resources/npm-debug.log*
resources/yarn-debug.log*
resources/yarn-error.log*

# Development server files (keep manifest.json for production)
resources/.vite/client.js
resources/.vite/client.mjs
resources/.vite/index.html
resources/.cache/

# Source files (keep built files only)
resources/src/
resources/package.json
resources/package-lock.json
resources/yarn.lock
resources/vite.config.mjs
resources/webpack.config.js
resources/rollup.config.js
resources/README.md

# SCSS source files (keep compiled CSS only)
resources/**/*.scss
resources/**/*.sass

# Development configs
resources/.eslintrc*
resources/.prettierrc*
resources/tsconfig.json
resources/babel.config.js
resources/.env.local
resources/.env.development

# Editor files
.vscode/
.idea/
*.swp
*.swo
*~

# OS generated files
.DS_Store
.DS_Store?
._*
.Spotlight-V100
.Trashes
ehthumbs.db
Thumbs.db

# Logs
*.log
resources/logs/

# Temporary files
*.tmp
*.temp

# WordPress debug
debug.log
wp-content/debug.log

# Deployment builds
*-production/
';

            file_put_contents($gitignore_file, $gitignore_content);
            \WP_CLI::success("Created .gitignore file at {$gitignore_file}");
        }
    }

    /**
     * Read .gitignore patterns
     */
    private function getGitignorePatterns($theme_root)
    {
        $gitignore_file = $theme_root . "/.gitignore";
        $patterns = [];

        if (file_exists($gitignore_file)) {
            $lines = file(
                $gitignore_file,
                FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES,
            );
            foreach ($lines as $line) {
                $line = trim($line);
                // Skip comments and empty lines
                if ($line && !str_starts_with($line, "#")) {
                    $patterns[] = $line;
                }
            }
        }

        return $patterns;
    }

    /**
     * Check if path matches gitignore patterns
     */
    private function matchesGitignorePattern($path, $patterns)
    {
        foreach ($patterns as $pattern) {
            // Convert gitignore pattern to regex-like matching
            if (str_ends_with($pattern, "/")) {
                // Directory pattern
                $pattern = rtrim($pattern, "/");
                if (
                    str_starts_with($path, $pattern . "/") ||
                    $path === $pattern
                ) {
                    return true;
                }
            } elseif (str_contains($pattern, "*")) {
                // Wildcard pattern
                $regex_pattern = str_replace(
                    ["*", "/"],
                    [".*", "\/"],
                    $pattern,
                );
                if (preg_match("/^" . $regex_pattern . '$/', $path)) {
                    return true;
                }
            } else {
                // Exact match or directory match
                if (
                    $path === $pattern ||
                    str_starts_with($path, $pattern . "/")
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
