<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\UnitTest;

use PHP\BitTorrent\Tracker\Application,
    PHP\BitTorrent\Tracker\Event,
    PHP\BitTorrent\Tracker\Exception\RuntimeException;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase {
    /**
     * @covers PHP\BitTorrent\Tracker\Application::__construct
     * @covers PHP\BitTorrent\Tracker\Application::bootstrap
     * @covers PHP\BitTorrent\Tracker\Application::run
     */
    public function testTriggersEventsWhenRun() {
        $application = new Application(array(
            'tracker' => array(),
            'backend' => $this->getMock('PHP\BitTorrent\Tracker\Backend\BackendInterface'),
            'eventListeners' => array(
                'empty' => null,
                'test' => array(
                    'callback' => function($event) { echo 1; },
                    'events' => array('event'),
                    'priority' => 50,
                ),
                'bar' => function() { return new SomeListener(2, 40); },
                'foo' => new SomeListener(3, 30),
                'foobar' => array(
                    'callback' => function($event) { echo 0; },
                    'events' => array(
                        'event' => 100,
                    ),
                ),
            ),
        ));
        $this->expectOutputString('0123');
        $application->run('event');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid event listener definition
     * @covers PHP\BitTorrent\Tracker\Application::bootstrap
     * @covers PHP\BitTorrent\Tracker\Application::run
     */
    public function testThrowsExceptionWhenEventListenerIsNotCorrectlyConfigured() {
        $application = new Application(array(
            'tracker' => array(),
            'backend' => function() { return $this->getMock('PHP\BitTorrent\Tracker\Backend\BackendInterface'); },
            'eventListeners' => array(
                'test' => array(
                    'callback' => function($event) {},
                ),
            ),
        ));
        $application->run('event');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid backend adapter
     * @covers PHP\BitTorrent\Tracker\Application::bootstrap
     * @covers PHP\BitTorrent\Tracker\Application::run
     */
    public function testThrowsExceptionWhenBackendIsNotCorrectlyConfigured() {
        $application = new Application(array(
            'tracker' => array(),
            'backend' => function() { return new \stdClass(); },
            'eventListeners' => array(
                'test' => array(
                    'callback' => function($event) {},
                ),
            ),
        ));
        $application->run('event');
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Application::run
     */
    public function testSendsCorrectResponseWhenApplicationThrowsException() {
        $application = new Application(array(
            'tracker' => array(),
            'backend' => $this->getMock('PHP\BitTorrent\Tracker\Backend\BackendInterface'),
            'eventListeners' => array(
                'listener' => array(
                    'callback' => function($event) { throw new RuntimeException('Some message'); },
                    'events' => array('event'),
                ),
            ),
        ));
        $this->expectOutputString('d14:failure reason12:Some messagee');
        $application->run('event');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Some message
     * @covers PHP\BitTorrent\Tracker\Application::run
     */
    public function testDoesNotCatchAllExceptions() {
        $application = new Application(array(
            'tracker' => array(),
            'backend' => $this->getMock('PHP\BitTorrent\Tracker\Backend\BackendInterface'),
            'eventListeners' => array(
                'listener' => array(
                    'callback' => function($event) { throw new \RuntimeException('Some message'); },
                    'events' => array('event'),
                ),
            ),
        ));
        $application->run('event');
    }
}

class SomeListener implements Event\Listener {
    private $output;
    private $priority;

    public function __construct($output, $priority) {
        $this->output = $output;
        $this->priority = $priority;
    }

    public function attach(Event\Manager $manager) {
        $manager->attach('event', array($this, 'invoke'), $this->priority);
    }

    public function invoke(Event\Event $event) {
        echo $this->output;
    }
}
