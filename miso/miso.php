<?php
/**
 * Plugin Name:  Miso Integration
 * Description:  A plugin to integrate Miso with WordPress
 * Version:      1.0.0
 * @package      Miso_Integration
 */

require_once __DIR__ . '/vendor/autoload.php';

// Miso client for API calls
require_once __DIR__ . '/client.php';

// operations
require_once __DIR__ . '/operations.php';

// filters: including function that transform WP post to Miso record
require_once __DIR__ . '/filters.php';

// actions: automatic cascade post updates to Miso catalog
require_once __DIR__ . '/actions.php';

// adds commands to WP CLI
require_once __DIR__ . '/wp-cli.php';

// adds admin pages
require_once __DIR__ . '/admin.php';
