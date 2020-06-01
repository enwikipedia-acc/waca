<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Fragments\RequestListData;
use Waca\Helpers\SearchHelpers\RequestSearchHelper;
use Waca\Security\SecurityManager;
use Waca\SessionAlert;
use Waca\Tasks\PagedInternalPageBase;
use Waca\WebRequest;

class PageSearch extends PagedInternalPageBase
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
            $excludeNonConfirmed = WebRequest::getBoolean('excludeNonConfirmed');

            $validationError = "";
            if (!$this->validateSearchParameters($searchType, $searchTerm, $validationError)) {
                SessionAlert::error($validationError, "Search error");

                $this->assign('term', $searchTerm);
                $this->assign('target', $searchType);
                $this->assign('excludeNonConfirmed', $excludeNonConfirmed);
                $this->assign('hasResultset', false);
                return;
            }

            $requestSearch = RequestSearchHelper::get($this->getDatabase());

            $this->setSearchHelper($requestSearch);
            $this->setupLimits();

            if ($excludeNonConfirmed) {
                $requestSearch->withConfirmedEmail();
            }

            switch ($searchType) {
                case 'name':
                    $this->getNameSearchResults($requestSearch, $searchTerm);
                    break;
                case 'email':
                    $this->getEmailSearchResults($requestSearch, $searchTerm);
                    break;
                case 'ip':
                    $this->getIpSearchResults($requestSearch, $searchTerm);
                    break;
                case 'comment':
                    $this->getCommentSearchResults($requestSearch, $searchTerm);
                    break;
            }

            /** @var Request[] $results */
            $results = $requestSearch->getRecordCount($count)->fetch();

            $this->setupPageData($count, [
                'term'                => $searchTerm,
                'type'                => $searchType,
                'excludeNonConfirmed' => $excludeNonConfirmed,
            ]);

            // deal with results
            $this->assign('requests', $this->prepareRequestData($results));
            $this->assign('resultCount', count($results));
            $this->assign('hasResultset', true);

            $this->setTemplate('search/main.tpl');
        }
        else {
            $this->assign('target', 'name');
            $this->assign('hasResultset', false);
            $this->assign('limit', 50);
            $this->assign('excludeNonConfirmed', true);
            $this->setTemplate('search/main.tpl');
        }
    }

    /**
     * Gets search results by name
     *
     * @param RequestSearchHelper $searchHelper
     * @param string              $searchTerm
     */
    private function getNameSearchResults(RequestSearchHelper $searchHelper, $searchTerm)
    {
        $padded = '%' . $searchTerm . '%';
        $searchHelper->byName($padded);
    }

    /**
     * Gets search results by comment
     *
     * @param RequestSearchHelper $searchHelper
     * @param string              $searchTerm
     */
    private function getCommentSearchResults(RequestSearchHelper $searchHelper, $searchTerm)
    {
        $padded = '%' . $searchTerm . '%';
        $searchHelper->byComment($padded);

        $securityManager = $this->getSecurityManager();
        $currentUser = User::getCurrent($this->getDatabase());
        $securityResult = $securityManager->allows('RequestData', 'seeRestrictedComments', $currentUser);

        if ($securityResult !== SecurityManager::ALLOWED) {
            $searchHelper->byCommentSecurity(['requester', 'user']);
        }
    }

    /**
     * Gets search results by email
     *
     * @param        $searchHelper
     * @param string $searchTerm
     *
     * @throws ApplicationLogicException
     */
    private function getEmailSearchResults(RequestSearchHelper $searchHelper, $searchTerm)
    {
        if ($searchTerm === "@") {
            throw new ApplicationLogicException('The search term "@" is not valid for email address searches!');
        }

        $padded = '%' . $searchTerm . '%';

        $searchHelper->byEmailAddress($padded)->excludingPurgedData($this->getSiteConfiguration());
    }

    /**
     * Gets search results by IP address or XFF IP address
     *
     * @param RequestSearchHelper $searchHelper
     * @param string              $searchTerm
     */
    private function getIpSearchResults(RequestSearchHelper $searchHelper, $searchTerm)
    {
        $searchHelper
            ->byIp($searchTerm)
            ->excludingPurgedData($this->getSiteConfiguration());
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
        if (!in_array($searchType, array('name', 'email', 'ip', 'comment'))) {
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
