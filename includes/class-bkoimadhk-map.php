<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bkoimadhk_Map {

    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-bkoimadhk-map-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-bkoimadhk-map-public.php';
        require_once plugin_dir_path(__FILE__) . '../includes/class-bkoimadhk-map-widget-setting.php';
        require_once plugin_dir_path(__FILE__) . '../includes/class-bkoimadhk-map-checkout-setting.php';
    }

    private function define_admin_hooks() {
        $widget_setting = new Bkoimadhk_Map_Widget_Settings();
        $checkout_setting = new Bkoimadhk_Map_Checkout_Settings();
        $plugin_admin = new Bkoimadhk_Map_Admin($widget_setting, $checkout_setting);
        add_action('admin_menu', [$plugin_admin, 'bkoimadhk_add_plugin_admin_menu']);
        add_action('wp_ajax_bkoimadhk_delete_barikoi_shortcode', [$plugin_admin, 'bkoimadhk_delete_barikoi_shortcode']);
    }

    private function define_public_hooks() {
        $plugin_public = new Bkoimadhk_Map_Public();
        add_shortcode('bkoimadhk_map_shortcode', [$plugin_public, 'bkoimadhk_display_map']);
    }

    public function bkoimadhk_run() {
        // Initialization code if needed
    }
}
