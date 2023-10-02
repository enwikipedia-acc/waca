<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\DomainSwitchNotAllowedException;
use Waca\Router\RequestRouter;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageDomainSwitch extends InternalPageBase
{
    /**
     * @inheritDoc
     */
    protected function main()
    {
        if (!WebRequest::wasPosted()) {
            $this->redirect('/');

            return;
        }

        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);

        /** @var Domain|false $newDomain */
        $newDomain = Domain::getById(WebRequest::postInt('newdomain'), $database);

        if ($newDomain === false) {
            $this->redirect('/');

            return;
        }

        try {
            $this->getDomainAccessManager()->switchDomain($currentUser, $newDomain);
        }
        catch(DomainSwitchNotAllowedException $ex){
            throw new AccessDeniedException($this->getSecurityManager(), $this->getDomainAccessManager());
        }

        // try to stay on the same page if possible.
        // This only checks basic ACLs and not domain privileges, so this may still result in a 403.

        $referrer = WebRequest::postString('referrer');
        $priorPath = explode('/', $referrer);
        $router = new RequestRouter();
        $route = $router->getRouteFromPath($priorPath);

        if ($this->barrierTest($route[1], $currentUser, $route[0])) {
            $this->redirect('/' . $referrer);
        } else {
            $this->redirect('/');
        }
    }
}