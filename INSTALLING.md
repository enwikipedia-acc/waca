This is a brief installation guide for developers/testers etc to get this system up-and-running on a new machine.

# Prerequisites

* Web server
* MySQL 5.5+ (or equivalent)
* PHP 5.3+

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

* MySQL 5.5.40-0ubuntu0.12.04.1
* PHP 5.3.10-1ubuntu3.15
* Apache 2.2.22 (Ubuntu)
* Ubuntu 12.04 LTS (Wikimedia Labs)

### stwalkerster's main development environment

* MariaDB 5.5.35
* PHP 5.5.11 (NTS VC11-x86 build)
* IIS 8.5
* Windows 8.1

