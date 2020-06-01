<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\Request;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Fragments\RequestListData;
use Waca\Helpers\SearchHelpers\RequestSearchHelper;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageSearch extends InternalPageBase
{
    use RequestListData;

    /**
     * Main function for this page, when no specific actions are called.
     */
    protected function main()
    {
        $this->setHtmlTitle('Search');

        // Dual-mode page
        if (WebRequest::getString('type') !== null) {
            $searchType = WebRequest::getString('type');
            $searchTerm = WebRequest::getString('term');

            $validationError = "";
            if (!$this->validateSearchParameters($searchType, $searchTerm, $validationError)) {
                SessionAlert::error($validationError, "Search error");
                $this->redirect("search");

                return;
            }

            $results = array();

            switch ($searchType) {
                case 'name':
                    $results = $this->getNameSearchResults($searchTerm);
                    break;
                case 'email':
                    $results = $this->getEmailSearchResults($searchTerm);
                    break;
                case 'ip':
                    $results = $this->getIpSearchResults($searchTerm);
                    break;
            }

            // deal with results
            $this->assign('requests', $this->prepareRequestData($results));
            $this->assign('resultCount', count($results));
            $this->assign('term', $searchTerm);
            $this->assign('target', $searchType);
            $this->assign('hasResultset', true);

            $this->setTemplate('search/main.tpl');
        }
        else {
            $this->assign('target', 'name');
            $this->assign('hasResultset', false);
            $this->setTemplate('search/main.tpl');
        }
    }

    /**
     * Gets search results by name
     *
     * @param string $searchTerm
     *
     * @return Request[]
     */
    private function getNameSearchResults($searchTerm)
    {
        $padded = '%' . $searchTerm . '%';

        /** @var Request[] $requests */
        $requests = RequestSearchHelper::get($this->getDatabase())
            ->byName($padded)
            ->fetch();

        return $requests;
    }

    /**
     * Gets search results by email
     *
     * @param string $searchTerm
     *
     * @return Request[]
     * @throws ApplicationLogicException
     */
    private function getEmailSearchResults($searchTerm)
    {
        if ($searchTerm === "@") {
            throw new ApplicationLogicException('The search term "@" is not valid for email address searches!');
        }

        $padded = '%' . $searchTerm . '%';

        /** @var Request[] $requests */
        $requests = RequestSearchHelper::get($this->getDatabase())
            ->byEmailAddress($padded)
            ->excludingPurgedData($this->getSiteConfiguration())
            ->fetch();

        return $requests;
    }

    /**
     * Gets search results by IP address or XFF IP address
     *
     * @param string $searchTerm
     *
     * @return Request[]
     */
    private function getIpSearchResults($searchTerm)
    {
        /** @var Request[] $requests */
        $requests = RequestSearchHelper::get($this->getDatabase())
            ->byIp($searchTerm)
            ->excludingPurgedData($this->getSiteConfiguration())
            ->fetch();

        return $requests;
    }

    /**
     * @param string $searchType
     * @param string $searchTerm
     *
     * @param string $errorMessage
     *
     * @return bool true if parameters are valid
     */
    protected function validateSearchParameters($searchType, $searchTerm, &$errorMessage)
    {
        if (!in_array($searchType, array('name', 'email', 'ip'))) {
            $errorMessage = 'Unknown search type';

            return false;
        }

        if ($searchTerm === '%' || $searchTerm === '' || $searchTerm === null) {
            $errorMessage = 'No search term specified entered';

            return false;
        }

        $errorMessage = "";

        return true;
    }
}
