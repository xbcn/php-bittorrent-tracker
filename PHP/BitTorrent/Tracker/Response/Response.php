<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Response;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class representing a response from the tracker
 *
 * @package Tracker\Http\Response
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Response extends SymfonyResponse implements ResponseInterface {}
