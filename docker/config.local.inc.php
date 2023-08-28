<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

// Sane defaults for Docker-based ACC development environments. When running `docker compose up`,
// this file will be copied into the ACC root if a local config file does not already exist.

$toolserver_username = "waca";
$toolserver_password = "waca";
$toolserver_host = "database";
$toolserver_database = "waca";

$whichami = 'docker_dev';

// URL of the current copy of the tool.
$baseurl = "http://localhost:8080";
$cookiepath = '/';

$amqpConfiguration = ['host' => 'msgbroker', 'port' => 5672, 'user' => 'guest', 'password' => 'guest', 'vhost' => '/', 'exchange' => 'main', 'tls' => false];

$enableEmailConfirm = 1; // Emails will be sent to the mailsink docker-compose service
