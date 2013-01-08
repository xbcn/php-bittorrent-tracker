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
     * Set the peer id
     *
     * @param string $id The id to set
     * @return PeerInterface
     */
    function setId($id);

    /**
     * Get the peer id
     *
     * @return string
     */
    function getId();

    /**
     * Set the peer ip address
     *
     * @param string $ip The ip address to set
     * @return PeerInterface
     */
    function setIp($ip);

    /**
     * Get the ip address
     *
     * @return string
     */
    function getIp();

    /**
     * Set the port the client uses
     *
     * @param int $port The port
     * @return PeerInterface
     */
    function setPort($port);

    /**
     * Get the port the client uses
     *
     * @return int
     */
    function getPort();

    /**
     * Set or get the seed flag
     *
     * @param boolean $flag
     * @return PeerInterface|boolean
     */
    function isSeed($flag = null);
}
