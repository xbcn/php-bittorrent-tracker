<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker;

use PHP\BitTorrent\Tracker\Event,
    PHP\BitTorrent\Tracker\Exception\RuntimeException,
    PHP\BitTorrent\Tracker\Request\RequestInterface,
    PHP\BitTorrent\Encoder,
    PHP\BitTorrent\EncoderInterface;

/**
 * BitTorrent tracker
 *
 * @package Tracker
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Tracker implements Event\Listener {
    /**
     * Parameters
     *
     * @var array
     */
    private $params = array(
        // The interval used by BitTorrent clients to decide on how often to fetch new peers
        'interval' => 1800,

        // Automatically register all torrents requested
        'autoRegister' => false,

        // Max. number of peers to give on a request
        'maxGive' => 200,
    );

    /**
     * An encoder
     *
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * Class constructor
     *
     * @param array $params Parameters for the tracker
     * @param EncoderInterface $encoder An encoder instance
     */
    public function __construct(array $params = array(), EncoderInterface $encoder = null) {
        $this->params = array_replace_recursive($this->params, $params);

        if ($encoder === null) {
            $encoder = new Encoder();
        }

        $this->encoder = $encoder;
    }

    /**
     * Get a parameter
     *
     * @param string $key The key of the parameter
     * @return mixed Returns null if the parameter does not exist
     */
    public function getParam($key) {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    /**
     * Set a parameter
     *
     * @param string $key The key of the parameter
     * @param mixed $value The value of the parameter
     * @return Tracker
     */
    public function setParam($key, $value) {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(Event\Manager $manager) {
        $manager->attach('tracker.announce', array($this, 'announce'))
                ->attach('tracker.scrape', array($this, 'scrape'));
    }

    /**
     * Handle a scrape request
     *
     * @param Event\EventInterface $event The event object
     */
    public function scrape(Event\EventInterface $event) {
        $backend = $event->getBackend();

        // Fetch optional info hash sent by the client
        $infoHash = $event->getRequest()->getInfoHash();

        if ($infoHash && !is_array($infoHash)) {
            $infoHash = array($infoHash);
        }

        $existsCheck = true;

        if (empty($infoHash)) {
            $existsCheck = false;
            $infoHash = $backend->getTorrents(1, $backend->getNumTorrents());
        }

        $body = array('files' => array());

        foreach ($infoHash as $hash) {
            if ($existsCheck && !$backend->torrentExists($hash)) {
                continue;
            }

            $peers = $backend->getTorrentPeers($hash);

            $complete = 0;
            $incomplete = 0;
            $downloaded = (int) $backend->getNumTorrentDownloads($hash);

            foreach ($peers as $peer) {
                if ($peer->left() === 0) {
                    $complete++;
                } else {
                    $incomplete++;
                }
            }

            $body['files'][$hash] = array(
                'complete'   => $complete,
                'incomplete' => $incomplete,
                'downloaded' => $downloaded,
            );
        }

        $event->getResponse()->setContent($this->encoder->encodeDictionary($body));
    }

    /**
     * Handle an announce
     *
     * @param Event\EventInterface $event The event object
     * @throws RuntimeException
     */
    public function announce(Event\EventInterface $event) {
        $request = $event->getRequest();
        $eventManager = $event->getManager();
        $backend = $event->getBackend();

        $eventManager->trigger('request.validate');

        $infoHash = $request->getInfoHash();

        if (!$backend->torrentExists($infoHash)) {
            // Do we want to automatically register the torrent?
            if ($this->getParam('autoRegister')) {
                $eventManager->trigger('torrent.register');
            } else {
                throw new RuntimeException('Torrent not found on this tracker');
            }
        }

        $peer = $request->getPeer();
        $peerExists = $backend->torrentPeerExists($infoHash, $peer);
        $requestEvent = $request->getEvent();

        if ($requestEvent === RequestInterface::EVENT_STOPPED && $peerExists) {
            // If 'stopped' the client has stopped the torrent. If info about the peer exist, delete the peer
            $eventManager->trigger('peer.delete');
        } else if ($requestEvent === RequestInterface::EVENT_COMPLETED && $peerExists) {
            // If 'completed' the user has downloaded the file
            $eventManager->trigger('peer.complete');
        } else if ($requestEvent === RequestInterface::EVENT_STARTED) {
            // If 'started' the client has just started the download. The peer does not exist yet
            $eventManager->trigger('peer.register');
        } else {
            if ($peerExists) {
                // Just a regular update
                $eventManager->trigger('peer.update');
            } else {
                // Invalid event
                throw new RuntimeException('Unexpected error');
            }
        }

        // Max. number of torrent peers to give
        $maxGive = (int) $this->getParam('maxGive');

        // Fetch the peers for this torrent (excluding the current one)
        $allPeers = $backend->getTorrentPeers($infoHash, $maxGive, $peer);

        // Force usage of the maxGive param in case the backend ignores it
        $allPeers = array_slice($allPeers, 0, $maxGive);

        // Set the response body
        $responseBody = $this->getResponseBody($request, $allPeers);
        $event->getResponse()->setContent($this->encoder->encodeDictionary($responseBody));
    }

    /**
     * Get the response body
     *
     * @param RequestInterface $request The current request
     * @param array $allPeers The peers to add to the body
     * @return array
     */
    private function getResponseBody(RequestInterface $request, array $allPeers) {
        // Initialize (in)complete variables
        $complete = 0;
        $incomplete = 0;

        if ($request->getCompact()) {
            // Compact response
            $peers = '';

            foreach ($allPeers as $peer) {
                $peers .= pack('Nn', ip2long($peer->ip()), $peer->port());

                if ($peer->left() === 0) {
                    $complete++;
                } else {
                    $incomplete++;
                }
            }
        } else {
            // Regular response
            $peers = array();

            foreach ($allPeers as $peer) {
                $p = array(
                    'ip'   => $peer->ip(),
                    'port' => $peer->port(),
                );

                // Include peer id unless specified otherwise
                if (!$request->getNoPeerId()) {
                    $p['peer id'] = $peer->id();
                }

                $peers[] = $p;

                if ($peer->left() === 0) {
                    $complete++;
                } else {
                    $incomplete++;
                }
            }
        }

        return array(
            'interval'   => (int) $this->getParam('interval'),
            'complete'   => $complete,
            'incomplete' => $incomplete,
            'peers'      => $peers,
        );
    }
}
