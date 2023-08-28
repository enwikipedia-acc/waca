<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/
/** @noinspection SqlConstantCondition */

namespace Waca\ConsoleTasks;

use Waca\PdoDatabase;
use Waca\Tasks\ConsoleTaskBase;

class OldRequestCleanupTask extends ConsoleTaskBase
{
    public function execute()
    {
        $database = $this->getDatabase();
        $expiryTime = [':expiry' => $this->getSiteConfiguration()->getEmailConfirmationExpiryDays()];

        // start by fetching the number of unconfirmed requests which have expired
        $eligibleRecords = $this->getExpiredCount($database, $expiryTime);

        // fetch the number of unconfirmed requests which have expired and which have no FK constraints which would
        // otherwise prevent their deletion
        $eligibleUnconstrainedRecords = $this->getExpiredUnconstrainedCount($database, $expiryTime);

        // Delete any requester comments for expired requests
        $requesterCommentDelete = <<<SQL
            DELETE FROM comment
            WHERE 1 = 1 
                -- only requester comments
                AND comment.visibility = 'requester'
                -- where the following record exists
                AND exists(
                    SELECT 1 FROM request r
                    WHERE 1 = 1
                        -- a request matching the currently-checked comment
                        AND comment.request = r.id
                        -- and the request is expired
                        AND r.date < DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL :expiry DAY)
                        -- no confirmed email address
                        AND r.emailconfirm <> 'Confirmed'
                        -- not already marked as stale
                        AND r.emailconfirm <> 'Stale'
                        -- email confirmation was requested
                        AND r.emailconfirm <> ''
                        -- no non-requester comments exist (nobody has commented on the request)
                        AND NOT exists (SELECT 1 FROM comment c2 WHERE c2.request = r.id AND c2.visibility <> 'requester')
                        -- no jobqueue entries for this request exist
                        AND NOT exists (SELECT 1 FROM jobqueue j WHERE j.request = r.id)
                        -- no log entries for this request exist
                        AND NOT exists (SELECT 1 FROM log l WHERE l.objectid = r.id and l.objecttype = 'Request')
                );
SQL;
        $statement = $database->prepare($requesterCommentDelete);
        $statement->execute($expiryTime);
        $deletedComments = $statement->rowCount();

        // Delete any expired requests with no remaining FK constraints
        $requestDelete = <<<SQL
            DELETE FROM request
            WHERE 1 = 1
              -- request date older than X days ago
              AND request.date < DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL :expiry DAY)
              -- no confirmed email address
              AND request.emailconfirm <> 'Confirmed'
              -- not already marked as stale
              AND request.emailconfirm <> 'Stale'
              -- email confirmation was requested
              AND request.emailconfirm <> ''
              -- no comments exist (we just deleted requester comments)
              AND NOT exists(SELECT 1 FROM comment c WHERE c.request = request.id)
              -- no jobqueue entries for this request exist
              AND NOT exists(SELECT 1 FROM jobqueue j WHERE j.request = request.id)
              -- no log entries for this request exist
              AND NOT exists(SELECT 1 FROM log l WHERE l.objectid = request.id and l.objecttype = 'Request');
SQL;
        $statement = $database->prepare($requestDelete);
        $statement->execute($expiryTime);
        $deletedRequests = $statement->rowCount();

        // We've deleted all we can sensibly get away with. Disable the ability to email-confirm requests, and close
        // them as stale. The purge job will pick up the clearing of any private data.
        // Note - *very* few requests should get this far; it normally means a tool admin has overridden the
        // email-confirmation lockout and done something to the non-confirmed request.

        $splatExpired = <<<SQL
            UPDATE request 
                SET emailconfirm = 'Stale', status = 'Closed', updateversion = updateversion + 1
            WHERE 1 = 1
                AND request.date < DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL :expiry DAY)
                AND request.emailconfirm <> 'Confirmed'
                AND request.emailconfirm <> 'Stale'
                AND request.emailconfirm <> ''
            ;
SQL;

        $statement = $database->prepare($splatExpired);
        $statement->execute($expiryTime);
        $requestsMarkedStale = $statement->rowCount();

        // All done.
        $database->commit();

        printf('Cleanup: %d expired; %d unconstrained, %d comments deleted, %d requests deleted, %d marked stale',
            $eligibleRecords, $eligibleUnconstrainedRecords, $deletedComments, $deletedRequests, $requestsMarkedStale);
    }

    private function getExpiredCount(PdoDatabase $database, array $expiryTime)
    {
        $statement = $database->prepare(<<<SQL
            SELECT COUNT(*) FROM request r
            WHERE 1 = 1 
              -- request date older than X days ago
              AND r.date < DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL :expiry DAY)
              -- no confirmed email address
              AND r.emailconfirm <> 'Confirmed'
              -- not already marked as stale
              AND r.emailconfirm <> 'Stale'
              -- email confirmation was requested
              AND r.emailconfirm <> '';
SQL
        );

        $statement->execute($expiryTime);
        $eligibleRecords = $statement->fetchColumn();
        $statement->closeCursor();

        return $eligibleRecords;
    }

    private function getExpiredUnconstrainedCount(PdoDatabase $database, array $expiryTime)
    {
        $statement = $database->prepare(<<<SQL
            SELECT COUNT(*) FROM request r
            WHERE 1 = 1 
                -- request date older than X days ago
                AND r.date < DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL :expiry DAY)
                -- no confirmed email address
                AND r.emailconfirm <> 'Confirmed'
                -- not already marked as stale
                AND r.emailconfirm <> 'Stale'
                -- email confirmation was requested
                AND r.emailconfirm <> ''
                -- no comments for this request exist
                AND NOT exists(SELECT 1 FROM comment c WHERE c.request = r.id)
                -- no jobqueue entries for this request exist
                AND NOT exists(SELECT 1 FROM jobqueue j WHERE j.request = r.id)
                -- no log entries for this request exist
                AND NOT exists(SELECT 1 FROM log l WHERE l.objectid = r.id AND l.objecttype = 'Request');
SQL
        );

        $statement->execute($expiryTime);
        $eligibleRecords = $statement->fetchColumn();
        $statement->closeCursor();

        return $eligibleRecords;
    }
}