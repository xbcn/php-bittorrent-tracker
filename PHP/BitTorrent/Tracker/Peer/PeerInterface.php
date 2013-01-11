<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Peer;

/**
 * Peer interface
 *
 * @package Tracker\Peer
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface PeerInterface {
    /**
     * Set or get the id
     *
     * @param string|null $id The id to set
     * @return string|PeerInterface
     */
    function id($id = null);

    /**
     * Set or get the peer ip address
     *
     * @param string|null $ip The ip address to set
     * @return string|PeerInterface
     */
    function ip($ip = null);

    /**
     * Set or get the port the client uses
     *
     * @param int|null $port The port
     * @return int|PeerInterface
     */
    function port($port = null);

    /**
     * Set or get the amount of bytes the peer has left to download
     *
     * @param int|null $left Number of bytes
     * @return int|PeerInterface
     */
    function left($left = null);
}
