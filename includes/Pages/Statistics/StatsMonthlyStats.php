<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\Statistics;

use PDO;
use Waca\Tasks\InternalPageBase;

class StatsMonthlyStats extends InternalPageBase
{
    public function main()
    {
        $this->setHtmlTitle('Monthly Stats :: Statistics');

        $query = <<<SQL
WITH activemonths AS (
    -- Pull all values from two generated sequences (values 2008 to 2050, and values 1 to 12) to generate dates.
    -- Filter the resulting set down to months between the earliest and latest log entries.
    -- This gives us a complete list of all months when the tool has been active, even if there was no activity on the
    -- tool for a specific month, which we use to avoid a cross-join (which is... awkward... in MySQL)
    SELECT (y.seq * 100) + m.seq AS sortkey
    FROM seq_2008_to_2050 y
             CROSS JOIN seq_1_to_12 m
    WHERE (y.seq * 100) + m.seq >= (SELECT MIN(EXTRACT(YEAR_MONTH FROM timestamp)) FROM log WHERE timestamp > 0)
      AND (y.seq * 100) + m.seq <= (SELECT MAX(EXTRACT(YEAR_MONTH FROM timestamp)) FROM log WHERE timestamp > 0)
),
submitted AS (
    -- this join on activemonths is needed to properly coalesce values to 0 after the point at which email
    -- confirmation was enabled - null afterwards means no data, thus zero. Null beforehand means no email
    -- confirmation
    SELECT activemonths.sortkey,
           CASE WHEN activemonths.sortkey > 200910 THEN COALESCE(s.submitted, 0) ELSE s.submitted END as submitted
    FROM activemonths
             LEFT JOIN (
        SELECT EXTRACT(YEAR_MONTH FROM l.timestamp) AS sortkey,
               COUNT(DISTINCT l.objectid)           AS submitted
        FROM log l
        WHERE 1 = 1
          AND l.action = 'Email Confirmed'
          AND l.objecttype = 'Request'
        GROUP BY EXTRACT(YEAR_MONTH FROM l.timestamp)
    ) s ON activemonths.sortkey = s.sortkey
),
closed AS (
    -- distinct requests which have a request closure log entry in a month
    SELECT
        EXTRACT(YEAR_MONTH FROM l.timestamp) AS sortkey,
        COUNT(DISTINCT l.objectid) AS closed
    FROM log l
    WHERE l.action LIKE 'Closed%'
      AND l.objecttype = 'Request'
      AND l.timestamp > 1
    GROUP BY EXTRACT(YEAR_MONTH FROM l.timestamp)
),
activeusers AS (
    -- distinct users who have a log entry in a month
    SELECT
        EXTRACT(YEAR_MONTH FROM l.timestamp) AS sortkey,
        COUNT(DISTINCT l.user) AS activeusers
    FROM log l
    WHERE 1=1
      AND l.action <> 'ReceiveReserved'
      AND l.user <> -1
    GROUP BY EXTRACT(YEAR_MONTH FROM l.timestamp), MONTHNAME(l.timestamp), EXTRACT(YEAR FROM l.timestamp)
),
responsetimestats AS (
    -- average first-response time for requests, split by deferred and non-deferred requests
    SELECT EXTRACT(YEAR_MONTH from d.submitdate) AS sortkey,
           ROUND(AVG(CASE WHEN d.deferred = 0 THEN UNIX_TIMESTAMP(d.responsedate) - UNIX_TIMESTAMP(d.confirmdate) END), 0) AS nondeferred,
           ROUND(AVG(CASE WHEN d.deferred = 1 THEN UNIX_TIMESTAMP(d.responsedate) - UNIX_TIMESTAMP(d.confirmdate) END), 0) AS deferred,
           STDDEV_POP(CASE WHEN d.deferred = 0 THEN UNIX_TIMESTAMP(d.responsedate) - UNIX_TIMESTAMP(d.confirmdate) END) AS nondeferred_stddev,
           STDDEV_POP(CASE WHEN d.deferred = 1 THEN UNIX_TIMESTAMP(d.responsedate) - UNIX_TIMESTAMP(d.confirmdate) END) AS deferred_stddev
    FROM (
        -- distinct request/response pairs, but only the minimum timestamp on first response.
        -- aka the first response.
        SELECT DISTINCT 
            r.id,
            r.date                                                 AS submitdate,
            log_emailconf.timestamp                                AS confirmdate,
            MIN(log_close.timestamp) OVER (PARTITION BY r.id)      AS responsedate,
            CASE WHEN log_deferral.timestamp > 0 THEN 1 ELSE 0 END AS deferred
        from request r
            -- join on email confirmation log entries for this request
            INNER JOIN log log_emailconf ON 1=1 
                AND log_emailconf.objecttype = 'Request' 
                AND log_emailconf.objectid = r.id 
                AND log_emailconf.action = 'Email Confirmed'
            -- join on the first close event for this request
            LEFT JOIN (
                SELECT logfc.objectid AS request, MIN(logfc.timestamp) AS timestamp
                FROM log logfc
                WHERE 1 = 1
                    AND (logfc.action LIKE 'Closed %' OR logfc.action = 'SentMail')
                    AND logfc.action <> 'Closed 0'
                    AND logfc.objecttype = 'Request'
                    AND logfc.timestamp > 0
                GROUP BY logfc.objectid
            ) log_close ON log_close.request = r.id
            -- join on the first deferral event for this request. Used only as a flag to determine if the request
            -- was deferred.
            LEFT JOIN (
                SELECT logfd.objectid AS request, MIN(logfd.timestamp) AS timestamp
                FROM log logfd
                WHERE 1 = 1
                    AND logfd.action LIKE 'Deferred to %'
                    AND logfd.objecttype = 'Request'
                    AND logfd.timestamp > 0
                    AND logfd.user <> -1 -- skip system deferrals
                GROUP BY logfd.objectid
            ) log_deferral ON log_deferral.request = r.id
            WHERE 1 = 1
                AND r.status = 'Closed'
                AND log_close.timestamp IS NOT NULL
        ) d
    GROUP BY EXTRACT(YEAR_MONTH FROM d.submitdate)
)
SELECT /* StatsMonthlyStats */
    sortkey,
    year, month,
    submitted,
    submitted - LAG(submitted, 1) OVER (ORDER BY sortkey) AS submitted_delta,
    closed,
    closed - LAG(closed, 1) OVER (ORDER BY sortkey) AS closed_delta,
    submitted - closed AS open_req_delta,
    activeusers,
    activeusers - LAG(activeusers, 1) OVER (ORDER BY sortkey) AS activeusers_delta,
    nondeferred,
    nondeferred - LAG(nondeferred, 1) OVER (ORDER BY sortkey) AS nondeferred_delta,
    nondeferred_stddev,
    deferred,
    deferred - LAG(deferred, 1) OVER (ORDER BY sortkey) AS deferred_delta,
    deferred_stddev
FROM (
     SELECT activemonths.sortkey,
            substr(activemonths.sortkey, 1, 4) AS year,
            monthname(str_to_date(concat('2000-',substr(activemonths.sortkey, 5, 2),'-01'), '%Y-%m-%d')) AS month,
            submitted.submitted, -- the coalesce for this is done in the CTE instead.
            COALESCE(closed.closed, 0) as closed,
            COALESCE(activeusers.activeusers, 0) as activeusers,
            nondeferred, -- no coalesce is appropriate here, due to this being an average already
            nondeferred_stddev,
            deferred, -- no coalesce is appropriate here, due to this being an average already
            deferred_stddev
     FROM activemonths
         LEFT JOIN submitted ON submitted.sortkey = activemonths.sortkey
         LEFT JOIN closed ON closed.sortkey = activemonths.sortkey
         LEFT JOIN activeusers ON activeusers.sortkey = activemonths.sortkey
         LEFT JOIN responsetimestats ON responsetimestats.sortkey = activemonths.sortkey
) outerjoin
ORDER BY sortkey ASC;
SQL;

        $database = $this->getDatabase();
        $statement = $database->query($query);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->assign('dataTable', $data);
        $this->assign('statsPageTitle', 'Monthly Statistics');
        $this->setTemplate('statistics/monthly-stats.tpl');
    }
}
