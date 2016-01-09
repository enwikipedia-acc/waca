This is a brief installation guide for developers/testers etc to get this system up-and-running on a new machine.

# Prerequisites

* Web server
* MySQL 5.5+ (or equivalent)
* PHP 5.5+

The webserver must be configured to pre-process *.php files through the PHP engine before sending them to a client.

You must also have a database which you can use with the tool.

## PHP configuration
You'll also need some PHP extensions:

* mbstring
* mysql
* pdo
* pdo_mysql
* session
* date
* pcre
* curl
* mcrypt
* openssl

There's nothing special here, these are all standard PHP extensions that are bundled with PHP - you may 
just need to switch some of them on in the php.ini file.

Useful (but optional) extensions:
* xdebug (http://xdebug.org/)

## Known good configurations

### Production

* MySQL 5.5.46-0ubuntu0.12.04.2
* PHP 5.6.9-0+deb8u1
* Apache 2.4.10 (Debian)
* Debian Jessie (Wikimedia Labs)

### stwalkerster's main development environment

* MariaDB 10.0.15
* PHP 5.6.1 (NTS VC11-x86 build)
* PHP Development server
* Windows 10

# Basic information

You'll need to import the database, and create a config.local.inc.php file (see below). The database schema files can be found in the sql/ subdirectory, or a single-file dump can be found here: https://jenkins.stwalkerster.co.uk/job/waca-database-build/

# XAMPP setup

**Please note, XAMPP is not required. Any webserver properly configured will do. This is just a quick-start method if you want it.**

This was written using Windows 10.

1. Install Git (or Git Extensions! It's awesome. https://code.google.com/p/gitextensions/ )
2. Install XAMPP (https://www.apachefriends.org/index.html)
  3. This was tested using XAMPP 5.6.3
  4. You don't need to install anything other than Apache, PHP, MySQL, and PHPMyAdmin (technically, PHPMyAdmin is optional)
  5. This also assumes you're installing it under C:\xampp\.
5. Start Apache and MySQL from the XAMPP control panel
6. Clone the ACC repo to C:\xampp\htdocs\waca\
6. Browse to http://localhost/phpmyadmin/ and create a new database called "waca".
7. EITHER: 
  8. Import the following files into your new database in this order:
    8. C:\xampp\htdocs\waca\sql\db-structure.sql
    9. Anything in the C:\xampp\htdocs\waca\sql\seed\ directory
    10. Anything in the C:\xampp\htdocs\waca\sql\patches\ directory in numerical order.
  9. OR: Grab schema.sql from https://jenkins.stwalkerster.co.uk/job/waca-database-build/ IF THE BUILD IS SUCCESSFUL AND CURRENT and run that into the database.
10. Create the configuration file (see below).

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
$filepath = "C:/xampp/htdocs/waca/"; 
$cookiepath = '/waca/';

$whichami = "MyName";

// these turn off features which you probably want off for ease of development.
$enableEmailConfirm = 0;
$forceIdentification = false;
$locationProviderClass = "FakeLocationProvider";
$antispoofProviderClass = "FakeAntiSpoofProvider";
$rdnsProviderClass = "FakeRDnsLookupProvider";

$useOauthSignup = false;
$enforceOAuth = false;

```

This will become your personal config file. Any settings you define here will override those in config.inc.php.

Most settings are flags to turn on and off features for development systems which may or may not have the necessary tools or access keys to do stuff. Feel free to switch any of these on if you know what you're doing.

# First Login!

Browse to http://localhost/waca/acc.php, and log in! There's a user created by default called "Admin", with the password "Admin".

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
