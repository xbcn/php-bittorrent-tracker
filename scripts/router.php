<?php
/**
 * This file is part of the PHP BitTorrent
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

if ($_SERVER['SCRIPT_NAME'] === '/announce') {
    require 'public/tracker.php';
    exit;
} else if ($_SERVER['SCRIPT_NAME'] === '/scrape') {
    require 'public/scrape.php';
    exit;
}

return false;
