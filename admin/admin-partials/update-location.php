<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// 1. Early form handler to process POST and redirect before output
add_action('admin_init', 'bkoimadhk_handle_update_location_form');
function bkoimadhk_handle_update_location_form() {
    if (
        isset($_POST['submit']) &&
        isset($_POST['bkoimadhk_nonce']) &&
        wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bkoimadhk_nonce'])), 'bkoimadhk_create_shortcode')
    ) {
        include_once plugin_dir_path(__FILE__) . '../../includes/bkoimadhk-update-shortcode.php';

        $shortcode_name = bkoimadhk_update_shortcode();

        $shortcode_name = sanitize_text_field($shortcode_name);

        $redirect_url = add_query_arg(
            [
                'page'      => 'bkoimadhk-map-update-location',
                'shortcode' => $shortcode_name,
                'message'   => 'success',
                '_wpnonce'  => wp_create_nonce('bkoimadhk_update_location_nonce'),
            ],
            admin_url('admin.php')
        );

        wp_safe_redirect($redirect_url);
        exit;
    }
}

// 2. The admin page rendering function
function bkoimadhk_update_location_page() {
    // Verify nonce for GET requests
    if (
        ! isset( $_GET['_wpnonce'] ) ||
        ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bkoimadhk_update_location_nonce' )
    ) {
        wp_die( esc_html__( 'Security check failed.', 'barikoi-map' ) );
    }

    $api_key = get_option("bkoimadhk_api_key");

    if (empty($api_key)) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Please enter your Barikoi API key in the settings page before creating a shortcode.', 'barikoi-map') . '</p></div>';
        return;
    }

    if (isset($_GET['message']) && $_GET['message'] === "success") { 
        echo '<div class="notice notice-success"><p>' . esc_html__('Shortcode updated successfully.', 'barikoi-map') . '</p></div>';
    }

    if (isset($_GET['shortcode'])) {
        $shortcode = sanitize_text_field(wp_unslash($_GET['shortcode']));
        $shortcodes = get_option("bkoimadhk_shortcodes", []);

        if (isset($shortcodes[$shortcode]['locations'])) {
            $locations = $shortcodes[$shortcode]['locations'];
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Invalid shortcode.', 'barikoi-map') . '</p></div>';
            return;
        }
    } else {
        echo '<div class="notice notice-error"><p>' . esc_html__('Invalid shortcode.', 'barikoi-map') . '</p></div>';
        return;
    }

    wp_register_script('bkoimadhk-inline-script', '', [], BKOIMADHK_MAP_VERSION, true);
    wp_enqueue_script('bkoimadhk-inline-script');

    $inline_script = "localStorage.setItem('bkoimadhkApiKey', " . json_encode($api_key) . ");
                      localStorage.setItem('locations', " . json_encode($locations) . ");";
    wp_add_inline_script('bkoimadhk-inline-script', $inline_script);
    ?>
    
    <div class="wrap">
        <h1><?php esc_html_e('Update Location', 'barikoi-map'); ?></h1>

        <div class="input-container">
            <input type="text" id="locationInput" placeholder="<?php esc_attr_e('Enter location...', 'barikoi-map'); ?>" list="suggestions" oninput="fetchSuggestions()" />
            <datalist id="suggestions"></datalist>
            <span id="msger" style="display: none;"></span>
        </div>

        <div class="map-table-container">
            <div id="map" style="position: relative; width: 800px; height: 600px;">
                <div id="loadingOverlay" class="overlay"><?php esc_html_e('Loading...', 'barikoi-map'); ?></div>
            </div>
            
            <div class="table-container">
                <h2><?php esc_html_e('Locations Table', 'barikoi-map'); ?></h2>
                <div id="loader" style="display:none;text-align:left" class="loader"><?php esc_html_e('Loading...', 'barikoi-map'); ?></div>
                <table id="table-container" style="display:none">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Address', 'barikoi-map'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="locationsTableBody">
                        <!-- Rows will be added dynamically here -->
                    </tbody>
                </table>
            </div>
        </div>
        <br><br>
        <form method="post" action="" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to submit this form?', 'barikoi-map'); ?>');">
            <?php wp_nonce_field('bkoimadhk_create_shortcode', 'bkoimadhk_nonce'); ?>
            <table class="form-table" style="display:none">
                <tr>
                    <td>
                        <input type="hidden" name="finalJson" id="mapJson">
                        <input type="hidden" name="shortcode_code" value="<?php echo esc_attr($shortcode); ?>">
                    </td>
                </tr>
            </table>
            <input type="submit" name="submit" value="<?php esc_attr_e('Update Shortcode', 'barikoi-map'); ?>" class="button button-primary">
        </form>
    </div>
    <?php
}