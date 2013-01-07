<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\UnitTest\Event\Listener;

use PHP\BitTorrent\Tracker\Event\Listener\ResponseWriter;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class ResponseWriterTest extends ListenerTests {
    /**
     * @var ResponseWriter
     */
    private $listener;

    private $event;
    private $response;

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
        $this->response = $this->getMock('PHP\BitTorrent\Tracker\Response\Response');
        $this->event = $this->getMock('PHP\BitTorrent\Tracker\Event\EventInterface');
        $this->event->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));
        $this->listener = new ResponseWriter();
    }

    /**
     * Tear down the listener
     */
    public function tearDown() {
        $this->listener = null;
        $this->event = null;
        $this->response = null;
    }

    /**
     * @covers PHP\BitTorrent\Tracker\Event\Listener\ResponseWriter::send
     */
    public function testCanSendTheResponseAsAnEncodedString() {
        $this->response->expects($this->once())->method('send');
        $this->listener->send($this->event);
    }
}
