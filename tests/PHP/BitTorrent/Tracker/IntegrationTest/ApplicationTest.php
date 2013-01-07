<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\IntegrationTest;

use PHP\BitTorrent\Decoder,
    Guzzle\Http\Client,
    Guzzle\Http\Exception\BadResponseException,
    MongoClient;

/**
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Decoder
     */
    private $decoder;

    /**
     * @var int
     */
    private static $pid;

    /**
     * Start the httpd
     */
    public static function setUpBeforeClass() {
        $hostname = $GLOBALS['HTTPD_HOSTNAME'];
        $port = $GLOBALS['HTTPD_PORT'];

        if (!self::canConnectToHttpd($hostname, $port)) {
            // No connection. Let's try and fire up the built in httpd (requires php-5.4)
            self::$pid = self::startBuiltInHttpd(
                $hostname,
                $port,
                __DIR__ . '/../../../../../public'
            );

            sleep(1);

            if (!self::canConnectToHttpd($hostname, $port)) {
                self::markTestSkipped('Could not start the built in httpd');
            }
        }
    }

    /**
     * Kill the httpd
     */
    public static function tearDownAfterClass() {
        if (self::$pid) {
            exec('kill ' . self::$pid);
            self::$pid = null;
        }
    }


    /**
     * Set up the backend
     */
    public function setUp() {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('pecl/mongo is required to run this test');
        }

        $mongo = new MongoClient();
        $mongo->selectDB('phpBitTorrentTesting')->drop();

        $this->decoder = new Decoder();
    }

    /**
     * Tear down the backend
     */
    public function tearDown() {
        if (extension_loaded('mongo')) {
            $mongo = new MongoClient();
            $mongo->selectDB('phpBitTorrentTesting')->drop();
        }

        $this->decoder = null;
    }

    /**
     * Get some requests
     *
     * @return array[]
     */
    public function getQuery() {
        $infoHash = str_repeat('a', 20);
        $seed     = str_repeat('b', 20);
        $peer1    = str_repeat('c', 20);
        $peer2    = str_repeat('d', 20);
        $peer3    = str_repeat('e', 20);

        return array(
            array(
                array(

                    // Register seed
                    array(
                        'query' => array(
                            'event'      => 'started',
                            'info_hash'  => $infoHash,
                            'peer_id'    => $seed,
                            'port'       => '6666',
                            'uploaded'   => '0',
                            'downloaded' => '0',
                            'left'       => '0',
                            'ip'         => '127.0.0.1',
                        ),
                        'peers' => array(),
                    ),

                    // Register peer
                    array(
                        'query' => array(
                            'event'      => 'started',
                            'info_hash'  => $infoHash,
                            'peer_id'    => $peer1,
                            'port'       => '6666',
                            'uploaded'   => '0',
                            'downloaded' => '0',
                            'left'       => '1000',
                            'ip'         => '127.0.0.2',
                        ),
                        'peers' => array(
                            array(
                                'ip' => '127.0.0.1',
                                'peer id' => $seed,
                                'port' => 6666,
                            )
                        ),
                    ),

                    // Register another peer
                    array(
                        'query' => array(
                            'event'      => 'started',
                            'info_hash'  => $infoHash,
                            'peer_id'    => $peer2,
                            'port'       => '6666',
                            'uploaded'   => '0',
                            'downloaded' => '0',
                            'left'       => '1000',
                            'ip'         => '127.0.0.3',
                        ),
                        'peers' => array(
                            array(
                                'ip' => '127.0.0.1',
                                'peer id' => $seed,
                                'port' => 6666,
                            ),
                            array(
                                'ip' => '127.0.0.2',
                                'peer id' => $peer1,
                                'port' => 6666,
                            )
                        ),
                    ),

                    // Make first peer a seeder
                    array(
                        'query' => array(
                            'event'      => 'completed',
                            'info_hash'  => $infoHash,
                            'peer_id'    => $peer1,
                            'port'       => '6666',
                            'uploaded'   => '0',
                            'downloaded' => '1000',
                            'left'       => '0',
                            'ip'         => '127.0.0.2',
                        ),
                        'peers' => array(
                            array(
                                'ip' => '127.0.0.1',
                                'peer id' => $seed,
                                'port' => 6666,
                            ),
                            array(
                                'ip' => '127.0.0.3',
                                'peer id' => $peer2,
                                'port' => 6666,
                            )
                        ),
                    ),

                    // Drop original seed
                    array(
                        'query' => array(
                            'event'      => 'stopped',
                            'info_hash'  => $infoHash,
                            'peer_id'    => $seed,
                            'port'       => '6666',
                            'uploaded'   => '1000',
                            'downloaded' => '0',
                            'left'       => '0',
                            'ip'         => '127.0.0.1',
                        ),
                        'peers' => array(
                            array(
                                'ip' => '127.0.0.2',
                                'peer id' => $peer1,
                                'port' => 6666,
                            ),
                            array(
                                'ip' => '127.0.0.3',
                                'peer id' => $peer2,
                                'port' => 6666,
                            )
                        ),
                    ),

                    // Update second peer
                    array(
                        'query' => array(
                            'info_hash'  => $infoHash,
                            'peer_id'    => $peer2,
                            'port'       => '6666',
                            'uploaded'   => '0',
                            'downloaded' => '500',
                            'left'       => '500',
                            'ip'         => '127.0.0.3',
                        ),
                        'peers' => array(
                            array(
                                'ip' => '127.0.0.2',
                                'peer id' => $peer1,
                                'port' => 6666,
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider getQuery
     *
     * Test the application
     */
    public function testApplication($requests) {
        foreach ($requests as $request) {
            $client = new Client('http://localhost:8888');
            $guzzleRequest = $client->get('/tracker.php');

            foreach ($request['query'] as $key => $value) {
                $guzzleRequest->getQuery()->set($key, $value);
            }

            try {
                $response = $guzzleRequest->send();
            } catch (BadResponseException $e) {
                $response = $e->getResponse();
            }

            $response = $this->decoder->decode((string) $response->getBody());
            $peers = $response['peers'];
            $this->assertSame($peers, $request['peers']);
        }
    }

    /**
     * See if we have an httpd we can connect to
     *
     * @param string $host The hostname to connect to
     * @param int $port The port to use
     * @return boolean
     */
    private static function canConnectToHttpd($host, $port) {
        set_error_handler(function() { return true; });
        $sp = fsockopen($host, $port);
        restore_error_handler();

        if ($sp === false) {
            return false;
        }

        fclose($sp);

        return true;
    }

    /**
     * Start the built in httpd in php-5.4
     *
     * @param string $host The hostname to use
     * @param int $port The port to use
     * @param string $documentRoot The document root
     * @return int Returns the PID of the httpd
     */
    private static function startBuiltInHttpd($host, $port, $documentRoot) {
        if (version_compare(PHP_VERSION, '5.4.0') < 0) {
            return;
        }

        $command = sprintf('php -S %s:%d -t %s %s>/dev/null 2>&1 & echo $!',
                            $host,
                            $port,
                            $documentRoot,
                            __DIR__ . '/../../../../router.php');

        $output = array();
        exec($command, $output);

        return (int) $output[0];
    }
}
