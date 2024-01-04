<?php

use Miso\Operations;

if (!(defined('WP_CLI') && WP_CLI)) {
    return;
}

WP_CLI::add_command('miso', 'WP_CLI_MisoCommand');

class WP_CLI_MisoCommand {

    public function fullsync($args, $assoc_args) {

        $logger = new WP_CLI_Logger();

        try {
            Operations::sync_posts($args, [
                'logger' => $logger,
            ]);
        } catch (\Exception $e) {
            WP_CLI::error($e->getMessage());
        }

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
