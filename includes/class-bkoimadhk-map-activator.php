<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bkoimadhk_Map_Activator {

    public static function bkoimadhk_activate() {
        // Actions to perform during activation, like database setup
        if ( ! get_option( 'bkoimadhk_map_options' ) ) {
            add_option( 'bkoimadhk_map_options', [ 'version' => BKOIMADHK_MAP_VERSION ] );
        }
    }
}
