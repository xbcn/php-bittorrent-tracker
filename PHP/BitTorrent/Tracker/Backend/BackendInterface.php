<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Backend;

use PHP\BitTorrent\Tracker\Peer\PeerInterface;

/**
 * Backend interface
 *
 * @package Backend
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface BackendInterface {
    /**
     * Check if a torrent with a given info hash exists
     *
     * @param string $infoHash The info hash to look for
     * @return boolean True if the torrent exist, false otherwise
     */
    function torrentExists($infoHash);

    /**
     * Register an info hash
     *
     * If the info hash already exist, the backend should return false.
     *
     * @param string $infoHash The info hash to register
     * @return boolean Returns true on success or false on failure
     */
    function registerTorrent($infoHash);

    /**
     * Register a torrent peer
     *
     * If the torrent does not exist, or if the torrent peer already exists, return false.
     *
     * @param string $infoHash The info hash of the torrent
     * @param PeerInterface $peer A peer instance
     * @return boolean Returns true on success or false on failure
     */
    function registerTorrentPeer($infoHash, PeerInterface $peer);

    /**
     * Check if a peer for a given torrent exists
     *
     * @param string $infoHash The info hash of the torrent
     * @param PeerInterface $peer A peer instance
     * @return boolean Returns true if the peer exists, false otherwise
     */
    function torrentPeerExists($infoHash, PeerInterface $peer);

    /**
     * Remove a peer from a torrent
     *
     * If the torrent does not exist or if the peer does not exist, return false.
     *
     * @param string $infoHash The info hash of the torrent
     * @param PeerInterface $peer A peer instance
     * @return boolean Returns true on success or false otherwise
     */
    function deleteTorrentPeer($infoHash, PeerInterface $peer);

    /**
     * Remove a torrent
     *
     * If the torrent does not exist prior to calling this method it must return false.
     *
     * @param string $infoHash The info hash of the torrent
     * @return boolean Returns true on success or false otherwise
     */
    function deleteTorrent($infoHash);

    /**
     * Mark a torrent peer as complete (as a "seeder")
     *
     * @param string $infoHash The info hash of the torrent
     * @param PeerInterface $peer A peer instance
     * @return boolean Returns true on success or false otherwise
     */
    function torrentPeerComplete($infoHash, PeerInterface $peer);

    /**
     * Update information about a torrent peer
     *
     * If the torrent does not exist, return false.
     *
     * @param string $infoHash The info hash of the torrent
     * @param PeerInterface $peer A peer instance
     * @return boolean Returns true on success or false otherwise
     */
    function updateTorrentPeer($infoHash, PeerInterface $peer);

    /**
     * Get peers connected to a torrent
     *
     * If no peers exist or an error occurs, return an empty array
     *
     * @param string $infoHash The info hash of the torrent
     * @param int $limit Number of peers to return
     * @param PeerInterface $exclude A peer instance to exclude from the list
     * @return PeerInterface[] An error of peer instances
     */
    function getTorrentPeers($infoHash, $limit = null, PeerInterface $exclude = null);

    /**
     * Get a list of info hashes
     *
     * @param int $page The page
     * @param int $limit Limit the amount of hashes returned
     * @return string[] Returns an array of info hashes
     */
    function getTorrents($page = 1, $limit = 20);

    /**
     * Get the number of times a torrent has been downloaded
     *
     * If the torrent does not exist, return false.
     *
     * @param string $infoHash The info hash of the torrent
     * @return int|boolean
     */
    function getNumTorrentDownloads($infoHash);

    /**
     * Get the total amount of torrents
     *
     * @return int
     */
    function getNumTorrents();
}
