<?php
declare(strict_types=1);

namespace FileTypeIcons;

defined('ABSPATH') || exit;

class IconHandler
{
    /**
     * List of supported extensions and their associated icon type.
     */
    private const EXTENSION_MAP = [
        'pdf' => 'pdf',
        'doc' => 'word',
        'docx' => 'word',
        'odt' => 'word',
        'rtf' => 'word',
        'xls' => 'excel',
        'xlsx' => 'excel',
        'csv' => 'excel',
        'ods' => 'excel',
        'ppt' => 'powerpoint',
        'pptx' => 'powerpoint',
        'odp' => 'powerpoint',
        'txt' => 'text',
        'zip' => 'archives',
        'rar' => 'archives',
        'tar' => 'archives',
        'gz' => 'archives',
        '7z' => 'archives',
        'mp3' => 'audio',
        'wav' => 'audio',
        'm4a' => 'audio',
        'flac' => 'audio',
        'jpg' => 'images',
        'jpeg' => 'images',
        'png' => 'images',
        'gif' => 'images',
        'svg' => 'images',
        'webp' => 'images',
        'odg' => 'images',
        'mp4' => 'video',
        'avi' => 'video',
        'mov' => 'video',
        'mkv' => 'video',
        'webm' => 'video',
    ];

    /**
     * Default brand colors for each file type.
     */
    private const DEFAULT_COLORS = [
        'pdf' => '#E53935', // Red
        'word' => '#1565C0', // Blue
        'excel' => '#2E7D32', // Green
        'powerpoint' => '#D84315', // Orange
        'text' => '#616161', // Gray
        'archives' => '#8E24AA', // Purple
        'audio' => '#D81B60', // Pink
        'images' => '#00897B', // Teal
        'video' => '#F57C00', // Warm orange
    ];

    /**
     * SVG templates of the icons with a %%COLOR%% placeholder for dynamic color.
     */
    public const SVG_TEMPLATES = [
        'pdf' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" fill="%%COLOR%%" fill-opacity="0.25"/><path d="M14 2V8H20L14 2Z" fill="%%COLOR%%"/><rect x="4" y="12" width="16" height="7" rx="1" fill="%%COLOR%%"/><text x="12" y="17.2" font-size="4.5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="white">PDF</text></svg>',
        'word' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" fill="%%COLOR%%" fill-opacity="0.25"/><path d="M14 2V8H20L14 2Z" fill="%%COLOR%%"/><rect x="4" y="12" width="16" height="7" rx="1" fill="%%COLOR%%"/><text x="12" y="17.2" font-size="4.5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="white">DOC</text></svg>',
        'excel' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" fill="%%COLOR%%" fill-opacity="0.25"/><path d="M14 2V8H20L14 2Z" fill="%%COLOR%%"/><rect x="4" y="12" width="16" height="7" rx="1" fill="%%COLOR%%"/><text x="12" y="17.2" font-size="4.5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="white">XLS</text></svg>',
        'powerpoint' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" fill="%%COLOR%%" fill-opacity="0.25"/><path d="M14 2V8H20L14 2Z" fill="%%COLOR%%"/><rect x="4" y="12" width="16" height="7" rx="1" fill="%%COLOR%%"/><text x="12" y="17.2" font-size="4.5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="white">PPT</text></svg>',
        'text' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" fill="%%COLOR%%" fill-opacity="0.25"/><path d="M14 2V8H20L14 2Z" fill="%%COLOR%%"/><rect x="4" y="12" width="16" height="7" rx="1" fill="%%COLOR%%"/><text x="12" y="17.2" font-size="4.5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="white">TXT</text></svg>',
        'archives' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" fill="%%COLOR%%" fill-opacity="0.25"/><path d="M14 2V8H20L14 2Z" fill="%%COLOR%%"/><rect x="4" y="12" width="16" height="7" rx="1" fill="%%COLOR%%"/><text x="12" y="17.2" font-size="4.5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="white">ZIP</text></svg>',
        'audio' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" fill="%%COLOR%%" fill-opacity="0.25"/><path d="M14 2V8H20L14 2Z" fill="%%COLOR%%"/><rect x="4" y="12" width="16" height="7" rx="1" fill="%%COLOR%%"/><text x="12" y="17.2" font-size="4.5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="white">MP3</text></svg>',
        'images' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" fill="%%COLOR%%" fill-opacity="0.25"/><path d="M14 2V8H20L14 2Z" fill="%%COLOR%%"/><rect x="4" y="12" width="16" height="7" rx="1" fill="%%COLOR%%"/><text x="12" y="17.2" font-size="4.5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="white">IMG</text></svg>',
        'video' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" fill="%%COLOR%%" fill-opacity="0.25"/><path d="M14 2V8H20L14 2Z" fill="%%COLOR%%"/><rect x="4" y="12" width="16" height="7" rx="1" fill="%%COLOR%%"/><text x="12" y="17.2" font-size="4.5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="white">VID</text></svg>',
    ];

    /**
     * SVG templates of the Style 2 (Outline) icons.
     */
    public const SVG_TEMPLATES_STYLE_2 = [
        'pdf' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="%%COLOR%%" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><text x="12" y="17" font-size="5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="%%COLOR%%" stroke="none">PDF</text></svg>',
        'word' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="%%COLOR%%" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><text x="12" y="17" font-size="5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="%%COLOR%%" stroke="none">DOC</text></svg>',
        'excel' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="%%COLOR%%" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><text x="12" y="17" font-size="5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="%%COLOR%%" stroke="none">XLS</text></svg>',
        'powerpoint' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="%%COLOR%%" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><text x="12" y="17" font-size="5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="%%COLOR%%" stroke="none">PPT</text></svg>',
        'text' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="%%COLOR%%" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><text x="12" y="17" font-size="5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="%%COLOR%%" stroke="none">TXT</text></svg>',
        'archives' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="%%COLOR%%" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><text x="12" y="17" font-size="5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="%%COLOR%%" stroke="none">ZIP</text></svg>',
        'audio' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="%%COLOR%%" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><text x="12" y="17" font-size="5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="%%COLOR%%" stroke="none">MP3</text></svg>',
        'images' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="%%COLOR%%" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><text x="12" y="17" font-size="5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="%%COLOR%%" stroke="none">IMG</text></svg>',
        'video' => '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="%%COLOR%%" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><text x="12" y="17" font-size="5" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="%%COLOR%%" stroke="none">VID</text></svg>',
    ];

    /**
     * Base SVG template for Style 1 (Filled) with placeholders %%COLOR%%, %%LABEL%%, %%FONTSIZE%%, %%TEXTY%%.
     */
    public const SVG_BASE_STYLE_1 = '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" fill="%%COLOR%%" fill-opacity="0.25"/><path d="M14 2V8H20L14 2Z" fill="%%COLOR%%"/><rect x="4" y="12" width="16" height="7" rx="1" fill="%%COLOR%%"/><text x="12" y="%%TEXTY%%" font-size="%%FONTSIZE%%" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="white">%%LABEL%%</text></svg>';

    /**
     * Base SVG template for Style 2 (Outline) with placeholders %%COLOR%%, %%LABEL%%, %%FONTSIZE%%, %%TEXTY%%.
     */
    public const SVG_BASE_STYLE_2 = '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="%%COLOR%%" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><text x="12" y="%%TEXTY%%" font-size="%%FONTSIZE%%" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" text-anchor="middle" fill="%%COLOR%%" stroke="none">%%LABEL%%</text></svg>';

    /**
     * Generates an SVG for a given extension, style, and color.
     */
    public static function generate_svg(string $ext, int $style, string $color): string
    {
        $label = strtoupper($ext);
        $len = strlen($label);

        // Adjust font sizes and vertical positioning based on label length.
        // The background rect goes from y=12 to y=19, center is y=15.5.
        // y is the text baseline: adjusted to visually center capital letters.
        if ($style === 2) {
            $font_size = ($len > 3) ? '4' : '5';
            $text_y = ($len > 3) ? '16.7' : '17';
            $base = self::SVG_BASE_STYLE_2;
        } else {
            $font_size = ($len > 3) ? '3.5' : '4.5';
            $text_y = ($len > 3) ? '16.7' : '17.2';
            $base = self::SVG_BASE_STYLE_1;
        }

        return str_replace(
            ['%%COLOR%%', '%%LABEL%%', '%%FONTSIZE%%', '%%TEXTY%%'],
            [$color, $label, $font_size, $text_y],
            $base
        );
    }

    /**
     * Constructor - Registers filters and actions.
     */
    public function __construct()
    {
        // Register filters on site content
        add_filter('the_content', [$this, 'mimetype_to_icon'], 15);
        add_filter('widget_text_content', [$this, 'mimetype_to_icon'], 15);

        // Load front-end scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Filters HTML content to add icon classes to file links.
     */
    public function mimetype_to_icon(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        // Allow developers to disable icons dynamically
        if (apply_filters('fti_is_disabled', false, get_the_ID())) {
            return $content;
        }

        // Check if icons are disabled on this specific post/page
        $post_id = get_the_ID();
        if ($post_id && get_post_meta($post_id, '_fti_disabled', true)) {
            return $content;
        }

        // Retrieve extensions configured as active
        $extensions = $this->get_active_extensions();
        if (empty($extensions)) {
            return $content;
        }

        $pattern = $this->get_pattern($extensions);

        // If content contains no-fti exclusion class in container tags,
        // split the HTML to avoid filtering inside those tags
        if (strpos($content, 'no-fti') !== false) {
            $parts = preg_split('/(<(?:span|div)[^>]*?\bclass=["\'][^"\']*\bno-fti\b[^"\']*["\'][^>]*>.*?<\/(?:span|div)>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
            if (is_array($parts)) {
                $new_content = '';
                foreach ($parts as $part) {
                    // If it is the block containing no-fti class, add it as is without processing
                    if (preg_match('/^<(?:span|div)[^>]*?\bclass=["\'][^"\']*\bno-fti\b[^"\']*["\']/i', $part)) {
                        $new_content .= $part;
                    } else {
                        // Otherwise, apply icon replacement on this content fragment
                        $new_content .= preg_replace_callback($pattern, [$this, 'replace_link_callback'], $part) ?? $part;
                    }
                }
                return $new_content;
            }
        }

        return preg_replace_callback($pattern, [$this, 'replace_link_callback'], $content) ?? $content;
    }

    /**
     * Enqueues the base front-end stylesheet and injects dynamic inline CSS.
     */
    public function enqueue_assets(): void
    {
        wp_enqueue_style(
            'fti-frontend',
            FTI_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            FTI_VERSION
        );

        $this->add_inline_styles();
    }

    /**
     * Generates and injects dynamic inline CSS containing dimensions, position, and colors.
     */
    private function add_inline_styles(): void
    {
        $size = (int) get_option('fti_icon_size', 20);
        $position = get_option('fti_icon_position', 'left');
        $active = get_option('fti_active_types', ['pdf', 'word', 'excel', 'powerpoint', 'text']);
        $colors = get_option('fti_icon_colors', self::DEFAULT_COLORS);
        $style = (int) get_option('fti_icon_style', 1);

        $pseudo = in_array($position, ['left', 'above'], true) ? 'before' : 'after';

        $flex_direction = in_array($position, ['above', 'below'], true) ? 'column' : 'row';
        $margin_property = 'margin-right';
        if ($position === 'right') {
            $margin_property = 'margin-left';
        } elseif ($position === 'above') {
            $margin_property = 'margin-bottom';
        } elseif ($position === 'below') {
            $margin_property = 'margin-top';
        }
        $margin_value = in_array($position, ['above', 'below'], true) ? '4px' : '6px';

        $css = "a.fti-link {
    display: inline-flex;
    flex-direction: {$flex_direction};
    align-items: center;
    vertical-align: middle;
}\n";

        $css .= "a.fti-link::{$pseudo} {
    width: {$size}px;
    height: {$size}px;
    {$margin_property}: {$margin_value};
}\n";

        // Generate one CSS rule per extension (to show the correct label inside the SVG)
        foreach ($active as $type) {
            $color = $colors[$type] ?? self::DEFAULT_COLORS[$type] ?? '#333333';
            if (function_exists('sanitize_hex_color')) {
                $color = sanitize_hex_color($color);
            }

            foreach (self::EXTENSION_MAP as $ext => $mapped_type) {
                if ($mapped_type !== $type) {
                    continue;
                }

                $svg = self::generate_svg($ext, $style, $color);
                $svg_encoded = rawurlencode($svg);

                $css .= "a.fti-link.fti-{$ext}::{$pseudo} {
    background-image: url('data:image/svg+xml;utf8,{$svg_encoded}');
}\n";
            }
        }

        wp_add_inline_style('fti-frontend', $css);
    }

    /**
     * Callback for preg_replace_callback. Adds icon classes to links.
     */
    private function replace_link_callback(array $matches): string
    {
        $ext = strtolower($matches[3]);
        $type = self::EXTENSION_MAP[$ext] ?? 'text';

        $attributes_before = $matches[1];
        $attributes_after = $matches[4];

        // Combine attributes for analysis
        $attrs = trim($attributes_before . ' ' . $attributes_after);

        // Extract existing CSS classes
        $classes = [];
        if (preg_match('/class=["\']([^"\']*)["\']/', $attrs, $class_match)) {
            $classes = preg_split('/\s+/', trim($class_match[1]));
        }

        // If the link already has the 'fti-link' class, avoid reprocessing it
        if (in_array('fti-link', $classes, true)) {
            return $matches[0];
        }

        // Retrieve configured exclusion classes (+ no-fti and default button classes)
        $exclude_classes = get_option('fti_exclude_classes', []);
        $exclude_classes[] = 'no-fti';
        $exclude_classes[] = 'wp-block-file__button';
        $exclude_classes[] = 'wp-element-button';
        $exclude_classes[] = 'wp-block-button__link';
        $exclude_classes[] = 'btn';
        $exclude_classes[] = 'button';

        foreach ($exclude_classes as $exclude_class) {
            if (in_array(trim($exclude_class), $classes, true)) {
                return $matches[0];
            }
        }

        // Add new classes (fti-{ext} for extension-specific label)
        $new_classes = "fti-link fti-{$ext}";
        $attrs = $this->add_class_to_attributes($attrs, $new_classes);

        // Rebuild the <a> tag properly
        return '<a ' . $attrs . ' href="' . $matches[2] . '">' . $matches[5] . '</a>';
    }

    /**
     * Adds CSS classes to an HTML attributes string.
     */
    private function add_class_to_attributes(string $attrs, string $new_classes): string
    {
        if (preg_match('/class=["\']([^"\']*)["\']/', $attrs, $matches)) {
            $existing_classes = $matches[1];
            if (strpos($existing_classes, $new_classes) === false) {
                $updated_classes = trim($existing_classes . ' ' . $new_classes);
                $attrs = str_replace($matches[0], 'class="' . esc_attr($updated_classes) . '"', $attrs);
            }
        } else {
            $attrs = trim($attrs) . ' class="' . esc_attr($new_classes) . '"';
        }
        return $attrs;
    }

    /**
     * Returns extensions of active file types.
     */
    private function get_active_extensions(): array
    {
        $active_types = get_option('fti_active_types', ['pdf', 'word', 'excel', 'powerpoint', 'text']);
        $extensions = [];
        foreach ($active_types as $type) {
            foreach (self::EXTENSION_MAP as $ext => $mapped_type) {
                if ($mapped_type === $type) {
                    $extensions[] = $ext;
                }
            }
        }
        return $extensions;
    }

    /**
     * Generates link detection regex pattern.
     */
    private function get_pattern(array $extensions): string
    {
        $ext_list = implode('|', array_map('preg_quote', $extensions));
        // Detects <a> tags with href ending with active extension (+ optional query args)
        return '#<a\s([^>]*?)href=["\']([^"\']+\.(' . $ext_list . ')(?:\?[^"\']*)?)["\']([^>]*?)>(.*?)</a>#is';
    }
}
