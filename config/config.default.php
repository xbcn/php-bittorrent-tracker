<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
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

$config = array(
    /**
     * Tracker configuration
     *
     * @var array
     */
    'tracker' => array(
        // Automatically register torrents that does not exist on the tracker
        'autoRegister' => false,

        // Interval in seconds that the client should wait between sending regular requests to the
        // tracker
        'interval' => 1800,
    ),

    /**
     * Backend adapter
     *
     * See the different backend implementations for possible configuration parameters. The value
     * must be set to a closure returning an instance of
     * PHP\BitTorrent\Tracker\Backend\BackendInterface, or an implementation of said interface.
     *
     * @var PHP\BitTorrent\Tracker\Backend\BackendInterface|Closure
     */
    'backend' => function() {
        return new Backend\MongoDB();
    },

    /**
     * Event listeners
     *
     * An associative array where the keys are short names for the event listeners (not really used
     * for anything, but exists so you can override/unset some helpers from tracker.config.php).
     * The values of each element in this array can be one of the following:
     *
     * 1) An instance of the PHP\BitTorrent\Tracker\Event\Listener interface
     *
     * 2) A closure returning an instance of the
     *    PHP\BitTorrent\Tracker\Event\Listener interface
     *
     * 3) An array with the following keys:
     *
     *   - events (required)
     *   - callback (required)
     *   - priority
     *
     *   where 'events' is an array of events that 'callback' will subscribe to. If you have a
     *   single callback that subscribes to several events, and you want to use different priorities
     *   for the events, simply specify an associative array where the keys are the event names, and
     *   the values are the priorities. If you use this method, the 'priority' key will be ignored.
     *
     *   'callback' is any callable function. The function will receive a single argument, which is
     *   an instance of PHP\BitTorrent\Tracker\Event\EventInterface.
     *
     *   'priority' is the priority of your callback. This defaults to 1 (low priority). The
     *   priority can also be a negative number if you want your listeners to be triggered after
     *   the internal event listeners.
     *
     * @var array
     */
    'eventListeners' => array(
        'backendOperations' => function() { return new Listener\BackendOperations(); },
        'responseWriter' => function() { return new Listener\ResponseWriter(); },
        'requestValidator' => function() { return new Listener\RequestValidator(); },
    ),
);

if (file_exists(__DIR__ . '/config.php')) {
    $config = array_replace_recursive($config, require __DIR__ . '/config.php');
}

return $config;
