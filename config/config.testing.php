<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

use PHP\BitTorrent\Tracker\Backend,
    PHP\BitTorrent\Tracker\Event\Listener;

/**
 * Require composer autoloader
 */
require __DIR__ . '/../vendor/autoload.php';

return array(
    'tracker' => array(
        'autoRegister' => true,
    ),

    'backend' => function() {
        return new Backend\MongoDB(array(
            'database' => 'phpBitTorrentTesting',
        ));
    },

    'eventListeners' => array(
        'backendOperations' => function() { return new Listener\BackendOperations(); },
        'responseWriter' => function() { return new Listener\ResponseWriter(); },
        'requestValidator' => function() { return new Listener\RequestValidator(); },
    ),
);
