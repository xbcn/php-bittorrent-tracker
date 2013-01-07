<?php
if ($_SERVER['SCRIPT_NAME'] === '/announce') {
    require 'public/tracker.php';
    exit;
} else if ($_SERVER['SCRIPT_NAME'] === '/scrape') {
    require 'public/scrape.php';
    exit;
}

return false;
