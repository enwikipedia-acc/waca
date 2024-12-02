<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Exception;
use PDO;
use Waca\DataObjects\Comment;
use Waca\Helpers\Logger;
use Waca\Tasks\ConsoleTaskBase;

class AutoFlagCommentsTask extends ConsoleTaskBase
{
    public function execute()
    {
        $database = $this->getDatabase();

        $query = $database->prepare(<<<'SQL'
select c.id, r.domain
from comment c
inner join request r on r.id = c.request
where (
    1 = 0
    /* emails */
    or c.comment rlike '[^ @]+(?<!accounts-enwiki-l|unblock|functionaries-en|checkuser-l|info-en|enwiki-acc-admins|/|\\()@(?!lists.wikimedia.org|wikimedia.org|wikipedia.org|[a-z][a-z]wiki)[a-z\\.]+'
    -- or c.comment rlike 'gmail|yahoo' --  to many FPs
    -- ipv4
    OR c.comment rlike '[0-2]?[0-9]?[0-9]\\.[0-2]?[0-9]?[0-9]\\.[0-2]?[0-9]?[0-9]\\.[0-2]?[0-9]?[0-9]'
    -- ipv6
    OR (lower(c.comment) rlike '[0-9a-f]{1,4}:[0-9a-f]{1,4}:[0-9a-f]{1,4}' and c.comment not rlike '[0-2]?[0-9]:[0-5][0-9]:[0-5][0-9]')
    -- card pan
    OR c.comment rlike '[0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4}'
    OR c.comment rlike '(?<!ticket|ticket#|OTRS|OTRS #) \\+?(?!20[0-2][0-9][01][0-9][0-3][0-9]100[0-9]{5})[0-9]{9,}'
    -- phone numbers
    OR c.comment like '%mobile no%'
    OR c.comment like '%contact no%'
    OR c.comment like '%phone no%'
    OR c.comment like '%cell no%'
    OR c.comment rlike '\\+[0-9]{1}[0-9 .-]{5}'
    OR c.comment rlike '(?:phone(?: )?:|mobile(?: )?:|cell(?: )?:)[ 0-9+]'
    OR c.comment rlike '(^|\\s)(contact|phone|cell|mobile)( no| number| nbr)?( is)? ?:? ?[0-9+][0-9]+'
    OR c.comment rlike '[0-9]{3,} ?(ext|x)\\.? ?[0-9]{3,}'
    -- OR c.comment like '%telephone%' -- too many FP

    -- requested passwords
    OR c.comment like '%my password to be %'
    OR c.comment like '% password be %'
    OR c.comment rlike '(my )password (to |should )?(be|as)(?! soon| quickly|ap|\\?)'
    OR c.comment rlike '(as )(my )?password(?! reset)'
    OR c.comment rlike 'password(?: )?:'

    -- holy FP craziness, but full of matches.
    -- OR (c.comment rlike 'password' and c.user is null)

    -- banking
    OR c.comment rlike ' (a/c|acct) (no|number|nbr)( |\\.)'
    -- OR c.comment rlike '(?<!requested|conflicting|similar) acct'

    -- OR c.comment rlike ' card ' -- too many FP
    -- OR c.comment like '% bank %' -- too many FP

    -- all of these have too many FPs
    -- or c.comment rlike '(?<!ip )(?<!email )(?<!e-mail )(?<!this )address(?!ed)'
    -- OR c.comment rlike ' (ave|st(?!\\w)|road|rd(?!\\w))'
    -- or c.comment rlike ' (road|street|avenue) '
    -- or (c.comment rlike '(^|\\s)[0-9]{5,}\\s' and c.user is null)
    -- or (c.comment rlike ' (?:Alabama|AL|Kentucky|KY|Ohio|Alaska|AK|Louisiana|LA|Oklahoma|Arizona|AZ|Maine|Oregon|Arkansas|AR|Maryland|MD|Pennsylvania|PA|Massachusetts|MA|California|CA|Michigan|MI|Rhode Island|RI|Colorado|Minnesota|MN|South Carolina|SC|Connecticut|CT|Mississippi|MS|South Dakota|SD|Delaware|DE|Missouri|MO|Tennessee|TN|DC|Montana|MT|Texas|TX|Florida|FL|Nebraska|NE|Georgia|GA|Nevada|NV|Utah|UT|New Hampshire|NH|Vermont|VT|Hawaii|New Jersey|NJ|Virginia|VA|Idaho|New Mexico|NM|Illinois|IL|New York|NY|Washington|WA|Indiana|North Carolina|NC|West Virginia|WV|Iowa|IA|North Dakota|ND|Wisconsin|WI|Kansas|KS|Wyoming|WY)(?: |\\.)' and c.user is null)
)
-- only find comments which haven't previously been flagged
and not exists (select 1 from log l where l.objectid = c.id and action = 'UnflaggedComment')
-- only comments on closed requests (give humans a chance to flag these)
and exists (select 1 from request r where r.id = c.request and r.status = 'Closed')
and c.flagged <> 1
-- not all edited comments have log entries (yay historical reasons!)
and c.comment not like '%[redacted]%'
;
SQL
        );

        $success = $query->execute();

        if (!$success) {
            throw new Exception('Error in transaction: Could not load data.');
        }

        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $row) {
            /** @var Comment $dataObject */
            $dataObject = Comment::getById($row['id'], $database);

            Logger::flaggedComment($database, $dataObject, $row['domain']);
            $dataObject->setFlagged(true);
            $dataObject->save();
        }
    }
}