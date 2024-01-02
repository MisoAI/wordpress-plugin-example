<?php

if (!(defined('WP_CLI') && WP_CLI)) {
    return;
}

class Miso_Command {

    public function fullsync($args, $assoc_args) {

        global $miso;

        WP_CLI::line('Starting full sync...');

        $page = 1;
        $uploaded = 0;
        $wpIds = [];
        $records = [];

        try {
            do {
                // get paged posts
                $posts = new WP_Query(array(
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
    
        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }

        WP_CLI::success('Full sync complete. Uploaded ' . $uploaded . ' records. Deleted ' . count($idsToDelete) . ' records.');
    }

    public function debug($args, $assoc_args) {

        $id = $assoc_args['id'];
        $type = $assoc_args['type'];
        $query = $id ?
            array('p' => intval($id), 'posts_per_page' => 1) :
            array('post_type' => $type ?? 'post', 'posts_per_page' => 1, 'post_status' => 'publish');
        WP_CLI::line("\n[Query]");
        var_dump($query);

        $posts = new WP_Query($query);
        $post = $posts->posts[0];
        WP_CLI::line("\n[Post]");
        var_dump($post);

        $record = (array) apply_filters('post_to_record', $post);
        WP_CLI::line("\n[Record]");
        var_dump($record);
    }

}

WP_CLI::add_command('miso', 'Miso_Command');
