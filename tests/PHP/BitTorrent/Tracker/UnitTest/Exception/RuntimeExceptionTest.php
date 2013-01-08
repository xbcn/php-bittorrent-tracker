<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\UnitTest\Exception;

use PHP\BitTorrent\Tracker\Exception\RuntimeException;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class RuntimeExceptionTest extends \PHPUnit_Framework_TestCase {
    /**
     * @covers PHP\BitTorrent\Tracker\Exception\RuntimeException::getMessageEncoded()
     */
    public function testGetMessageEncoded() {
        // Failure message
        $message = 'This is just a test.';

        // Failure array
        $failure = array('failure reason' => $message);

        $encoder = $this->getMock('PHP\BitTorrent\EncoderInterface');
        $encoder->expects($this->once())->method('encodeDictionary')->with($failure);

        // Create a new exception
        $exception = new RuntimeException($message);
        $encoded = $exception->getMessageEncoded($encoder);
    }
}
