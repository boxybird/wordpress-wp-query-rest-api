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

    protected static function handleArgs(WP_REST_Request $request)
    {
        $defaults = apply_filters('boxybird/query/default-args', []);
        $args     = apply_filters('boxybird/query/override-args', $request->get_params());

        static::$args = array_merge($defaults, $args);
    }

    protected static function handleFormatResponse(WP_Query $query)
    {
        if (!has_filter('boxybird/query/format-response')) {
            return $query->post_count ? $query->posts : [];
        }

        return apply_filters('boxybird/query/format-response', $query);
    }

    protected static function handleResponse()
    {
        $query = new WP_Query(static::$args);

        $data = static::handleFormatResponse($query);

        return wp_send_json_success($data);
    }
}
