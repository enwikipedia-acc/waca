<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\User;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageErrorLogViewer extends InternalPageBase
{
    /**
     * @inheritDoc
     */
    protected function main()
    {
        $this->setHtmlTitle('Exception viewer');

        $user = User::getCurrent($this->getDatabase());
        $this->assign('canView', $this->barrierTest('view', $user));
        $this->assign('canRemove', $this->barrierTest('remove', $user));

        // Get the list of exception logs from the error log directory
        $errorLogDirectory = $this->getSiteConfiguration()->getErrorLog();
        $files = scandir($errorLogDirectory);

        // Exclude the files we know should be there
        $filteredFiles = array_filter($files, function($file) {
            return !in_array($file, ['.', '..', 'README.md']);
        });

        $exceptionDetails = array_map(function($item) use ($errorLogDirectory) {
            $filename = realpath($errorLogDirectory) . DIRECTORY_SEPARATOR . $item;

            return [
                'id'   => str_replace('.log', '', $item),
                'date' => date('Y-m-d H:i:s', filemtime($filename)),
                'data' => str_replace($this->getSiteConfiguration()->getFilePath(), '.',
                    unserialize(file_get_contents($filename))),
            ];
        }, $filteredFiles);

        $this->assign('exceptionEntries', $exceptionDetails);
        $this->setTemplate('errorlog/main.tpl');
    }

    protected function view()
    {
        $this->setHtmlTitle('Exception viewer');

        $requestedErrorId = WebRequest::getString('id');
        $safeFilename = $this->safetyCheck($requestedErrorId);

        if ($safeFilename === false) {
            $this->redirect('errorLog');

            return;
        }

        // note: at this point we've done sufficient sanity checks that we can be confident this value is safe to echo
        // back to the user.
        $this->assign('id', $requestedErrorId);
        $this->assign('date', date('Y-m-d H:i:s', filemtime($safeFilename)));

        $data = unserialize(file_get_contents($safeFilename));
        $this->assign('server', $data['server']);
        $this->assign('get', $data['get']);
        $this->assign('post', $data['post']);

        $this->assign('globalHandler', $data['globalHandler']);

        $exceptionList = [];
        $current = $data;
        do {
            $ex = [
                'exception' => $current['exception'],
                'message'   => str_replace($this->getSiteConfiguration()->getFilePath(), '.', $current['message']),
                'stack'     => str_replace($this->getSiteConfiguration()->getFilePath(), '.', $current['stack']),
            ];
            $exceptionList[] = $ex;

            $current = $current['previous'];
        }
        while ($current !== null);

        $this->assign('exceptionList', $exceptionList);

        $this->setTemplate('errorlog/details.tpl');
    }

    public function remove()
    {
        $safeFilename = $this->safetyCheck(WebRequest::getString('id'));

        if ($safeFilename === false) {
            $this->redirect('errorLog');

            return;
        }

        unlink($safeFilename);

        $this->redirect('errorLog');

        return;
    }

    /**
     * @param string|null $requestedErrorId
     *
     * @return bool|string
     */
    protected function safetyCheck(?string $requestedErrorId)
    {
        if ($requestedErrorId === null) {
            return false;
        }

        // security - only allow hex-encoded filenames, as this is what is generated.
        // This is prefixed with the configured directory. Path traversal is protected against due to . and / not being
        // part of the hex character set.
        if (!preg_match('/^[a-f0-9]{40}$/', $requestedErrorId)) {
            return false;
        }

        $errorLogDirectory = $this->getSiteConfiguration()->getErrorLog();
        $filename = realpath($errorLogDirectory) . DIRECTORY_SEPARATOR . $requestedErrorId . '.log';

        if (!file_exists($filename)) {
            return false;
        }

        return $filename;
    }
}