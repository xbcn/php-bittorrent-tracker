<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\UnitTest\Event\Listener;

use PHP\BitTorrent\Tracker\Event\Listener\RequestValidator,
    PHP\BitTorrent\Tracker\Request\Request,
    Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class RequestValidatorTest extends ListenerTests {
    /**
     * @var RequestValidator
     */
    private $listener;

    private $event;
    private $request;

    /**
     * {@inheritdoc}
     */
    protected function getListener() {
        return $this->listener;
    }

    /**
     * Set up the listener
     */
    public function setUp() {
        $this->request = new Request(array(
            'info_hash' => str_repeat('a', 20),
            'peer_id' => str_repeat('a', 20),
            'port' => 1349,
            'uploaded' => 123,
            'downloaded' => 123,
            'left' => 123,
        ));
        $this->event = $this->getMock('PHP\BitTorrent\Tracker\Event\EventInterface');
        $this->event->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->listener = new RequestValidator();
    }

    /**
     * Tear down the listener
     */
    public function tearDown() {
        $this->listener = null;
        $this->event = null;
        $this->request = null;
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validate
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validateEvent
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validatePort
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validatePeerId
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validateInfoHash
     */
    public function testValidateValidRequest() {
        $this->assertTrue($this->listener->validate($this->event));
    }

    /**
     * @expectedException PHP\BitTorrent\Tracker\Exception
     * @expectedExceptionMessage Missing query parameter: info_hash
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validate
     */
    public function testRequestWithMissingParameter() {
        $this->request->query->remove('info_hash');
        $this->listener->validate($this->event);
    }

    /**
     * @expectedException PHP\BitTorrent\Tracker\Exception
     * @expectedExceptionMessage Invalid event: some event
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validate
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validateEvent
     */
    public function testRequestWithInvalidEvent() {
        $this->request->query->set('event', 'some event');
        $this->listener->validate($this->event);
    }

    /**
     * @expectedException PHP\BitTorrent\Tracker\Exception
     * @expectedExceptionMessage Invalid port: 100000
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validate
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validatePort
     */
    public function testRequestWithInvalidPort() {
        $this->request->query->set('port', 100000);
        $this->listener->validate($this->event);
    }

    /**
     * @expectedException PHP\BitTorrent\Tracker\Exception
     * @expectedExceptionMessage Invalid info hash: value
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validate
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validateInfoHash
     */
    public function testRequestWithInvalidInfoHash() {
        $this->request->query->set('info_hash', 'value');
        $this->listener->validate($this->event);
    }

    /**
     * @expectedException PHP\BitTorrent\Tracker\Exception
     * @expectedExceptionMessage Invalid peer id: value
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validate
     * @covers PHP\BitTorrent\Tracker\Event\Listener\RequestValidator::validatePeerId
     */
    public function testRequestWithInvalidPeerId() {
        $this->request->query->set('peer_id', 'value');
        $this->listener->validate($this->event);
    }
}
