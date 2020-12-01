## 🚧 Experimental

# WP_Query as a REST API Endpoint

Query anything you want from the WordPress database using a single REST API endpoint.

**Simply put, this plugin allows to pass GET params from a url into the `WP_Query` class as `$args`**  

> `WP_Query` Reference: https://developer.wordpress.org/reference/classes/wp_query/

## Usage Examples

### PHP

Normally in a WordPress theme or plugin you would create an array of $args and pass them into the `WP_Query($args)` constructor. Like this:

```php
<?php

$args = [
    'post_type'      => 'post',
    'orderby'        => 'title',
    'posts_per_page' => 12,
    'category__in'   => [31, 12, 4],
    //... any other WP_Query args you want.
];

$query = new WP_Query($args);

// loop through results in PHP file.
```

Using this plugin, you can pass those same args from your front-end Javascript:

### Vanilla JS

```js
const url = 'https://your-site.com/wp-json/boxybird/wp-query/v1/args?post_type=post&orderby=title&posts_per_page=12&category__in[]=31&category__in[]=12&category__in[]=4'

fetch(url)
  .then(res => res.json())
  .then(data => {
    console.log(data)
  })

```

### jQuery

```js
const params = jQuery.param({
  post_type: 'post',
  orderby: 'title',
  posts_per_page: 12,
  category__in: [31, 12, 4],
  //... any other WP_Query args you want.
});

jQuery.get(`https:/your-site.com/wp-json/boxybird/wp-query/v1/args?${params}`).done((data) => {
  console.log(data);
});
```

## Hooks

> The examples below will use a demo site with a 'movie' post_type.
>
>https://wp-query.andrewrhyand.com/wp-json/boxybird/wp-query/v1/args?post_type=movie

### Formatting the JSON response

Out of the box, `WP_Query` will return the raw rows from the `wp_posts` table. Like this:

```json
{
  "success": true,
  "data": [
    {
      "ID": 569,
      "post_author": "1",
      "post_date": "2020-11-23 00:17:16",
      "post_date_gmt": "2020-11-23 00:17:16",
      "post_content": "An eclectic foursome of aspiring teenage witches get more than they bargained for as they lean into their newfound powers.",
      "post_title": "The Craft: Legacy",
      "post_excerpt": "",
      "post_status": "publish",
      "comment_status": "closed",
      "ping_status": "closed",
      "post_password": "",
      "post_name": "the-craft-legacy",
      "to_ping": "",
      "pinged": "",
      "post_modified": "2020-11-23 00:17:16",
      "post_modified_gmt": "2020-11-23 00:17:16",
      "post_content_filtered": "",
      "post_parent": 0,
      "guid": "https://wp-query.andrewrhyand.com/movies/the-craft-legacy",
      "menu_order": 0,
      "post_type": "movie",
      "post_mime_type": "",
      "comment_count": "0",
      "filter": "raw"
    },
    {
      "ID": 567,
      "post_author": "1",
      "post_date": "2020-11-23 00:17:16",
      "...and so on"
    }
  ]
}
```

The above may be useful in some situations, but more often than not you'll likely want to format the JSON response. This is the filter to do it:

```php
add_filter('boxybird/query/format-response', function (WP_Query $query) {
  // do something with $query and return.
});
```

Here's an example of how you could use the filter:

```php
add_filter('boxybird/query/format-response', function (WP_Query $query) {
    // Assign queried 'post_type'
    $post_type = strtolower($query->query_vars['post_type']);

    // If it's a 'movie' post_type, format like this:
    if ($post_type === 'movie') {
        return array_map(function ($movie) {
            return [
                'id'          => $movie->ID,
                'title'       => get_the_title($movie->ID),
                'description' => get_the_content(null, false, $movie->ID),
                'link'        => get_the_permalink($movie->ID),
                'genres'      => array_map(function ($term) {
                    return $term->name;
                }, get_the_terms($movie->ID, 'genre')),
                'details'     => array_map(function ($detail) {
                    return $detail[0];
                }, get_post_meta($movie->ID)),
                'description' => [
                    'short' => wp_trim_words(get_the_content(null, false, $movie->ID), 10),
                    'long'  => wp_trim_words(get_the_content(null, false, $movie->ID), 75),
                ],
                'images' => [
                    'full'      => get_the_post_thumbnail_url($movie->ID, 'full'),
                    'medium'    => get_the_post_thumbnail_url($movie->ID, 'medium'),
                    'thumbnail' => get_the_post_thumbnail_url($movie->ID, 'thumbnail'),
                ],
            ];
        }, $query->posts);
    }

    // If it's a 'post' post_type, format like this:
    if ($post_type === 'post') {
        return array_map(function ($post) {
            return [
                'id'      => $post->ID,
                'title'   => get_the_title($post->ID),
                'content' => get_the_content(null, false, $post->ID),
                'link'    => get_the_permalink($post->ID),
            ];
        }, $query->posts);
    }

    // If it's any other post_type, format like this:
    return array_map(function ($post) {
        return [
            'id'    => $post->ID,
            'title' => get_the_title($post->ID),
        ];
    }, $query->posts);
});
```

Focusing on the 'movie' post_type above, this would be the custom formatted response:

```json
{
  "success": true,
  "data": [
    {
      "id": 553,
      "title": "Dark Phoenix",
      "description": "The X-Men face their most formidable and powerful foe when one of their own, Jean Grey, starts to spiral out of control. During a rescue mission in outer space, Jean is nearly killed when she's hit by a mysterious cosmic force. Once she returns home, this force not only makes her infinitely more powerful, but far more unstable. The X-Men must now band together to save her soul and battle aliens that want to use Grey's new abilities to rule the galaxy.",
      "link": "https://wp-query.andrewrhyand.com/movies/dark-phoenix",
      "genres": [
        "Drama",
        "Horror",
        "Thriller"
      ],
      "details": {
        "budget": "200000000",
        "status": "Released",
        "tmdb_id": "320288",
        "imdb_id": "tt6565702",
        "revenue": "252442974",
        "runtime": "114",
        "tagline": "X-Men Dark Phoenix",
        "homepage": "http://darkphoenix.com",
        "popularity": "122.285",
        "vote_count": "4063",
        "vote_average": "61%",
        "release_date": "Jun 05, 2019",
        "_thumbnail_id": "554"
      },
      "short_description": {
        "short": "The X-Men face their most formidable and powerful foe when&hellip;",
        "long": "The X-Men face their most formidable and powerful foe when one of their own, Jean Grey, starts to spiral out of control. During a rescue mission in outer space, Jean is nearly killed when she's hit by a mysterious cosmic force. Once she returns home, this force not only makes her infinitely more powerful, but far more unstable. The X-Men must now band together to save her soul and battle aliens that want to use&hellip;"
      },
      "images": {
        "full": "https://wp-query.andrewrhyand.com/wp-content/uploads/2020/11/cCTJPelKGLhALq3r51A9uMonxKj.jpg",
        "medium": "https://wp-query.andrewrhyand.com/wp-content/uploads/2020/11/cCTJPelKGLhALq3r51A9uMonxKj-200x300.jpg",
        "thumbnail": "https://wp-query.andrewrhyand.com/wp-content/uploads/2020/11/cCTJPelKGLhALq3r51A9uMonxKj-150x150.jpg"
      }
    },
    {
      "id": 551,
      "title": "Enemy Lines",
      "description": "In the frozen, war torn landscape of occupied Poland during World War II, a crack team of allied commandos are sent on a deadly mission behind enemy lines to extract a rocket scientist from the hands of the Nazis.",
      "...and so on"
    }
  ]
}
```

### Default/Overriding WP_Query Args

#### Default Args

If you would like to add default `WP_Query $args` **BEFORE** the request params are applied, this is the filter to do it.  

```php
// Example
add_filter('boxybird/query/default-args', function ($args) {
    $args['posts_per_page'] = 12;

    return $args;
});
```

> Note: The above are defaults only. Meaning, if the incoming request specifies `posts_per_page`, it will override the `boxybird/query/default-args` filter defaults.

#### Override Args

If you would like to modify/remove incoming request params **BEFORE** running the `WP_Query`, this is the filter to do it.

```php
// Example.
add_filter('boxybird/query/override-args', function ($args) {
    // Don't allow more than 20 'posts_per_page'.
    if (isset($args['posts_per_page']) && $args['posts_per_page'] > 20) {
      $args['posts_per_page'] = 20;
    }

    // Always override 'post_status'
    $args['post_status'] = 'publish';

    return $args;
});
```

> Note: The above filter can be thought of as a security layer. If you never want an `$arg` to be passed to `WP_Query`, do it here! 

### Permissions Callback

If you would like to protected who has access to the `/wp-json/boxybird/wp-query/v1/args` endpoint, this is the filter.

> Reference: https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/#permissions-callback

```php
// Basic Example.
add_filter('boxybird/query/permission', function () {
  // Only logged in users have access.
  return is_user_logged_in();
});

// Example taken from the WordPress docs.
add_filter('boxybird/query/permission', function () {
  // Restrict endpoint to only users who have the edit_posts capability.
  if (!current_user_can('edit_posts')) {
    return new WP_Error('rest_forbidden', esc_html__('OMG you can not view private data.', 'my-text-domain'), ['status' => 401]);
  }

  // This is a black-listing approach. You could alternatively do this via white-listing, by returning false here and changing the permissions check.
  return true;
});
```
