<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Register the shortcode with prefixed callback
add_shortcode( 'barikoi_map', 'bkoimadhk_map_shortcode' );

function bkoimadhk_map_shortcode( $atts ) {
    // Extract shortcode attributes
    $atts = shortcode_atts( array( 'name' => '' ), $atts );
    $shortcode_name = sanitize_text_field( $atts['name'] );

    // Get stored shortcodes
    $stored_shortcodes = get_option( 'bkoimadhk_shortcodes', array() );

    if ( ! isset( $stored_shortcodes[ $shortcode_name ] ) ) {
        return '<p>' . esc_html__( 'Invalid shortcode name.', 'barikoi-map' ) . '</p>';
    }

    $locations = $stored_shortcodes[ $shortcode_name ]['locations'];
    $locations = json_decode( $locations, true );
    if ( ! is_array( $locations ) ) {
        return '<p>' . esc_html__( 'Invalid location data.', 'barikoi-map' ) . '</p>';
    }

    ob_start();

    $api_key = get_option( 'bkoimadhk_api_key' );
    if ( empty( $api_key ) ) {
        echo '<div class="notice notice-error"><p>' . esc_html__( 'Please enter your Barikoi API key in the settings page before creating a shortcode.', 'barikoi-map' ) . '</p></div>';
        return ob_get_clean();
    }

    $map_id = 'map-' . wp_rand();
    $default_zoom_level = intval( get_option( 'barikoidhk_default_zoom_level', 10 ) );
    $default_map_style = get_option( 'barikoidhk_default_map_style', 'Light' );
    $default_marker_icon = get_option( 'barikoidhk_custom_marker_icon' );

    // Default marker icon fallback if none set
    if ( empty( $default_marker_icon ) ) {
        $default_marker_icon = plugin_dir_url(dirname(__FILE__)) . '/../assets/img/marker.png';
    }

    // Map styles array
    $map_styles = array(
        'Dark'   => 'https://map.barikoi.com/styles/barikoi-dark-mode/style.json',
        'Bangla' => 'https://map.barikoi.com/styles/barikoi-bangla/style.json',
        'Light'  => 'https://map.barikoi.com/styles/planet_barikoi_v2/style.json',
    );

    $selected_map_style = isset( $map_styles[ $default_map_style ] ) ? $map_styles[ $default_map_style ] : $map_styles['Light'];

    // Register and enqueue dummy script handle for inline script
    wp_register_script( 'bkoimadhk-barikoi-map-inline', '', [], BKOIMADHK_MAP_VERSION, true );
    wp_enqueue_script( 'bkoimadhk-barikoi-map-inline' );

    // Prepare inline JS
    $inline_script = '
    (function() {
    console.log('.$default_zoom_level.');
        const apiKey = ' . wp_json_encode( $api_key ) . ';
        const mapId = ' . wp_json_encode( $map_id ) . ';
        const locations = ' . wp_json_encode( $locations ) . ';

        bkoigl.accessToken = apiKey;

        const map = new bkoigl.Map({
            container: mapId,
            center: [90.4125, 23.8103],
            zoom: ' . $default_zoom_level . ',
            style: ' . wp_json_encode( $selected_map_style ) . ',
        });

        const coordinates = [];

        locations.forEach((location) => {
            const popup = new bkoigl.Popup({ focusAfterOpen: false })
                .setHTML("<p>" + location.address + "</p>");

            const markerIcon = document.createElement("img");
            markerIcon.src = ' . wp_json_encode( $default_marker_icon ) . ';
            markerIcon.style.width = "35px";
            markerIcon.style.height = "35px";
            markerIcon.style.cursor = "pointer";

            const marker = new bkoigl.Marker({ draggable: false, element: markerIcon })
                .setLngLat([location.lng, location.lat])
                .setPopup(popup)
                .addTo(map);

            marker.getElement().addEventListener("mouseenter", () => {
                popup.setLngLat([location.lng, location.lat]).addTo(map);
            });

            marker.getElement().addEventListener("mouseleave", () => {
                popup.remove();
            });

            coordinates.push([location.lng, location.lat]);
        });

        if (coordinates.length > 0) {
            map.fitBounds(coordinates, {
                maxZoom: ' . $default_zoom_level . ',
                padding: 64
            });
        }

        map.addControl(new bkoigl.FullscreenControl());
        map.addControl(new bkoigl.NavigationControl());
        map.addControl(new bkoigl.ScaleControl());
    })();
    ';

    wp_add_inline_script( 'bkoimadhk-barikoi-map-inline', $inline_script );

    ?>

    <div id="<?php echo esc_attr( $map_id ); ?>" style="height: 500px;"></div>

    <?php
    return ob_get_clean();
}
