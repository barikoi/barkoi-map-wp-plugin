<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bkoimadhk_Map_Checkout_Settings {

    public function __construct(){
        add_action( 'admin_enqueue_scripts', [ $this, 'bkoimadhk_enqueue_media_uploader' ] );
    }
    
    public function bkoimadhk_sanitize_checkbox( $input ) {
        return ( isset( $input ) && $input == '1' ) ? '1' : '0'; // Sanitizes checkbox input
    }

    public function bkoimadhk_sanitize_text( $input ) {
        return sanitize_text_field( $input ); // Sanitizes text input
    }

    public function bkoimadhk_sanitize_coordinates( $input ) {
        return sanitize_text_field( $input ); // Sanitizes coordinates input
    }

    public function bkoimadhk_register_settings() {
        // Register the default map style setting
        register_setting( 'bkoimadhk_map_checkout_settings', 'bkoimadhk_map_checkout_style', [ $this, 'bkoimadhk_sanitize_text' ] );
        register_setting( 'bkoimadhk_map_checkout_settings', 'bkoimadhk_map_checkout_default_zoom_level', [ $this, 'bkoimadhk_sanitize_text' ] );
        register_setting( 'bkoimadhk_map_checkout_settings', 'bkoimadhk_map_checkout_map_switcher', [ $this, 'bkoimadhk_sanitize_checkbox' ] );
        register_setting( 'bkoimadhk_map_checkout_settings', 'bkoimadhk_map_checkout_add_autocomplete', [ $this, 'bkoimadhk_sanitize_checkbox' ] );
        register_setting( 'bkoimadhk_map_checkout_settings', 'bkoimadhk_map_checkout_default_coordinates', [ $this, 'bkoimadhk_sanitize_coordinates' ] );
        register_setting( 'bkoimadhk_map_checkout_settings', 'bkoimadhk_map_checkout_custom_marker_icon', [ $this, 'bkoimadhk_sanitize_text' ] );

        // Add settings section
        add_settings_section(
            'bkoimadhk_map_checkout_settings',
            __( 'Checkout Page Map & Autocomplete Settings', 'barikoi-map' ),
            '__return_false',
            'bkoimadhk-map-checkout'
        );

        add_settings_field(
            'bkoimadhk_map_checkout_style',
            __( 'Default Map Style', 'barikoi-map' ),
            [ $this, 'bkoimadhk_map_checkout_style_callback' ],
            'bkoimadhk-map-checkout',
            'bkoimadhk_map_checkout_settings'
        );

        add_settings_field(
            'bkoimadhk_map_checkout_default_zoom_level',
            __( 'Default Zoom Level', 'barikoi-map' ),
            [ $this, 'bkoimadhk_map_checkout_default_zoom_level_callback' ],
            'bkoimadhk-map-checkout',
            'bkoimadhk_map_checkout_settings'
        );

        add_settings_field(
            'bkoimadhk_map_checkout_map_switcher',
            __( 'Map', 'barikoi-map' ),
            [ $this, 'bkoimadhk_map_checkout_map_switcher_callback' ],
            'bkoimadhk-map-checkout',
            'bkoimadhk_map_checkout_settings'
        );

        add_settings_field(
            'bkoimadhk_map_checkout_add_autocomplete',
            __( 'Autocomplete', 'barikoi-map' ),
            [ $this, 'bkoimadhk_map_checkout_add_autocomplete_callback' ],
            'bkoimadhk-map-checkout',
            'bkoimadhk_map_checkout_settings'
        );

        add_settings_field(
            'bkoimadhk_map_checkout_default_coordinates',
            __( 'Default Coordinates', 'barikoi-map' ),
            [ $this, 'bkoimadhk_map_checkout_default_coordinates_callback' ],
            'bkoimadhk-map-checkout',
            'bkoimadhk_map_checkout_settings'
        );

        add_settings_field(
            'bkoimadhk_map_checkout_custom_marker_icon',
            __( 'Custom Marker Icon', 'barikoi-map' ),
            [ $this, 'bkoimadhk_map_checkout_custom_marker_icon_callback' ],
            'bkoimadhk-map-checkout',
            'bkoimadhk_map_checkout_settings'
        );
    }

    public function bkoimadhk_map_checkout_custom_marker_icon_callback() {
        $custom_marker_icon = get_option( 'bkoimadhk_map_checkout_custom_marker_icon' );
        echo '<input type="text" id="bkoimadhk_map_checkout_custom_marker_icon" name="bkoimadhk_map_checkout_custom_marker_icon" value="' . esc_url( $custom_marker_icon ) . '" size="40" />';
        echo '<input type="button" id="bkoimadhk_upload_button" class="button" value="' . esc_html__( 'Upload Marker Icon', 'barikoi-map' ) . '" />';

        if ( $custom_marker_icon ) {
            $attachment_id = attachment_url_to_postid( $custom_marker_icon );
            if ( $attachment_id ) {
                echo wp_kses_post( '<br>' . wp_get_attachment_image( $attachment_id, 'full', false, [
                    'alt'   => esc_attr__( 'Marker Icon', 'barikoi-map' ),
                    'style' => 'max-width: 100px; height: auto;',
                ] ) );
            }
        }
        echo '<br><small>' . esc_html__( 'Select or upload a custom marker icon for the map.', 'barikoi-map' ) . '</small>';
    }

    public function bkoimadhk_map_checkout_style_callback() {
        $map_styles = [
            'Light' => __( 'Light', 'barikoi-map' ),
            'Dark'  => __( 'Dark', 'barikoi-map' ),
            'Bangla'=> __( 'Bangla', 'barikoi-map' ),
        ];

        $current_style = get_option( 'bkoimadhk_map_checkout_style', 'Light' );

        echo '<select name="bkoimadhk_map_checkout_style">';
        foreach ( $map_styles as $style_key => $style_name ) {
            echo '<option value="' . esc_attr( $style_key ) . '" ' . selected( $current_style, $style_key, false ) . '>' . esc_html( $style_name ) . '</option>';
        }
        echo '</select>';
        echo '<br><small>' . esc_html__( 'Select your preferred map style.', 'barikoi-map' ) . '</small>';
    }

    public function bkoimadhk_map_checkout_default_zoom_level_callback() {
        $default_zoom_level = get_option( 'bkoimadhk_map_checkout_default_zoom_level', 10 );
        echo '<input type="number" name="bkoimadhk_map_checkout_default_zoom_level" value="' . esc_attr( $default_zoom_level ) . '" min="0" max="22" />';
        echo '<br><small>' . esc_html__( 'Set the default zoom level for the map (0 - 22). The default is 10.', 'barikoi-map' ) . '</small>';
    }

    public function bkoimadhk_map_checkout_map_switcher_callback() {
        $map_enabled = get_option( 'bkoimadhk_map_checkout_map_switcher', '1' );
        echo '<label><input type="radio" name="bkoimadhk_map_checkout_map_switcher" value="1" ' . checked( $map_enabled, '1', false ) . ' /> ' . esc_html__( 'Enable', 'barikoi-map' ) . '</label><br>';
        echo '<label><input type="radio" name="bkoimadhk_map_checkout_map_switcher" value="0" ' . checked( $map_enabled, '0', false ) . ' /> ' . esc_html__( 'Disable', 'barikoi-map' ) . '</label><br>';
    }

    public function bkoimadhk_map_checkout_add_autocomplete_callback() {
        $autocomplete_enabled = get_option( 'bkoimadhk_map_checkout_add_autocomplete', '1' );
        echo '<label><input type="radio" name="bkoimadhk_map_checkout_add_autocomplete" value="1" ' . checked( $autocomplete_enabled, '1', false ) . ' /> ' . esc_html__( 'Enable', 'barikoi-map' ) . '</label><br>';
        echo '<label><input type="radio" name="bkoimadhk_map_checkout_add_autocomplete" value="0" ' . checked( $autocomplete_enabled, '0', false ) . ' /> ' . esc_html__( 'Disable', 'barikoi-map' ) . '</label><br>';
    }

    public function bkoimadhk_map_checkout_default_coordinates_callback() {
        $default_coordinates = get_option( 'bkoimadhk_map_checkout_default_coordinates', '' );
        echo '<input type="text" name="bkoimadhk_map_checkout_default_coordinates" value="' . esc_attr( $default_coordinates ) . '" />';
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
            'bkoimadhk_checkout_media_uploader',
            plugin_dir_url( __FILE__ ) . '../assets/js/barikoi_checkout_media_uploader.js',
            [ 'jquery' ],
            filemtime( plugin_dir_path( __FILE__ ) . '../assets/js/barikoi_checkout_media_uploader.js' ),
            true
        );
    }
}
