<?php
declare(strict_types=1);

/**
 * Plugin Name: File Type Icons
 * Description: Automatically adds customizable SVG icons to file links (PDF, Word, Excel, PowerPoint, TXT).
 * Version:     1.0.4
 * Requires PHP: 8.0
 * Requires at least: 6.0
 * Text Domain: file-type-icons
 * Domain Path: /languages
 * Author:      Guilamu
 * Author URI:  https://github.com/guilamu
 * Update URI:  https://github.com/guilamu/file-type-icons/
 * Plugin URI:  https://github.com/guilamu/file-type-icons
 * License:     GPL-2.0-or-later
 */

namespace FileTypeIcons;

defined('ABSPATH') || exit;

// Plugin constants
define('FTI_VERSION', '1.0.4');
define('FTI_PLUGIN_FILE', __FILE__);
define('FTI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FTI_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader for FileTypeIcons namespace classes
spl_autoload_register(function (string $class): void {
    $prefix = 'FileTypeIcons\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relative = str_replace($prefix, '', $class);
    // Convert PascalCase to kebab-case (e.g. AdminSettings -> admin-settings)
    $kebab = preg_replace('/(?<!^)[A-Z]/', '-$0', $relative);
    if ($kebab === null) {
        $kebab = $relative;
    }
    
    $file = FTI_PLUGIN_DIR . 'includes/class-' . strtolower(str_replace('_', '-', $kebab)) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Bootstrap the plugin
add_action('plugins_loaded', [Plugin::class, 'init']);

// Register with Guilamu Bug Reporter
add_action('plugins_loaded', function(): void {
    if (class_exists('Guilamu_Bug_Reporter')) {
        \Guilamu_Bug_Reporter::register([
            'slug'        => 'file-type-icons',
            'name'        => 'File Type Icons',
            'version'     => FTI_VERSION,
            'github_repo' => 'guilamu/file-type-icons',
        ]);
    }
}, 20);
