<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Request;

use PHP\BitTorrent\Tracker\Peer\PeerInterface;

/**
 * Request interface
 *
 * @package Tracker\Http\Request
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface RequestInterface {
    /**#@+
     * Event types the client can send
     *
     * @var string
     */
    const EVENT_STARTED   = 'started';
    const EVENT_COMPLETED = 'completed';
    const EVENT_STOPPED   = 'stopped';
    const EVENT_NONE      = '';
    /**#@-*/

    /**
     * Get the info hash(es)
     *
     * @return string|array
     */
    function getInfoHash();

    /**
     * Get the peer that made the request
     *
     * @return PeerInterface
     */
    function getPeer();

    /**
     * Get the event the client is triggering
     *
     * This method must return one of the defined event constants.
     *
     * @return string
     */
    function getEvent();

    /**
     * Get the no_peer_id parameter from the client
     *
     * @return boolean
     */
    function getNoPeerId();

    /**
     * Get the compact parameter from the client
     *
     * @return boolean
     */
    function getCompact();
}
