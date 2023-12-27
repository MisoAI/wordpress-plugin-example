<?php
/**
 * Plugin Name:  Miso Integration
 * Description:  A plugin to integrate Miso with WordPress
 * Version:      1.0.0
 * 
 * @package      Miso_Integration
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/client.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

global $miso;
$miso = new \Miso\Client($_ENV['MISO_API_KEY']);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/updates.php';
require_once __DIR__ . '/wp-cli.php';
