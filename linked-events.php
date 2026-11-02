<?php
/**
 * Plugin Name: LinkedEvents
 * Description: Custom events manager with full list and upcoming events shortcodes.
 * Version: 1.1.0
 * Author: Stan Han
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'LE_EVENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'LE_EVENTS_URL',  plugin_dir_url( __FILE__ ) );

require_once LE_EVENTS_PATH . 'includes/cpt.php';
require_once LE_EVENTS_PATH . 'includes/admin.php';
require_once LE_EVENTS_PATH . 'includes/settings.php';
require_once LE_EVENTS_PATH . 'includes/shortcode-list.php';
require_once LE_EVENTS_PATH . 'includes/shortcode-upcoming.php';
require_once LE_EVENTS_PATH . 'includes/assets.php';

register_activation_hook( __FILE__, function() {
    require_once LE_EVENTS_PATH . 'includes/cpt.php';
    le_events_register_cpt();
    flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function() {
    flush_rewrite_rules();
} );
