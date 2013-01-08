<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Response;

use PHP\BitTorrent\Tracker\Peer\PeerInterface,
    PHP\BitTorrent\Encoder;

/**
 * Response interface
 *
 * @package Tracker\Http\Response
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface ResponseInterface {
    /**
     * Set the response content
     *
     * @param string $content
     * @return ResponseInterface
     */
    function setContent($content);

    /**
     * Get the content
     *
     * @return string
     */
    function getContent();

    /**
     * Send the response headers and body
     */
    function send();
}
