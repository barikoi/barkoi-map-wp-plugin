<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Early POST handler for creating shortcode, hooked on admin_init to run before output
add_action('admin_init', 'bkoimadhk_handle_create_shortcode_form');
function bkoimadhk_handle_create_shortcode_form() {
    if (
        isset($_POST['submit']) &&
        isset($_POST['bkoimadhk_nonce']) &&
        wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bkoimadhk_nonce'])), 'bkoimadhk_create_shortcode')
    ) {
        include_once plugin_dir_path(__FILE__) . '../../includes/bkoimadhk-create-shortcode.php';

        $msg = bkoimadhk_create_shortcode();
        
        set_transient('bkoimadhk_last_msg', $msg, 30); // store for 30 seconds
        
        wp_safe_redirect( admin_url('admin.php?page=bkoimadhk-map-add-places') );
    }
}

// The page rendering function that displays the create shortcode form and UI
function bkoimadhk_shortcode_page() {
    $api_key = get_option('bkoimadhk_api_key');

    if (empty($api_key)) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Please enter your Barikoi API key in the settings page before creating a shortcode.', 'barikoi-map') . '</p></div>';
        return;
    }
    
    $msg = get_transient('bkoimadhk_last_msg');
    if ($msg) {
        echo $msg;
        delete_transient('bkoimadhk_last_msg'); // clear after showing
    }

    $inline_script = 'localStorage.setItem("bkoimadhkApiKey", ' . json_encode($api_key) . ');';
    wp_register_script('bkoimadhk-inline-only-script', '', [], BKOIMADHK_MAP_VERSION, true);
    wp_enqueue_script('bkoimadhk-inline-only-script');
    wp_add_inline_script('bkoimadhk-inline-only-script', $inline_script);
    ?>
    
    <div class="wrap">
        <h1><?php esc_html_e('Create New Map Shortcode', 'barikoi-map'); ?></h1>

        <div class="input-container">
            <input type="text" id="locationInput" placeholder="<?php esc_attr_e('Enter location...', 'barikoi-map'); ?>" list="suggestions" onkeyup="fetchSuggestions()" />
            <datalist id="suggestions"></datalist>
            <span id="msger" style="display: none;"></span>
        </div>

        <div class="map-table-container">
            <div id="map" style="position: relative; width: 800px; height: 600px;">
                <div id="loadingOverlay" class="overlay"><?php esc_html_e('Loading...', 'barikoi-map'); ?></div>
            </div>

            <div class="table-container">
                <h2><?php esc_html_e('Locations Table', 'barikoi-map'); ?></h2>
                <table>
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
                    </td>
                </tr>
            </table>
            <input type="submit" name="submit" value="<?php esc_attr_e('Create Shortcode', 'barikoi-map'); ?>" class="button button-primary">
        </form>
    </div>
    <?php
}