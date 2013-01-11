<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Request;

use PHP\BitTorrent\Tracker\Exception\RuntimeException,
    PHP\BitTorrent\Tracker\Peer\PeerInterface,
    PHP\BitTorrent\Tracker\Peer\Peer,
    Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Class representing a request from a BitTorrent client
 *
 * @package Tracker\Http\Request
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Request extends SymfonyRequest implements RequestInterface {
    /**
     * Trust proxies (when fetching client ip)
     *
     * @var boolean
     */
    protected static $trustProxy = true;

    /**
     * The current peer
     *
     * @var PeerInterface
     */
    private $peer;

    /**
     * {@inheritdoc}
     */
    public function getInfoHash() {
        return $this->query->get('info_hash');
    }

    /**
     * {@inheritdoc}
     */
    public function getPeer() {
        if ($this->peer === null) {
            $this->peer = new Peer();
            $this->peer->id($this->query->get('peer_id'))
                       ->ip($this->getPeerIp())
                       ->port($this->query->get('port'))
                       ->left($this->query->get('left'));
        }

        return $this->peer;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvent() {
        return $this->query->has('event') ? $this->query->get('event') : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getNoPeerId() {
        return $this->query->has('no_peer_id') ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompact() {
        return $this->query->has('compact') ? true : false;
    }

    /**
     * Get the IP address of the peer
     *
     * @return string
     */
    private function getPeerIp() {
        if ($this->query->has('ip')) {
            return $this->query->get('ip');
        }

        return $this->getClientIp();
    }
}
