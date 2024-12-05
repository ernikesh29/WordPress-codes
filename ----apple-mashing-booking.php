<?php
/*
Plugin Name: Apple Mashing Booking
Description: Custom Booking system for Apple Mashing.
Version: 2.6
Author: Nikesh
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants
define('AM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include files
require_once AM_PLUGIN_DIR . 'includes/post-types.php';
require_once AM_PLUGIN_DIR . 'includes/enqueue-scripts.php';
require_once AM_PLUGIN_DIR . 'includes/meta-boxes.php';
require_once AM_PLUGIN_DIR . 'includes/ajax-handlers.php';
require_once AM_PLUGIN_DIR . 'includes/helpers.php';
require_once AM_PLUGIN_DIR . 'includes/shortcodes.php';
