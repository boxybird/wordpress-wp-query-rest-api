<?php

namespace BoxyBird\WpQuery;

use WP_Query;
use WP_REST_Request;

class Query
{
    protected static $args;

    public static function init()
    {
        add_action('rest_api_init', function () {
            register_rest_route('boxybird/wp-query/v1', '/args', [
                'methods'             => 'GET',
                'callback'            => [Query::class, 'callback'],
                'permission_callback' => [Query::class, 'permissionCallback'],
            ]);
        });
    }

    public static function callback(WP_REST_Request $request)
    {
        static::handleArgs($request);
        static::handleResponse();
    }

    public static function permissionCallback()
    {
        return apply_filters('boxybird/query/permission', true);
    }

    protected function handleArgs(WP_REST_Request $request)
    {
        $defaults  = apply_filters('boxybird/query/default-args', []);
        $overrides = apply_filters('boxybird/query/override-args', []);

        static::$args = array_merge($defaults, $request->get_params(), $overrides);
    }

    protected function handleResponse()
    {
        $query = new WP_Query(static::$args);

        $response = apply_filters('boxybird/query/format-response', $query);

        return wp_send_json_success($response);
    }
}
