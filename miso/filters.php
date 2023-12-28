<?php
function miso_post_to_record(WP_Post $post) {

    $tags = array_map(function (WP_Term $term) {
        return $term->name;
    }, wp_get_post_terms($post->ID, 'post_tag'));

    // TODO: more properties
    return [
        'product_id' => strval($post->ID),
        'type' => 'post',
        'title' => $post->post_title,
        'html' => $post->post_content,
        'tags' => $tags,
        'url' => get_permalink($post->ID),
    ];
}

add_filter('post_to_record', 'miso_post_to_record');
