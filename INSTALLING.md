This is a brief installation guide for developers/testers etc to get this system up-and-running on a new machine.

**Note that this document is for installing ACC in a "bare metal" environment. If you have Docker available and would
like to use that instead for essentially one-click ACC setup, please see [here](docker/README.md).**

# Prerequisites

* Web server
* MariaDB 10.11.3 (or equivalent)
* PHP 7.4+

The webserver must be configured to pre-process *.php files through the PHP engine before sending them to a client.

You must also have a database which you can use with the tool.

Note: MariaDB 10.2.1 is likely sufficient, but compatibility cannot be guaranteed as Production runs on 10.11.3.

## PHP configuration
You'll also need some PHP extensions:

* curl
* date
* mbstring
* openssl
* pcre
* pdo
* pdo_mysql
* session

There's nothing special here, these are all standard PHP extensions that are bundled with PHP - you may
just need to switch some of them on in the php.ini file.

Useful (but optional) extensions - only used for development:
* xdebug (http://xdebug.org/)
* runkit7 (http://pecl.php.net/runkit7 / https://github.com/runkit7/runkit7)

Note that runkit is a pain[1] to get working on Windows, and is only used by some unit tests. runkit7 may be different;
it's not known if anyone's actually tried it yet.

## Known good configurations

### Production

* MariaDB 10.11.3
* PHP 7.4.30
* Apache 2.4.54 (Debian)
* Debian Bullseye (Application; Wikimedia Cloud VPS)
* Debian Bookworm (Database; Wikimedia Cloud VPS)

# Basic information

You'll need to import the database, and create a config.local.inc.php file (see below). The database schema files can be found in the sql/ subdirectory, or a single-file dump can be found here: https://jenkins.stwalkerster.co.uk/job/waca-database-build/

# Basic setup

These steps assume you are comfortable with git, configuring a basic webserver with PHP support, managing a MariaDB database.

1. Clone this repo to your webserver's document root. You can put it in a subfolder if you wish.
2. Configure a new MariaDB database (eg `waca`) and a MariaDB user (eg `waca` / `waca`) with full permissions over that database
3. Run `npm install`
4. Generate the stylesheets:
  * `npm run build-scss`
5. run the database setup scripts (you'll be prompted for a password):
  * `./test_db.sh --create --host localhost --schema <dbname> --user <user>`
6. Create the configuration file (see below).

# Configuration File
Create a new PHP file called config.local.inc.php, and fill it with the following:
```php
<?php

// Database configuration settings
$toolserver_username = "waca";
$toolserver_password = "waca";
$toolserver_host = "localhost";
$toolserver_database = "waca";

// Disconnect from IRC notifications and the wiki database.
$ircBotNotificationsEnabled = 0;

// Paths and stuff
$baseurl = "http://localhost/waca"; // assuming install in a subfolder
$cookiepath = '/waca/';

$whichami = "MyName";

// these turn off features which you probably want off for ease of development.
$enableEmailConfirm = 0;

$useOauthSignup = false;
$enforceOAuth = false;
$forceIdentification = false;
$toolUserAgent = "SomethingIdentifyingYou (+mailto:contactdetailshere)";


```

This will become your personal config file. Any settings you define here will override those in config.inc.php.

NOTE: You should review config.inc.php in its entirety as it will likely contain other settings you are interested in.

Most settings are flags to turn on and off features for development systems which may or may not have the necessary tools or access keys to do stuff. Feel free to switch any of these on if you know what you're doing.

# First Login!

Browse to http://localhost/waca/internal.php, and log in! There's a user created by default called "Admin", with the password "enwpaccAdmin1!".

Note that this user account is configured to skip the standard checks that the user has identified to the Wikimedia Foundation. By default, all additional users created will require an "on-wiki" username to match a username on the identification noticeboard, or will require the `forceidentified` flag to be set to `1` in the `user` table in the database. There is no user interface for doing this by design. 

# OAuth setup

OK, so this is a tricky one, but worth doing so your environment is the same as the production one.

1. Get an account on https://accounts-oauth.wmflabs.org/ - you'll need to ask someone who's already got an account to create you one.
2. Go to `Special:OAuthConsumerRegistration`, and request a token for a new consumer. The OAuth "callback" URL field needs to point to /internal.php/oauth/callback/authorise (or wherever that script is located on your webserver). Importantly, this must be how *you* see it - it's fine to put a localhost url in here. It's used as the target of a redirect, so as long as your browser can see it, it should be fine. At the moment, we only need basic rights, but that may change in the future. Ignore the usage restrictions and RSA key - we don't use those.
3. You'll get two hexadecimal strings - **don't lose these** - put them in your config.local.inc.php file as `$oauthConsumerToken` and `$oauthSecretToken`
4. Go to `Special:OAuthManageConsumers/proposed`, click review/manage on your consumer and approve it.
5. In the tool, go to Domain Management and modify the connected wiki article and API paths to point to the equivalents for https://accounts-oauth.wmflabs.org/
6. Set up a few more properties in config.local.inc.php:

```php
$oauthMediaWikiCanonicalServer = "http://accounts-oauth.wmflabs.org";

// optional
$useOauthSignup = true;
$enforceOAuth = true;
```



You should now be able to use OAuth!

[1]: https://github.com/zenovich/runkit/issues/22
