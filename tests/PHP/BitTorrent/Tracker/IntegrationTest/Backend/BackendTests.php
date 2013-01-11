<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\IntegrationTest\Backend;

use PHP\BitTorrent\Tracker\Peer\Peer,
    PHP\BitTorrent\Tracker\Peer\PeerInterface,
    PHP\BitTorrent\Tracker\Backend\BackendInterface;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
abstract class BackendTests extends \PHPUnit_Framework_TestCase {
    /**
     * @var BackendInterface
     */
    private $backend;

    /**
     * Get the backend we want to test
     *
     * @return BackendInterface
     */
    abstract protected function getBackend();

    /**
     * Set up the backend
     */
    public function setUp() {
        $this->backend = $this->getBackend();
    }

    /**
     * Tear down the backend
     */
    public function tearDown() {
        $this->backend = null;
    }

    /**
     * Get a random info hash
     *
     * @return string
     */
    private function getInfoHash() {
        return substr('infoHash_' . sha1(uniqid('', true)), 0, 20);
    }

    /**
     * Get a random peer id
     *
     * @return string
     */
    private function getPeerId() {
        return substr('peerId_' . sha1(uniqid('', true)), 0, 20);
    }

    /**
     * Get a peer mock
     *
     * @param boolean $setId Whether or not to set the peer id
     * @param boolean $left How much the peer has left to download
     * @return PeerInterface
     */
    private function getPeer($setId = true, $left = 123) {
        $peer = $this->getMock('PHP\BitTorrent\Tracker\Peer\PeerInterface');

        if ($setId) {
            $peer->expects($this->any())->method('id')->will($this->returnValue($this->getPeerId()));
        }

        $peer->expects($this->any())->method('ip')->will($this->returnValue('127.0.0.1'));
        $peer->expects($this->any())->method('port')->will($this->returnValue(123));
        $peer->expects($this->any())->method('left')->will($this->returnValue($left));

        return $peer;
    }

    public function testTorrentExistsWithANonExistingTorrent() {
        $this->assertFalse($this->backend->torrentExists('random hash value that does not exist'));
    }

    public function testInsertTorrent() {
        $hash = $this->getInfoHash();
        $this->assertSame(0, $this->backend->getNumTorrents());
        $this->assertTrue($this->backend->registerTorrent($hash));
        $this->assertTrue($this->backend->torrentExists($hash));
        $this->assertSame(1, $this->backend->getNumTorrents());
    }

    public function testInsertTorrentThatAlreadyExists() {
        $hash = $this->getInfoHash();
        $this->assertSame(0, $this->backend->getNumTorrents());
        $this->assertTrue($this->backend->registerTorrent($hash));
        $this->assertFalse($this->backend->registerTorrent($hash));
        $this->assertSame(1, $this->backend->getNumTorrents());
    }

    public function testTorrentPeerExistsWithANonExistingPeer() {
        $hash = $this->getInfoHash();
        $this->assertTrue($this->backend->registerTorrent($hash));

        $peerId = $this->getPeerId();
        $peer = $this->getPeer(false);
        $peer->expects($this->once())->method('id')->will($this->returnValue($peerId));

        $this->assertFalse($this->backend->torrentPeerExists($hash, $peer));
    }

    public function testTorrentPeerExistsWithANonExistingTorrent() {
        $this->assertFalse($this->backend->torrentPeerExists($this->getInfoHash(), $this->getPeer()));
    }

    public function testRegisterTorrentPeerWithTorrentThatDoesNotExist() {
        $hash = 'info hash that does not exist';
        $this->assertFalse($this->backend->registerTorrentPeer($hash, $this->getPeer()));
    }

    public function testRegisterTorrentPeer() {
        $hash = $this->getInfoHash();
        $peer = $this->getPeer();

        $this->assertTrue($this->backend->registertorrent($hash));
        $this->assertTrue($this->backend->registerTorrentPeer($hash, $peer));
    }

    public function testRegisterSameTorrentPeerTwice() {
        $hash = $this->getInfoHash();
        $peer = $this->getPeer();

        $this->assertTrue($this->backend->registertorrent($hash));
        $this->assertTrue($this->backend->registerTorrentPeer($hash, $peer));
        $this->assertFalse($this->backend->registerTorrentPeer($hash, $peer));
    }

    public function testDeleteTorrentThatDoesNotExist() {
        $this->assertFalse($this->backend->deleteTorrent($this->getInfoHash()));
    }

    public function testDeleteTorrent() {
        $infoHash = $this->getInfoHash();

        $this->assertTrue($this->backend->registerTorrent($infoHash));
        $this->assertTrue($this->backend->deleteTorrent($infoHash));
        $this->assertFalse($this->backend->deleteTorrent($infoHash));
    }

    public function testGetTorrentsWithNoneRegistered() {
        $this->assertSame(array(), $this->backend->getTorrents());
    }

    public function testGetTorrents() {
        $hashes = array();

        $this->assertSame(0, $this->backend->getNumTorrents());

        for ($i = 0; $i < 10; $i++) {
            $hash = $this->getInfoHash();
            $hashes[] = $hash;
            $this->assertTrue($this->backend->registerTorrent($hash));
        }

        $this->assertSame(10, $this->backend->getNumTorrents());

        $result = $this->backend->getTorrents(1, 100);

        $this->assertCount(10, $result);

        foreach ($hashes as $hash) {
            if (!in_array($hash, $result)) {
                $this->fail('Missing hash from the result');
            }
        }

        $parts = array();
        $parts[] = $this->backend->getTorrents(1, 2);
        $parts[] = $this->backend->getTorrents(2, 2);
        $parts[] = $this->backend->getTorrents(3, 2);
        $parts[] = $this->backend->getTorrents(4, 2);
        $parts[] = $this->backend->getTorrents(5, 2);

        $this->assertSame($parts[0][0], $result[0]);
        $this->assertSame($parts[0][1], $result[1]);
        $this->assertSame($parts[1][0], $result[2]);
        $this->assertSame($parts[1][1], $result[3]);
        $this->assertSame($parts[2][0], $result[4]);
        $this->assertSame($parts[2][1], $result[5]);
        $this->assertSame($parts[3][0], $result[6]);
        $this->assertSame($parts[3][1], $result[7]);
        $this->assertSame($parts[4][0], $result[8]);
        $this->assertSame($parts[4][1], $result[9]);
    }

    public function testDeleteTorrentPeerWhenTorrentDoesNotExist() {
        $this->assertFalse($this->backend->deleteTorrentPeer($this->getInfoHash(), $this->getPeer()));
    }

    public function testDeleteTorrentPeerWhenPeerDoesNotExist() {
        $hash = $this->getInfoHash();
        $this->assertTrue($this->backend->registerTorrent($hash));
        $this->assertFalse($this->backend->deleteTorrentPeer($hash, $this->getPeer()));
    }

    public function testDeleteTorrentPeer() {
        $hash = $this->getInfoHash();
        $peer = $this->getPeer();
        $this->assertTrue($this->backend->registerTorrent($hash));
        $this->assertTrue($this->backend->registerTorrentPeer($hash, $peer));
        $this->assertTrue($this->backend->deleteTorrentPeer($hash, $peer));
        $this->assertFalse($this->backend->deleteTorrentPeer($hash, $peer));
    }

    public function testGetTorrentPeersForATorrentThatDoesNotExist() {
        $this->assertSame(array(), $this->backend->getTorrentPeers($this->getInfoHash()));
    }

    public function testGetTorrentPeersWhenNoPeersAreRegistered() {
        $hash = $this->getInfoHash();
        $this->assertTrue($this->backend->registerTorrent($hash));
        $this->assertSame(array(), $this->backend->getTorrentPeers($hash));
    }

    public function testGetTorrentPeersUsingLimitAndExclusion() {
        $hash = $this->getInfoHash();
        $this->assertTrue($this->backend->registerTorrent($hash));

        $peers = array(
            array('peer' => $this->getPeer(true, 0), 'seed' => true),
            array('peer' => $this->getPeer(true, 0), 'seed' => true),
            array('peer' => $this->getPeer(true, 123), 'seed' => false),
            array('peer' => $this->getPeer(true, 123), 'seed' => false),
            array('peer' => $this->getPeer(true, 0), 'seed' => true),
        );

        foreach ($peers as $peer) {
            $this->backend->registerTorrentPeer($hash, $peer['peer']);
        }

        $this->assertCount(3, $this->backend->getTorrentPeers($hash, 3));

        $result = $this->backend->getTorrentPeers($hash, 5, $peers[0]['peer']);
        $this->assertCount(4, $result);
        $seeders = 0;

        foreach ($result as $peer) {
            if ($peer->id() == $peers[0]['peer']->id()) {
                $this->fail('Peer not excluded from the result');
            }

            if ($peer->left()) {
                $seeders++;
            }
        }

        $this->assertSame(2, $seeders, 'Too few seeders in the result set');
    }

    public function testUpdateTorrentPeerWhenTorrentDoesNotExist() {
        $this->assertFalse($this->backend->updateTorrentPeer($this->getInfoHash(), $this->getPeer()));
    }

    public function testUpdateTorrentPeerThatDoesNotExist() {
        $hash = $this->getInfoHash();
        $this->assertTrue($this->backend->registerTorrent($hash));
        $this->assertFalse($this->backend->updateTorrentPeer($hash, $this->getPeer()));
    }

    public function testTorrentPeerCompleteAndUpdateTorrentPeer() {
        $hash = $this->getInfoHash();

        // Create an actual peer
        $peerId = $this->getPeerId();

        $peer = new Peer();
        $peer->id($peerId);
        $peer->ip('127.0.0.1');
        $peer->port(123);
        $peer->left(123);

        $this->assertTrue($this->backend->registerTorrent($hash));
        $this->assertTrue($this->backend->registerTorrentPeer($hash, $peer));

        $peer->left(12);

        $this->assertTrue($this->backend->updateTorrentPeer($hash, $peer));
        $peers = $this->backend->getTorrentPeers($hash);
        $this->assertSame($peer->id(), $peers[0]->id());
        $this->assertSame(12, $peers[0]->left());

        $this->assertTrue($this->backend->torrentPeerComplete($hash, $peer));
        $peers = $this->backend->getTorrentPeers($hash);

        $this->assertSame($peer->id(), $peers[0]->id());
        $this->assertSame(0, $peers[0]->left());
    }

    public function testGetNumTorrentDownloads() {
        $hash = $this->getInfoHash();

        $this->assertTrue($this->backend->registerTorrent($hash));
        $this->assertSame(0, $this->backend->getNumTorrentDownloads($hash));

        // Create peers
        $peer1 = new Peer();
        $peer1->id($this->getPeerId());
        $peer1->ip('127.0.0.1');
        $peer1->port(123);
        $peer1->left(123);

        $peer2 = new Peer();
        $peer2->id($this->getPeerId());
        $peer2->ip('127.0.0.1');
        $peer2->port(1234);
        $peer2->left(234);

        $this->assertTrue($this->backend->registerTorrentPeer($hash, $peer1));
        $this->assertTrue($this->backend->registerTorrentPeer($hash, $peer2));

        $this->assertTrue($this->backend->torrentPeerComplete($hash, $peer1));
        $this->assertSame(1, $this->backend->getNumTorrentDownloads($hash));

        $this->assertTrue($this->backend->torrentPeerComplete($hash, $peer2));
        $this->assertSame(2, $this->backend->getNumTorrentDownloads($hash));
    }

    public function testMarkTorrentPeerCompleteWhenTorrentDoesNotExist() {
        $this->assertFalse($this->backend->torrentPeerComplete('hash that does not exist', $this->getMock('PHP\BitTorrent\Tracker\Peer\PeerInterface')));
    }

    public function testGetNumTorrentDownloadsWhenTorrentDoesNotExist() {
        $this->assertFalse($this->backend->getNumTorrentDownloads('hash that does not exist'));
    }
}
