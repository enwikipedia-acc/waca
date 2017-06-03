<?php

if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} // Web clients die.

ini_set('display_errors', 1);
ini_set('memory_limit', '256M');

require_once 'config.inc.php';
require_once 'functions.php';
require_once 'includes/PdoDatabase.php';

/** @var PdoDatabase $database */
$database = gGetDb();

$locationProvider = new IpLocationProvider($database, $locationProviderApiKey);

$done = false;
while (!$done) {
    echo "Beginning txn\n";
    $database->beginTransaction();

    try {
        $statement = $database->query(<<<SQL
            SELECT p.prox
            FROM (
              SELECT trim(substring_index(substring_index(r.forwardedip, ',', n.n), ',', -1)) prox
              FROM request r
                INNER JOIN (SELECT 1 n
                            UNION ALL
                            SELECT 2
                            UNION ALL SELECT 3
                            UNION ALL
                            SELECT 4
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
            $done = true;
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
    } catch (Exception $ex) {
        echo ". Encountered exception: " . $ex->getMessage(). "\n";
        $database->rollBack();
        echo ". Rolled back txn\n";
        throw $ex;
    } finally {
        if($database->hasActiveTransaction()){
            $database->rollBack();
            echo ". Rolled back txn\n";
        }
    }
}

echo "Done.\n";
