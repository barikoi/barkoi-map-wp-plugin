<?php
/**
 * Plugin Name: BariKoi Map
 * Text Domain: barikoi-map
 * Plugin URI:  https://barikoi.com/
 * Description: A WordPress plugin to integrate BariKoi Maps.
 * Version:     1.0.0
 * Author:      Joyprokash Chakrabarty
 * Author URI:  https://miscpros.com
 * License:     GPL-2.0+
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Define constants
 */
define("BKOIMADHK_MAP_VERSION", '1.0.0');
define("BKOIMADHK_MAP_DIR", plugin_dir_path(__FILE__));

/**
 * Activation and Deactivation hooks
 */
function bkoimadhk_activate_plugin() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-bkoimadhk-map-activator.php';
    BkoimaDhk_Map_Activator::bkoimadhk_activate();
}

function bkoimadhk_deactivate_plugin() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-bkoimadhk-map-deactivator.php';
    BkoimaDhk_Map_Deactivator::bkoimadhk_deactivate();
}

register_activation_hook(__FILE__, 'bkoimadhk_activate_plugin');
register_deactivation_hook(__FILE__, 'bkoimadhk_deactivate_plugin');

/**
 * Enqueue admin assets
 */

 function bkoimadhk_enqueue_plugin_assets($hook) {
    $allowed_hooks = [
        'toplevel_page_barikoi-map',
        'barikoi_page_bkoimadhk-map-add-places',
        'barikoi_page_bkoimadhk-map-places',
        'barikoi_page_bkoimadhk-map-update-location',
        'admin_page_bkoimadhk-map-update-location'
    ];
    
    //echo $hook;exit;
    // Load styles/scripts only on allowed pages
    if (!in_array($hook, $allowed_hooks)) {
        return;
    }

    wp_enqueue_style('bkoimadhk-map-css', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '1.0.0');
    wp_enqueue_script('bkoimadhk-map-js', plugin_dir_url(__FILE__) . 'assets/js/map.js', array(), '1.0.0', true);
    wp_enqueue_style('bkoimadhk-gl-css', plugin_dir_url(__FILE__) . 'assets/css/bkoi-gl.css', array(), '1.0.0');
    wp_enqueue_script('bkoimadhk-gl-js', plugin_dir_url(__FILE__) . 'assets/js/bkoi-gl.js', array(), '1.0.0', false);
}
add_action('admin_enqueue_scripts', 'bkoimadhk_enqueue_plugin_assets', 10, 1);


/**
 * Enqueue front-end assets
 */
function bkoimadhk_enqueue_theme_assets() {
    wp_enqueue_style('bkoimadhk-gl-css', plugin_dir_url(__FILE__) . 'assets/css/bkoi-gl.css', array(), '1.0.0');
    wp_enqueue_script('bkoimadhk-gl-js', plugin_dir_url(__FILE__) . 'assets/js/bkoi-gl.js', array(), '1.0.0', false);
}
add_action('wp_enqueue_scripts', 'bkoimadhk_enqueue_theme_assets');

/**
 * WooCommerce fix
 */
add_filter('woocommerce_checkout_update_order_review_fragments', '__return_empty_array');

/**
 * Include plugin components
 */
include_once plugin_dir_path(__FILE__) . 'includes/bkoimadhk-create-shortcode.php';
include_once plugin_dir_path(__FILE__) . 'includes/bkoimadhk-display-shortcode.php';
include_once plugin_dir_path(__FILE__) . 'includes/bkoimadhk_custom_checkout_address_autocomplete.php';
include_once plugin_dir_path(__FILE__) . 'includes/bkoimadhk_custom_checkout_address_from_reverse_geo.php';
require plugin_dir_path(__FILE__) . 'includes/class-bkoimadhk-map.php';

/**
 * Run plugin
 */
function bkoimadhk_run_plugin() {
    $plugin = new BkoimaDhk_Map();
    $plugin->bkoimadhk_run();
}

bkoimadhk_run_plugin();
