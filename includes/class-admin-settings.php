<?php
declare(strict_types=1);

namespace FileTypeIcons;

defined('ABSPATH') || exit;

class AdminSettings {
    /**
     * Default brand colors for each file type.
     */
    private const DEFAULT_COLORS = [
        'pdf'        => '#E53935',
        'word'       => '#1565C0',
        'excel'      => '#2E7D32',
        'powerpoint' => '#D84315',
        'text'       => '#616161',
        'archives'   => '#8E24AA',
        'audio'      => '#D81B60',
        'images'     => '#00897B',
        'video'      => '#F57C00',
    ];

    /**
     * Supported file types and their labels.
     */
    private const AVAILABLE_TYPES = [
        'pdf'        => 'PDF (.pdf)',
        'word'       => 'Microsoft Word & OpenDocument (.doc, .docx, .odt, .rtf)',
        'excel'      => 'Microsoft Excel, CSV & OpenDocument (.xls, .xlsx, .csv, .ods)',
        'powerpoint' => 'Microsoft PowerPoint & OpenDocument (.ppt, .pptx, .odp)',
        'text'       => 'Text File (.txt)',
        'archives'   => 'Archive (.zip, .rar, .tar, .gz, .7z)',
        'audio'      => 'Audio (.mp3, .wav, .m4a, .flac)',
        'images'     => 'Image (.jpg, .png, .gif, .svg, .webp, .odg)',
        'video'      => 'Video (.mp4, .avi, .mov, .mkv, .webm)',
    ];

    /**
     * Constructor - Registers admin actions.
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_options_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_filter('plugin_action_links_' . plugin_basename(FTI_PLUGIN_FILE), [$this, 'add_settings_link']);
        add_filter('plugin_row_meta', [$this, 'add_view_details_link'], 10, 2);
    }

    /**
     * Adds the "Settings" link on the installed plugins page.
     */
    public function add_settings_link(array $links): array {
        $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=file-type-icons')) . '">' . esc_html__('Settings', 'file-type-icons') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Adds the "View details" thickbox link to the plugin row meta.
     */
    public function add_view_details_link(array $links, string $file): array {
        if (plugin_basename(FTI_PLUGIN_FILE) !== $file) {
            return $links;
        }

        $links[] = sprintf(
            '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
            esc_url(self_admin_url(
                'plugin-install.php?tab=plugin-information&plugin=file-type-icons'
                . '&TB_iframe=true&width=772&height=926'
            )),
            esc_attr__('More information about File Type Icons', 'file-type-icons'),
            esc_attr__('File Type Icons', 'file-type-icons'),
            esc_html__('View details', 'file-type-icons')
        );

        if (class_exists('Guilamu_Bug_Reporter')) {
            $links[] = sprintf(
                '<a href="#" class="guilamu-bug-report-btn" data-plugin-slug="%s" data-plugin-name="%s">%s</a>',
                'file-type-icons',
                esc_attr__('File Type Icons', 'file-type-icons'),
                esc_html__('🐛 Report a Bug', 'file-type-icons')
            );
        } else {
            $links[] = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                'https://github.com/guilamu/guilamu-bug-reporter/releases',
                esc_html__('🐛 Report a Bug (install Bug Reporter)', 'file-type-icons')
            );
        }

        return $links;
    }

    /**
     * Registers the admin options page under Settings > File Type Icons.
     */
    public function add_options_page(): void {
        add_options_page(
            __('File Type Icons Settings', 'file-type-icons'),
            __('File Type Icons', 'file-type-icons'),
            'manage_options',
            'file-type-icons',
            [$this, 'render_options_page']
        );
    }

    /**
     * Enqueues styles and scripts only for the plugin options page.
     */
    public function enqueue_admin_assets(string $hook): void {
        if ($hook !== 'settings_page_file-type-icons') {
            return;
        }
        wp_enqueue_style('tabler-icons', 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css', [], '2.44.0');
        wp_enqueue_style('fti-admin-style', FTI_PLUGIN_URL . 'assets/css/admin.css', [], time());
        wp_enqueue_script('fti-admin-script', FTI_PLUGIN_URL . 'assets/js/admin.js', [], time(), true);
        wp_localize_script('fti-admin-script', 'ftiAdmin', [
            'templates' => [
                '1' => IconHandler::SVG_TEMPLATES,
                '2' => IconHandler::SVG_TEMPLATES_STYLE_2,
            ],
            'savedMsg' => esc_html__('Changes saved successfully', 'file-type-icons')
        ]);
    }

    /**
     * Declares options via the Settings API.
     */
    public function register_settings(): void {
        register_setting('fti_settings_group', 'fti_icon_size', [
            'type'              => 'integer',
            'sanitize_callback' => [$this, 'sanitize_icon_size'],
            'default'           => 20
        ]);
        register_setting('fti_settings_group', 'fti_icon_position', [
            'type'              => 'string',
            'sanitize_callback' => [$this, 'sanitize_icon_position'],
            'default'           => 'left'
        ]);
        register_setting('fti_settings_group', 'fti_icon_style', [
            'type'              => 'integer',
            'sanitize_callback' => [$this, 'sanitize_icon_style'],
            'default'           => 1
        ]);

        register_setting('fti_settings_group', 'fti_active_types', [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize_active_types'],
            'default'           => ['pdf', 'word', 'excel', 'powerpoint', 'text', 'archives', 'audio', 'images', 'video']
        ]);

        register_setting('fti_settings_group', 'fti_icon_colors', [
            'type'              => 'object',
            'sanitize_callback' => [$this, 'sanitize_icon_colors'],
            'default'           => self::DEFAULT_COLORS
        ]);

        register_setting('fti_settings_group', 'fti_exclude_classes', [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize_exclude_classes'],
            'default'           => []
        ]);
    }

    /**
     * Renders options page HTML (Premium Light UI).
     */
    public function render_options_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $size = (int) get_option('fti_icon_size', 20);
        $position = get_option('fti_icon_position', 'left');
        $style = (int) get_option('fti_icon_style', 1);
        $active = get_option('fti_active_types', ['pdf', 'word', 'excel', 'powerpoint', 'text', 'archives', 'audio', 'images', 'video']);
        $colors = get_option('fti_icon_colors', self::DEFAULT_COLORS);
        $exclusions = get_option('fti_exclude_classes', []);
        $exclusions_text = implode("\n", $exclusions);
        
        $abbreviations = [
            'pdf'        => 'PDF',
            'word'       => 'DOC',
            'excel'      => 'XLS',
            'powerpoint' => 'PPT',
            'text'       => 'TXT',
            'archives'   => 'ZIP',
            'audio'      => 'MP3',
            'images'     => 'IMG',
            'video'      => 'VID',
        ];
        ?>
        <div class="wrap">
            <h2 class="sr-only" style="padding-bottom: 9px;"><?php esc_html_e('File Type Icons', 'file-type-icons'); ?></h2>
            <form action="options.php" method="post" id="fti-settings-form">
                <?php settings_fields('fti_settings_group'); ?>
                
                <div class="wp-sti">
                    <!-- General Settings -->
                    <div class="s-card">
                        <div class="s-ch">
                            <i class="ti ti-settings" style="font-size:15px;color:var(--color-text-secondary)" aria-hidden="true"></i>
                            <span class="s-ct"><?php esc_html_e('General Settings', 'file-type-icons'); ?></span>
                        </div>
                        <div class="s-cb fti-split-layout">
                            <div class="fti-split-left">
                                <div class="frow">
                                    <div>
                                        <div class="fl"><?php esc_html_e('Icon Size', 'file-type-icons'); ?></div>
                                        <div class="fh"><?php esc_html_e('Displayed dimension, in pixels', 'file-type-icons'); ?></div>
                                    </div>
                                    <div class="szr">
                                        <input type="range" id="szsl" min="8" max="256" value="<?php echo esc_attr($size); ?>" step="1">
                                        <input type="number" name="fti_icon_size" id="szni" min="8" max="256" value="<?php echo esc_attr($size); ?>">
                                        <span class="szunit">px</span>
                                    </div>
                                </div>
                                <div class="frow">
                                    <div>
                                        <div class="fl"><?php esc_html_e('Icon Position', 'file-type-icons'); ?></div>
                                        <div class="fh"><?php esc_html_e('Relative to the link text', 'file-type-icons'); ?></div>
                                    </div>
                                    <div class="pgrid">
                                        <input type="hidden" name="fti_icon_position" id="fti_icon_position" value="<?php echo esc_attr($position); ?>">
                                        <button type="button" class="pbtn <?php echo ($position === 'left') ? 'on' : ''; ?>" data-value="left"><i class="ti ti-chevron-left" aria-hidden="true"></i><em><?php esc_html_e('Left', 'file-type-icons'); ?></em></button>
                                        <button type="button" class="pbtn <?php echo ($position === 'right') ? 'on' : ''; ?>" data-value="right"><i class="ti ti-chevron-right" aria-hidden="true"></i><em><?php esc_html_e('Right', 'file-type-icons'); ?></em></button>
                                        <button type="button" class="pbtn <?php echo ($position === 'above') ? 'on' : ''; ?>" data-value="above"><i class="ti ti-chevron-up" aria-hidden="true"></i><em><?php esc_html_e('Above', 'file-type-icons'); ?></em></button>
                                        <button type="button" class="pbtn <?php echo ($position === 'below') ? 'on' : ''; ?>" data-value="below"><i class="ti ti-chevron-down" aria-hidden="true"></i><em><?php esc_html_e('Below', 'file-type-icons'); ?></em></button>
                                    </div>
                                </div>
                                <div class="frow">
                                    <div>
                                        <div class="fl"><?php esc_html_e('Icon Style', 'file-type-icons'); ?></div>
                                        <div class="fh"><?php esc_html_e('Visual file rendering', 'file-type-icons'); ?></div>
                                    </div>
                                    <div class="seg">
                                        <input type="hidden" name="fti_icon_style" id="fti_icon_style" value="<?php echo esc_attr($style); ?>">
                                        <button type="button" class="sbtn <?php echo ($style === 1) ? 'on' : ''; ?>" data-value="1"><?php esc_html_e('Filled', 'file-type-icons'); ?></button>
                                        <button type="button" class="sbtn <?php echo ($style === 2) ? 'on' : ''; ?>" data-value="2"><?php esc_html_e('Outline', 'file-type-icons'); ?></button>
                                    </div>
                                </div>
                            </div>
                            <div class="fti-split-right">
                                <div class="fti-preview-title"><?php esc_html_e('Live Link Preview', 'file-type-icons'); ?></div>
                                <div class="fti-preview-card">
                                    <?php
                                    $flex_direction = in_array($position, ['above', 'below'], true) ? 'column' : 'row';
                                    $margin_style = '';
                                    if ($position === 'left') {
                                        $margin_style = 'margin-right: 6px;';
                                    } elseif ($position === 'right') {
                                        $margin_style = 'margin-left: 6px;';
                                    } elseif ($position === 'above') {
                                        $margin_style = 'margin-bottom: 4px;';
                                    } elseif ($position === 'below') {
                                        $margin_style = 'margin-top: 4px;';
                                    }
                                    $icon_order = in_array($position, ['left', 'above'], true) ? 'order: 1;' : 'order: 2;';
                                    $text_order = in_array($position, ['left', 'above'], true) ? 'order: 2;' : 'order: 1;';
                                    ?>
                                    <a href="#" class="fti-preview-link" style="display: inline-flex; align-items: center; justify-content: center; flex-direction: <?php echo esc_attr($flex_direction); ?>;" onclick="return false;">
                                        <span class="fti-preview-icon-wrap" style="width: <?php echo esc_attr($size); ?>px; height: <?php echo esc_attr($size); ?>px; <?php echo esc_attr($margin_style); ?> <?php echo esc_attr($icon_order); ?>">
                                            <?php
                                            $templates = ($style === 2) ? IconHandler::SVG_TEMPLATES_STYLE_2 : IconHandler::SVG_TEMPLATES;
                                            $pdf_color = $colors['pdf'] ?? '#E53935';
                                            if (isset($templates['pdf'])) {
                                                echo str_replace('%%COLOR%%', $pdf_color, $templates['pdf']);
                                            }
                                            ?>
                                        </span>
                                        <span class="fti-preview-text" style="<?php echo esc_attr($text_order); ?>"><?php esc_html_e('doc.pdf', 'file-type-icons'); ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Types & Colors -->
                    <div class="s-card">
                        <div class="s-ch">
                            <i class="ti ti-folder-open" style="font-size:15px;color:var(--color-text-secondary)" aria-hidden="true"></i>
                            <span class="s-ct"><?php esc_html_e('File Types & Colors', 'file-type-icons'); ?></span>
                            <span class="s-cbadge"><?php echo count(self::AVAILABLE_TYPES); ?> <?php esc_html_e('types', 'file-type-icons'); ?></span>
                        </div>
                        <div class="s-cb">
                            <div class="ftbar">
                                <button type="button" id="fti-check-all"><i class="ti ti-check" aria-hidden="true"></i> <?php esc_html_e('Check All', 'file-type-icons'); ?></button>
                                <button type="button" id="fti-uncheck-all"><i class="ti ti-x" aria-hidden="true"></i> <?php esc_html_e('Uncheck All', 'file-type-icons'); ?></button>
                                <button type="button" id="fti-reset-colors" style="margin-left:auto"><i class="ti ti-refresh" aria-hidden="true"></i> <?php esc_html_e('Reset Colors', 'file-type-icons'); ?></button>
                            </div>
                            <div class="ftcols">
                                <span><?php esc_html_e('Active', 'file-type-icons'); ?></span>
                                <span></span>
                                <span><?php esc_html_e('File Type', 'file-type-icons'); ?></span>
                                <span style="text-align:right"><?php esc_html_e('Color', 'file-type-icons'); ?></span>
                            </div>
                            <div id="ftg">
                                <?php
                                foreach (self::AVAILABLE_TYPES as $type => $label) {
                                    $is_active = in_array($type, $active, true);
                                    $row_class = $is_active ? 'ftrow' : 'ftrow off';
                                    $color = $colors[$type] ?? self::DEFAULT_COLORS[$type] ?? '#333333';
                                    $default_color = self::DEFAULT_COLORS[$type] ?? '#333333';
                                    $abbrev = $abbreviations[$type] ?? strtoupper($type);
                                    
                                    // Translate type label
                                    $translated_label = __($label, 'file-type-icons');
                                    $matches = [];
                                    $name_part = $translated_label;
                                    $ext_part = '';
                                    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $translated_label, $matches)) {
                                        $name_part = trim($matches[1]);
                                        $ext_part = trim($matches[2]);
                                    }
                                    ?>
                                    <div class="<?php echo esc_attr($row_class); ?>" data-type="<?php echo esc_attr($type); ?>">
                                        <label class="tgl" aria-label="<?php echo esc_attr(sprintf(__('Enable %s', 'file-type-icons'), $name_part)); ?>">
                                            <input type="checkbox" name="fti_active_types[]" value="<?php echo esc_attr($type); ?>" <?php checked($is_active); ?> class="fti-type-checkbox">
                                            <span class="ts"></span>
                                        </label>
                                        <?php
                                        $templates = ($style === 2) ? IconHandler::SVG_TEMPLATES_STYLE_2 : IconHandler::SVG_TEMPLATES;
                                        $svg_html = '';
                                        if (isset($templates[$type])) {
                                            $svg_html = str_replace('%%COLOR%%', $color, $templates[$type]);
                                        }
                                        ?>
                                        <div class="fti-admin-preview-icon" data-type="<?php echo esc_attr($type); ?>">
                                            <?php echo $svg_html; ?>
                                        </div>
                                        <div>
                                            <div class="ftname"><?php echo esc_html($name_part); ?></div>
                                            <div class="ftext"><?php echo esc_html($ext_part); ?></div>
                                        </div>
                                        <div class="cctrl">
                                            <span class="chex" data-type="<?php echo esc_attr($type); ?>"><?php echo esc_html(strtoupper($color)); ?></span>
                                            <div class="cdot" style="background:<?php echo esc_attr($color); ?>;" data-type="<?php echo esc_attr($type); ?>">
                                                <input type="color" name="fti_icon_colors[<?php echo esc_attr($type); ?>]" value="<?php echo esc_attr($color); ?>" data-default="<?php echo esc_attr($default_color); ?>" class="fti-color-picker" aria-label="<?php echo esc_attr(sprintf(__('Color of %s', 'file-type-icons'), $name_part)); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Exclusions -->
                    <div class="s-card">
                        <div class="s-ch">
                            <i class="ti ti-ban" style="font-size:15px;color:var(--color-text-secondary)" aria-hidden="true"></i>
                            <span class="s-ct"><?php esc_html_e('Class Exclusions', 'file-type-icons'); ?></span>
                        </div>
                        <div class="s-cb">
                            <div class="frow">
                                <div>
                                    <div class="fl"><?php esc_html_e('CSS classes to exclude', 'file-type-icons'); ?></div>
                                    <div class="fh"><?php esc_html_e('One class per line, without the leading dot. Any link containing one of them will be ignored.', 'file-type-icons'); ?></div>
                                </div>
                                <textarea name="fti_exclude_classes" class="xcta" placeholder="no-icon&#10;menu-item&#10;wp-block-button__link"><?php echo esc_textarea($exclusions_text); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="savebar">
                        <button type="submit" id="svbtn"><i class="ti ti-device-floppy" aria-hidden="true"></i> <?php esc_html_e('Save Changes', 'file-type-icons'); ?></button>
                        <span class="smsg" id="svmsg"></span>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Sanitizes Icon Size.
     */
    public function sanitize_icon_size($input): int {
        $val = absint($input);
        return ($val >= 8 && $val <= 256) ? $val : 20;
    }

    /**
     * Sanitizes Icon Position.
     */
    public function sanitize_icon_position($input): string {
        $val = sanitize_text_field($input);
        return in_array($val, ['left', 'right', 'above', 'below'], true) ? $val : 'left';
    }

    /**
     * Sanitizes Icon Style.
     */
    public function sanitize_icon_style($input): int {
        $val = absint($input);
        return ($val === 1 || $val === 2) ? $val : 1;
    }

    /**
     * Sanitizes Active Types.
     */
    public function sanitize_active_types($input): array {
        if (!is_array($input)) {
            return [];
        }
        $sanitized = [];
        foreach ($input as $type) {
            $type = sanitize_key($type);
            if (array_key_exists($type, self::AVAILABLE_TYPES)) {
                $sanitized[] = $type;
            }
        }
        return $sanitized;
    }

    /**
     * Sanitizes colors array.
     */
    public function sanitize_icon_colors($input): array {
        if (!is_array($input)) {
            return self::DEFAULT_COLORS;
        }
        $sanitized = [];
        foreach (self::AVAILABLE_TYPES as $type => $label) {
            $color = $input[$type] ?? self::DEFAULT_COLORS[$type];
            if (function_exists('sanitize_hex_color')) {
                $color = sanitize_hex_color($color);
            }
            $sanitized[$type] = $color ?: self::DEFAULT_COLORS[$type];
        }
        return $sanitized;
    }

    /**
     * Sanitizes exclusion classes.
     */
    public function sanitize_exclude_classes($input): array {
        if (!is_string($input)) {
            return [];
        }
        $lines = explode("\n", $input);
        $sanitized = [];
        foreach ($lines as $line) {
            $class = sanitize_html_class(trim($line));
            if (!empty($class)) {
                $sanitized[] = $class;
            }
        }
        return $sanitized;
    }
}
