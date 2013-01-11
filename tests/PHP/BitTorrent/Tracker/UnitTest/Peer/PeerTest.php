<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
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
     * @covers PHP\BitTorrent\Tracker\Peer\Peer::ip
     */
    public function testCanSetAndGetIp() {
        $this->assertNull($this->peer->ip());
        $ip = '127.0.0.1';
        $this->assertSame($this->peer, $this->peer->ip($ip));
        $this->assertSame($ip, $this->peer->ip());
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Peer\Peer::id
     */
    public function testCanSetAndGetId() {
        $this->assertNull($this->peer->id());
        $id = str_repeat('a', 20);
        $this->assertSame($this->peer, $this->peer->id($id));
        $this->assertSame($id, $this->peer->id());
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Peer\Peer::port
     */
    public function testCanSetAndGetPort() {
        $this->assertNull($this->peer->port());
        $port = 6666;
        $this->assertSame($this->peer, $this->peer->port($port));
        $this->assertSame($port, $this->peer->port());
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Peer\Peer::left
     */
    public function testCanSetAndGetLeft() {
        $this->assertNull($this->peer->left());
        $left = 123;
        $this->assertSame($this->peer, $this->peer->left($left));
        $this->assertSame($left, $this->peer->left());
    }
}
