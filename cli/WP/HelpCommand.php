<?php

namespace WordPressSkin\CLI\WP;

use \WP_CLI_Command;
use \WP_CLI;

/**
 * WP-Skin Help commands for WP-CLI
 */
class HelpCommand extends \WP_CLI_Command
{
    /**
     * Show WP-Skin CLI help
     *
     * ## EXAMPLES
     *
     *     wp skin:help
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke($args, $assoc_args)
    {
        \WP_CLI::line("");
        \WP_CLI::line("NAME");
        \WP_CLI::line("WP-Skin");

        \WP_CLI::line("");
        \WP_CLI::line("DESCRIPTION");
        \WP_CLI::line(
            "WP-Skin is a unified theme management system for WordPress with Laravel-style syntax.",
        );
        \WP_CLI::line(
            "It provides modern development patterns, SCSS support, and production deployment tools.",
        );

        \WP_CLI::line("");
        \WP_CLI::line("USAGE");
        \WP_CLI::line("  wp skin:<command> [<args>]");

        \WP_CLI::line("");

        \WP_CLI::line("DEPLOYMENT COMMANDS:");
        \WP_CLI::line(
            "  wp skin:deploy                       Create production build",
        );
        \WP_CLI::line(
            "    --output=<directory>                 Output directory for production build",
        );
        \WP_CLI::line(
            "    --skip-build                         Skip asset building",
        );
        \WP_CLI::line(
            "    --clean                              Clean output directory before building",
        );
        \WP_CLI::line("");

        \WP_CLI::line("ASSET COMMANDS:");
        \WP_CLI::line(
            "  wp skin:build                        Build CSS/JS for production",
        );
        \WP_CLI::line(
            "    --production                         Build for production (minified)",
        );
        \WP_CLI::line(
            "  wp skin:watch                        Watch and rebuild assets",
        );
        \WP_CLI::line("");

        \WP_CLI::line("SCAFFOLDING COMMANDS:");
        \WP_CLI::line(
            "  wp skin:component <name>             Create new component",
        );
        \WP_CLI::line(
            "    --with-scss                          Create SCSS file for component",
        );
        \WP_CLI::line(
            "    --with-js                            Create JS file for component",
        );
        \WP_CLI::line(
            "  wp skin:layout <name>                Create new layout",
        );

        \WP_CLI::line("");

        \WP_CLI::line("INFORMATION COMMANDS:");
        \WP_CLI::line(
            "  wp skin:info                         Show WP-Skin information",
        );
        \WP_CLI::line(
            "  wp skin:status                       Check theme status",
        );
        \WP_CLI::line("");

        \WP_CLI::line("EXAMPLES:");
        \WP_CLI::line("  wp skin:deploy --clean");
        \WP_CLI::line("  wp skin:component navbar --with-scss --with-js");
        \WP_CLI::line("  wp skin:layout homepage");
        \WP_CLI::line("  wp skin:build --production");

        \WP_CLI::line("MODE CONFIGURATION:");
        \WP_CLI::line("Set WPSKIN_MODE in your functions.php:");
        \WP_CLI::line(
            "  define('WPSKIN_MODE', 'theme');   // For theme development (default)",
        );
        \WP_CLI::line("");

        \WP_CLI::line("VERSION:");
        if (function_exists("wpskin_version")) {
            \WP_CLI::line("  WP-Skin: " . wpskin_version());
        }
        \WP_CLI::line("");
    }
}
