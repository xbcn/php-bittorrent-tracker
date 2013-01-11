#!/usr/bin/env php
<?php
/**
 * This file is part of the PHP BitTorrent Tracker package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent\Tracker\Backend;

use PHP\BitTorrent\Encoder;

$config = require __DIR__ . '/../config/config.default.php';

$backend = $config['backend'];

if (is_callable($backend) && !($backend instanceof BackendInterface)) {
    $backend = $backend();
}

if (!$backend instanceof BackendInterface) {
    echo "Invalid backend definition" . PHP_EOL;
    exit(1);
}

$result = array(
    'completed' => array(),
    'peers' => array(),
);

$numTorrents = $backend->getNumTorrents();

foreach ($backend->getTorrents(1, $numTorrents) as $infoHash) {
    $result['completed'][$infoHash] = (int) $backend->getNumTorrentDownloads($infoHash);

    foreach ($backend->getTorrentPeers($infoHash) as $peer) {
        $result['peers'][$infoHash][$peer->id()] = array(
            'ip' => $peer->ip(),
            'left' => $peer->left(),
            'port' => $peer->port(),
        );
    }
}

$encoder = new Encoder(array(
    'encodeEmptyArrayAsDictionary' => true,
));
echo $encoder->encode($result);
