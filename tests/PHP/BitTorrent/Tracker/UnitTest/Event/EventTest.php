<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\UnitTest\Event;

use PHP\BitTorrent\Tracker\Event\Event;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class EventTest extends \PHPUnit_Framework_TestCase {
    /**
     * @covers PHP\BitTorrent\Tracker\Event\Event::__construct
     * @covers PHP\BitTorrent\Tracker\Event\Event::getName
     * @covers PHP\BitTorrent\Tracker\Event\Event::getRequest
     * @covers PHP\BitTorrent\Tracker\Event\Event::getResponse
     * @covers PHP\BitTorrent\Tracker\Event\Event::getBackend
     * @covers PHP\BitTorrent\Tracker\Event\Event::getManager
     * @covers PHP\BitTorrent\Tracker\Event\Event::propagationIsStopped
     * @covers PHP\BitTorrent\Tracker\Event\Event::stopPropagation
     */
    public function testAccessors() {
        $name = 'event.name';
        $request = $this->getMock('PHP\BitTorrent\Tracker\Request\RequestInterface');
        $response = $this->getMock('PHP\BitTorrent\Tracker\Response\ResponseInterface');
        $backend = $this->getMock('PHP\BitTorrent\Tracker\Backend\BackendInterface');
        $manager = $this->getMockBuilder('PHP\BitTorrent\Tracker\Event\Manager')->disableOriginalConstructor()->getMock();

        $event = new Event($name, $request, $response, $backend, $manager);

        $this->assertSame($name, $event->getName());
        $this->assertSame($request, $event->getRequest());
        $this->assertSame($response, $event->getResponse());
        $this->assertSame($backend, $event->getBackend());
        $this->assertSame($manager, $event->getManager());

        $this->assertFalse($event->propagationIsStopped());
        $event->stopPropagation(true);
        $this->assertTrue($event->propagationIsStopped());
        $event->stopPropagation(false);
        $this->assertFalse($event->propagationIsStopped());
    }
}
