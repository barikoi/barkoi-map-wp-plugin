<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function bkoimadhk_create_shortcode() {
    // Check if the nonce is set and valid
    $nonce = isset($_POST['bkoimadhk_nonce']) ? sanitize_text_field(wp_unslash($_POST['bkoimadhk_nonce'])) : '';
            if (!isset($nonce) || !wp_verify_nonce($nonce, 'bkoimadhk_create_shortcode')) {
                wp_die(esc_html__('Security check failed', 'barikoi-map'));
            }
    // Validate and sanitize form input
    if ( ! isset($_POST['finalJson']) ) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Shortcode creation failed', 'barikoi-map') . '</p></div>';
        return;
    }

    // Generate a unique shortcode name
    $shortcode_name = bkoimadhk_generate_random_shortcode_name();

    // Sanitize JSON input safely (allow safe HTML tags, although JSON ideally should be pure string)
    $locationJson = wp_kses_post(wp_unslash($_POST['finalJson'])); 

    // Get existing shortcodes safely
    $stored_shortcodes = get_option( 'bkoimadhk_shortcodes', array() );

    // Update shortcodes with sanitized values
    $stored_shortcodes[ $shortcode_name ] = array(
        'name'      => $shortcode_name,
        'locations' => $locationJson,
    );

    update_option( 'bkoimadhk_shortcodes', $stored_shortcodes );

    $msg = '<div class="notice notice-success"><p>' . esc_html__('Shortcode created successfully:', 'barikoi-map') . ' [barikoi_map name="' . esc_html( $shortcode_name ) . '"]</p></div>';
    
    return $msg;
}

// Generate a unique shortcode name with prefix
function bkoimadhk_generate_random_shortcode_name() {
    return 'shortcode_' . sanitize_key( uniqid() );
}
