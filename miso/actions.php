<?php

// cascade save_post
function miso_update_post($id, WP_Post $post, $update) {
    if (wp_is_post_revision($id) || wp_is_post_autosave($id)) {
        return $post;
    }

    $client = Miso\miso_create_client();

    // transform to Miso record
    $record = (array) apply_filters($post->post_type.'_to_record', $post);
    
    if ($post->post_status !== 'publish') {
        // shall delete from Miso catalog
        $client->products->delete([$record['product_id']]);
    } else {
        // shall update the record
        $client->products->upload([$record]);
    }

    return $post;
}

add_action('save_post', 'miso_update_post', 10, 3);

// cascade update_post_meta
// TODO
