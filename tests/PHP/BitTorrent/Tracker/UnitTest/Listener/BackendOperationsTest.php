<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\UnitTest\Event\Listener;

use PHP\BitTorrent\Tracker\Event\Listener\BackendOperations,
    PHP\BitTorrent\Tracker\Event\EventInterface,
    PHP\BitTorrent\Tracker\Request\RequestInterface,
    PHP\BitTorrent\Tracker\Backend\BackendInterface;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class BackendOperationsTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var BackendOperations
     */
    private $listener;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var EventInterface
     */
    private $event;

    /**
     * @var BackendInterface
     */
    private $backend;

    /**
     * Set up the listener
     */
    public function setUp() {
        $this->backend = $this->getMock('PHP\BitTorrent\Tracker\Backend\BackendInterface');
        $this->request = $this->getMock('PHP\BitTorrent\Tracker\Request\RequestInterface');
        $this->event = $this->getMock('PHP\BitTorrent\Tracker\Event\EventInterface');
        $this->event->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->event->expects($this->any())->method('getBackend')->will($this->returnValue($this->backend));
        $this->listener = new BackendOperations();
    }

    /**
     * Tear down the listener
     */
    public function tearDown() {
        $this->listener = null;
        $this->event = null;
        $this->request = null;
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Listener\BackendOperations::attach
     */
    public function testAttachesItselfToTheManager() {
        $manager = $this->getMockBuilder('PHP\BitTorrent\Tracker\Event\Manager')->disableOriginalConstructor()->getMock();
        $manager->expects($this->atLeastOnce())->method('attach')->with($this->isType('string'), $this->isType('callable'))->will($this->returnSelf());
        $this->listener->attach($manager);
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Listener\BackendOperations::torrentRegister
     */
    public function testCanRegisterTorrent() {
        $infoHash = 'hash';
        $this->request->expects($this->once())->method('getInfoHash')->will($this->returnValue($infoHash));
        $this->backend->expects($this->once())->method('registerTorrent')->with($infoHash);
        $this->listener->torrentRegister($this->event);
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Listener\BackendOperations::peerDelete
     */
    public function testCanDeletePeer() {
        $infoHash = 'hash';
        $peer = $this->getMock('PHP\BitTorrent\Tracker\Peer\PeerInterface');
        $this->request->expects($this->once())->method('getInfoHash')->will($this->returnValue($infoHash));
        $this->request->expects($this->once())->method('getPeer')->will($this->returnValue($peer));
        $this->backend->expects($this->once())->method('deleteTorrentPeer')->with($infoHash, $peer);
        $this->listener->peerDelete($this->event);
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Listener\BackendOperations::peerComplete
     */
    public function testCanMarkPeerAsSeed() {
        $infoHash = 'hash';
        $peer = $this->getMock('PHP\BitTorrent\Tracker\Peer\PeerInterface');
        $this->request->expects($this->once())->method('getInfoHash')->will($this->returnValue($infoHash));
        $this->request->expects($this->once())->method('getPeer')->will($this->returnValue($peer));
        $this->backend->expects($this->once())->method('torrentPeerComplete')->with($infoHash, $peer);
        $this->listener->peerComplete($this->event);
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Listener\BackendOperations::peerRegister
     */
    public function testCanRegisterPeer() {
        $infoHash = 'hash';
        $peer = $this->getMock('PHP\BitTorrent\Tracker\Peer\PeerInterface');
        $this->request->expects($this->once())->method('getInfoHash')->will($this->returnValue($infoHash));
        $this->request->expects($this->once())->method('getPeer')->will($this->returnValue($peer));
        $this->backend->expects($this->once())->method('registerTorrentPeer')->with($infoHash, $peer);
        $this->listener->peerRegister($this->event);
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Listener\BackendOperations::peerUpdate
     */
    public function testCanUpdatePeer() {
        $infoHash = 'hash';
        $peer = $this->getMock('PHP\BitTorrent\Tracker\Peer\PeerInterface');
        $this->request->expects($this->once())->method('getInfoHash')->will($this->returnValue($infoHash));
        $this->request->expects($this->once())->method('getPeer')->will($this->returnValue($peer));
        $this->backend->expects($this->once())->method('updateTorrentPeer')->with($infoHash, $peer);
        $this->listener->peerUpdate($this->event);
    }
}
