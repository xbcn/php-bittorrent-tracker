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

use PHP\BitTorrent\Tracker\Event\Listener;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
abstract class ListenerTests extends \PHPUnit_Framework_TestCase {
    /**
     * Get the listener to test
     *
     * @return Listener
     */
    abstract protected function getListener();

    public function testAttachesItselfToTheEventManager() {
        $manager = $this->getMockBuilder('PHP\BitTorrent\Tracker\Event\Manager')->disableOriginalConstructor()->getMock();
        $manager->expects($this->atLeastOnce())->method('attach')->with($this->isType('string'), $this->isType('callable'))->will($this->returnSelf());

        $this->getListener()->attach($manager);
    }
}
