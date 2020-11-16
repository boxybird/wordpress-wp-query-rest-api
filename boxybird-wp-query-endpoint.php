<?php

/**
 * Plugin Name:       WP_Query REST API Endpoint
 * Version:           0.0.1
 * Author:            Andrew Rhyand
 * Author URI:        https://andrewrhyand.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('WPINC')) {
    die;
}

require_once plugin_dir_path(__FILE__) . 'src/Query.php';

BoxyBird\WpQuery\Query::init();
