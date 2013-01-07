<?php
/**
 * This file is part of the PHP BitTorrent
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
     * Whether or not the peer is a seed
     *
     * @var boolean
     */
    private $seed = false;

    /**
     * {@inheritdoc}
     */
    public function setIp($ip) {
        $this->ip = $ip;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIp() {
        return $this->ip;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId() {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setPort($port) {
        $this->port = (int) $port;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeed($flag = null) {
        if ($flag !== null) {
            $this->seed = (boolean) $flag;

            return $this;
        }

        return $this->seed;
    }
}
