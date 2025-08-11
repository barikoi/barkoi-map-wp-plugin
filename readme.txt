=== BariKoi Map ===
Contributors: barikoi
Tags: maps, woocommerce, checkout, location, geolocation
Requires at least: 6.7
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate Barikoi Maps into WooCommerce for an interactive location picker and custom location display.

== Description ==

The Barikoi Maps WooCommerce Location Picker plugin not only enhances the WooCommerce checkout experience but also offers the ability to embed Barikoi Maps directly into WordPress pages. This feature allows website owners to display custom places, such as stores, service points, or landmarks, on an interactive map. By leveraging Barikoi Maps, businesses can provide visitors with clear visual references to important locations, improving navigation and customer engagement. This dual functionality makes the plugin a powerful tool for both e-commerce delivery optimization and location-based content display.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Once activated, navigate to the plugin settings page and configure your API key and settings.

== Development and Build Tools ==

The JavaScript file `assets/js/bkoi-gl.js` & css file `assets/css/bkoi-gl.css` is a minified production build.

The full, human-readable source code and development history are publicly available at:

https://github.com/barikoi/bkoi-gl-js/tree/bkoi-gl-v2

This source code is maintained separately and built using standard JavaScript tooling (e.g., Rollup ).

To comply with WordPress.org guidelines, this source is provided for review, study, or contribution.

== External services ==

This plugin connects to the Barikoi mapping and geocoding services to display maps and location information.

The plugin uses the Barikoi Map Styles from URLs like:

- https://map.barikoi.com/styles/barikoi-dark-mode/style.json
- https://map.barikoi.com/styles/barikoi-bangla/style.json
- https://map.barikoi.com/styles/planet_barikoi_v2/style.json

It also sends location data such as longitude and latitude to Barikoi's reverse geocoding API to retrieve address details. This happens whenever a user interacts with location-related features that require geocoding.

The data sent may include:
- User's latitude and longitude coordinates
- Barikoi API key (provided by the site administrator)

These services are provided by Barikoi:  
- Terms of Service: https://barikoi.com/terms  
- Privacy Policy: https://barikoi.com/privacy-policy


== Frequently Asked Questions ==

= How do I set up the Barikoi Maps API key? =

Go to the plugin settings page in WordPress and enter your Barikoi API key to enable the location picker functionality.

= Can I customize the locations displayed on the map? =

Yes, you can add custom locations through the plugin’s settings page and display them on the map as markers.

= Is the plugin compatible with all WooCommerce themes? =

The plugin is designed to work with most WooCommerce themes. If you encounter issues, contact our support for assistance.

== Changelog ==

= 1.0.0 =
* Initial release with Barikoi Maps integration
* WooCommerce checkout location picker feature added
* Ability to display custom locations on interactive maps
