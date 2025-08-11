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

        wp_localize_script(
            'bkoimadhk_custom_checkout_reverse_geo',
            'bkoimadhk_reverseGeoConfig',
            array(
                'apiKey' => esc_attr( get_option('bkoimadhk_api_key') ),
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'bkoimadhk_enqueue_custom_checkout_address_from_reverse_geo_scripts' );
