<?php
/**
 * GitHub Auto-Updater
 *
 * Enables automatic updates from GitHub releases for the plugin.
 *
 * @package FileTypeIcons
 */

declare(strict_types=1);

namespace FileTypeIcons;

use stdClass;
use WP_Error;
use Parsedown;

defined('ABSPATH') || exit;

/**
 * Class GithubUpdater
 *
 * Handles automatic updates from GitHub releases and the plugin details thickbox popup.
 */
class GithubUpdater
{
    /**
     * GitHub username.
     *
     * @var string
     */
    private const GITHUB_USER = 'guilamu';

    /**
     * GitHub repository name.
     *
     * @var string
     */
    private const GITHUB_REPO = 'file-type-icons';

    /**
     * Plugin file path relative to plugins directory.
     *
     * @var string
     */
    private const PLUGIN_FILE = 'file-type-icons/file-type-icons.php';

    /**
     * Plugin slug.
     *
     * @var string
     */
    private const PLUGIN_SLUG = 'file-type-icons';

    /**
     * Plugin display name.
     *
     * @var string
     */
    private const PLUGIN_NAME = 'File Type Icons';

    /**
     * Plugin description.
     *
     * @var string
     */
    private const PLUGIN_DESCRIPTION = 'Automatically adds customizable SVG icons to file links (PDF, Word, Excel, PowerPoint, TXT).';

    /**
     * Minimum WordPress version required.
     *
     * @var string
     */
    private const REQUIRES_WP = '6.0';

    /**
     * WordPress version tested up to.
     *
     * @var string
     */
    private const TESTED_WP = '6.7';

    /**
     * Minimum PHP version required.
     *
     * @var string
     */
    private const REQUIRES_PHP = '8.0';

    /**
     * Text domain for translations.
     *
     * @var string
     */
    private const TEXT_DOMAIN = 'file-type-icons';

    /**
     * Cache key prefix for GitHub release data.
     *
     * @var string
     */
    private const CACHE_KEY = 'fti_github_release';

    /**
     * Cache expiration in seconds (12 hours).
     *
     * @var int
     */
    private const CACHE_EXPIRATION = 43200;

    /**
     * Optional GitHub token for private repos or rate limit avoidance.
     *
     * @var string
     */
    private const GITHUB_TOKEN = '';

    /**
     * Initialize the updater.
     *
     * @return void
     */
    public static function init(): void
    {
        add_filter('update_plugins_github.com', array(self::class, 'check_for_update'), 10, 4);
        add_filter('plugins_api', array(self::class, 'plugin_info'), 20, 3);
        add_filter('upgrader_source_selection', array(self::class, 'fix_folder_name'), 10, 4);
        add_action('admin_head', array(self::class, 'plugin_info_css'));
    }

    /**
     * Get release data from GitHub with caching.
     *
     * @return array|null Release data or null on failure.
     */
    private static function get_release_data(): ?array
    {
        $release_data = get_transient(self::CACHE_KEY);

        if (false !== $release_data && is_array($release_data)) {
            return $release_data;
        }

        $response = wp_remote_get(
            sprintf('https://api.github.com/repos/%s/%s/releases/latest', self::GITHUB_USER, self::GITHUB_REPO),
            array(
                'user-agent' => 'WordPress/' . self::PLUGIN_SLUG,
                'timeout' => 15,
                'headers' => !empty(self::GITHUB_TOKEN)
                    ? array('Authorization' => 'token ' . self::GITHUB_TOKEN)
                    : array(),
            )
        );

        if (is_wp_error($response)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(self::PLUGIN_NAME . ' Update Error: ' . $response->get_error_message());
            }
            return null;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if (200 !== $response_code) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(self::PLUGIN_NAME . " Update Error: HTTP {$response_code}");
            }
            return null;
        }

        $release_data = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($release_data['tag_name'])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(self::PLUGIN_NAME . ' Update Error: No tag_name in release');
            }
            return null;
        }

        set_transient(self::CACHE_KEY, $release_data, self::CACHE_EXPIRATION);

        return $release_data;
    }

    /**
     * Get the download URL for the plugin package.
     *
     * @param array $release_data Release data from GitHub API.
     * @return string Download URL for the plugin package.
     */
    private static function get_package_url(array $release_data): string
    {
        if (!empty($release_data['assets']) && is_array($release_data['assets'])) {
            foreach ($release_data['assets'] as $asset) {
                if (
                    isset($asset['browser_download_url']) &&
                    isset($asset['name']) &&
                    str_ends_with($asset['name'], '.zip')
                ) {
                    return $asset['browser_download_url'];
                }
            }
        }

        return $release_data['zipball_url'] ?? '';
    }

    /**
     * Get a package URL suitable for the plugin details footer action button.
     *
     * @param array|null $release_data Release data from GitHub API.
     * @return string
     */
    private static function get_plugin_info_download_link(?array $release_data = null): string
    {
        if (is_array($release_data)) {
            $package_url = self::get_package_url($release_data);

            if ('' !== $package_url) {
                return $package_url;
            }
        }

        return sprintf(
            'https://github.com/%s/%s/releases/latest/download/%s.zip',
            self::GITHUB_USER,
            self::GITHUB_REPO,
            self::GITHUB_REPO
        );
    }

    /**
     * Check for plugin updates from GitHub.
     *
     * @param array|false $update      The plugin update data.
     * @param array       $plugin_data Plugin headers.
     * @param string      $plugin_file Plugin file path.
     * @param array       $locales     Installed locales.
     * @return array|false Updated plugin data or false.
     */
    public static function check_for_update($update, array $plugin_data, string $plugin_file, $locales)
    {
        if (self::PLUGIN_FILE !== $plugin_file) {
            return $update;
        }

        $release_data = self::get_release_data();
        if (null === $release_data) {
            return $update;
        }

        $new_version = ltrim($release_data['tag_name'], 'v');

        if (version_compare($plugin_data['Version'], $new_version, '>=')) {
            return $update;
        }

        return array(
            'id'            => 'github.com/' . self::GITHUB_USER . '/' . self::GITHUB_REPO,
            'slug'          => self::PLUGIN_SLUG,
            'plugin'        => self::PLUGIN_FILE,
            'new_version'   => $new_version,
            'version'       => $new_version,
            'package'       => self::get_package_url($release_data),
            'url'           => $release_data['html_url'],
            'tested'        => self::TESTED_WP,
            'requires_php'  => self::REQUIRES_PHP,
            'compatibility' => new stdClass(),
            'icons'         => array(),
            'banners'       => array(),
        );
    }

    /**
     * Provide plugin information for the WordPress plugin details popup.
     *
     * @param false|object|array $res    The result object or array.
     * @param string             $action The type of information being requested.
     * @param object             $args   Plugin API arguments.
     * @return false|object Plugin information or false.
     */
    public static function plugin_info($res, $action, $args)
    {
        if ('plugin_information' !== $action) {
            return $res;
        }

        if (!isset($args->slug) || self::PLUGIN_SLUG !== $args->slug) {
            return $res;
        }

        $plugin_file       = WP_PLUGIN_DIR . '/' . self::PLUGIN_FILE;
        $plugin_data       = get_plugin_data($plugin_file, false, false);
        $release_data      = self::get_release_data();
        $installed_version = $plugin_data['Version'] ?? '1.0.0';
        $release_version   = ($release_data && !empty($release_data['tag_name']))
            ? ltrim($release_data['tag_name'], 'v')
            : '';
        $version           = $installed_version;

        if ($release_version !== '' && version_compare($release_version, $installed_version, '>')) {
            $version = $release_version;
        }

        $res               = new stdClass();
        $res->name         = self::PLUGIN_NAME;
        $res->slug         = self::PLUGIN_SLUG;
        $res->plugin       = self::PLUGIN_FILE;
        $res->version      = $version;
        $res->author       = sprintf('<a href="https://github.com/%s">%s</a>', self::GITHUB_USER, self::GITHUB_USER);
        $res->homepage     = sprintf('https://github.com/%s/%s', self::GITHUB_USER, self::GITHUB_REPO);
        $res->requires     = self::REQUIRES_WP;
        $res->tested       = self::TESTED_WP;
        $res->requires_php = self::REQUIRES_PHP;

        $download_link = self::get_plugin_info_download_link($release_data);

        if ('' !== $download_link) {
            $res->download_link = $download_link;
        }

        if ($release_data && !empty($release_data['published_at'])) {
            $res->last_updated  = $release_data['published_at'];
        }

        $readme = self::parse_readme();

        $res->sections = array(
            'description'  => !empty($readme['description'])
                ? $readme['description']
                : '<p>' . esc_html(self::PLUGIN_DESCRIPTION) . '</p>',
        );

        if (!empty($readme['installation'])) {
            $res->sections['installation'] = $readme['installation'];
        }

        if (!empty($readme['faq'])) {
            $res->sections['faq'] = $readme['faq'];
        }

        $changelog_html = '';

        if ($release_data && !empty($release_data['body']) && version_compare($installed_version, $version, '<')) {
            $changelog_html .= '<h4>' . esc_html($version) . '</h4>'
                             . self::markdown_to_html($release_data['body']);
        }

        if (!empty($readme['changelog'])) {
            $changelog_html .= $readme['changelog'];
        }

        $res->sections['changelog'] = !empty($changelog_html)
            ? $changelog_html
            : sprintf(
                '<p>See <a href="https://github.com/%s/%s/releases" target="_blank">GitHub releases</a> for changelog.</p>',
                esc_attr(self::GITHUB_USER),
                esc_attr(self::GITHUB_REPO)
            );

        return $res;
    }

    /**
     * Inject CSS overrides and optional sidebar info in the plugin-information iframe.
     */
    public static function plugin_info_css(): void
    {
        if (!isset($_GET['plugin'], $_GET['tab'])) {
            return;
        }
        if ('plugin-information' !== sanitize_text_field(wp_unslash($_GET['tab']))
            || self::PLUGIN_SLUG !== sanitize_text_field(wp_unslash($_GET['plugin']))) {
            return;
        }

        $pattern_css = '--s: 27px;'
            . '--c1: #b2b2b2;'
            . '--c2: #ffffff;'
            . '--c3: #d9d9d9;'
            . '--_g: var(--c3) 0 120deg, #0000 0;';

        $pattern_bg = 'conic-gradient(from -60deg at 50% calc(100%/3), var(--_g)),'
            . 'conic-gradient(from 120deg at 50% calc(200%/3), var(--_g)),'
            . 'conic-gradient(from 60deg at calc(200%/3), var(--c3) 60deg, var(--c2) 0 120deg, #0000 0),'
            . 'conic-gradient(from 180deg at calc(100%/3), var(--c1) 60deg, var(--_g)),'
            . 'linear-gradient(90deg, var(--c1) calc(100%/6), var(--c2) 0 50%,'
            . 'var(--c1) 0 calc(500%/6), var(--c2) 0)';

        echo '<style>'
            . '#plugin-information-title.with-banner {'
            .   $pattern_css
            .   'background: ' . $pattern_bg . ' !important;'
            .   'background-size: calc(1.732 * var(--s)) var(--s) !important;'
            . '}'
            . '#plugin-information-title.with-banner h2 {'
            .   'position: relative;'
            .   'font-family: "Helvetica Neue", sans-serif;'
            .   'display: inline-block;'
            .   'font-size: 30px;'
            .   'line-height: 1.68;'
            .   'box-sizing: border-box;'
            .   'max-width: 100%;'
            .   'padding: 0 15px;'
            .   'margin-top: 174px;'
            .   'color: #fff;'
            .   'background: rgba(29, 35, 39, 0.9);'
            .   'text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);'
            .   'box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);'
            .   'border-radius: 8px;'
            . '}'
            . '#section-holder .section h2 { margin: 1.5em 0 0.5em; clear: none; }'
            . '#section-holder .section h3 { margin: 1.5em 0 0.5em; }'
            . '#section-holder .section > :first-child { margin-top: 0; }'
            . '.md-table { display: table; width: 100%; border-collapse: collapse; margin: 1em 0; font-size: 13px; }'
            . '.md-tr { display: table-row; }'
            . '.md-tr > span { display: table-cell; padding: 6px 10px; border: 1px solid #ddd; vertical-align: top; }'
            . '.md-th > span { font-weight: 600; background: #f5f5f5; }'
            . '</style>';

        echo '<script>'
            . 'document.addEventListener("DOMContentLoaded",function(){'
            . 'var title=document.getElementById("plugin-information-title");'
            . 'if(title){title.classList.add("with-banner");}'
            . '});'
            . '</script>';
    }

    /**
     * Parse the local readme.txt or README.md into description, installation, FAQ and changelog HTML.
     *
     * @return array{description: string, installation: string, faq: string, changelog: string}
     */
    private static function parse_readme(): array
    {
        $plugin_dir  = WP_PLUGIN_DIR . '/' . dirname(self::PLUGIN_FILE);
        $readme_path = '';

        if (file_exists($plugin_dir . '/README.md')) {
            $readme_path = $plugin_dir . '/README.md';
        } elseif (file_exists($plugin_dir . '/readme.txt')) {
            $readme_path = $plugin_dir . '/readme.txt';
        }

        if (empty($readme_path)) {
            return array();
        }

        $content = file_get_contents($readme_path);
        if (false === $content) {
            return array();
        }

        // Clean WordPress-specific headings/meta block for readme.txt
        $is_txt = str_ends_with(strtolower($readme_path), '.txt');

        if ($is_txt) {
            // Remove meta header block (anything up to the first double-equals or first section)
            $content = preg_replace('/^.*?==\s*Description\s*==/is', '## Description', $content);
            // Replace === Section === with ## Section
            $content = preg_replace('/===\s*([^\n\r=]+?)\s*===/m', '## $1', $content);
            // Replace == Section == with ## Section
            $content = preg_replace('/==\s*([^\n\r=]+?)\s*==/m', '## $1', $content);
            // Replace = Section = with ### Section
            $content = preg_replace('/=\s*([^\n\r=]+?)\s*=/m', '### $1', $content);
        } else {
            // Remove the main title line (# Title).
            $content = preg_replace('/^#\s+[^\n]+\n*/m', '', $content, 1);
        }

        $utility_sections = array(
            'changelog', 'requirements', 'installation', 'faq',
            'project structure', 'acknowledgements', 'license',
        );

        $parts = preg_split('/^##\s+/m', $content);

        $description  = trim($parts[0] ?? '');
        $installation = '';
        $faq          = '';
        $changelog    = '';

        for ($i = 1, $count = count($parts); $i < $count; $i++) {
            $lines = explode("\n", $parts[$i], 2);
            $title = strtolower(trim($lines[0]));
            $body  = trim($lines[1] ?? '');

            if ('installation' === $title) {
                $installation .= $body . "\n\n";
            } elseif ('faq' === $title || 'frequently asked questions' === $title) {
                $faq .= $body . "\n\n";
            } elseif ('changelog' === $title) {
                $changelog .= $body . "\n\n";
            } elseif (!in_array($title, $utility_sections, true)) {
                $description .= "\n\n## " . trim($lines[0]) . "\n" . $body;
            }
        }

        return array(
            'description'  => self::markdown_to_html(trim($description)),
            'installation' => self::markdown_to_html(trim($installation)),
            'faq'          => self::markdown_to_html(trim($faq)),
            'changelog'    => self::markdown_to_html(trim($changelog)),
        );
    }

    /**
     * Convert Markdown to HTML using Parsedown.
     */
    private static function markdown_to_html(string $markdown): string
    {
        if ('' === $markdown) {
            return '';
        }

        $markdown = preg_replace('/!\[[^\]]*\]\([^\)]+\)/', '', $markdown);
        $markdown = preg_replace('/<p\b[^>]*>\s*(?:(?:<a\b[^>]*>\s*)?<img\b[^>]*>\s*(?:<\/a>\s*)?)+<\/p>\s*/is', '', $markdown);
        $markdown = preg_replace('/(?:<a\b[^>]*>\s*)?<img\b[^>]*>\s*(?:<\/a>)?/i', '', $markdown);

        if (!class_exists('Parsedown')) {
            require_once __DIR__ . '/Parsedown.php';
        }

        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);

        $html = $parsedown->text($markdown);
        $html = self::tables_to_divs($html);

        return $html;
    }

    /**
     * Convert HTML tables to div/span structures compatible with wp_kses.
     */
    private static function tables_to_divs(string $html): string
    {
        return preg_replace_callback('/<table>(.*?)<\/table>/s', function ($m) {
            $table_html = $m[1];
            $output = '<div class="md-table">';

            preg_match_all('/<tr>(.*?)<\/tr>/s', $table_html, $rows);

            foreach ($rows[1] as $idx => $row_content) {
                $is_header = (0 === $idx && strpos($table_html, '<thead>') !== false);
                $row_class = $is_header ? 'md-tr md-th' : 'md-tr';

                preg_match_all('/<t[hd]>(.*?)<\/t[hd]>/s', $row_content, $cells);

                $output .= '<div class="' . $row_class . '">';
                foreach ($cells[1] as $cell) {
                    $output .= '<span>' . $cell . '</span>';
                }
                $output .= '</div>';
            }

            $output .= '</div>';
            return $output;
        }, $html);
    }

    /**
     * Rename the extracted folder to match the expected plugin folder name.
     *
     * @param string      $source        File source location.
     * @param string      $remote_source Remote file source location.
     * @param object      $upgrader      WP_Upgrader instance.
     * @param array       $hook_extra    Extra arguments passed to hooked filters.
     * @return string|WP_Error The corrected source path or WP_Error on failure.
     */
    public static function fix_folder_name($source, $remote_source, $upgrader, $hook_extra)
    {
        global $wp_filesystem;

        if (!isset($hook_extra['plugin'])) {
            return $source;
        }

        if (self::PLUGIN_FILE !== $hook_extra['plugin']) {
            return $source;
        }

        $correct_folder = dirname(self::PLUGIN_FILE);
        $source_folder  = basename(untrailingslashit($source));

        if ($source_folder === $correct_folder) {
            return $source;
        }

        $new_source = trailingslashit($remote_source) . $correct_folder . '/';

        if ($wp_filesystem && $wp_filesystem->move($source, $new_source)) {
            return $new_source;
        }

        if ($wp_filesystem && $wp_filesystem->copy($source, $new_source, true) && $wp_filesystem->delete($source, true)) {
            return $new_source;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '%s updater: failed to rename update folder from %s to %s',
                self::PLUGIN_NAME,
                $source,
                $new_source
            ));
        }

        return new WP_Error(
            'rename_failed',
            __('Unable to rename the update folder. Please retry or update manually.', self::TEXT_DOMAIN)
        );
    }
}
