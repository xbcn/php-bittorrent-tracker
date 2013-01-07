<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\UnitTest\Peer;

use PHP\BitTorrent\Tracker\Peer\Peer;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class PeerTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var PHP\BitTorrent\Tracker\Peer
     */
    private $peer;

    /**
     * Set up the peer instance
     */
    public function setUp() {
        $this->peer = new Peer();
    }

    /**
     * Tear down the peer instance
     */
    public function tearDown() {
        $this->peer = null;
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Peer\Peer::setIp
     * @covers PHP\BitTorrent\Tracker\Peer\Peer::getIp
     */
    public function testSetGetIp() {
        $this->assertNull($this->peer->getIp());
        $ip = '127.0.0.1';
        $this->assertSame($this->peer, $this->peer->setIp($ip));
        $this->assertSame($ip, $this->peer->getIp());
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Peer\Peer::setId
     * @covers PHP\BitTorrent\Tracker\Peer\Peer::getId
     */
    public function testSetGetId() {
        $this->assertNull($this->peer->getId());
        $id = str_repeat('a', 20);
        $this->assertSame($this->peer, $this->peer->setId($id));
        $this->assertSame($id, $this->peer->getId());
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Peer\Peer::setPort
     * @covers PHP\BitTorrent\Tracker\Peer\Peer::getPort
     */
    public function testSetGetPort() {
        $this->assertNull($this->peer->getPort());
        $port = 6666;
        $this->assertSame($this->peer, $this->peer->setPort($port));
        $this->assertSame($port, $this->peer->getPort());
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Peer\Peer::isSeed
     */
    public function testIsSeed() {
        $this->assertFalse($this->peer->isSeed());
        $this->assertSame($this->peer, $this->peer->isSeed(true));
        $this->assertTrue($this->peer->isSeed());
        $this->assertSame($this->peer, $this->peer->isSeed(false));
        $this->assertFalse($this->peer->isSeed());
    }
}
