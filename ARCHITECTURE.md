# Overall Architecture

## Entry points
* `internal.php` - the web-based entry point for all non-request-form web GUI access.

### Legacy entry points
* `api.php` - the web-based API. Access to unauthenticated data only.
* `index.php` - the request form. Allows submission of data only.
* `redir.php` - performs redirects. No known security impact. 

### Deprecated entry points
* `acc.php`
* `search.php`
* `statistics.php`
* `team.php` - Static-ish page.
* `users.php`

## Web request routing

1. A web request is received, and hits `internal.php`. Configuration is read, and an instance of `WebStart` created.
1. `WebStart` sets up the environment, initialises things like Smarty and providers, then runs the main application logic.
1. The main application logic gets the correct route from the rules defined in `RequestRouter`, and executes the relevant page.

# General points

## Namespacing

The root namespace for all classes should be `\Waca`, which maps to `includes/`. The namespace structure should exactly 
match the folder structure.

Note: There are a bunch of older classes in the `includes/` folder which are in the global namespace. As time permits,
these should be moved to a real namespace, or deprecated and replaced.

Tests follow a similar architecture, and should be placed in their corresponding folder under `tests/`, whose root 
namespace is `\Waca\Tests`. Thus, for a class `\Waca\WebRequest` at the file path `includes/WebRequest.php` would have a 
corresponding test class `\Waca\Tests\WebRequestTest` at the file path `tests/WebRequestTest.php`.


## Superglobals

Don't use these _anywhere_ **except** `WebRequest`. Data leaving `WebRequest` should be of a declared type 
(int, string, etc). Don't assume that there is protection in place.

## Security

`PageBase` provides a security barrier. Use it.

Override the method `getSecurityConfiguration()`. It returns an object of type `\Waca\SecurityConfiguration`. Inside that
method you can filter by the route if you want - it's accessible from the class! The overall idea of this is that each
right is checked in turn (admin, user, checkuser, community, declined, suspended, new) to see if they apply to the user.
If they do, then that security rule is applied. If it's a DENY rule, then the user is instantly rejected, even if there
are other ALLOW rules that permit them access. The default setting for all rules is to have no effect on the outcome of
the security check. If there's no DENY rules hit, and no ALLOW rules hit by the end of the check, then they are by 
default denied access. 


Anything marked `@category Security-Critical` is probably critical to maintaining the security of the tool, so should
be triple-checked before being modified in any way! Don't forget that this includes the initial deployment!

