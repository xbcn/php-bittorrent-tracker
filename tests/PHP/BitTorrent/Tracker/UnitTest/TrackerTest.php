<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\UnitTest;

use PHP\BitTorrent\Tracker\Tracker,
    PHP\BitTorrent\Tracker\Request\RequestInterface,
    PHP\BitTorrent\Tracker\Peer\PeerInterface,
    PHP\BitTorrent\Tracker\UnitTest\Event\Listener\ListenerTests;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class TrackerTest extends ListenerTests {
    /**
     * @var Tracker
     */
    private $tracker;

    private $backend;
    private $request;
    private $response;
    private $manager;
    private $encoder;

    /**
     * {@inheritdoc}
     */
    protected function getListener() {
        return $this->tracker;
    }

    /**
     * Set up the listener
     */
    public function setUp() {
        $this->request = $this->getMock('PHP\BitTorrent\Tracker\Request\RequestInterface');
        $this->response = $this->getMock('PHP\BitTorrent\Tracker\Response\ResponseInterface');
        $this->backend = $this->getMock('PHP\BitTorrent\Tracker\Backend\BackendInterface');
        $this->manager= $this->getMockBuilder('PHP\BitTorrent\Tracker\Event\Manager')->disableOriginalConstructor()->getMock();

        $this->event = $this->getMock('PHP\BitTorrent\Tracker\Event\EventInterface');
        $this->event->expects($this->any())->method('getBackend')->will($this->returnValue($this->backend));
        $this->event->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->event->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));
        $this->event->expects($this->any())->method('getManager')->will($this->returnValue($this->manager));
        $this->encoder = $this->getMock('PHP\BitTorrent\EncoderInterface');
        $this->tracker = new Tracker(array(), $this->encoder);
    }

    /**
     * Tear down the listener
     */
    public function tearDown() {
        $this->tracker = null;
        $this->event = null;
        $this->backend = null;
        $this->request = null;
        $this->response = null;
        $this->manager = null;
        $this->encoder = null;
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Tracker::scrape
     */
    public function testSupportsScrapeWithNoInfoHashSpecified() {
        $infoHashes = array(
            str_repeat('a', 20),
            str_repeat('b', 20),
            str_repeat('c', 20),
        );
        $peer1 = $this->getMock('PHP\BitTorrent\Tracker\Peer\PeerInterface');
        $peer1->expects($this->any())->method('isSeed')->will($this->returnValue(false));
        $peer2 = $this->getMock('PHP\BitTorrent\Tracker\Peer\PeerInterface');
        $peer2->expects($this->any())->method('isSeed')->will($this->returnValue(false));
        $seed = $this->getMock('PHP\BitTorrent\Tracker\Peer\PeerInterface');
        $seed->expects($this->any())->method('isSeed')->will($this->returnValue(true));

        $this->backend->expects($this->once())->method('getAllTorrents')->will($this->returnValue($infoHashes));
        $this->backend->expects($this->at(1))->method('torrentExists')->with($infoHashes[0])->will($this->returnValue(true));
        $this->backend->expects($this->at(2))->method('getTorrentPeers')->with($infoHashes[0])->will($this->returnValue(array($peer1, $peer2)));
        $this->backend->expects($this->at(3))->method('getNumTorrentDownloads')->with($infoHashes[0])->will($this->returnValue(10));
        $this->backend->expects($this->at(4))->method('torrentExists')->with($infoHashes[1])->will($this->returnValue(false));
        $this->backend->expects($this->at(5))->method('torrentExists')->with($infoHashes[2])->will($this->returnValue(true));
        $this->backend->expects($this->at(6))->method('getTorrentPeers')->with($infoHashes[2])->will($this->returnValue(array($seed, $peer1)));
        $this->backend->expects($this->at(7))->method('getNumTorrentDownloads')->with($infoHashes[2])->will($this->returnValue(15));

        $this->encoder->expects($this->once())->method('encodeDictionary')->with(array(
            'files' => array(
                $infoHashes[0] => array(
                    'downloaded' => 10,
                    'complete' => 0,
                    'incomplete' => 2,
                ),
                $infoHashes[2] => array(
                    'downloaded' => 15,
                    'complete' => 1,
                    'incomplete' => 1,
                ),
            ),
        ))->will($this->returnValue('encoded string'));
        $this->response->expects($this->once())->method('setContent')->with('encoded string');
        $this->request->expects($this->once())->method('getInfoHash')->will($this->returnValue(null));

        $this->tracker->scrape($this->event);
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Tracker::scrape
     */
    public function testSupportsScrapeWithInfoHashesInTheQuery() {
        $query = str_repeat('a', 20);

        $this->backend->expects($this->at(0))->method('torrentExists')->with($query)->will($this->returnValue(true));
        $this->backend->expects($this->at(1))->method('getTorrentPeers')->with($query)->will($this->returnValue(array()));
        $this->backend->expects($this->at(2))->method('getNumTorrentDownloads')->with($query)->will($this->returnValue(1));

        $this->encoder->expects($this->once())->method('encodeDictionary')->with(array(
            'files' => array(
                $query => array(
                    'downloaded' => 1,
                    'complete' => 0,
                    'incomplete' => 0,
                ),
            ),
        ))->will($this->returnValue('encoded string'));
        $this->response->expects($this->once())->method('setContent')->with('encoded string');
        $this->request->expects($this->once())->method('getInfoHash')->will($this->returnValue($query));

        $this->tracker->scrape($this->event);
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Tracker::scrape
     */
    public function testSupportsScrapeWithInfoHashesInTheQueryThatDoesNotExist() {
        $query = str_repeat('a', 20);

        $this->backend->expects($this->at(0))->method('torrentExists')->with($query)->will($this->returnValue(false));

        $this->encoder->expects($this->once())->method('encodeDictionary')->with(array('files' => array()))->will($this->returnValue('encoded string'));
        $this->response->expects($this->once())->method('setContent')->with('encoded string');
        $this->request->expects($this->once())->method('getInfoHash')->will($this->returnValue($query));

        $this->tracker->scrape($this->event);
    }

    /**
     * @expectedException PHP\BitTorrent\Tracker\Exception\RuntimeException
     * @expectedExceptionMessage Torrent not found on this tracker
     * @covers PHP\BitTorrent\Tracker\Tracker::announce
     */
    public function testThrowsExceptionWhenAnnouncingATorrentThatDoesNotExistAndAutoRegisteringIsDisabled() {
        $infoHash = str_repeat('a', 20);
        $this->manager->expects($this->once())->method('trigger')->with('request.validate');
        $this->request->expects($this->once())->method('getInfoHash')->will($this->returnValue($infoHash));
        $this->backend->expects($this->once())->method('torrentExists')->with($infoHash)->will($this->returnValue(false));

        $this->tracker->announce($this->event);
    }

    public function getResponseBody() {
        $id1 = str_repeat('a', 20);
        $id2 = str_repeat('b', 20);
        $id3 = str_repeat('c', 20);
        $id4 = str_repeat('d', 20);

        $ip1 = '127.0.0.1';
        $ip2 = '127.0.0.2';
        $ip3 = '127.0.0.3';
        $ip4 = '127.0.0.4';

        $port1 = $port2 = $port3 = $port4 = 1234;

        $peer1 = $this->getPeer($id1, $ip1, $port1);
        $peer2 = $this->getPeer($id2, $ip2, $port2);
        $seed1 = $this->getSeed($id3, $ip3, $port3);
        $seed2 = $this->getSeed($id4, $ip4, $port4);

        return array(
            array(array($peer1, $peer2, $seed1, $seed2), 0, 0, array(
                'interval' => 1800,
                'complete' => 2,
                'incomplete' => 2,
                'peers' => array(
                    array('ip' => $ip1, 'port' => $port1, 'peer id' => $id1),
                    array('ip' => $ip2, 'port' => $port2, 'peer id' => $id2),
                    array('ip' => $ip3, 'port' => $port3, 'peer id' => $id3),
                    array('ip' => $ip4, 'port' => $port4, 'peer id' => $id4),
                ),
            )),

            array(array($peer1, $peer2, $seed1, $seed2), 0, 1, array(
                'interval' => 1800,
                'complete' => 2,
                'incomplete' => 2,
                'peers' =>
                    pack('Nn', ip2long($ip1), $port1) .
                    pack('Nn', ip2long($ip2), $port2) .
                    pack('Nn', ip2long($ip3), $port3) .
                    pack('Nn', ip2long($ip4), $port4)
            )),

            array(array($peer1, $peer2, $seed1), 1, 0, array(
                'interval' => 1800,
                'complete' => 1,
                'incomplete' => 2,
                'peers' => array(
                    array('ip' => $ip1, 'port' => $port1),
                    array('ip' => $ip2, 'port' => $port2),
                    array('ip' => $ip3, 'port' => $port3),
                ),
            )),
        );
    }

    /**
     * @dataProvider getResponseBody
     * @covers PHP\BitTorrent\Tracker\Tracker::announce
     * @covers PHP\BitTorrent\Tracker\Tracker::getParam
     * @covers PHP\BitTorrent\Tracker\Tracker::setParam
     * @covers PHP\BitTorrent\Tracker\Tracker::getResponseBody
     */
    public function testCanAutomaticallyRegisterTorrentsWhenATorrentIsStarted($peers, $noPeerId, $compact, $body) {
        $infoHash = str_repeat('a', 20);
        $this->manager->expects($this->at(0))->method('trigger')->with('request.validate');
        $this->manager->expects($this->at(1))->method('trigger')->with('torrent.register');
        $this->manager->expects($this->at(2))->method('trigger')->with('peer.register');
        $this->request->expects($this->any())->method('getInfoHash')->will($this->returnValue($infoHash));
        $this->backend->expects($this->any())->method('torrentExists')->with($infoHash)->will($this->returnValue(false));
        $this->request->expects($this->any())->method('getEvent')->will($this->returnValue(RequestInterface::EVENT_STARTED));
        $this->request->expects($this->any())->method('getPeer')->will($this->returnValue($this->getMock('PHP\BitTorrent\Tracker\Peer\PeerInterface')));
        $this->request->expects($this->any())->method('getNoPeerId')->will($this->returnValue($noPeerId));
        $this->request->expects($this->any())->method('getCompact')->will($this->returnValue($compact));
        $this->backend->expects($this->any())->method('getTorrentPeers')->will($this->returnValue($peers));
        $this->encoder->expects($this->once())->method('encodeDictionary')->with($body)->will($this->returnValue('encoded string'));
        $this->response->expects($this->any())->method('setContent')->with('encoded string');

        $this->tracker->setParam('autoRegister', true)
                      ->announce($this->event);
    }

    /**
     * Fetch announce events and their events
     *
     * @return array[]
     */
    public function getEvents() {
        return array(
            array(RequestInterface::EVENT_STOPPED, 'peer.delete'),
            array(RequestInterface::EVENT_COMPLETED, 'peer.complete'),
            array(RequestInterface::EVENT_NONE, 'peer.update'),
        );
    }

    /**
     * @dataProvider getEvents
     * @covers PHP\BitTorrent\Tracker\Tracker::announce
     */
    public function testSupportsAllEventsWhenAnnouncing($requestEvent, $event) {
        $infoHash = str_repeat('a', 20);
        $peer = $this->getPeer(str_repeat('a', 20), '127.0.0.1', 1234);
        $this->manager->expects($this->at(0))->method('trigger')->with('request.validate');
        $this->manager->expects($this->at(1))->method('trigger')->with($event);
        $this->request->expects($this->any())->method('getInfoHash')->will($this->returnValue($infoHash));
        $this->backend->expects($this->any())->method('torrentExists')->with($infoHash)->will($this->returnValue(true));
        $this->backend->expects($this->any())->method('torrentPeerExists')->with($infoHash, $peer)->will($this->returnValue(true));
        $this->request->expects($this->any())->method('getEvent')->will($this->returnValue($requestEvent));
        $this->request->expects($this->any())->method('getPeer')->will($this->returnValue($peer));
        $this->backend->expects($this->any())->method('getTorrentPeers')->will($this->returnValue(array()));

        $this->tracker->announce($this->event);
    }

    /**
     * @expectedException PHP\BitTorrent\Tracker\Exception\RuntimeException
     * @expectedExceptionMessage Unexpected error
     * @covers PHP\BitTorrent\Tracker\Tracker::announce
     */
    public function testThrowsExceptionWhenUpdatingAPeerThatDoesNotExist() {
        $infoHash = str_repeat('a', 20);
        $peer = $this->getPeer(str_repeat('a', 20), '127.0.0.1', 1234);
        $this->request->expects($this->any())->method('getInfoHash')->will($this->returnValue($infoHash));
        $this->backend->expects($this->any())->method('torrentExists')->with($infoHash)->will($this->returnValue(true));
        $this->backend->expects($this->any())->method('torrentPeerExists')->with($infoHash, $peer)->will($this->returnValue(false));
        $this->request->expects($this->any())->method('getEvent')->will($this->returnValue(RequestInterface::EVENT_NONE));
        $this->request->expects($this->any())->method('getPeer')->will($this->returnValue($peer));
        $this->backend->expects($this->any())->method('getTorrentPeers')->will($this->returnValue(array()));

        $this->tracker->announce($this->event);
    }

    /**
     * Get a peer mock
     *
     * @param string $id The peer id
     * @param string $ip The peer ip
     * @param int $port The peer port
     * @param boolean $seed Whether or not the peer is a seed
     * @return PeerInterface
     */
    private function getPeer($id, $ip, $port, $seed = false) {
        $peer = $this->getMock('PHP\BitTorrent\Tracker\Peer\PeerInterface');
        $peer->expects($this->any())->method('getId')->will($this->returnValue($id));
        $peer->expects($this->any())->method('getIp')->will($this->returnValue($ip));
        $peer->expects($this->any())->method('getPort')->will($this->returnValue($port));
        $peer->expects($this->any())->method('isSeed')->will($this->returnValue($seed));

        return $peer;
    }

    /**
     * Get a peer mock which is a seeder
     *
     * @param string $id The seed id
     * @param string $ip The seed ip
     * @param int $port The seed port
     * @return PeerInterface
     */
    private function getSeed($id, $ip, $port) {
        return $this->getPeer($id, $ip, $port, true);
    }
}
