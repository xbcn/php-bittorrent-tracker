# PHP BitTorrent Tracker
PHP BitTorrent Tracker is a standalone torrent tracker written in PHP. It is not a complete tracker system with user registration and such (like for instance [BtiTracker](http://sourceforge.net/projects/btit-tracker/)), but a simple standalone tracker. You can extend it to do user statistics and such using event listeners.

## Requirements
PHP BitTorrent Tracker requires PHP 5.3.x or above. The recommended version is 5.3.2 or newer.

It also requires the [pecl/mongo](http://pecl.php.net/mongo) extension (>= 1.3.0) or [Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html).

## Installation
PHP BitTorrent Tracker can be installed using Composer:

```
wget https://getcomposer.org/installer | php
php composer.phar create-project christeredvartsen/php-bittorrent-tracker
```

You will also need a web server for the tracker.

### Apache
A sample virtual host configuration file can be found in `config/tracker.apache.conf.dist`.

### Built in httpd in php-5.4
You can also use the [built in httpd in php-5.4](http://no2.php.net/manual/en/features.commandline.webserver.php) to run the tracker. This is mostly used for testing purposes. Run the following command in the project root directory to start the tracker on port [1349](http://en.wikipedia.org/wiki/1349_%28band%29):

```php
php -S localhost:1349 -t public
```

If you want to support `/announce` and `/scrape` when running the built in httpd, you can use a router:

```php
<?php
if ($_SERVER['SCRIPT_NAME'] === '/announce') {
    require 'public/tracker.php';
    exit;
} else if ($_SERVER['SCRIPT_NAME'] === '/scrape') {
    require 'public/scrape.php';
    exit;
}

return false;
```

and start the built in httpd like this:

```php
php -S localhost:1349 -t public scripts/router.php
```
## Configuration
The tracker will per default use `config/config.default.php` for configuration. You can provide your own `config/config.php` where you can override the default values. If you for instance want to enable automatic registration of new torrents, you can enable it like this in your `config/config.php` file:

```php
<?php
return array(
    'tracker' => array(
        'autoRegister' => true,
    ),
);
```

Have a look in the `config/config.default.php` file to see which configuration values you might want to override.


## Event listeners
The tracker enables you to customize the way it works by letting you attach your own event listeners to the triggered events. By using this you can for instance require user id's or specific keys present in the URL (if you want to record download/upload statistics for instance). Such an event listener is shipped with the package, and you can enable it like this in your `config/config.php` file:

```php
<?php
namespace PHP\BitTorrent\Tracker\Event;

return array(
    // ...

    'eventListeners' => array(
        'userStats' => new Listener\UserStats(),
    ),

    // ...
);
```

The listener uses MongoDB for storing the data, and is mostly included to showcase the event listener system. Have a look at the documentation in `config/config.default.php` to see how you can attach listeners if other ways.

## Generate downloaded info file for bttrack
If you want to stop using this tracker and migrate to bttrack (the official [BitTorrent](http://www.bittorrent.com) tracker) you can generate a compatible file using a shell script that ships with PHP BitTorrent Tracker:

```
./scripts/generateDFile.php > dfile
```

After doing this you can start bttrack using the generated file:

```
bttrack --port 1349 --dfile dfile
```

The script that generates this file uses the current tracker configuration.
