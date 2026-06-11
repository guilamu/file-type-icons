<?php
/**
 * File Type Icons — Uninstall
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

// Delete options stored in the database
delete_option('fti_icon_size');
delete_option('fti_icon_position');
delete_option('fti_icon_style');
delete_option('fti_hover_effect');
delete_option('fti_active_types');
delete_option('fti_icon_colors');
delete_option('fti_exclude_classes');

// Delete all post meta data '_fti_disabled' from the database
delete_post_meta_by_key('_fti_disabled');
