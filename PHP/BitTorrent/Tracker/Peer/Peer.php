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
 * This class represents a peer that is connected to the BitTorrent tracker
 *
 * @package Tracker\Peer
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Peer implements PeerInterface {
    /**
     * Ip address of the peer
     *
     * @var string
     */
    private $ip;

    /**
     * ID of the peer
     *
     * @var string
     */
    private $id;

    /**
     * Port number the peer uses
     *
     * @var int
     */
    private $port;

    /**
     * The amount of bytes the peer has left to download
     *
     * @var int
     */
    private $left;

    /**
     * {@inheritdoc}
     */
    public function ip($ip = null) {
        if ($ip === null) {
            return $this->ip;
        }

        $this->ip = $ip;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function id($id = null) {
        if ($id === null) {
            return $this->id;
        }

        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function port($port = null) {
        if ($port === null) {
            return $this->port;
        }

        $this->port = (int) $port;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function left($left = null) {
        if ($left === null) {
            return $this->left;
        }

        $this->left = (int) $left;

        return $this;
    }
}
