This is a brief installation guide for developers/testers etc to get this system up-and-running on a new machine.

# Prerequisites

* Web server
* MariaDB 10.3.22+ (or equivalent)
* PHP 7.3+

The webserver must be configured to pre-process *.php files through the PHP engine before sending them to a client.

You must also have a database which you can use with the tool.

Note: MariaDB 10.2.1 is likely sufficient, but compatibility cannot be guaranteed as Production runs on 10.3.22.

## PHP configuration
You'll also need some PHP extensions:

* curl
* date
* dom
* gmp
* json
* mbstring
* openssl
* pcre
* pdo
* pdo_mysql
* session
* simplexml

There's nothing special here, these are all standard PHP extensions that are bundled with PHP - you may
just need to switch some of them on in the php.ini file.

Useful (but optional) extensions - only used for development:
* xdebug (http://xdebug.org/)
* runkit (http://pecl.php.net/runkit / https://github.com/zenovich/runkit/)

Note that runkit is a pain[1] to get working on Windows, and is only used by some unit tests.

## Known good configurations

### Production

* MariaDB 10.1.37
* PHP 7.3.14
* Apache 2.4.38 (Debian)
* Debian Buster (Wikimedia Labs)

# Basic information

You'll need to import the database, and create a config.local.inc.php file (see below). The database schema files can be found in the sql/ subdirectory, or a single-file dump can be found here: https://jenkins.stwalkerster.co.uk/job/waca-database-build/

# XAMPP setup

**Please note, XAMPP is not required. Any webserver properly configured will do. This is just a quick-start method if you want it.**

This was written using Windows 10.

1. Install Git (or Git Extensions! It's awesome. https://code.google.com/p/gitextensions/ )
2. Install XAMPP (https://www.apachefriends.org/index.html)
  * This was tested using XAMPP 5.6.3
  * You don't need to install anything other than Apache, PHP, MySQL, and PHPMyAdmin (technically, PHPMyAdmin is optional)
  * This also assumes you're installing it under C:\xampp\.
3. Start Apache and MySQL from the XAMPP control panel
4. Clone the ACC repo to C:\xampp\htdocs\waca\
5. Browse to http://localhost/phpmyadmin/ and create a new database called "waca".
6. Run `composer install` (https://getcomposer.org)
8. Generate the stylesheets:
  * `cd maintenance/; php RegenerateStylesheets.php`
8. run the database setup scripts:
  * `./test_db.sh 1 localhost <dbname> <user> <password>`
9. Create the configuration file (see below).

# Docker
There is **experimental** support for Docker. Knowledge of Docker is assumed here if you want to use it - this is not
a configuration which the team will spend much time to support.

You will still need to create the configuration file as below, but you should be able to just run `docker-compose up -d`
in this folder, and two containers should start - one for the database listening on port 3306, and one for the web
application listening on port 8080. The configuration should use "waca" as the username, password, and database name,
and "database" for the hostname. The `$baseurl` setting should also be set to `http://127.0.0.1:8080`. All other 
installation steps including dependencies and loading the initial database schema are handled automatically by Docker.

# Configuration File
Create a new PHP file called config.local.inc.php, and fill it with the following:
```php
<?php

// Database configuration settings
$toolserver_username = "root";
$toolserver_password = "";
$toolserver_host = "localhost";
$toolserver_database = "waca";

// Disconnect from IRC notifications and the wiki database.
$ircBotNotificationsEnabled = 0;
$dontUseWikiDb = 1;

// Paths and stuff
$baseurl = "http://localhost/waca";
$cookiepath = '/waca/';

$whichami = "MyName";

// these turn off features which you probably want off for ease of development.
$enableEmailConfirm = 0;
$locationProviderClass = "FakeLocationProvider";
$antispoofProviderClass = "FakeAntiSpoofProvider";
$rdnsProviderClass = "FakeRDnsLookupProvider";

$useOauthSignup = false;
$enforceOAuth = false;

```

This will become your personal config file. Any settings you define here will override those in config.inc.php.

Most settings are flags to turn on and off features for development systems which may or may not have the necessary tools or access keys to do stuff. Feel free to switch any of these on if you know what you're doing.

# First Login!

Browse to http://localhost/waca/internal.php, and log in! There's a user created by default called "Admin", with the password "enwpaccAdmin1!".

Note that this user account is configured to skip the standard checks that the user has identified to the Wikimedia Foundation. By default, all additional users created will require an "on-wiki" username to match a username on the identification noticeboard, or will require the `forceidentified` flag to be set to `1` in the `user` table in the database. There is no user interface for doing this by design. 

# OAuth setup

OK, so this is a tricky one, but worth doing so your environment is the same as the production one.

1. Get an account on https://accounts-oauth.wmflabs.org/ - you'll need to ask someone who's already got an account to create you one.
2. Go to `Special:OAuthConsumerRegistration`, and request a token for a new consumer. The OAuth "callback" URL field needs to point to /oauth/callback.php (or wherever that script is located on your webserver). Importantly, this must be how *you* see it - it's fine to put a localhost url in here. It's used as the target of a redirect, so as long as your browser can see it, it should be fine. At the moment, we only need basic rights, but that may change in the future. Ignore the usage restrictions and RSA key - we don't use those.
3. You'll get two hexadecimal strings - **don't lose these** - put them in your config.local.inc.php file as `$oauthConsumerToken` and `$oauthSecretToken`
4. Go to `Special:OAuthManageConsumers/proposed`, click review/manage on your consumer and approve it.
5. Set up a few more properties in config.local.inc.php:

```php
$oauthBaseUrl = "https://accounts-oauth.wmflabs.org/w/index.php?title=Special:OAuth";
$oauthBaseUrlInternal = $oauthBaseUrl;
$oauthMediaWikiCanonicalServer = "http://accounts-oauth.wmflabs.org";

$useOauthSignup = true;
$enforceOAuth = true;
```

You should now be able to use OAuth!

[1]: https://github.com/zenovich/runkit/issues/22
