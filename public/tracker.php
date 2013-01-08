<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker;

use Exception;

if (!defined('MAIN_EVENT')) {
    define('MAIN_EVENT', 'tracker.announce');
}

try {
    // Fetch the configuration
    $configPath = defined('CONFIG_PATH') ? CONFIG_PATH : __DIR__ . '/../config/config.default.php';

    $config = require $configPath;

    $application = new Application($config);
    $application->run(MAIN_EVENT);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    trigger_error('Uncaught Exception with message: ' . $e->getMessage(), E_USER_ERROR);
}
