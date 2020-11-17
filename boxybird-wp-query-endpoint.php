<?php

/**
 * Plugin Name:       WP_Query REST API Endpoint
 * Description:       Query anything you want from the WordPress database using a single REST API endpoint.
 * Version:           0.0.4
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
