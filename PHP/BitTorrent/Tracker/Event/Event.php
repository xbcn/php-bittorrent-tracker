<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Event;

use PHP\BitTorrent\Tracker\Request\RequestInterface as Request,
    PHP\BitTorrent\Tracker\Response\ResponseInterface as Response,
    PHP\BitTorrent\Tracker\Backend\BackendInterface as Backend;

/**
 * Tracker event
 *
 * @package Event
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Event implements EventInterface {
    /**
     * Name of the event
     *
     * @var string
     */
    private $name;

    /**
     * Request instance
     *
     * @var Request
     */
    private $request;

    /**
     * Response instance
     *
     * @var Response
     */
    private $response;

    /**
     * Backend
     *
     * @var Backend
     */
    private $backend;

    /**
     * Event manager
     *
     * @var Manager
     */
    private $manager;

    /**
     * Propagation flag
     *
     * @var boolean
     */
    private $propagationIsStopped = false;

    /**
     * Class constructor
     *
     * @param string $name Name of the event
     * @param Request $request Request instance
     * @param Response $response Response instance
     * @param Backend $backend The current backend
     * @param Manager $manager The event manager
     */
    public function __construct($name, Request $request, Response $response, Backend $backend, Manager $manager) {
        $this->name = $name;
        $this->request = $request;
        $this->response = $response;
        $this->backend = $backend;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackend() {
        return $this->backend;
    }

    /**
     * {@inheritdoc}
     */
    public function getManager() {
        return $this->manager;
    }

    /**
     * {@inheritdoc}
     */
    public function stopPropagation($flag) {
        $this->propagationIsStopped = $flag;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function propagationIsStopped() {
        return $this->propagationIsStopped;
    }
}
