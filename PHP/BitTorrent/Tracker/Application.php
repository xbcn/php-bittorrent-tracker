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

use PHP\BitTorrent\Tracker\Request\Request,
    PHP\BitTorrent\Tracker\Response\Response,
    PHP\BitTorrent\Tracker\Response\ResponseInterface,
    PHP\BitTorrent\Tracker\Event\Manager as EventManager,
    PHP\BitTorrent\Tracker\Event\Listener as EventListener,
    PHP\BitTorrent\Tracker\Backend\BackendInterface,
    PHP\BitTorrent\Encoder,
    PHP\BitTorrent\Tracker\Exception,
    RuntimeException,
    InvalidArgumentException;

/**
 * Tracker application
 *
 * @package Tracker
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Application {
    /**
     * Application configuration
     *
     * @var array
     */
    private $config;

    /**
     * Application event manager
     *
     * @var EventManager
     */
    private $eventManager;

    /**
     * Response to the client
     *
     * @var ResponseInterface
     */
    private $response;

    /**
     * Class constructor
     *
     * @param array $config Application config
     */
    public function __construct(array $config) {
        $this->config = $config;
        $this->bootstrap();
    }

    /**
     * Bootstrap the application
     */
    private function bootstrap() {
        // Create an encoder used by the response and a possible exception
        $encoder = new Encoder();

        // Create request and response objects
        $request = Request::createFromGlobals();
        $this->response = new Response();
        $this->response->headers->set('Content-Type', 'text/plain');

        // Backend adapter
        $backend = $this->config['backend'];

        if (is_callable($backend) && !($backend instanceof BackendInterface)) {
            $backend = $backend();
        }

        if (!$backend instanceof BackendInterface) {
            throw new InvalidArgumentException('Invalid backend adapter');
        }

        // Create the event manager
        $this->eventManager = new EventManager($request, $this->response, $backend);

        // Attach custom listeners
        $listeners = $this->config['eventListeners'];

        foreach ($listeners as $definition) {
            if (!$definition) {
                continue;
            }

            if (is_callable($definition) && !($definition instanceof EventListener)) {
                $definition = $definition();
            }

            if ($definition instanceof EventListener) {
                $definition->attach($this->eventManager);
                continue;
            }

            if (!empty($definition['callback']) && !empty($definition['events'])) {
                $callback = $definition['callback'];
                $priority = isset($definition['priority']) ? $definition['priority'] : 1;

                foreach ($definition['events'] as $key => $value) {
                    $event = $value;

                    if (is_string($key)) {
                        // We have an associative array with <event> => <priority>
                        $event = $key;
                        $priority = $value;
                    }

                    $this->eventManager->attach($event, $callback, $priority);
                }
            } else {
                throw new InvalidArgumentException('Invalid event listener definition', 500);
            }
        }

        // Create the tracker
        $tracker = new Tracker($this->config['tracker']);
        $tracker->attach($this->eventManager);
    }

    /**
     * Run the application
     *
     * @param string $event The main event
     */
    public function run($event) {
        try {
            $this->eventManager->trigger($event)
                               ->trigger('response.send');
        } catch (Exception $e) {
            $this->response->setContent($e->getMessageEncoded(new Encoder()));
            $this->response->send();
        }
    }
}
