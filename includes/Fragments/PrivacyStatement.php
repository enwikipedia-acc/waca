<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Fragments;

use Waca\Helpers\MarkdownRenderingHelper;

trait PrivacyStatement
{
    protected abstract function assign($name, $value);
    protected abstract function templatePath();
    protected abstract function setTemplate($name);
    protected abstract function skipAlerts();
    protected abstract function getSiteConfiguration();

    protected function main()
    {
        $path = $this->getSiteConfiguration()->getPrivacyStatementPath();

        if ($path == null || !file_exists($path)) {
            if (!headers_sent()) {
                header('HTTP/1.1 404 Not Found');
            }

            $this->skipAlerts();
            $this->setTemplate('404.tpl');
            return;
        }

        $markdown = file_get_contents($path);

        $renderer = new MarkdownRenderingHelper();
        $this->assign('content', $renderer->doRender($markdown));

        $this->setTemplate($this->templatePath());
    }
}