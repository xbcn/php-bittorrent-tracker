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
    PHP\BitTorrent\Tracker\Exception\RuntimeException,
    PHP\BitTorrent\Tracker\Request\RequestInterface;

/**
 * Event listener to validate request parameters
 *
 * @package Event\Listeners
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class RequestValidator implements Event\Listener {
    /**
     * Required query parameters
     *
     * @var array
     */
    static public $requiredQueryParams = array(
        'info_hash',
        'peer_id',
        'port',
        'uploaded',
        'downloaded',
        'left',
    );

    /**
     * Attach to the event manager
     *
     * @param Event\Manager $manager The event manager
     */
    public function attach(Event\Manager $manager) {
        $manager->attach('request.validate', array($this, 'validate'));
    }

    /**
     * Validate a request
     *
     * @param Event\EventInterface $event The event instance
     * @return boolean
     */
    public function validate(Event\EventInterface $event) {
        $request = $event->getRequest();
        $query = $request->query;

        foreach (static::$requiredQueryParams as $key) {
            if (!$query->has($key)) {
                throw new RuntimeException('Missing query parameter: ' . $key);
            }
        }

        $this->validateEvent($request->getEvent());
        $this->validatePort($query->get('port'));
        $this->validateInfoHash($query->get('info_hash'));
        $this->validatePeerId($query->get('peer_id'));

        return true;
    }

    /**
     * Validate the event from the client
     *
     * @param string $event The event from the client
     * @throws RuntimeException
     */
    protected function validateEvent($event) {
        switch ($event) {
            case RequestInterface::EVENT_NONE:
            case RequestInterface::EVENT_STARTED:
            case RequestInterface::EVENT_STOPPED:
            case RequestInterface::EVENT_COMPLETED:
                break;
            default:
                throw new RuntimeException('Invalid event: ' . $event);
        }
    }

    /**
     * Validate the port from the client
     *
     * @param int $port The port from the client
     * @throws RuntimeException
     */
    protected function validatePort($port) {
        if (!$port || $port > 65535) {
            throw new RuntimeException('Invalid port: ' . $port);
        }
    }

    /**
     * Validate the info hash
     *
     * @param array|string $infoHash The info hash(es) from the client
     * @throws RuntimeException
     */
    protected function validateInfoHash($infoHash) {
        if (!is_array($infoHash)) {
            $infoHash = array($infoHash);
        }

        foreach ($infoHash as $hash) {
            if (strlen($hash) !== 20) {
                throw new RuntimeException('Invalid info hash: ' . $hash);
            }
        }
    }

    /**
     * Validate the peer id
     *
     * @param string $peerId The peer id from the client
     * @throws RuntimeException
     */
    protected function validatePeerId($peerId) {
        if (strlen($peerId) !== 20) {
            throw new RuntimeException('Invalid peer id: ' . $peerId);
        }
    }
}
