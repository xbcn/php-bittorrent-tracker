<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Event;

/**
 * Event listener interface
 *
 * @package Event
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface Listener {
    /**
     * Attach to the event manager
     *
     * @param Manager $manager The event manager
     */
    function attach(Manager $manager);
}
