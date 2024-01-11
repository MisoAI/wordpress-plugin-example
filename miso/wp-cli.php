<?php

use Miso\Operations;

if (!(defined('WP_CLI') && WP_CLI)) {
    return;
}

WP_CLI::add_command('miso', 'WP_CLI_MisoCommand');

class WP_CLI_MisoCommand {

    public function sync($args, $assoc_args) {

        //$logger = new WP_CLI_Logger();

        add_action('miso_task_progress', 'miso_wp_cli_log');
        try {
            Operations::sync_posts([]);
        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        } finally {
            remove_action('miso_task_progress', 'miso_wp_cli_log');
        }

    }

    public function debug($args, $assoc_args) {

        $id = $assoc_args['id'];
        $type = $assoc_args['type'];
        $query = $id ?
            ['p' => intval($id), 'posts_per_page' => 1] :
            ['post_type' => $type ?? 'post', 'posts_per_page' => 1, 'post_status' => 'publish'];
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

function miso_wp_cli_log($task) {
    switch ($task['status']) {
        case 'started':
            WP_CLI::line("Sync posts started.");
            break;
        case 'running':
            $data = $task['data'] ?? [];
            $total = $data['total'] ?? 0;
            $uploaded = $data['uploaded'] ?? 0;
            $deleted = $data['deleted'] ?? 0;
            switch ($data['phase']) {
                case 'upload':
                    WP_CLI::line("Uploaded {$uploaded}/{$total} records...");
                    break;
                case 'delete':
                    WP_CLI::line("Deleted {$deleted} records...");
                    break;
            }
            break;
        case 'done':
            WP_CLI::success("Sync posts completed.");
            break;
    }
}

class WP_CLI_Logger {

    public function success($message) {
        WP_CLI::success($message);
    }

    public function error($message) {
        WP_CLI::error($message);
    }

    public function log($message) {
        WP_CLI::line($message);
    }

    public function debug($message) {
        // TODO: take constructor options
        WP_CLI::line($message);
    }

}
