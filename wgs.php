<?php

declare(strict_types=1);

/**
 * Plugin Name: WoogoStores Plugin
 * Plugin URI: https://woogostores.com
 * Description: Makes the configurations for Woogo Stores.
 * Version: 1.0.0
 * Author: Patrick Leblanc
 * Author URI: https://woogostores.com
 * License: GPL3
 */

defined('ABSPATH') || exit;

if (version_compare(PHP_VERSION, '8.0', '<')) {
    exit(sprintf('Woogostores requires PHP 8.0 or higher. Your WordPress site is using PHP %s.', PHP_VERSION));
}

require_once dirname(__FILE__).'/vendor/autoload.php';

$reportingPlugin = new \Woogostores\Plugin\Plugin(__FILE__);

$reportingPlugin->load();
