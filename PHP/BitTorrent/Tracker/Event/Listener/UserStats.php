<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Event\Listener;

use PHP\BitTorrent\Tracker\Event,
    MongoClient,
    MongoCollection,
    MongoException;

/**
 * Record user statistics
 *
 * @package Event\Listeners
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class UserStats implements Event\Listener {
    /**
     * Mongo client instance
     *
     * @var MongoClient
     */
    private $mongoClient;

    /**
     * The torrent and peer collections
     *
     * @var array
     */
    private $collection;

    /**
     * Parameters for the backend
     *
     * @var array
     */
    private $params = array(
        // Database name
        'database' => 'phpBitTorrent',

        // Collection names
        'collection' => 'userStats',

        // Server connection string
        'server' => 'mongodb://localhost:27017',

        // Options passed to the MongoDB instance
        'options' => array('connect' => true, 'timeout' => 1000),
    );

    /**
     * Class constructor
     *
     * @param array $params Parameters for the backend
     * @param MongoClient $client Mongo client instance
     * @param MongoCollection $collection MongoCollection instance
     */
    public function __construct(array $params = null, MongoClient $client = null,
                                MongoCollection $collection = null
    ) {
        if ($params !== null) {
            $this->params = array_replace_recursive($this->params, $params);
        }

        if ($client !== null) {
            $this->mongoClient = $client;
        }

        if ($collection !== null) {
            $this->collection = $collection;
        }
    }

    /**
     * Attach to the event manager
     *
     * @param Event\Manager $manager The event manager
     */
    public function attach(Event\Manager $manager) {
        $manager->attach('peer.update', array($this, 'updateUserStats'));
    }

    /**
     * Update user statistics
     *
     * @param Event\EventInterface $event The event instance
     */
    public function updateUserStats(Event\EventInterface $event) {
        $query = $event->getRequest()->query;

        $userId = $query->get('userId');

        if ($userId) {
            $infoHash = $this->decode($query->get('info_hash'));
            $downloaded = $query->get('downloaded');
            $uploaded = $query->get('uploaded');

            try {
                $this->getCollection()->update(
                    array(
                        'userId' => $userId,
                        'infoHash' => $infoHash,
                    ),
                    array(
                        'userId' => $userId,
                        'infoHash' => $infoHash,
                        'downloaded' => $downloaded,
                        'uploaded' => $uploaded,
                    ),
                    array(
                        'multiple' => false,
                        'upsert' => true,
                    )
                );
            } catch (MongoException $e) {
                trigger_error('Could not update user statistics (' . $e->getMessage() . ')', E_USER_WARNING);
            }
        }
    }

    /**
     * Get the mongo collection instance
     *
     * @return MongoCollection
     */
    private function getCollection() {
        if ($this->collection === null) {
            try {
                $this->collection = $this->getMongoClient()->selectCollection(
                    $this->params['database'],
                    $this->params['collection']
                );
            } catch (MongoException $e) {
                throw new RuntimeException('Could not select collection', 500, $e);
            }
        }

        return $this->collection;
    }

    /**
     * Get the mongo client instance
     *
     * @return MongoClient
     */
    private function getMongoClient() {
        if ($this->mongoClient === null) {
            try {
                $this->mongoClient = new MongoClient($this->params['server'], $this->params['options']);
            } catch (MongoException $e) {
                throw new RuntimeException('Could not connect to database', 500, $e);
            }
        }

        return $this->mongoClient;
    }

    /**
     * Decode a value
     *
     * @param string $value The value to decode
     * @return string
     */
    private function decode($value) {
        return bin2hex(urldecode($value));
    }
}
