<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\IntegrationTest\Event\Listener;

use PHP\BitTorrent\Tracker\Event\Listener\UserStats,
    PHP\BitTorrent\Tracker\UnitTest\Event\Listener\ListenerTests,
    PHP\BitTorrent\Tracker\Event,
    PHP\BitTorrent\Tracker\Request\Request,
    MongoClient;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class UserStatsTest extends ListenerTests {
    /**
     * @var UserStats
     */
    private $listener;

    /**
     * @var string
     */
    private $db = 'phpBitTorrentTest';

    /**
     * {@inheritdoc}
     */
    protected function getListener() {
        return $this->listener;
    }

    /**
     * Set up the listener
     */
    public function setUp() {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('pecl/mongo is required to run this test');
        }

        $mongo = new MongoClient();
        $mongo->selectDB($this->db)->drop();

        $this->listener = new UserStats(array(
            'database' => $this->db,
            'collection' => 'stats'
        ));
    }

    /**
     * Tear down the listener
     */
    public function tearDown() {
        if (extension_loaded('mongo')) {
            $mongo = new MongoClient();
            $mongo->selectDB($this->db)->drop();
        }

        $this->listener = null;
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Listener\UserStats::updateUserStats
     * @covers PHP\BitTorrent\Tracker\Event\Listener\UserStats::getMongoClient
     * @covers PHP\BitTorrent\Tracker\Event\Listener\UserStats::getCollection
     * @covers PHP\BitTorrent\Tracker\Event\Listener\UserStats::decode
     */
    public function testUpdatesUserStatsWhenRequestHasSufficientData() {
        $mongo = new MongoClient();
        $this->assertNull($mongo->selectDB($this->db)->selectCollection('stats')->findOne(array('userId' => 1)));

        $request = Request::create('http://tracker');
        $request->query->set('userId', 1);
        $request->query->set('info_hash', 'aaaaaaaaaaaaaaaaaaaa');
        $request->query->set('downloaded', 123);
        $request->query->set('uploaded', 456);


        $event = new Event\Event(
            'peer.update',
            $request,
            $this->getMock('PHP\BitTorrent\Tracker\Response\Response'),
            $this->getMock('PHP\BitTorrent\Tracker\Backend\BackendInterface'),
            $this->getMockBuilder('PHP\BitTorrent\Tracker\Event\Manager')->disableOriginalConstructor()->getMock()
        );
        $this->listener->updateUserStats($event);

        $row = $mongo->selectDB($this->db)->selectCollection('stats')->findOne(array('userId' => 1));
        $this->assertSame('6161616161616161616161616161616161616161', $row['infoHash']);
        $this->assertSame(123, $row['downloaded']);
        $this->assertSame(456, $row['uploaded']);
    }
}
