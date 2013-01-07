<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Backend;

use PHP\BitTorrent\Tracker\Peer\PeerInterface,
    PHP\BitTorrent\Tracker\Peer\Peer,
    PHP\BitTorrent\Tracker\Exception\RuntimeException,
    MongoClient,
    MongoCollection,
    MongoException,
    DateTime;

/**
 * MongoDB backend
 *
 * Valid parameters for this driver:
 *
 * - (string) database Name of the database. Defaults to 'phpBitTorrent'
 * - (array) collection Array with two keys:
 *   - (string) torrent Name of the torrent collection. Defaults to 'torrents'
 *   - (string) peer Name of the peer collection. Defaults to 'peers'
 * - (string) server The server string to use when connecting to MongoDB. Defaults to
 *                   'mongodb://localhost:27017'
 * - (array) options Options to use when creating the MongoClient instance. Defaults to
 *                   array('connect' => true, 'timeout' => 1000).
 *
 * @package Backend
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class MongoDB implements BackendInterface {
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
    private $collections = array(
        'torrent' => null,
        'peer' => null,
    );

    /**
     * Parameters for the backend
     *
     * @var array
     */
    private $params = array(
        // Database name
        'database' => 'phpBitTorrent',

        // Collection names
        'collections' => array(
            'torrent' => 'torrents',
            'peer' => 'peers',
        ),

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
     * @param MongoCollection $torrentCollection MongoCollection instance
     * @param MongoCollection $peerCollection MongoCollection instance
     */
    public function __construct(array $params = null, MongoClient $client = null,
                                MongoCollection $torrentCollection = null,
                                MongoCollection $peerCollection = null) {
        if ($params !== null) {
            $this->params = array_replace_recursive($this->params, $params);
        }

        if ($client !== null) {
            $this->mongoClient = $client;
        }

        if ($torrentCollection !== null) {
            $this->collections['torrent'] = $torrentCollection;
        }

        if ($peerCollection !== null) {
            $this->collections['peer'] = $peerCollection;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function torrentExists($infoHash) {
        $data = $this->getTorrentCollection()->findOne(array(
            'infoHash' => $this->decode($infoHash)
        ));

        if ($data) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllTorrents() {
        try {
            $infoHashes = array();
            $cursor = $this->getTorrentCollection()->find(array(), array(
                'infoHash' => true,
            ));

            foreach ($cursor as $item) {
                $infoHashes[] = $this->encode($item['infoHash']);
            }
        } catch (MongoException $e) {
            return array();
        }

        return $infoHashes;
    }

    /**
     * {@inheritdoc}
     */
    public function torrentPeerExists($infoHash, PeerInterface $peer) {
        $data = $this->getPeerCollection()->findOne(array(
            'infoHash' => $this->decode($infoHash),
            'id' => $this->decode($peer->getId()),
        ));

        if ($data) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTorrentPeers($infoHash, $limit = null, PeerInterface $exclude = null) {
        try {
            $query = array('infoHash' => $this->decode($infoHash));

            if ($exclude) {
                $query['id'] = array('$ne' => $this->decode($exclude->getId()));
            }

            $cursor = $this->getPeerCollection()->find($query);

            if ($limit !== null) {
                $cursor->limit((int) $limit);
            }

            $peers = array();

            foreach ($cursor as $p) {
                $peer = new Peer();
                $peer->setIp($p['ip'])
                     ->setId($this->encode($p['id']))
                     ->setPort($p['port'])
                     ->isSeed((boolean) $p['seed']);

                $peers[] = $peer;
            }
        } catch (MongoException $e) {
            return array();
        }

        return $peers;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTorrentPeer($infoHash, PeerInterface $peer) {
        try {
            $query = array(
                'infoHash' => $this->decode($infoHash),
                'id' => $this->decode($peer->getId()),
            );

            $data = $this->getPeerCollection()->findOne($query);

            if (!$data) {
                return false;
            }

            $this->getPeerCollection()->remove($query, array('justOne' => true));
        } catch (MongoException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTorrent($infoHash) {
        try {
            $query = array(
                'infoHash' => $this->decode($infoHash),
            );

            $data = $this->getTorrentCollection()->findOne($query);

            if (!$data) {
                return false;
            }

            // Remove torrent
            $this->getTorrentCollection()->remove($query, array('justOne' => true));

            // Remove all connected peers
            $this->getPeerCollection()->remove($query);
        } catch (MongoException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function registerTorrentPeer($infoHash, PeerInterface $peer) {
        if (!$this->torrentExists($infoHash)) {
            // Torrent does not exist
            return false;
        }

        if ($this->torrentPeerExists($infoHash, $peer)) {
            // Peer already exists
            return false;
        }

        $time = time();

        $data = array(
            'infoHash' => $this->decode($infoHash),
            'id' => $this->decode($peer->getId()),
            'ip' => $peer->getIp(),
            'port' => $peer->getPort(),
            'seed' => ($peer->isSeed() ? 1 : 0),
            'time' => $time,
            'updated' => $time,
        );

        try {
            $this->getPeerCollection()->insert($data);
        } catch (MongoException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTorrentPeer($infoHash, PeerInterface $peer) {
        if (!$this->torrentExists($infoHash)) {
            return false;
        }

        if (!$this->torrentPeerExists($infoHash, $peer)) {
            return false;
        }

        // Update information about the peer
        $updatedData = array(
            'ip' => $peer->getIp(),
            'port' => $peer->getPort(),
            'seed' => ($peer->isSeed() ? 1 : 0),
            'updated' => time(),
        );

        try {
            $this->getPeerCollection()->update(
                array(
                    'infoHash' => $this->decode($infoHash),
                    'id' => $this->decode($peer->getId())
                ),
                array('$set' => $updatedData),
                array('multiple' => false)
            );
        } catch (MongoException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function torrentPeerComplete($infoHash, PeerInterface $peer) {
        if (!$this->updateTorrentPeer($infoHash, $peer)) {
            return false;
        }

        try {
            $this->getTorrentCollection()->update(
                array('infoHash' => $this->decode($infoHash)),
                array('$inc' => array('downloaded' => 1)),
                array('multiple' => false)
            );
        } catch (MongoException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function registerTorrent($infoHash) {
        if ($this->torrentExists($infoHash)) {
            return false;
        }

        try {
            $this->getTorrentCollection()->insert(array(
                'infoHash' => $this->decode($infoHash),
                'downloaded' => 0,
            ));
        } catch (MongoException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumTorrentDownloads($infoHash) {
        if (!$this->torrentExists($infoHash)) {
            return false;
        }

        try {
            $data = $this->getTorrentCollection()->findOne(array(
                'infoHash' => $this->decode($infoHash)
            ), array(
                'downloaded' => true,
            ));

            return (int) $data['downloaded'];
        } catch (MongoException $e) {
            return false;
        }
    }

    /**
     * Get the torrent mongo collection
     *
     * @return MongoCollection
     */
    private function getTorrentCollection() {
        return $this->getcollection('torrent');
    }

    /**
     * Get the peer mongo collection
     *
     * @return MongoCollection
     */
    private function getPeerCollection() {
        return $this->getcollection('peer');
    }

    /**
     * Get the mongo collection instance
     *
     * @param string $type "peer" or "torrent"
     * @return MongoCollection
     */
    private function getCollection($type) {
        if ($this->collections[$type] === null) {
            try {
                $this->collections[$type] = $this->getMongoClient()->selectCollection(
                    $this->params['database'],
                    $this->params['collections'][$type]
                );
            } catch (MongoException $e) {
                throw new RuntimeException('Could not select collection', 500, $e);
            }
        }

        return $this->collections[$type];
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
     * Decode a value (peer_id and info_hash from the request)
     *
     * @param string $value The value to decode
     * @return string
     */
    private function decode($value) {
        return bin2hex(urldecode($value));
    }

    /**
     * Encode a value (peer_id and info_hash from the request)
     *
     * @param string $value The value to encode
     * @return string
     */
    private function encode($value) {
        return function_exists('hex2bin') ? hex2bin(urlencode($value)) : pack('H*', $value);
    }
}
