<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\UnitTest\Request;

use PHP\BitTorrent\Tracker\Request\Request,
    PHP\BitTorrent\Tracker\Request\RequestInterface;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class RequestTest extends \PHPUnit_Framework_TestCase {
    /**
     * Query
     *
     * @var array
     */
    private $query = array(
        'info_hash' => 'aaaaaaaaaaaaaaaaaaaa',
        'peer_id' => 'aaaaaaaaaaaaaaaaaaaa',
        'ip' => '1.2.3.4',
        'port' => 123,
        'uploaded' => 234,
        'downloaded' => 345,
        'left' => 456,
        'event' => RequestInterface::EVENT_NONE,
    );

    /**
     * @covers PHP\BitTorrent\Tracker\Request\Request::getInfoHash
     * @covers PHP\BitTorrent\Tracker\Request\Request::getEvent
     * @covers PHP\BitTorrent\Tracker\Request\Request::getNoPeerId
     * @covers PHP\BitTorrent\Tracker\Request\Request::getCompact
     * @covers PHP\BitTorrent\Tracker\Request\Request::getPeer
     * @covers PHP\BitTorrent\Tracker\Request\Request::getPeerIp
     */
    public function testAccessors() {
        $request = new Request($this->query);

        $this->assertSame($this->query['info_hash'], $request->getInfoHash());
        $this->assertSame($this->query['event'], $request->getEvent());
        $this->assertFalse($request->getNoPeerId());
        $this->assertFalse($request->getCompact());
        $peer = $request->getPeer();
        $this->assertInstanceOf('PHP\BitTorrent\Tracker\Peer\PeerInterface', $peer);
        $this->assertSame($this->query['peer_id'], $peer->getId());
        $this->assertSame($this->query['port'], $peer->getPort());
        $this->assertSame($this->query['ip'], $peer->getIp());
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Request\Request::getNoPeerId
     */
    public function testGetNoPeerId() {
        $request = new Request(array('no_peer_id' => 1));
        $this->assertTrue($request->getNoPeerId());
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Request\Request::getCompact
     */
    public function testGetCompact() {
        $request = new Request(array('compact' => 1));
        $this->assertTrue($request->getCompact());
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Request\Request::getInfoHash
     */
    public function testGetInfoHashWithMultipleInfoHashesInTheRequest() {
        $query = $this->query;
        $query['info_hash'] = array(
            'hash#1',
            'hash#2',
            'hash#3',
        );
        $request = new Request($query);
        $infoHash = $request->getInfoHash();

        $this->assertInternalType('array', $infoHash);
        $this->assertCount(3, $infoHash);

        $this->assertSame($query['info_hash'][0], $infoHash[0]);
        $this->assertSame($query['info_hash'][1], $infoHash[1]);
        $this->assertSame($query['info_hash'][2], $infoHash[2]);
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Request\Request::getPeer
     */
    public function testGetPeerWhenPeerIsSeed() {
        $query = array(
            'info_hash' => 'aaaaaaaaaaaaaaaaaaaa',
            'peer_id' => 'aaaaaaaaaaaaaaaaaaaa',
            'ip' => '1.2.3.4',
            'port' => 123,
            'uploaded' => 0,
            'downloaded' => 0,
            'left' => 0,
            'event' => RequestInterface::EVENT_STARTED,
        );

        $request = new Request($query);
        $peer = $request->getPeer();
        $this->assertTrue($peer->isSeed());
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Request\Request::getPeer
     * @covers PHP\BitTorrent\Tracker\Request\Request::getPeerIp
     */
    public function testCanDetectIpWithoutQueryParameter() {
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';

        $query = array(
            'info_hash' => 'aaaaaaaaaaaaaaaaaaaa',
            'peer_id' => 'aaaaaaaaaaaaaaaaaaaa',
            'port' => 123,
            'uploaded' => 234,
            'downloaded' => 345,
            'left' => 456,
            'event' => RequestInterface::EVENT_NONE,
        );

        $request = new Request($query, array(), array(), array(), array(), array('REMOTE_ADDR' => '1.2.3.4'));

        $peer = $request->getPeer();
        $this->assertSame('1.2.3.4', $peer->getIp());
    }
}
