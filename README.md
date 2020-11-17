## 🚧 Experimental!

# WP_Query as a REST API Endpoint

Query anything you want from the WordPress database using a single REST API endpoint.

## Usage Examples

### URL

```
https://example.com/wp-json/boxybird/wp-query/v1/args?post_type=post&posts_per_page=5&tag__in[]=15&tag__in[]=22&orderby=title
```

### jQuery

```js
const params = jQuery.param({
  post_type: 'post',
  posts_per_page: 5,
  tag__in: [15, 22],
  orderby: 'title',
});

jQuery.get(`https://example.com/wp-json/boxybird/wp-query/v1/args?${params}`).done((response) => {
  console.log(response);
});
```

## Hooks

### Format JSON response

```php
add_filter('boxybird/query/format-response', function (WP_Query $query) {
    return array_map(function ($post) {
        return [
            'id'       => $post->ID,
            'title'    => get_the_title($post->ID),
            'content'  => get_the_content(null, false, $post->ID),
            'link'     => get_the_permalink($post->ID),
            'some_acf' => get_field('some_acf', $post->ID),
            'excerpt'  => [
                'short' => wp_trim_words(get_the_content(null, false, $post->ID), 25),
                'long'  => wp_trim_words(get_the_content(null, false, $post->ID), 75),
            ],
            'image' => [
                'full'      => get_the_post_thumbnail_url($post->ID, 'full'),
                'medium'    => get_the_post_thumbnail_url($post->ID, 'medium'),
                'thumbnail' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
            ],
        ];
    }, $query->posts);
});
```

### Add/Modify WP_Query Args

> https://developer.wordpress.org/reference/classes/wp_query/

#### Default Args

```php
// These $args will be overridden by any matching request params
add_filter('boxybird/query/default-args', function ($args) {
    $args['posts_per_page'] = 12;

    return $args;
});
```

#### Override Args

```php
// These $args will override any matching request params
add_filter('boxybird/query/override-args', function ($args) {
    $args['posts_per_page'] = 5;

    return $args;
});
```

### Permissions Callback

> https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/#permissions-callback

```php
add_filter('boxybird/query/permission', function () {
    // Restrict endpoint to only users who have the edit_posts capability.
    if (!current_user_can('edit_posts')) {
        return new WP_Error('rest_forbidden', esc_html__('OMG you can not view private data.', 'my-text-domain'), ['status' => 401]);
    }

    // This is a black-listing approach. You could alternatively do this via white-listing, by returning false here and changing the permissions check.
    return true;
});
```
