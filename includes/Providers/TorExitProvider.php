<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Providers;

use Waca\Helpers\HttpHelper;
use Waca\PdoDatabase;

class TorExitProvider
{
    /** @var PdoDatabase */
    private $database;

    /**
     * TorExitProvider constructor.
     *
     * @param PdoDatabase $database
     */
    public function __construct(PdoDatabase $database)
    {
        $this->database = $database;
    }

    /**
     * Checks whether an IP address is a Tor exit node for one of the pre-cached IP addresses.
     *
     * @param string $ip IP Address
     *
     * @return bool
     */
    public function isTorExit($ip)
    {
        $statement = $this->database->prepare('SELECT COUNT(1) FROM tornodecache WHERE ipaddr = :ip');

        $statement->execute(array(':ip' => $ip));

        $count = $statement->fetchColumn();
        $statement->closeCursor();

        if ($count > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public static function regenerate(PdoDatabase $database, HttpHelper $httpHelper, $destinationIps)
    {
        $query = <<<SQL
INSERT INTO tornodecache (ipaddr, exitaddr, exitport)
VALUES (:ipaddr, :exitaddr, :exitport)
ON DUPLICATE KEY
UPDATE touched = CURRENT_TIMESTAMP, updateversion = updateversion + 1
SQL;

        $statement = $database->prepare($query);

        foreach ($destinationIps as $ip) {
            echo 'Fetching data for ' . $ip . PHP_EOL;

            $statement->bindValue(':exitaddr', $ip);

            $http = $httpHelper->get(
                'https://check.torproject.org/cgi-bin/TorBulkExitList.py',
                array(
                    'ip'   => $ip,
                    'port' => 80,
                ));

            $https = $httpHelper->get(
                'https://check.torproject.org/cgi-bin/TorBulkExitList.py',
                array(
                    'ip'   => $ip,
                    'port' => 443,
                ));

            foreach (array(80 => $http, 443 => $https) as $port => $response) {
                echo '  Running for port ' . $ip . ':' . $port . PHP_EOL;

                $statement->bindValue(':exitport', $port);

                $lines = explode("\n", $response);

                foreach ($lines as $line) {
                    // line contains a comment char, just skip the line.
                    // This is OK as of 2016-04-06  --stw
                    if (strpos($line, '#') !== false) {
                        continue;
                    }

                    $statement->bindValue(':ipaddr', $line);
                    $statement->execute();
                }
            }

            echo 'Done for ' . $ip . PHP_EOL;
        }

        // kill old cached entries
        $database->exec('DELETE FROM tornodecache WHERE touched < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)');
    }
}