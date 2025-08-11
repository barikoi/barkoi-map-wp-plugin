<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function bkoimadhk_enqueue_custom_checkout_address_from_reverse_geo_scripts() {
       if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        wp_enqueue_script(
            'bkoimadhk_custom_checkout_reverse_geo',
            plugin_dir_url(__FILE__) . '../assets/js/custom-checkout-reverse-geo.js',
            array( 'jquery', 'wp-data' ),
            filemtime(plugin_dir_path(__FILE__) . '../assets/js/custom-checkout-reverse-geo.js'),
            true
        );
        
         $default_marker_icon = plugin_dir_url(dirname(__FILE__)) . 'assets/img/marker.png';
         
        $value = get_option('bkoimadhk_map_checkout_custom_marker_icon');

        if ($value === '' || $value === null) {
            $value = $default_marker_icon;
        }

        wp_localize_script(
            'bkoimadhk_custom_checkout_reverse_geo',
            'bkoimadhk_reverseGeoConfig',
            array(
                'apiKey'             => sanitize_text_field( get_option('bkoimadhk_api_key') ),
                'default_map_style'  => sanitize_text_field( get_option('bkoimadhk_map_checkout_style', 'Light') ),
                'default_zoom_level' => (int) get_option('bkoimadhk_map_checkout_default_zoom_level', 10),
                'default_coordinates'=> get_option('bkoimadhk_map_checkout_default_coordinates', ''), // sanitize based on format
                'map_switch'         => (bool) get_option('bkoimadhk_map_checkout_map_switcher', '1'),
                'autocomplete_switch'=> (bool) get_option('bkoimadhk_map_checkout_add_autocomplete', '1'),
                'default_marker_icon'=> $value,
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'bkoimadhk_enqueue_custom_checkout_address_from_reverse_geo_scripts' );
