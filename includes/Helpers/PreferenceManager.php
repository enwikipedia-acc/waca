<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Helpers;

use PDO;
use Waca\DataObjects\User;
use Waca\DataObjects\UserPreference;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;
use Waca\WebRequest;

class PreferenceManager
{
    const PREF_WELCOMETEMPLATE = 'welcomeTemplate';
    const PREF_SKIP_JS_ABORT = 'skipJsAbort';
    const PREF_EMAIL_SIGNATURE = 'emailSignature';
    const PREF_CREATION_MODE = 'creationMode';
    const PREF_SKIN = 'skin';
    const PREF_DEFAULT_DOMAIN = 'defaultDomain';
    const PREF_QUEUE_HELP = 'showQueueHelp';

    const ADMIN_PREF_PREVENT_REACTIVATION = 'preventReactivation';

    const CREATION_MANUAL = 0;
    const CREATION_OAUTH = 1;
    const CREATION_BOT = 2;
    /** @var PdoDatabase */
    private $database;
    /** @var int */
    private $user;
    /** @var ?int */
    private $domain;
    /** @var PreferenceManager|null */
    private static $currentUser = null;
    private $cachedPreferences = null;

    public function __construct(PdoDatabase $database, int $user, ?int $domain)
    {
        $this->database = $database;
        $this->user = $user;
        $this->domain = $domain;
    }

    public static function getForCurrent(PdoDatabase $database): PreferenceManager
    {
        if (self::$currentUser === null) {
            $user = User::getCurrent($database)->getId();
            $domain = WebRequest::getSessionDomain();

            self::$currentUser = new self($database, $user, $domain);
        }

        return self::$currentUser;
    }

    public function setLocalPreference(string $preference, $value): void
    {
        if ($this->cachedPreferences === null) {
            $this->loadPreferences();
        }

        if ($this->cachedPreferences[$preference]['value'] == $value
            && $this->cachedPreferences[$preference]['global'] === false) {
            return;
        }

        $localPreference = UserPreference::getLocalPreference($this->database, $this->user, $preference, $this->domain);
        if ($localPreference === false) {
            $localPreference = new UserPreference();
            $localPreference->setDatabase($this->database);
            $localPreference->setDomain($this->domain);
            $localPreference->setUser($this->user);
            $localPreference->setPreference($preference);
        }

        $localPreference->setValue($value);
        $localPreference->save();

        $this->cachedPreferences[$preference] = [
            'value'  => $value,
            'global' => false,
        ];
    }

    public function setGlobalPreference(string $preference, $value): void
    {
        if ($this->cachedPreferences === null) {
            $this->loadPreferences();
        }

        if ($this->cachedPreferences[$preference]['value'] == $value
            && $this->cachedPreferences[$preference]['global'] === true) {
            return;
        }

        $this->deleteLocalPreference($preference);

        $globalPreference = UserPreference::getGlobalPreference($this->database, $this->user, $preference);
        if ($globalPreference === false) {
            $globalPreference = new UserPreference();
            $globalPreference->setDatabase($this->database);
            $globalPreference->setDomain(null);
            $globalPreference->setUser($this->user);
            $globalPreference->setPreference($preference);
        }

        $globalPreference->setValue($value);
        $globalPreference->save();

        $this->cachedPreferences[$preference] = [
            'value'  => $value,
            'global' => true,
        ];
    }

    public function getPreference(string $preference)
    {
        if ($this->cachedPreferences === null) {
            $this->loadPreferences();
        }

        if (!isset($this->cachedPreferences[$preference])) {
            return null;
        }

        return $this->cachedPreferences[$preference]['value'];
    }

    public function isGlobalPreference(string $preference) : ?bool
    {
        if ($this->cachedPreferences === null) {
            $this->loadPreferences();
        }

        if (!isset($this->cachedPreferences[$preference])) {
            return null;
        }

        return $this->cachedPreferences[$preference]['global'];
    }

    protected function deleteLocalPreference(string $preference): void
    {
        $getStatement = $this->database->prepare('SELECT * FROM userpreference WHERE preference = :preference AND USER = :user AND domain = :domain');
        $getStatement->execute([
            ':user'       => $this->user,
            ':preference' => $preference,
            ':domain'     => $this->domain
        ]);

        $localPreference = $getStatement->fetchObject(UserPreference::class);
        if ($localPreference !== false) {
            $localPreference->setDatabase($this->database);
            $localPreference->delete();
        }
    }

    protected function loadPreferences(): void
    {
        /**
         * OK, this is a bit of a complicated query.
         * It's designed to get all the preferences defined for a user in a specified domain, falling back to globally
         * defined preferences if a local preference isn't set. As such, this query is the *heart* of how global
         * preferences work.
         *
         * Starting with the WHERE, we filter rows:
         *   a) where the row's domain is the domain we're looking for
         *   b) where the row's domain is null, thus it's a global setting
         *   c) if we don't have a domain we're looking for, fall back to global only
         *
         * The MAX(...) OVER(...) is a window function, *not* an aggregate. It basically takes the max of all selected
         * rows' domain columns, grouped by the preference column. Since any number N < null, this highlights all the
         * correct settings (local has precedence over global) such that prefpart == domain.
         *
         * -1 is used to represent null in the COALESCE() calls, since domain.id is an unsigned int hence -1 is an
         * impossible value
         */
        $sql = /** @lang SQL */
            <<<'EOF'
WITH allprefs AS (
    SELECT up.domain, up.preference, MAX(up.domain) OVER (PARTITION BY up.preference) AS prefpart, up.value, CASE WHEN up.domain IS NULL THEN 1 END AS isglobal
    FROM userpreference up
    WHERE COALESCE(up.domain, :domainc, -1) = COALESCE(:domain, -1)
      AND up.user = :user
)
SELECT p.preference, p.value, coalesce(p.isglobal, 0) as isglobal
FROM allprefs p
WHERE COALESCE(p.prefpart, -1) = COALESCE(p.domain, -1);
EOF;
        $statement = $this->database->prepare($sql);

        $statement->execute([
            ':domain'  => $this->domain,
            ':domainc' => $this->domain,
            ':user'    => $this->user,
        ]);

        $rawPrefs = $statement->fetchAll(PDO::FETCH_ASSOC);
        $prefs = [];

        foreach ($rawPrefs as $p) {
            $prefs[$p['preference']] = [
                'value'  => $p['value'],
                'global' => $p['isglobal'] == 1,
            ];
        }

        $this->cachedPreferences = $prefs;
    }
}
