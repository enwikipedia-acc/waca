<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tasks;

use Exception;
use Smarty\Exception as SmartyException;
use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\ExceptionHandler;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\Fragments\TemplateOutput;
use Waca\Helpers\PreferenceManager;
use Waca\Security\ContentSecurityPolicyManager;
use Waca\Security\TokenManager;
use Waca\SessionAlert;
use Waca\WebRequest;

abstract class PageBase extends TaskBase implements IRoutedTask
{
    use TemplateOutput;
    /** @var string Smarty template to display */
    protected $template = "base.tpl";
    /** @var string HTML title. Currently unused. */
    protected $htmlTitle;
    /** @var bool Determines if the page is a redirect or not */
    protected $isRedirecting = false;
    /** @var array Queue of headers to be sent on successful completion */
    protected $headerQueue = array();
    /** @var string The name of the route to use, as determined by the request router. */
    private $routeName = null;
    /** @var TokenManager */
    protected $tokenManager;
    /** @var ContentSecurityPolicyManager */
    private $cspManager;
    /** @var string[] Extra JS files to include */
    private $extraJs = array();
    /** @var bool Don't show (and hence clear) session alerts when this page is displayed  */
    private $hideAlerts = false;

    /**
     * Sets the route the request will take. Only should be called from the request router or barrier test.
     *
     * @param string $routeName        The name of the route
     * @param bool   $skipCallableTest Don't use this unless you know what you're doing, and what the implications are.
     *
     * @throws Exception
     * @category Security-Critical
     */
    final public function setRoute($routeName, $skipCallableTest = false)
    {
        // Test the new route is callable before adopting it.
        if (!$skipCallableTest && !is_callable(array($this, $routeName))) {
            throw new Exception("Proposed route '$routeName' is not callable.");
        }

        // Adopt the new route
        $this->routeName = $routeName;
    }

    /**
     * Gets the name of the route that has been passed from the request router.
     * @return string
     */
    final public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * Performs generic page setup actions
     */
    final protected function setupPage()
    {
        $this->setUpSmarty();

        $database = $this->getDatabase();
        $currentUser = User::getCurrent($database);
        $this->assign('currentUser', $currentUser);
        $this->assign('skin', PreferenceManager::getForCurrent($database)->getPreference(PreferenceManager::PREF_SKIN));
        $this->assign('currentDomain', Domain::getCurrent($database));
        $this->assign('loggedIn', (!$currentUser->isCommunityUser()));
    }

    /**
     * Runs the page logic as routed by the RequestRouter
     *
     * Only should be called after a security barrier! That means only from execute().
     */
    final protected function runPage()
    {
        $database = $this->getDatabase();

        // initialise a database transaction
        if (!$database->beginTransaction()) {
            throw new Exception('Failed to start transaction on primary database.');
        }

        try {
            // run the page code
            $this->{$this->getRouteName()}();

            $database->commit();
        }
        /** @noinspection PhpRedundantCatchClauseInspection */
        catch (ApplicationLogicException $ex) {
            // it's an application logic exception, so nothing went seriously wrong with the site. We can use the
            // standard templating system for this.

            // Firstly, let's undo anything that happened to the database.
            $database->rollBack();

            // Reset smarty
            $this->setupPage();

            $this->skipAlerts();

            // Set the template
            $this->setTemplate('exception/application-logic.tpl');
            $this->assign('message', $ex->getMessage());

            // Force this back to false
            $this->isRedirecting = false;
            $this->headerQueue = array();
        }
        /** @noinspection PhpRedundantCatchClauseInspection */
        catch (OptimisticLockFailedException $ex) {
            // it's an optimistic lock failure exception, so nothing went seriously wrong with the site. We can use the
            // standard templating system for this.

            // Firstly, let's undo anything that happened to the database.
            $database->rollBack();

            // Reset smarty
            $this->setupPage();

            // Set the template
            $this->skipAlerts();
            $this->setTemplate('exception/optimistic-lock-failure.tpl');
            $this->assign('message', $ex->getMessage());

            $this->assign('debugTrace', false);

            if ($this->getSiteConfiguration()->getDebuggingTraceEnabled()) {
                ob_start();
                var_dump(ExceptionHandler::getExceptionData($ex));
                $textErrorData = ob_get_contents();
                ob_end_clean();

                $this->assign('exceptionData', $textErrorData);
                $this->assign('debugTrace', true);
            }

            // Force this back to false
            $this->isRedirecting = false;
            $this->headerQueue = array();
        }
        finally {
            // Catch any hanging on transactions
            if ($database->hasActiveTransaction()) {
                $database->rollBack();
            }
        }

        // run any finalisation code needed before we send the output to the browser.
        $this->finalisePage();

        // Send the headers
        $this->sendResponseHeaders();

        // Check we have a template to use!
        if ($this->template !== null) {
            $content = $this->fetchTemplate($this->template);
            ob_clean();
            print($content);
            ob_flush();

            return;
        }
    }

    /**
     * Performs final tasks needed before rendering the page.
     */
    protected function finalisePage()
    {
        if ($this->isRedirecting) {
            $this->template = null;

            return;
        }

        $this->assign('extraJs', $this->extraJs);

        if (!$this->hideAlerts) {
            // If we're actually displaying content, we want to add the session alerts here!
            $this->assign('alerts', SessionAlert::getAlerts());
            SessionAlert::clearAlerts();
        }

        $this->assign('htmlTitle', $this->htmlTitle);
    }

    /**
     * @return TokenManager
     */
    public function getTokenManager()
    {
        return $this->tokenManager;
    }

    /**
     * @param TokenManager $tokenManager
     */
    public function setTokenManager($tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @return ContentSecurityPolicyManager
     */
    public function getCspManager(): ContentSecurityPolicyManager
    {
        return $this->cspManager;
    }

    /**
     * @param ContentSecurityPolicyManager $cspManager
     */
    public function setCspManager(ContentSecurityPolicyManager $cspManager): void
    {
        $this->cspManager = $cspManager;
    }

    /**
     * Skip the display of session alerts in this page
     */
    public function skipAlerts(): void
    {
        $this->hideAlerts = true;
    }

    /**
     * Sends the redirect headers to perform a GET at the destination page.
     *
     * Also nullifies the set template so Smarty does not render it.
     *
     * @param string      $page   The page to redirect requests to (as used in the UR)
     * @param null|string $action The action to use on the page.
     * @param null|array  $parameters
     * @param null|string $script The script (relative to index.php) to redirect to
     */
    final protected function redirect($page = '', $action = null, $parameters = null, $script = null)
    {
        $currentScriptName = WebRequest::scriptName();

        // Are we changing script?
        if ($script === null || substr($currentScriptName, -1 * count($script)) === $script) {
            $targetScriptName = $currentScriptName;
        }
        else {
            $targetScriptName = $this->getSiteConfiguration()->getBaseUrl() . '/' . $script;
        }

        $pathInfo = array($targetScriptName);

        $pathInfo[1] = $page;

        if ($action !== null) {
            $pathInfo[2] = $action;
        }

        $url = implode('/', $pathInfo);

        if (is_array($parameters) && count($parameters) > 0) {
            $url .= '?' . http_build_query($parameters);
        }

        $this->redirectUrl($url);
    }

    /**
     * Sends the redirect headers to perform a GET at the new address.
     *
     * Also nullifies the set template so Smarty does not render it.
     *
     * @param string $path URL to redirect to
     */
    final protected function redirectUrl($path)
    {
        // 303 See Other = re-request at new address with a GET.
        $this->headerQueue[] = 'HTTP/1.1 303 See Other';
        $this->headerQueue[] = "Location: $path";

        $this->setTemplate(null);
        $this->isRedirecting = true;
    }

    /**
     * Sets the name of the template this page should display.
     *
     * @param string $name
     *
     * @throws Exception
     */
    final protected function setTemplate($name)
    {
        if ($this->isRedirecting) {
            throw new Exception('This page has been set as a redirect, no template can be displayed!');
        }

        $this->template = $name;
    }

    /**
     * Adds an extra JS file to to the page
     *
     * @param string $path The path (relative to the application root) of the file
     */
    final protected function addJs($path)
    {
        if (in_array($path, $this->extraJs)) {
            // nothing to do
            return;
        }

        $this->extraJs[] = $path;
    }

    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    abstract protected function main();

    /**
     * Takes a smarty template string and sets the HTML title to that value
     *
     * @param string $title
     *
     * @throws SmartyException
     */
    final protected function setHtmlTitle($title)
    {
        $this->htmlTitle = $this->smarty->fetch('string:' . $title);
    }

    public function execute()
    {
        if ($this->getRouteName() === null) {
            throw new Exception('Request is unrouted.');
        }

        if ($this->getSiteConfiguration() === null) {
            throw new Exception('Page has no configuration!');
        }

        $this->setupPage();

        $this->runPage();
    }

    public function assignCSRFToken()
    {
        $token = $this->tokenManager->getNewToken();
        $this->assign('csrfTokenData', $token->getTokenData());
    }

    public function validateCSRFToken()
    {
        if (!$this->tokenManager->validateToken(WebRequest::postString('csrfTokenData'))) {
            throw new ApplicationLogicException('Form token is not valid, please reload and try again');
        }
    }

    protected function sendResponseHeaders()
    {
        if (headers_sent()) {
            throw new ApplicationLogicException('Headers have already been sent! This is likely a bug in the application.');
        }

        // send the CSP headers now
        header($this->getCspManager()->getHeader());

        foreach ($this->headerQueue as $item) {
            if (mb_strpos($item, "\r") !== false || mb_strpos($item, "\n") !== false) {
                // Oops. We're not allowed to do this.
                throw new Exception('Unable to split header');
            }

            header($item);
        }
    }
}
