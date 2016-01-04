This is a brief installation guide for developers/testers etc to get this system up-and-running on a new machine.

There are two ways to set up your development environment - do it yourself (install all the software and prerequisites
manually on your own), or use Vagrant to automagically set everything up for you in a nice self-contained VM.

# With Vagrant
Follow these directions to set up your development environment automagically with Vagrant.  Proceed to the "ACC Setup"
section when done.

**WARNING: The Vagrant environment bundled in this repository is NOT SUITABLE FOR PRODUCTION USE.  It must ONLY
be used for development purposes only!**

## Prerequisites
To use the automagic setup with Vagrant, you'll just need to install two pieces of software - Vagrant, which acts as
an automatic provisioning system, and Oracle VirtualBox, which provides the actual virtual machine service Vagrant
uses.

**Note that as of this writing, Windows 10 is *not* supported!**

As of this writing, Vagrant 1.7.4 or greater is required, though for good measure, you should probably be running
Vagrant 1.8.0 or greater - as of this writing, 1.8.1 is the latest version.  You can download Vagrant from
[here][vagrant-site].

You'll also need to install Oracle VirtualBox, which Vagrant will use to create its virtual machine environment.
VirtualBox 5.0.0 or later is required.  You can download VirtualBox from [here][virtualbox-site] - make sure you
perform a full installation so that all necessary drivers are installed.

## Create Vagrant environment
To create your virtual Vagrant environment, just open a terminal (Command Prompt on Windows) and change directories to
the folder you checked-out ACC's code to, then just run the command `vagrant up`.  It's that simple!

Vagrant will automatically download everything you need to create a development environment for ACC, from the OS
(currently Ubuntu 14.04LTS x64) to Apache and MySQL.  It will also import a seeded ACC-ready database into MySQL.

If this is your first time launching the Vagrant instance, please be patient - it will take some time to get everything
set up.  It took about 15 minutes on FastLizard4's laptop to do the first-time instance creation.  But, it's all
automated!

When the vagrant process terminates, your instance is ready to use!  The contents of your local waca repository will
automatically sync to Vagrant continuously while the VM is running.

When you're done with your Vagrant instance, you can run the command `vagrant halt` to power off the VM but leaving it
in place and ready to be used.  When you next run `vagrant up`, the VM will power up rapidly and be ready for use almost
immediately.  Alternatively, you can run `vagrant destroy` which will remove all traces of the VM from your system.  In
this case, the next time you run `vagrant up`, Vagrant will build a fresh VM as if it was your first time.

Finally, the command `vagrant provision` will re-run the setup procedure and update the software installed on your VM.

## Set up hostname entry
Before you can access your VM's web server, you'll need to add its IP address to your hostsfile.  How this is done
varies based on the OS; you can find some information on how to do this [on Wikipedia][wikipedia-hostsfile].

You'll want to point the hostname 'acc-vagrant' to the VM's IP address, which defaults to 192.168.56.101.

## Important information
Some information you'll need to know when configuring ACC in your new Vagrant instance:

* The MySQL root password is '123'.  A user with all permissions was also automatically created; the username is 'acc'
  and the password is also '123'.
* The VM's hostname is acc-vagrant, and once you have set up the hostname entry as described in the above section, will
  be accessible at http://acc-vagrant.
* The Vagrant instance's PHP installation is bundled with [xdebug][xdebug-site].

# Do-It-Yourself
Follow these directions to set up your development environment manually.  Proceed to the "ACC Setup" section when done.

## Prerequisites

* Web server
* MySQL 5.5+ (or equivalent)
* PHP 5.3+

The webserver must be configured to pre-process *.php files through the PHP engine before sending them to a client.

You must also have a database which you can use with the tool.

### PHP configuration
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

### Known good configurations

#### Production

* MySQL 5.5.40-0ubuntu0.12.04.1
* PHP 5.3.10-1ubuntu3.15
* Apache 2.2.22 (Ubuntu)
* Ubuntu 12.04 LTS (Wikimedia Labs)

#### stwalkerster's main development environment

* MariaDB 5.5.35
* PHP 5.5.11 (NTS VC11-x86 build)
* IIS 8.5
* Windows 8.1

## Basic information

You'll need to import the database, and create a config.local.inc.php file (see below). The database schema files can be found in the sql/ subdirectory, or a single-file dump can be found here: https://jenkins.stwalkerster.co.uk/job/waca-database-build/

## XAMPP setup

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

# ACC Setup

## Configuration File
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

If you used Vagrant to set up your ACC development environment, you'll want to use the following "paths and stuff"
configuration instead of the options given above:

```

// Paths and stuff
$baseurl = "http://acc-vagrant/waca";
$filepath = "/var/www/waca/"; 
$cookiepath = '/waca/';

```

Most settings are flags to turn on and off features for development systems which may or may not have the necessary tools or access keys to do stuff. Feel free to switch any of these on if you know what you're doing.

## First Login!

Browse to http://localhost/waca/acc.php (or, if you used Vagrant, http://acc-vagrant/waca/acc.php), and log in! There's
a user created by default called "Admin", with the password "Admin".

## OAuth setup

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

[vagrant-site]: https://www.vagrantup.com
[virtualbox-site]: https://www.virtualbox.org/
[xdebug-site]: http://xdebug.org/
[wikipedia-hostsfile]: https://en.wikipedia.org/wiki/Hosts_(file)
