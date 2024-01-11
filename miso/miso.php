<?php
/**
 * Plugin Name:  Miso Integration
 * Description:  A plugin to integrate Miso with WordPress
 * Version:      1.0.0
 * @package      Miso_Integration
 */

 require_once __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
 require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/client.php';
require_once __DIR__ . '/operations.php';
require_once __DIR__ . '/database.php';

// filters: including function that transform WP post to Miso record
require_once __DIR__ . '/filters.php';

// actions: automatic cascade post updates to Miso catalog
require_once __DIR__ . '/actions.php';

// adds commands to WP CLI
require_once __DIR__ . '/wp-cli.php';

// adds admin pages
require_once __DIR__ . '/admin.php';

register_activation_hook(__FILE__, function() {
    Miso\DataBase::install();
});
register_deactivation_hook(__FILE__, function() {
    Miso\DataBase::uninstall();
});
