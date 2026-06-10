<?php
declare(strict_types=1);

namespace FileTypeIcons;

defined('ABSPATH') || exit;

class Plugin {
    /**
     * Initializes the plugin and instantiates the required modules.
     */
    public static function init(): void {
        // Load the translation files of the plugin
        load_plugin_textdomain(
            'file-type-icons',
            false,
            dirname(plugin_basename(FTI_PLUGIN_FILE)) . '/languages'
        );

        // Instantiate components depending on the context
        if (is_admin()) {
            new AdminSettings();
        } else {
            new IconHandler();
        }

        // Disabler must run on both sides (shortcode, Gutenberg meta box, exclusions)
        new Disabler();

        // Initialize GitHub automatic updates
        GithubUpdater::init();
    }
}
