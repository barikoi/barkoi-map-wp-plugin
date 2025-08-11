<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bkoimadhk_Map_Widget_Settings {

    public function __construct(){
        add_action('admin_enqueue_scripts', [ $this, 'bkoimadhk_enqueue_media_uploader' ]);
    }
    
    // Sanitize function for each setting
    public function bkoimadhk_sanitize_text($input) {
        return sanitize_text_field($input); // Sanitizes text input (e.g., styles, marker icons)
    }

    public function bkoimadhk_sanitize_checkbox($input) {
        return (isset($input) && $input == '1') ? '1' : '0'; // Sanitizes checkbox input
    }

    public function bkoimadhk_register_settings() {
        // Register the default map style setting
        register_setting('bkoimadhk_map_widget_settings', 'bkoimadhk_default_map_style', [ $this, 'bkoimadhk_sanitize_text' ]); // Sanitizing map style
        register_setting('bkoimadhk_map_widget_settings', 'bkoimadhk_default_zoom_level', [ $this, 'bkoimadhk_sanitize_text' ]); // Sanitizing zoom level (as text)
        register_setting('bkoimadhk_map_widget_settings', 'bkoimadhk_custom_marker_icon', [ $this, 'bkoimadhk_sanitize_text' ]); // Sanitizing marker icon URL or path

        // Add settings section
        add_settings_section(
            'bkoimadhk_map_widget_settings',
            __('Map Shortcode Settings', 'barikoi-map'),
            '__return_false',
            'bkoimadhk-map-widget'
        );
    
        add_settings_field(
            'bkoimadhk_default_map_style',
            __('Default Map Style', 'barikoi-map'),
            [ $this, 'bkoimadhk_default_map_style_callback' ],
            'bkoimadhk-map-widget',
            'bkoimadhk_map_widget_settings'
        );

        add_settings_field(
            'bkoimadhk_default_zoom_level',
            __('Default Zoom Level', 'barikoi-map'),
            [ $this, 'bkoimadhk_default_zoom_level_callback' ],
            'bkoimadhk-map-widget',
            'bkoimadhk_map_widget_settings'
        );

        add_settings_field(
            'bkoimadhk_custom_marker_icon',
            __('Custom Marker Icon', 'barikoi-map'),
            [ $this, 'bkoimadhk_custom_marker_icon_callback' ],
            'bkoimadhk-map-widget',
            'bkoimadhk_map_widget_settings'
        );
    }

    // Map styles dropdown callback for settings
    public function bkoimadhk_default_map_style_callback() {
        // Define available map styles
        $map_styles = [
            'Light' => __('Light', 'barikoi-map'),
            'Dark' => __('Dark', 'barikoi-map'),
            'Bangla' => __('Bangla', 'barikoi-map'),
        ];

        // Retrieve the current style from the database (default to 'Light' if not set)
        $current_style = get_option('bkoimadhk_default_map_style', 'Light');

        // Create a dropdown
        echo '<select name="bkoimadhk_default_map_style">';
        foreach ($map_styles as $style_key => $style_name) {
            echo '<option value="' . esc_attr($style_key) . '" ' . selected($current_style, $style_key, false) . '>' . esc_html($style_name) . '</option>';
        }
        echo '</select>';
        echo '<br><small>' . esc_html__('Select your preferred map style.', 'barikoi-map') . '</small>';
    }

    // Zoom level callback for settings
    public function bkoimadhk_default_zoom_level_callback() {
        // Retrieve the stored default zoom level from the database, defaulting to 10 if not set
        $default_zoom_level = get_option('bkoimadhk_default_zoom_level', 10);
        // Render the input field
        echo '<input type="number" name="bkoimadhk_default_zoom_level" value="' . esc_attr($default_zoom_level) . '" min="0" max="22" />';
        echo '<br><small>' . esc_html__('Set the default zoom level for the map (0 - 22). The default is 10.', 'barikoi-map') . '</small>';
    }

    public function bkoimadhk_custom_marker_icon_callback() {
        // Retrieve the stored custom marker icon URL from the options table
        $custom_marker_icon = get_option('bkoimadhk_custom_marker_icon');
        echo '<input type="text" id="bkoimadhk_custom_marker_icon" name="bkoimadhk_custom_marker_icon" value="' . esc_url($custom_marker_icon) . '" size="40" />';
        echo '<input type="button" id="upload_button" class="button" value="' . esc_html__('Upload Marker Icon', 'barikoi-map') . '" />';

        // Display the uploaded image preview if available
        if ($custom_marker_icon) {
            $attachment_id = attachment_url_to_postid($custom_marker_icon);
            if ($attachment_id) {
                echo wp_kses_post('<br>' . wp_get_attachment_image($attachment_id, 'full', false, [
                    'alt' => esc_attr__('Marker Icon', 'barikoi-map'),
                    'style' => 'max-width: 100px; height: auto;',
                ]));
            }
        }

        echo '<br><small>' . esc_html__('Select or upload a custom marker icon for the map.', 'barikoi-map') . '</small>';
    }

    public function bkoimadhk_enqueue_media_uploader($hook) {
        $allowed_hooks = [
            'toplevel_page_barikoi-map',
            'barikoi_page_bkoimadhk-map-add-places',
            'barikoi_page_bkoimadhk-map-places',
            'barikoi_page_bkoimadhk-map-update-location',
        ];
        
        //echo $hook;exit;
        // Load styles/scripts only on allowed pages
        if (!in_array($hook, $allowed_hooks)) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_script(
            'bkoimadhk_media_uploader',
            plugin_dir_url(__FILE__) . '../assets/js/barikoi_media_uploader.js',
            ['jquery'],
            filemtime(plugin_dir_path(__FILE__) . '../assets/js/barikoi_media_uploader.js'),
            true
        );
    }

}
