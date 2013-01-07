<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker;

use PHP\BitTorrent\EncoderInterface;

/**
 * Exception class for the tracker
 *
 * @package Tracker\Exception
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
Interface Exception {
    /**
     * Encode the error message so it can be sent to a BitTorrent client
     *
     * This method will send a bencoded message to a bittorrent client telling it that an error
     * has occured. When the client receives a dictionary with the key "failure reason" it knows
     * something is wrong.
     *
     * @param EncoderInterface $encoder Encoder used to encode the exception message
     * @return string
     */
    function getMessageEncoded(EncoderInterface $encoder);
}
