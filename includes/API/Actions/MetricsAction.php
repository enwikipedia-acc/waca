<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\API\Actions;

use PDO;
use Waca\API\IApiAction;
use Waca\Tasks\TextApiPageBase;

/**
 * API Metrics action
 */
class MetricsAction extends TextApiPageBase implements IApiAction
{
    private array $metrics = [];

    private function defineMetric(string $name, string $help, string $type = 'gauge'): void
    {
        $this->metrics[$name] = ['help' => $help, 'type' => $type, 'values' => []];
    }

    private function setMetric(string $name, array $labels = [], int $value = 0): void
    {
        $calculatedLabel = '';

        if (count($labels) > 0) {
            ksort($labels);

            $labelData = [];
            foreach ($labels as $label => $labelValue) {
                $labelData[] = $label . '="' . $labelValue . '"';
            }

            $calculatedLabel = '{' . implode(',', $labelData) . '}';
        }

        $this->metrics[$name]['values'][$calculatedLabel] = $value;
    }

    public function runApiPage(): string
    {
        $this->defineMetric('acc_users', 'Number of users');
        $statement = $this->getDatabase()->query('SELECT status, COUNT(*) AS count FROM user GROUP BY status;');

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->setMetric('acc_users', ['status' => $row['status']], $row['count']);
        }
        $statement->closeCursor();

        $this->defineMetric('acc_active_domain_users', 'Number of active users in each domain');
        $statement = $this->getDatabase()->query('
            SELECT d.shortname, COUNT(1) AS count FROM userdomain ud 
            INNER JOIN user u ON ud.user = u.id
            INNER JOIN domain d on ud.domain = d.id
            WHERE u.status = \'Active\'
            GROUP BY d.shortname;');

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->setMetric('acc_active_domain_users', ['domain' => $row['shortname']], $row['count']);
        }
        $statement->closeCursor();


        $this->defineMetric('acc_active_domain_roles', 'Number of active users in each role');
        $statement = $this->getDatabase()->query('
            SELECT coalesce(d.shortname, \'\') AS domain, ur.role, COUNT(1) AS count
            FROM userrole ur
            INNER JOIN user u ON ur.user = u.id
            LEFT JOIN domain d ON ur.domain = d.id
            WHERE u.status = \'Active\' AND ur.role <> \'user\'
            GROUP BY d.shortname, ur.role;');

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->setMetric('acc_active_domain_roles', ['domain' => $row['domain'], 'role' => $row['role']], $row['count']);
        }
        $statement->closeCursor();


        $this->defineMetric('acc_active_domain_bans', 'Number of active bans in each domain');
        $statement = $this->getDatabase()->query('
            SELECT coalesce(d.shortname, \'\') AS domain, COUNT(1) AS count
            FROM ban b LEFT JOIN domain d ON b.domain = d.id
            WHERE (b.duration > UNIX_TIMESTAMP() OR b.duration is null) AND b.active = 1
            GROUP BY d.shortname;');

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->setMetric('acc_active_domain_bans', ['domain' => $row['domain'], 'role' => $row['role']], $row['count']);
        }
        $statement->closeCursor();


        $this->defineMetric('acc_queued_requests', 'Number of requests in each queue');
        $statement = $this->getDatabase()->query('
            SELECT r.status, d.shortname, rq.header, COUNT(1) as count FROM request r
            INNER JOIN domain d on r.domain = d.id
            LEFT JOIN waca.requestqueue rq ON r.queue = rq.id
            WHERE r.status <> \'Closed\' AND r.emailconfirm = \'Confirmed\'
            GROUP BY r.status, d.shortname, rq.header;');

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->setMetric('acc_queued_requests', ['status' => $row['status'], 'shortname' => $row['shortname'], 'queue' => $row['header']], $row['count']);
        }
        $statement->closeCursor();

        return $this->writeMetrics();
    }

    private function writeMetrics() : string
    {
        $data = '';

        foreach ($this->metrics as $name => $metricData) {
            $data .= "# HELP {$name} {$metricData['help']}\n";
            $data .= "# TYPE {$name} {$metricData['type']}\n";
            foreach ($metricData['values'] as $label => $value) {
                $data .= "{$name}{$label} {$value}\n";
            }
        }

        return $data;
    }
}
