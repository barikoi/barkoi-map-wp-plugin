<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function bkoimadhk_enqueue_custom_checkout_autocomplete_scripts() {
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        wp_enqueue_script(
            'bkoimadhk_custom_checkout_autocomplete',
            plugin_dir_url(__FILE__) . '../assets/js/custom-checkout-autocomplete.js',
            array( 'jquery', 'wp-data' ),
            filemtime(plugin_dir_path(__FILE__) . '../assets/js/custom-checkout-autocomplete.js'),
            true
        );

        wp_localize_script(
            'bkoimadhk_custom_checkout_autocomplete',
            'bkoimadhk_autocompleteConfig',
            array(
                'apiKey'             => esc_attr( get_option('bkoimadhk_api_key') ),
                'default_map_style'  => esc_attr( get_option('bkoimadhk_map_checkout_style', 'Light') ),
                'default_zoom_level' => esc_attr( get_option('bkoimadhk_map_checkout_default_zoom_level', 10) ),
                'default_coordinates'=> esc_attr( get_option('bkoimadhk_map_checkout_default_coordinates', '') ),
                'map_switch'         => esc_attr( get_option('bkoimadhk_map_checkout_map_switcher', '1') ),
                'autocomplete_switch'=> esc_attr( get_option('bkoimadhk_map_checkout_add_autocomplete', '1') ),
                'default_marker_icon'=> esc_attr( get_option('bkoimadhk_map_checkout_custom_marker_icon', '') ),
            )
        );

        wp_enqueue_style(
            'bkoimadhk_custom_checkout_autocomplete_styles',
            plugin_dir_url(__FILE__) . '../assets/css/custom-checkout-autocomplete.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . '../assets/css/custom-checkout-autocomplete.css')
        );
    }
}
add_action( 'wp_enqueue_scripts', 'bkoimadhk_enqueue_custom_checkout_autocomplete_scripts' );
