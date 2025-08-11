<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function bkoimadhk_shortcods_list() {
    // Get API key
    $api_key = get_option("bkoimadhk_api_key");

    // Check if the API key exists
    if (empty($api_key)) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Please enter your Barikoi API key in the settings page before creating a shortcode.', 'barikoi-map') . '</p></div>';
        return;
    }

    // Retrieve shortcodes from the database
    $shortcodes = get_option("bkoimadhk_shortcodes", []);  // Default to an empty array if no shortcodes exist
    $nonce   = wp_create_nonce('bkoimadhk_delete_nonce');

    // Inline JS
    $inline_script = "
        localStorage.setItem('bkoimadhkApiKey', " . json_encode($api_key) . ");
        
        function copyToClipboard(text) {
            const tempInput = document.createElement('input');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            alert('Copied to clipboard: ' + text);
        }

        function deleteShortcode(shortcodeId) {
            if (confirm('Are you sure you want to delete this shortcode?')) {
                const data = {
                    action: 'bkoimadhk_delete_shortcode',
                    shortcode_id: shortcodeId,
                    _ajax_nonce: " . json_encode($nonce) . "
                };
                jQuery.post(ajaxurl, data, function(response) {
                    if (response.success) {
                        alert('Shortcode deleted successfully.');
                        location.reload();
                    } else {
                        alert('Error deleting shortcode: ' + response.data);
                    }
                });
            }
        }
    ";

    wp_register_script('bkoimadhk-inline-script', '', [], BKOIMADHK_MAP_VERSION, true);
    wp_enqueue_script('bkoimadhk-inline-script');
    wp_add_inline_script('bkoimadhk-inline-script', $inline_script);
    ?>

    <div class="wrap">
        <h1><?php esc_html_e('My Shortcode List', 'barikoi-map'); ?></h1>

        <div class="map-table-container">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Shortcode', 'barikoi-map'); ?></th>
                            <th><?php esc_html_e('Action', 'barikoi-map'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nonce_update = wp_create_nonce('bkoimadhk_update_location_nonce');

                        if (!empty($shortcodes)) {
                            foreach ($shortcodes as $shortcode => $location) {
                                ?>
                                <tr>
                                    <td>
                                        <span onclick="copyToClipboard('[barikoi_map name=&quot;<?php echo esc_html($shortcode); ?>&quot;]')" style="cursor: pointer; color: blue;">
                                            [barikoi_map name="<?php echo esc_html($shortcode); ?>"]
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=bkoimadhk-map-update-location&shortcode=' . urlencode($shortcode) . '&_wpnonce=' . $nonce_update)); ?>" class="button button-secondary">
                                            <?php esc_html_e('Edit', 'barikoi-map'); ?>
                                        </a>
                                        <button class="button button-danger" onclick="deleteShortcode('<?php echo esc_js($shortcode); ?>')">
                                            <?php esc_html_e('Delete', 'barikoi-map'); ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="2">' . esc_html__('No shortcodes available.', 'barikoi-map') . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

function bkoimadhk_delete_shortcode() {
   
    check_ajax_referer('bkoimadhk_delete_nonce');
    $shortcode_id = isset($_POST['shortcode_id']) ? sanitize_text_field(wp_unslash($_POST['shortcode_id'])) : '';
    $shortcodes = get_option("bkoimadhk_shortcodes", []);
    if (isset($shortcodes[$shortcode_id])) {
        unset($shortcodes[$shortcode_id]);
        update_option("bkoimadhk_shortcodes", $shortcodes);
        wp_send_json_success();
    } else {
        wp_send_json_error(__('Shortcode not found.', 'barikoi-map'));
    }
}

add_action( 'wp_ajax_bkoimadhk_delete_shortcode', 'bkoimadhk_delete_shortcode' );
add_action( 'wp_ajax_nopriv_bkoimadhk_delete_shortcode', 'bkoimadhk_delete_shortcode' );
