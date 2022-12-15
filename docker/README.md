This page describes running a development/testing instance of ACC within Docker, which may be easier than performing a
"bare metal" install on a web server.

**Note that, currently, this is only intended for development and testing use, and this should not be used in
production (yet).** If you're looking for the "bare metal" installation instructions, please see
[here](../INSTALLING.md).

OAuth is also not covered as part of this document, nor is it handled by the Docker ACC setup. Refer to the
[INSTALLING](../INSTALLING.md) file for OAuth setup instructions.

# Prerequisites
* Reasonably recent version of Docker with Docker Compose support on any Docker-supported platform
  * Tested with Docker Desktop v4.0+ on Windows and macOS
* (Optional) A reverse-proxy/tunnel tool with TLS support for testing features that require HTTPS, such as WebAuthn
  * For example, [ngrok](https://ngrok.com/)

Note that this document assumes that you cloned this repo into a folder called "waca". If this is not the case, some
names of things may differ from what is given below (e.g., container and volume names will be prefixed with whatever
you named the folder instead of "waca").

# Use
To start ACC using Docker, simply navigate in your terminal to the folder where you have cloned the waca repo and run
the command `docker compose up --detach`. This will automatically pull the containers needed to run ACC and its
dependencies and install prerequisites including Composer automatically. You shouldn't need to do anything else!

Note that the first run will require an Internet connection and may take some time as images are downloaded from Docker
Hub; however, subsequent starts should generally be faster and shouldn't require an Internet connection as things will
be saved locally.

Four Docker Compose services will be started, each within its own container:

| Service name  | Container name       | Description                                                       |
|---------------|----------------------|-------------------------------------------------------------------|
| `application` | `waca-application-1` | Apache web server with PHP 7.4 hosting the actual ACC app.        |
| `database`    | `waca-database-1`    | MariaDB 10.5 database server used by ACC.                         |
| `msgbroker`   | `waca-msgbroker-1`   | RabbitMQ 3.8 message broker server used for notifications.        |
| `mailsink`    | `waca-mailsink-1`    | A simple mailsink running a dummy SMTP server which ACC will use. |

If it doesn't already exist, a `config.local.inc.php` file will automatically be created in the repo root containing
sane defaults for a Docker-based ACC environment, and configuring ACC to work with the other services provided within
the Docker Compose environment. (If you already have a `config.local.inc.php`, you may wish to delete it before starting
ACC in Docker, or compare it with the [Docker default ACC config](config.local.inc.php) to see what you should change.)

In addition, on first run, the database service will automatically create a new database, apply migrations, and seed
some initial data, so it will be ready for use.

Once started, the application can be accessed at http://localhost:8080. Log in to the internal interface at
http://localhost:8080/internal.php with the username `Admin` and password `enwpaccAdmin1!`. XDebug is installed and
will attempt to contact your IDE on port 9003.

Any changes you make in the repo to the app, including code and config changes, should reflect automatically and almost
immediately without needing to restart any Docker services or containers.

(Note: If you optionally want to use a reverse-proxy/tunnel service like ngrok to access ACC, you will need to update
the `$baseurl` variable in your `config.local.inc.php` file _in the repo root_ accordingly, then use that to access ACC.
Do _not_ update the `config.local.inc.php` file in the `docker` folder!)

The database can be accessed at `localhost:3306` using a standard MariaDB or MySQL client. The database is called
`waca`. There will also be a `root` user and a `waca` user, both with the password `waca`.

The RabbitMQ server can be accessed at `localhost:5672`, while the web-based RabbitMQ Management Interface can be
accessed at http://localhost:15672 with the username `guest` and password `guest`. The Management Interface is useful
for easily reading the queues to see if IRC notifications are as you expect. ACC will send everything to a queue called
`main`.

The mailsink can be accessed by visiting http://localhost:8081, no auth required. Your ACC instance will send all emails
here.

To shut down ACC, simply run the command `docker compose down`. Note that while the MariaDB database contents will
persist through restarts, RabbitMQ and mailsink data may not.

If you wish to reset your database and start from scratch, shut down ACC then simply delete the Docker volume for the
database using the command `docker volume rm waca_mysql-data` (note `mysql` instead of `mariadb` for compatibility). The
database will be re-created and re-seeded on next start (useful for resolving migration conflicts).

You can also blow away your entire setup by shutting down ACC then running the command `docker system prune -af
--volumes`, however this will also affect any non-ACC Docker containers and volumes you may have. (You may wish to be
more surgical but this is left as an exercise for the reader). You shouldn't need to do this unless something has really
broken, or you really need to ensure that you are starting from scratch. The next time you run ACC will take longer as
images are re-downloaded and an Internet connection will be required.

# Implementation details
A brief explanation of the various files that go into making ACC work in Docker, should you wish to mess with them. In
general, you will need to stop (`docker compose down`) and start (`docker compose up --detach`) for changes to any of
these files to have effect.

* [`/docker-compose.yml`](/docker-compose.yml) - Defines the Docker Compose setup, including the four services and their
  basic configurations.
* [`/Dockerfile`](/Dockerfile) - Defines how the container for the `application` container, which will run ACC itself,
  is built. Essentially, these are the steps being run to configure the "server" that will run ACC, including installing
  packages through `apt-get`, installing Composer, and setting up XDebug.
* [`/docker/config.local.inc.php`](/docker/config.local.inc.php) - The default config file that will be copied to the
  repo root when starting ACC in Docker, if no config already exists.
* [`/docker/database.sh`](/docker/database.sh) - Script that is run on start by the `database` service if the `waca`
  database does not exist.
* [`/docker/entrypoint.sh`](/docker/entrypoint.sh) - Script that is run by the `application` service on every start.
  Notably, creates the initial config (if it doesn't exist) and starts Apache.
* [`/docker/msmtprc`](/docker/msmtprc) - SMTP config for the `application` service; defines where ACC (or, more
  accurately, PHP) will send all emails
* [`/docker/rabbitmq.conf`](/docker/rabbitmq.conf) - RabbitMQ broker server base configuration
* [`/docker/rabbitmq-definitions.json`](/docker/rabbitmq-definitions.json) - Defines the RabbitMQ user, exchanges,
  queues, bindings, etc. Should not be directly modified; instead, [generated by RabbitMQ itself][rabbitmq-schema-def].

[rabbitmq-schema-def]: https://www.rabbitmq.com/definitions.html
