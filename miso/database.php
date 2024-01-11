<?php

namespace Miso;

class DataBase {

    public static function install() {
        self::create_task_table();
    }

    public static function uninstall() {
        self::drop_task_table();
    }

    public static function current_task() {
        global $wpdb;
        $table_name = self::table_name('task');
        $sql = "SELECT * FROM {$table_name} WHERE status = 'running' OR status = 'started' ORDER BY created_at DESC LIMIT 1";
        $task = $wpdb->get_row($sql, ARRAY_A);
        return $task;
    }

    public static function recent_tasks() {
        global $wpdb;
        $table_name = self::table_name('task');
        $sql = "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 10";
        $tasks = $wpdb->get_results($sql, ARRAY_A);
        return $tasks;
    }

    public static function update_task($task) {
        global $wpdb;
        $table_name = self::table_name('task');
        $wpdb->replace($table_name, array_merge($task, [
            'modified_at' => current_time('mysql'),
        ]));
    }

    protected static function create_task_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = self::table_name('task');
        $sql = "
            CREATE TABLE IF NOT EXISTS {$table_name} (
                id varchar(255) NOT NULL,
                type varchar(255) NOT NULL,
                args text NOT NULL,
                status varchar(255) NOT NULL,
                data text,
                created_at timestamp DEFAULT CURRENT_TIMESTAMP,
                modified_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate;
        ";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    protected static function drop_task_table() {
        global $wpdb;
        $table_name = self::table_name('task');
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }

    protected static function table_name($name) {
        global $wpdb;
        return $wpdb->prefix . 'miso_' . $name;
    }

}
