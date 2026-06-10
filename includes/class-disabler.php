<?php
declare(strict_types=1);

namespace FileTypeIcons;

defined('ABSPATH') || exit;

class Disabler {
    /**
     * Constructor - Registers the shortcode and admin hooks.
     */
    public function __construct() {
        // Register the shortcode
        add_shortcode('no_fti', [$this, 'render_shortcode']);

        // Register the post meta
        add_action('init', [$this, 'register_meta']);

        // Add the classic meta box for editors
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post', [$this, 'save_meta_box']);
    }

    /**
     * Renders the [no_fti]...[/no_fti] shortcode.
     */
    public function render_shortcode(array $atts, string $content = ''): string {
        return '<span class="no-fti">' . do_shortcode($content) . '</span>';
    }

    /**
     * Registers the post meta for REST API (required for Gutenberg).
     */
    public function register_meta(): void {
        $post_types = ['post', 'page'];
        foreach ($post_types as $post_type) {
            register_post_meta($post_type, '_fti_disabled', [
                'show_in_rest'  => true,
                'single'        => true,
                'type'          => 'boolean',
                'auth_callback' => function(): bool {
                    return current_user_can('edit_posts');
                }
            ]);
        }
    }

    /**
     * Adds the classic meta box in the sidebar.
     */
    public function add_meta_box(): void {
        $screens = ['post', 'page'];
        foreach ($screens as $screen) {
            add_meta_box(
                'fti_disabled_meta_box',
                __('File Type Icons', 'file-type-icons'),
                [$this, 'render_meta_box_html'],
                $screen,
                'side',
                'default'
            );
        }
    }

    /**
     * Displays the meta box HTML.
     */
    public function render_meta_box_html(\WP_Post $post): void {
        wp_nonce_field('fti_save_meta_box', 'fti_meta_box_nonce');
        
        $value = get_post_meta($post->ID, '_fti_disabled', true);
        $checked = checked((string) $value, '1', false);
        
        echo '<p>';
        echo '<label for="fti_disabled_field">';
        echo '<input type="checkbox" id="fti_disabled_field" name="fti_disabled_field" value="1" ' . $checked . ' />';
        echo ' ' . esc_html__('Disable icons on this page', 'file-type-icons');
        echo '</label>';
        echo '</p>';
    }

    /**
     * Saves the meta box modifications.
     */
    public function save_meta_box(int $post_id): void {
        if (!isset($_POST['fti_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['fti_meta_box_nonce'], 'fti_save_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $is_disabled = isset($_POST['fti_disabled_field']) && $_POST['fti_disabled_field'] === '1' ? '1' : '0';
        update_post_meta($post_id, '_fti_disabled', $is_disabled);
    }
}
