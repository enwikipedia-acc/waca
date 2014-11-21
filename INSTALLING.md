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

# Basic information

You'll need to import the database, and create a config.local.inc.php file.

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
10. Create a new PHP file
