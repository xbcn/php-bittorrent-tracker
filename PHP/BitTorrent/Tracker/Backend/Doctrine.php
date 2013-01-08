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
    Doctrine\DBAL\Configuration,
    Doctrine\DBAL\DriverManager,
    Doctrine\DBAL\Connection,
    PDO;

/**
 * Doctrine 2 backend
 *
 * Parameters for this driver:
 *
 * - (string) dbname: Name of the database to connect to
 * - (string) user: Username to use when connecting
 * - (string) password: Password to use when connecting
 * - (string) host: Hostname to use when connecting
 * - (string) driver: Which driver to use
 *
 * @package Backend
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Doctrine implements BackendInterface {
    /**
     * Parameters for the Doctrine connection
     *
     * @var array
     */
    private $params = array(
        'dbname'   => null,
        'user'     => null,
        'password' => null,
        'host'     => null,
        'driver'   => null,
    );

    /**
     * Default table names for the database
     *
     * @var array
     */
    private $tableNames = array(
        'torrent' => 'torrent',
        'peer'  => 'peer',
    );

    /**
     * Doctrine connection
     *
     * @var Connection
     */
    private $connection;

    /**
     * Class constructor
     *
     * @param array $params Parameters for the driver
     * @param Connection $connection Optional connection instance. Primarily used for testing
     */
    public function __construct(array $params, Connection $connection = null) {
        $this->params = array_merge($this->params, $params);

        if ($connection !== null) {
            $this->setConnection($connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function torrentExists($infoHash) {
        return (boolean) $this->getTorrentId($infoHash);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllTorrents() {
        $hashes = array();

        $query = $this->getConnection()->createQueryBuilder();
        $query->select('t.infoHash')
              ->from($this->tableNames['torrent'], 't');

        $stmt = $query->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $hashes[] = $row['infoHash'];
        }

        return $hashes;
    }

    /**
     * {@inheritdoc}
     */
    public function torrentPeerExists($infoHash, PeerInterface $peer) {
        $torrentId = $this->getTorrentId($infoHash);

        if (!$torrentId) {
            return false;
        }

        $query = $this->getConnection()->createQueryBuilder();
        $query->select('p.id')
              ->from($this->tableNames['peer'], 'p')
              ->where('p.peerId = :peerId')
              ->andWhere('p.torrentId = :torrentId')
              ->setParameters(array(
                  ':peerId' => $peer->getId(),
                  ':torrentId' => $torrentId,
              ));

        $stmt = $query->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return !empty($row);
    }

    /**
     * {@inheritdoc}
     */
    public function getTorrentPeers($infoHash, $limit = null, PeerInterface $exclude = null) {
        $torrentId = $this->getTorrentId($infoHash);

        if (!$torrentId) {
            return array();
        }

        $query = $this->getConnection()->createQueryBuilder();
        $query->select('p.*')
              ->from($this->tableNames['peer'], 'p')
              ->where('p.torrentId = :torrentId')
              ->setParameter(':torrentId', $torrentId);

        if ($exclude) {
            $query->andWhere('p.peerId != :excludePeerId')
                  ->setParameter(':excludePeerId', $exclude->getId());
        }

        if ($limit) {
            $query->setMaxResults((int) $limit);
        }

        $stmt = $query->execute();
        $peers = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $peer = new Peer();
            $peer->setIp($row['ip'])
                 ->setId($row['peerId'])
                 ->setPort($row['port'])
                 ->isSeed((boolean) $row['seed']);

            $peers[] = $peer;
        }

        return $peers;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTorrentPeer($infoHash, PeerInterface $peer) {
        $torrentId = $this->getTorrentId($infoHash);

        if (!$torrentId) {
            return false;
        }

        $query = $this->getConnection()->createQueryBuilder();
        $result = $query->delete($this->tableNames['peer'])
                        ->where('peerId = :peerId')
                        ->andWhere('torrentId = :torrentId')
                        ->setParameters(array(
                            ':peerId' => $peer->getId(),
                            ':torrentId' => $torrentId,
                        ))
                        ->execute();

        return (boolean) $result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTorrent($infoHash) {
        $torrentId = $this->getTorrentId($infoHash);

        if (!$torrentId) {
            return false;
        }

        $query = $this->getConnection()->createQueryBuilder();
        $result = $query->delete($this->tableNames['torrent'])
                        ->where('id = :torrentId')
                        ->setParameters(array(
                            ':torrentId' => $torrentId,
                        ))
                        ->execute();

        return (boolean) $result;
    }

    /**
     * {@inheritdoc}
     */
    public function registerTorrentPeer($infoHash, PeerInterface $peer) {
        $torrentId = $this->getTorrentId($infoHash);

        if (!$torrentId) {
            return false;
        }

        if ($this->torrentPeerExists($infoHash, $peer)) {
            return false;
        }

        $time = time();

        $this->getConnection()->insert($this->tableNames['peer'], array(
            'torrentId' => $torrentId,
            'peerId' => $peer->getId(),
            'ip' => $peer->getIp(),
            'port' => $peer->getPort(),
            'registered' => $time,
            'updated' => $time,
            'seed' => ($peer->isSeed() ? 1 : 0),
        ));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTorrentPeer($infoHash, PeerInterface $peer) {
        $torrentId = $this->getTorrentId($infoHash);

        if (!$torrentId) {
            return false;
        }

        return (boolean) $this->getConnection()->update($this->tableNames['peer'], array(
            'ip' => $peer->getIp(),
            'port' => $peer->getPort(),
            'updated' => time(),
            'seed' => ($peer->isSeed() ? 1 : 0),
        ), array(
            'peerId' => $peer->getId(),
            'torrentId' => $torrentId,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function torrentPeerComplete($infoHash, PeerInterface $peer) {
        if (!$this->updateTorrentPeer($infoHash, $peer)) {
            return false;
        }

        return (boolean) $this->getConnection()->update($this->tableNames['torrent'], array(
            'downloaded' => $this->getNumTorrentDownloads($infoHash) + 1,
        ), array(
            'id' => $this->getTorrentId($infoHash),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function registerTorrent($infoHash) {
        $torrentId = $this->getTorrentId($infoHash);

        // If the torrent already exist, return false
        if ($torrentId) {
            return false;
        }

        $this->getConnection()->insert($this->tableNames['torrent'], array(
            'infoHash' => $infoHash,
            'downloaded' => 0,
        ));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumTorrentDownloads($infoHash) {
        $torrentId = $this->getTorrentId($infoHash);

        if (!$torrentId) {
            return false;
        }

        $query = $this->getConnection()->createQueryBuilder();
        $query->select('t.downloaded')
              ->from($this->tableNames['torrent'], 't')
              ->where('t.id = :torrentId')
              ->setParameter(':torrentId', $torrentId);

        $stmt = $query->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $row['downloaded'];
    }

    /**
     * {@inheritdoc}
     */
    public function getNumTorrents() {
        $query = $this->getConnection()->createQueryBuilder();
        $query->select('COUNT(t.id)')
              ->from($this->tableNames['torrent'], 't');

        $stmt = $query->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Set the connection instance
     *
     * @param Connection $connection The connection instance
     */
    private function setConnection(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * Get the Doctrine connection
     *
     * @return Connection
     */
    private function getConnection() {
        if ($this->connection === null) {
            $this->connection = DriverManager::getConnection($this->params, new Configuration());
        }

        return $this->connection;
    }

    /**
     * Get the internal torrent ID
     *
     * @param string $infoHash The info hash of the torrent
     * @return boolean|int Returns false if the torrent does not exist, or the numeric id otherwise
     */
    private function getTorrentId($infoHash) {
        $query = $this->getConnection()->createQueryBuilder();
        $query->select('t.id')
              ->from($this->tableNames['torrent'], 't')
              ->where('t.infoHash = :infoHash')
              ->setParameter(':infoHash', $infoHash);

        $stmt = $query->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($row)) {
            return false;
        }

        return (int) $row['id'];
    }
}
