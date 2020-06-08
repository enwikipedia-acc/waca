# Overall Architecture

## Entry points
* `internal.php` - the web-based entry point for all non-request-form web GUI access.

### Legacy entry points
* `api.php` - the web-based API. Access to unauthenticated data only.
* `index.php` - the request form. Allows submission of data only.
* `redir.php` - performs redirects. No known security impact. 

### Deprecated entry points
* `acc.php` - Deleted
* `search.php` - Deleted
* `statistics.php` - Deleted
* `team.php` - Deleted
* `users.php` - Deleted

## Web request routing

1. A web request is received, and hits `internal.php`. Configuration is read, and an instance of `WebStart` created.
1. `WebStart` sets up the environment, initialises things like Smarty and providers, then runs the main application logic.
1. The main application logic gets the correct route from the rules defined in `RequestRouter`, and executes the relevant page.

# General points

## Namespaces

The root namespace for all classes should be `\Waca`, which maps to `includes/`. The namespace structure should exactly 
match the folder structure.

Note: There are a bunch of older classes in the `includes/` folder which are in the global namespace. As time permits,
these should be moved to a real namespace, or deprecated and replaced.

Tests follow a similar architecture, and should be placed in their corresponding folder under `tests/`, whose root 
namespace is `\Waca\Tests`. Thus, for a class `\Waca\WebRequest` at the file path `includes/WebRequest.php` would have a 
corresponding test class `\Waca\Tests\WebRequestTest` at the file path `tests/WebRequestTest.php`.

## Banned stuff

### Globals

Kind-of banned. Our configuration system is entirely based in global variables, and we need to fix this. If something 
uses a global variable it becomes extremely hard to test. If it's a configuration variable you want to use, grab it from
the closest configuration object. If it's not in the configuration object, this is a good opportunity to move it there!

### Super-globals

Don't use these _anywhere_ **except** `WebRequest`. Data leaving `WebRequest` should be of a declared type 
(int, string, etc), or a filtered value (`filter_var`). Don't assume that this data is secure because it's come from
`WebRequest` - it's user data and should be assumed to be malicious.

Super-globals contain user data and untrustworthy - everything should be treated with utmost suspicion. If all access to
super-globals comes through predefined methods which behave in a well-known and well-tested way, we can be reasonably
confident that if we expect a string, we actually have a string. Don't assume that this string is safe though!

Also, super-globals are not settable, even from phpunit, so we _have_ to wrap calls to super-globals in something we
can mock, or we can't test whatever uses them.

### `mail()`

This is allowed in exactly one place - `\Waca\Helpers\EmailHelper`. Everything else should attempt to acquire an 
`IEmailHelper` from somewhere and use it - be aware that it may not be an `EmailHelper` you get, but it _will_ be an 
`IEmailHelper`.

The basic reason for this is we don't want unit testing to actually send emails.

### Non-parameterised database calls

Mostly, this means preparing a query, then executing it with parameters. Direct calls to `PdoDatabase::query()` and 
`PdoDatabase::exec()` _are_ still allowed, but the entire statement *must* be hardcoded. No user input is allowed. 

Allowed code:
```php
$actualVersion = $database->query("SELECT version FROM schemaversion")->fetchColumn();

$this->dbObject->exec("delete from antispoofcache where timestamp < date_sub(now(), interval 3 hour);");

$statement = $this->dbObject->prepare("INSERT INTO antispoofcache (username, data) VALUES (:username, :data);");
$statement->bindValue(":username", $this->username);
$statement->bindValue(":data", $this->data);
$success = $statement->execute();
```

This is a bright-line - anything that breaches this runs the risk of introducing serious vulnerabilities. Don't do it.

### Anything else that can't be mocked.

This means hardcoded file paths, web addresses, etc.

## Security

`InternalPageBase` provides a security barrier. You must use this with every page - it's actually enforced by the 
design of the application.

Every action that you define in the RequestRouter (including the implicit `main`) creates a permission, which by default
is not assigned to any role, so you will get 403 errors if you don't grant it to a role. Please think carefully about
which roles this is granted to, define further permissions which can be tested with `barrierTest` if necessary. If this
necessary, it probably indicates something not-quite-right with the design of the page - the intended use is to test if
a user has permissions for a different page or a different action, such that a button can be shown or hidden as 
appropriate.

You can assign permissions to roles in two ways, either to allow the permission or to deny the permission. Deny takes
precedence over allow, so if a user is in two roles, one denies and the other allows, the user is denied the permission.
If there's no explicit allow or deny in any role granted to a user, the default is to deny.

Anything marked `@category Security-Critical` is probably critical to maintaining the security of the tool, so should
be triple-checked before being modified in any way! Don't forget that this includes the initial deployment!

## Exceptions

Don't be afraid to use them! Unhandled Exceptions will be caught at a global level by the tool, although the error message that
this produces is not a very readable or user-friendly one.

Apart from that, there are two types of exception which are handled in a special way by the tool: `EnvironmentException`
and `ReadableException`. The former will push the tool offline for that request, and is intended to be used in situations
where there is no possible way the tool could run, such as a missing PHP extension, database unavailable, etc. The second
is designed for situations where the current state of the tool or a user's request is so appallingly bad that we don't
want to continue at all, such as access denied, user not identified, etc. These will build a templated HTML message for
the user, and display it before exiting.