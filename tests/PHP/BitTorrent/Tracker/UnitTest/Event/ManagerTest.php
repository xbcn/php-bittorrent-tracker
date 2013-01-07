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

use PHP\BitTorrent\Tracker\Event\Manager;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class ManagerTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Manager
     */
    private $manager;

    /**
     * Set up the event manager
     *
     * @covers PHP\BitTorrent\Tracker\Event\Manager::__construct
     */
    public function setUp() {
        $request = $this->getMock('PHP\BitTorrent\Tracker\Request\RequestInterface');
        $response = $this->getMock('PHP\BitTorrent\Tracker\Response\ResponseInterface');
        $backend = $this->getMock('PHP\BitTorrent\Tracker\Backend\BackendInterface');

        $this->manager = new Manager($request, $response, $backend);
    }

    /**
     * Tear down the event manager
     */
    public function tearDown() {
        $this->manager = null;
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Manager::attach
     * @expectedException InvalidArgumentException
     */
    public function testThrowsExceptionIfCallbackIsNotCallable() {
        $this->manager->attach('event', 'some string');
    }

    /**
     * Callback used in the method below
     */
    public function someCallback($event) {
        echo 3;
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Manager::attach
     * @covers PHP\BitTorrent\Tracker\Event\Manager::trigger
     */
    public function testCanAttachAndExecuteRegularCallbacksInAPrioritizedFashion() {
        $callback1 = function ($event) { echo 1; };
        $callback2 = function ($event) { echo 2; };
        $callback3 = array($this, 'someCallback');

        $this->assertSame(
            $this->manager,
            $this->manager->attach('event1', $callback1)
                          ->attach('event2', $callback2, 1)
                          ->attach('event2', $callback3, 2)
                          ->attach('event3', $callback3)
                          ->attach('event4', $callback1)
        );

        $this->expectOutputString('1321');

        $this->manager->trigger('otherevent')
                      ->trigger('event1')
                      ->trigger('event2')
                      ->trigger('event4');
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Manager::trigger
     */
    public function testLetsListenerStopPropagation() {
        $callback1 = function($event) { echo 1; };
        $callback2 = function($event) { echo 2; };
        $callback3 = function($event) { echo 3; };
        $stopper = function($event) {
            $event->stopPropagation(true);
        };

        $this->manager->attach('event', $callback1, 3)
                      ->attach('event', $stopper, 2)
                      ->attach('event', $callback2, 1)
                      ->attach('otherevent', $callback3);

        $this->expectOutputString('13');

        $this->assertSame(
            $this->manager,
            $this->manager->trigger('event')
                          ->trigger('otherevent')
        );
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Manager::hasListenersForEvent
     */
    public function testCanCheckIfTheManagerHasListenersForSpecificEvents() {
        $this->manager->attach('event', function($event) {});
        $this->assertFalse($this->manager->hasListenersForEvent('some.event'));
        $this->assertTrue($this->manager->hasListenersForEvent('event'));
    }
}
