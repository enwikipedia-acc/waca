<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Exception;
use PDO;
use Waca\Tasks\ConsoleTaskBase;

class PrecacheGeolocationTask extends ConsoleTaskBase
{
    public function execute()
    {
        $database = $this->getDatabase();
        $locationProvider = $this->getLocationProvider();

        while (true) {
            echo "Beginning txn\n";
            $database->beginTransaction();

            try {
                // fetch a bunch of un-geolocated IPs from the database.
                // Note we have to parse the forwardedip field in the database so we can test against the geolocation
                // table.
                //
                // This guarantees we get ten unlocated IPs back, unless there actually aren't 10 available.
                //
                // Alternatives include downloading a small set of forwarded IPs, splitting it in PHP, constructing an
                // IN() clause dynamically, sending that back to the database to check if there are geolocation entries,
                // then repeating until we have 10 to process - and the fact that we'd have to potentially retrieve all
                // IPs from the database before we find any at all. This way keeps all of that legwork in the database,
                // at the cost of a more complex query.
                $statement = $database->query(<<<SQL
                    SELECT /* PrecacheGeolocationTask */ p.prox
                    FROM (
                      SELECT trim(substring_index(substring_index(r.forwardedip, ',', n.n), ',', -1)) prox
                      FROM request r
                        INNER JOIN (
                          SELECT 1 n
                          UNION ALL SELECT 2
                          UNION ALL SELECT 3
                          UNION ALL SELECT 4
                          UNION ALL SELECT 5) n
                        ON char_length(r.forwardedip) - char_length(replace(r.forwardedip, ',', '')) >= n.n - 1
                      WHERE ip <> '127.0.0.1'
                    ) p
                    WHERE NOT EXISTS (SELECT 1 FROM geolocation g WHERE g.address = p.prox)
                    LIMIT 10;
SQL
                );

                $missingIps = $statement->fetchAll(PDO::FETCH_COLUMN);

                $count = count($missingIps);
                if ($count === 0) {
                    echo ". Found nothing to do.\n";
                    break;
                }

                echo ". Picked {$count} IP addresses\n";

                foreach ($missingIps as $ip) {
                    echo ". . Getting location for {$ip}...\n";
                    $data = json_encode($locationProvider->getIpLocation($ip));
                    echo ". . . {$data}\n";
                }

                echo ". IP location fetch complete.\n";
                $database->commit();
                echo ". Committed txn.\n";
            }
            catch (Exception $ex) {
                echo ". Encountered exception: " . $ex->getMessage() . "\n";
                $database->rollBack();
                echo ". Rolled back txn\n";
                throw $ex;
            }
            finally {
                if ($database->hasActiveTransaction()) {
                    $database->rollBack();
                    echo ". Rolled back txn\n";
                }
            }
        }

        echo "Done.\n";
    }
}