<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function bkoimadhk_update_shortcode() {
    // Check if the nonce is set and valid
    $nonce = isset( $_POST['barikoi_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['barikoi_nonce'] ) ) : '';
    
    if (
                ! isset( $_POST['bkoimadhk_nonce'] ) ||
                ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bkoimadhk_nonce'] ) ), 'bkoimadhk_create_shortcode' )
            ) {
                wp_die( esc_html__( 'Security check failed', 'barikoi-map' ) );
            }

    // Validate and sanitize form input
    if ( ! isset( $_POST['finalJson'] ) || ! isset( $_POST['shortcode_code'] ) ) {
        echo '<div class="notice notice-error"><p>' . esc_html__( 'Shortcode update failed', 'barikoi-map' ) . '</p></div>';
        return;
    }

    $shortcode_name = sanitize_text_field( wp_unslash( $_POST['shortcode_code'] ) );
    $locationJson   = wp_kses_post( wp_unslash( $_POST['finalJson'] ) ); // Ensure safe JSON input
   // echo $locationJson;exit;
    // Get existing shortcodes safely
    $stored_shortcodes = get_option( 'bkoimadhk_shortcodes', array() );

    // Update shortcodes with sanitized values
    $stored_shortcodes[ $shortcode_name ] = array(
        'name'      => $shortcode_name,
        'locations' => $locationJson,
    );
    
    //echo "<pre>";
    //print_r($stored_shortcodes);exit;

    update_option( 'bkoimadhk_shortcodes', $stored_shortcodes );

    return esc_html( $shortcode_name );
}