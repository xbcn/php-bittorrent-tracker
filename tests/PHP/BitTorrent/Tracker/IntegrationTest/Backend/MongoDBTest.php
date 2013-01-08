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

use PHP\BitTorrent\Tracker\Backend\MongoDB,
    MongoClient;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class MongoDBTest extends BackendTests {
    /**
     * @var string
     */
    private $dbName = 'phpBitTorrentIntegrationTestDb';

    /**
     * {@inheritdoc}
     */
    protected function getBackend() {
        return new MongoDB(array(
            'database' => $this->dbName,
        ));
    }

    /**
     * Set up the backend
     */
    public function setUp() {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('pecl/mongo is required to run this test');
        }

        $mongo = new MongoClient();
        $mongo->selectDB($this->dbName)->drop();

        parent::setUp();
    }

    /**
     * Tear down the backend
     */
    public function tearDown() {
        if (extension_loaded('mongo')) {
            $mongo = new MongoClient();
            $mongo->selectDB($this->dbName)->drop();
        }

        parent::tearDown();
    }
}
