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

use PHP\BitTorrent\Tracker\Backend\Doctrine,
    PDO;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class DoctrineTest extends BackendTests {
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * {@inheritdoc}
     */
    protected function getBackend() {
        return new Doctrine(array(
            'pdo' => $this->pdo,
        ));
    }

    /**
     * Set up method
     */
    public function setUp() {
        if (!extension_loaded('PDO')) {
            $this->markTestSkipped('PDO is required to run this test');
        }

        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite is required to run this test');
        }

        if (!class_exists('Doctrine\DBAL\DriverManager')) {
            $this->markTestSkipped('Doctrine is required to run this test');
        }

        // Create tmp tables
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->query("
            DROP TABLE IF EXISTS torrent
        ");
        $this->pdo->query("
            CREATE TABLE torrent (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                infoHash BLOB UNIQUE,
                downloaded INTEGER NOT NULL default '0'
            )
        ");
        $this->pdo->query("
            DROP TABLE IF EXISTS peer
        ");
        $this->pdo->query("
            CREATE TABLE peer (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                torrentId INTEGER NOT NULL default '0',
                peerId BLOB NOT NULL,
                ip TEXT NOT NULL,
                port INTEGER NOT NULL default '0',
                seed BOOLEAN NOT NULL default '0',
                registered INTEGER NOT NULL,
                updated INTEGER NOT NULL,
                UNIQUE (torrentId,peerId)
            )
        ");

        parent::setUp();
    }

    /**
     * Tear down method
     */
    public function tearDown() {
        if (extension_loaded('pdo_sqlite')) {
            $this->pdo = null;
        }

        parent::tearDown();
    }
}
