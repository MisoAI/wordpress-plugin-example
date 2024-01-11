<?php

namespace Miso;

use Miso\Utils;
use Miso\DataBase;

class Operations {

    public static function current_task() {
        return DataBase::current_task();
    }

    public static function recent_tasks() {
        return DataBase::recent_tasks();
    }

    public static function enqueue_sync_posts($args = []) {

        // TODO: bounce if another task is running

        $task_id = Utils::uuidv4();

        as_enqueue_async_action('miso_sync_posts_hook', [[
            'task_id' => $task_id,
        ]]);

        $task = [
            'id' => $task_id,
            'type' => 'sync_posts',
            'args' => $args,
            'data' => [],
            'status' => 'queued',
        ];

        self::update_task_progress($task);
    }

    public static function sync_posts($args) {

        // TODO: bounce if another task is running

        $task = [
            'id' => $args['task_id'] ?? Utils::uuidv4(),
            'type' => 'sync_posts',
            'args' => $args,
            'data' => [],
            'status' => 'started',
        ];

        self::update_task_progress($task);

        $query = $args['query'] ?? [
            'post_type' => 'post',
            'post_status' => 'publish',
        ];

        try {
            $miso = miso_create_client();

            $total = (new \WP_Query($query))->found_posts;
            $task['status'] = 'running';
            $task['data']['phase'] = 'upload';
            $task['data']['total'] = $total;
            $task['data']['uploaded'] = 0;

            self::update_task_progress($task);

            $page = 1;
            $wpIds = [];
            $records = [];

            do {
                // get paged posts
                $posts = new \WP_Query(array_merge($query, [
                    'posts_per_page' => 100,
                    'paged' => $page,
                ]));
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
                        $task['data']['uploaded'] += count($records);
                        self::update_task_progress($task);
                        $records = [];
                    }
                }

                $page++;
            } while (true);

            // send to Miso API
            if (count($records) > 0) {
                $miso->products->upload($records);
                $task['data']['uploaded'] += count($records);
                self::update_task_progress($task);
            }

            // compare ids and delete records that no longer exist
            $task['data']['phase'] = 'delete';
            self::update_task_progress($task);

            $misoIds = $miso->products->ids();
            $idsToDelete = array_diff($misoIds, $wpIds);
            $deleted = count($idsToDelete);
            if ($deleted > 0) {
                $miso->products->delete($idsToDelete);
            }

            $task['data']['deleted'] = $deleted;
            $task['data']['phase'] = 'done';
            $task['status'] = 'done';
            self::update_task_progress($task);

        } catch (\Exception $e) {
            $task['status'] = 'failed';
            $task['data']['error'] = $e->getMessage();
            self::update_task_progress($task);
            throw $e;
        }
    }

    protected static function update_task_progress($task) {
        DataBase::update_task($task);
        do_action('miso_task_progress', $task);
    }

}

add_action('miso_sync_posts_hook', [__NAMESPACE__ . '\Operations', 'sync_posts'], 10, 1);
