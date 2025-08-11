<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Bkoimadhk_Map_Admin
{
    public function __construct($widget_setting, $checkout_setting) {
        add_action('admin_init', [$this, 'bkoimadhk_register_settings']);
        add_action('admin_init', [$widget_setting, 'bkoimadhk_register_settings']);
        add_action('admin_init', [$checkout_setting, 'bkoimadhk_register_settings']);
    }

    public function bkoimadhk_add_plugin_admin_menu() {
        add_menu_page(
            'BariKoi',
            'BariKoi',
            'manage_options',
            'barikoi-map',
            [$this, 'bkoimadhk_display_plugin_admin_page'],
            'dashicons-location'
        );

        add_submenu_page(
            'barikoi-map',
            'Settings',
            'Settings',
            'manage_options',
            'barikoi-map',
            [$this, 'bkoimadhk_display_plugin_admin_page']
        );

        add_submenu_page(
            'barikoi-map',
            'Add Places',
            'Add Places',
            'manage_options',
            'bkoimadhk-map-add-places',
            'bkoimadhk_shortcode_page'
        );

        add_submenu_page(
            'barikoi-map',
            'My Places',
            'My Places',
            'manage_options',
            'bkoimadhk-map-places',
            'bkoimadhk_shortcods_list'
        );

        add_submenu_page(
            null, // Hidden submenu page
            'Update Location',
            'Update Location',
            'manage_options',
            'bkoimadhk-map-update-location',
            'bkoimadhk_update_location_page'
        );
    }

    public function bkoimadhk_display_plugin_admin_page() {
        echo '<div class="wrap">';

        // General Settings form
        echo '<form method="post" action="options.php">';
        settings_fields('bkoimadhk_map_settings');
        do_settings_sections('barikoi-map');
        submit_button('Save API Key');
        echo '</form>';

        // Widget Settings form
        echo '<form method="post" action="options.php">';
        settings_fields('bkoimadhk_map_widget_settings');
        do_settings_sections('bkoimadhk-map-widget');
        submit_button('Save Map Widget Settings');
        echo '</form>';

        // Checkout Settings form
        echo '<form method="post" action="options.php">';
        settings_fields('bkoimadhk_map_checkout_settings');
        do_settings_sections('bkoimadhk-map-checkout');
        submit_button('Save Map Checkout Settings');
        echo '</form>';

        echo '</div>';
    }

    public function bkoimadhk_sanitize_api_key($input) {
        return sanitize_text_field($input);
    }

    public function bkoimadhk_register_settings() {
        register_setting('bkoimadhk_map_settings', 'bkoimadhk_api_key', [$this, 'bkoimadhk_sanitize_api_key']);

        add_settings_section(
            'bkoimadhk_map_settings_section',
            'Barikoi Map Settings',
            null,
            'barikoi-map'
        );

        add_settings_field(
            'bkoimadhk_api_key',
            'Barikoi API Key',
            [$this, 'bkoimadhk_api_key_callback'],
            'barikoi-map',
            'bkoimadhk_map_settings_section'
        );
    }

    public function bkoimadhk_api_key_callback() {
        $api_key = get_option('bkoimadhk_api_key');
        echo '<input type="text" name="bkoimadhk_api_key" value="' . esc_attr($api_key) . '" size="40" /><br>';
        echo '<small>For API KEY, please click <a href="https://developer.barikoi.com/register" target="_blank" rel="noopener noreferrer">here</a></small>';
    }
}

// Include admin partials with prefixed function names
require_once plugin_dir_path(__FILE__) . 'admin-partials/add-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'admin-partials/shortcodes-list.php';
require_once plugin_dir_path(__FILE__) . 'admin-partials/update-location.php';

// AJAX action with prefixed callback
add_action('wp_ajax_bkoimadhk_delete_barikoi_shortcode', 'bkoimadhk_delete_barikoi_shortcode');
