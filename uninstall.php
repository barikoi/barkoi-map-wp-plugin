<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Clean up stored options
delete_option("bkoimadhk_map_options");
