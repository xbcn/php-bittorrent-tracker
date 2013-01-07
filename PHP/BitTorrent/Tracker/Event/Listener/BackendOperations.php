<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Event\Listener;

use PHP\BitTorrent\Tracker\Event;

/**
 * Execute backend operations
 *
 * @package Event\Listeners
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class BackendOperations implements Event\Listener {
    /**
     * Attach to the event manager
     *
     * @param Event\Manager $manager The event manager
     */
    public function attach(Event\Manager $manager) {
        $manager->attach('peer.delete', array($this, 'peerDelete'))
                ->attach('peer.update', array($this, 'peerUpdate'))
                ->attach('peer.complete', array($this, 'peerComplete'))
                ->attach('peer.register', array($this, 'peerRegister'))
                ->attach('torrent.register', array($this, 'torrentRegister'));
    }

    /**
     * Register Torrent
     *
     * @param Event\EventInterface $event The event instance
     */
    public function torrentRegister(Event\EventInterface $event) {
        $event->getBackend()->registerTorrent($event->getRequest()->getInfoHash());
    }

    /**
     * Delete peer
     *
     * @param Event\EventInterface $event The event instance
     */
    public function peerDelete(Event\EventInterface $event) {
        $request = $event->getRequest();
        $event->getBackend()->deleteTorrentPeer($request->getInfoHash(), $request->getPeer());
    }

    /**
     * Register peer
     *
     * @param Event\EventInterface $event The event instance
     */
    public function peerRegister(Event\EventInterface $event) {
        $request = $event->getRequest();
        $event->getBackend()->registerTorrentPeer($request->getInfoHash(), $request->getPeer());
    }

    /**
     * Update peer
     *
     * @param Event\EventInterface $event The event instance
     */
    public function peerUpdate(Event\EventInterface $event) {
        $request = $event->getRequest();
        $event->getBackend()->updateTorrentPeer($request->getInfoHash(), $request->getPeer());
    }

    /**
     * Peer is complete
     *
     * @param Event\EventInterface $event The event instance
     */
    public function peerComplete(Event\EventInterface $event) {
        $request = $event->getRequest();
        $event->getBackend()->torrentPeerComplete($request->getInfoHash(), $request->getPeer());
    }
}
