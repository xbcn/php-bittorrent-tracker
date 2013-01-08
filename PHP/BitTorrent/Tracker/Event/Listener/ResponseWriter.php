<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Event\Listener;

use PHP\BitTorrent\Tracker\Event,
    PHP\BitTorrent\Encoder;

/**
 * Event listener that sends the response to the client
 *
 * @package Event\Listeners
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class ResponseWriter implements Event\Listener {
    /**
     * Attach to the event manager
     *
     * @param Event\Manager $manager The event manager
     */
    public function attach(Event\Manager $manager) {
        $manager->attach('response.send', array($this, 'send'));
    }

    /**
     * Send response
     *
     * @param Event\EventInterface $event The event instance
     */
    public function send(Event\EventInterface $event) {
        $event->getResponse()->send();
    }
}
