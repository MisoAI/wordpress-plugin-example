<?php

namespace Miso;

use Miso\Client;

class Operations {

    public static function sync_posts($args, $ctx = []) {

        $api_key = get_option('miso_settings')['api_key'] ?? null;
        if (!$api_key) {
            throw new \Exception('API key is required');
        }

        $miso = new Client([
            'api_key' => $api_key,
        ]);

        $logger = $ctx['logger'] ?? new NopLogger();
        $logger->log('Starting full sync...');

        $page = 1;
        $uploaded = 0;
        $wpIds = [];
        $records = [];

        do {
            // get paged posts
            $posts = new \WP_Query(array(
                'post_type' => 'post',
                'posts_per_page' => 100,
                'paged' => $page,
                'post_status' => 'publish',
            ));
            if (!$posts->have_posts()) {
                break;
            }

            // transform posts to Miso records
            foreach ($posts->posts as $post) {
                $record = (array) apply_filters('post_to_record', $post);
                $records[] = $record;

                // keep track of post IDs
                $wpIds[] = $record['product_id'];

                // send to Miso API
                if (count($records) >= 20) {
                    $miso->products->upload($records);
                    $uploaded += count($records);
                    $records = [];
                }
            }

            $page++;

        } while (true);

        // send to Miso API
        if (count($records) > 0) {
            $miso->products->upload($records);
            $uploaded += count($records);
        }

        // compare ids and delete records that no longer exist
        $misoIds = $miso->products->ids();
        $idsToDelete = array_diff($misoIds, $wpIds);
        if (count($idsToDelete) > 0) {
            $miso->products->delete($idsToDelete);
        }

        $logger->success('Full sync complete. Uploaded ' . $uploaded . ' records. Deleted ' . count($idsToDelete) . ' records.');
    }

}

class NopLogger {

    public function success($message) {}

    public function error($message) {}

    public function log($message) {}

    public function debug($message) {}

}