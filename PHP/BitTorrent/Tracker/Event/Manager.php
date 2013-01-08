<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Event;

use PHP\BitTorrent\Tracker\Request\RequestInterface as Request,
    PHP\BitTorrent\Tracker\Response\ResponseInterface as Response,
    PHP\BitTorrent\Tracker\Backend\BackendInterface as Backend,
    InvalidArgumentException,
    SplPriorityQueue;

/**
 * Event manager
 *
 * @package Event
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Manager {
    /**
     * Callbacks that can be triggered
     *
     * @var array
     */
    private $callbacks;

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
     * Backend instance
     *
     * @var Backend
     */
    private $backend;

    /**
     * Class constructor
     *
     * @param Request $request Request instance
     * @param Response $response Response instance
     * @param Backend $backend The current backend
     */
    public function __construct(Request $request, Response $response, Backend $backend) {
        $this->request = $request;
        $this->response = $response;
        $this->backend = $backend;
    }

    /**
     * Attach a callable to an event
     *
     * @param string $eventName The event to attach to
     * @param callback $callback Code that will be called when the event is triggered
     * @param int $priority Priority of the callback
     * @throws InvalidArgumentException
     * @return Manager
     */
    public function attach($eventName, $callback, $priority = 1) {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Callback for event ' . $eventName . ' is not callable');
        }

        if (!$this->hasListenersForEvent($eventName)) {
            $this->callbacks[$eventName] = new SplPriorityQueue();
        }

        $this->callbacks[$eventName]->insert($callback, $priority);

        return $this;
    }

    /**
     * Trigger a given event
     *
     * @param string $eventName The name of the event to trigger
     * @return Manager
     */
    public function trigger($eventName) {
        if (!empty($this->callbacks[$eventName])) {
            $event = new Event($eventName, $this->request, $this->response, $this->backend, $this);

            // Trigger all listeners for this event and pass in the event instance
            foreach ($this->callbacks[$eventName] as $callback) {
                call_user_func($callback, $event);

                if ($event->propagationIsStopped()) {
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Whether or not the manager has event listeners that subscribes to a specific event
     *
     * @param string $eventName The name of the event to check
     * @return boolean
     */
    public function hasListenersForEvent($eventName) {
        return !empty($this->callbacks[$eventName]);
    }
}
