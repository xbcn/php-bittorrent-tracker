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
 * Tracker event interface
 *
 * @package Event
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface EventInterface {
    /**
     * Get the name
     *
     * @return string
     */
    function getName();

    /**
     * Get the request
     *
     * @return Request
     */
    function getRequest();

    /**
     * Get the response
     *
     * @return Response
     */
    function getResponse();

    /**
     * Get the backend
     *
     * @return Backend
     */
    function getBackend();

    /**
     * Get the manager
     *
     * @return Manager
     */
    function getManager();

    /**
     * Whether or not to stop the execution of more listeners for the current event
     *
     * @param boolean $flag True to stop, false to continue
     * @return EventInterface
     */
    function stopPropagation($flag);

    /**
     * Return whether or not the propagation should stop
     *
     * @return boolean
     */
    function propagationIsStopped();
}
