<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tasks;

use Waca\Helpers\SearchHelpers\SearchHelperBase;
use Waca\WebRequest;

abstract class PagedInternalPageBase extends InternalPageBase
{
    /** @var SearchHelperBase */
    private $searchHelper;
    private $page;
    private $limit;

    /**
     * Sets up the pager with the current page, current limit, and total number of records.
     *
     * @param int   $count
     * @param array $formParameters
     */
    protected function setupPageData($count, $formParameters)
    {
        $page = $this->page;
        $limit = $this->limit;

        // The number of pages on the pager to show. Must be odd
        $pageLimit = 9;

        $pageData = array(
            // Can the user go to the previous page?
            'canprev'   => $page != 1,
            // Can the user go to the next page?
            'cannext'   => ($page * $limit) < $count,
            // Maximum page number
            'maxpage'   => max(1, ceil($count / $limit)),
            // Limit to the number of pages to display
            'pagelimit' => $pageLimit,
        );

        // number of pages either side of the current to show
        $pageMargin = (($pageLimit - 1) / 2);

        // Calculate the number of pages either side to show - this is for situations like:
        //  [1]  [2] [[3]] [4]  [5]  [6]  [7]  [8]  [9] - where you can't just use the page margin calculated
        $pageData['lowpage'] = max(1, $page - $pageMargin);
        $pageData['hipage'] = min($pageData['maxpage'], $page + $pageMargin);
        $pageCount = ($pageData['hipage'] - $pageData['lowpage']) + 1;

        if ($pageCount < $pageLimit) {
            if ($pageData['lowpage'] == 1 && $pageData['hipage'] < $pageData['maxpage']) {
                $pageData['hipage'] = min($pageLimit, $pageData['maxpage']);
            }
            elseif ($pageData['lowpage'] > 1 && $pageData['hipage'] == $pageData['maxpage']) {
                $pageData['lowpage'] = max(1, $pageData['maxpage'] - $pageLimit + 1);
            }
        }

        // Put the range of pages into the page data
        $pageData['pages'] = range($pageData['lowpage'], $pageData['hipage']);

        $this->assign("pagedata", $pageData);

        $this->assign("limit", $limit);
        $this->assign("page", $page);

        $this->setupFormParameters($formParameters);
    }

    protected function setSearchHelper(SearchHelperBase $searchHelper)
    {
        $this->searchHelper = $searchHelper;
    }

    protected function setupLimits()
    {
        $limit = WebRequest::getInt('limit');
        if ($limit === null) {
            $limit = 100;
        }

        $page = WebRequest::getInt('page');
        if ($page === null) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $this->searchHelper->limit($limit, $offset);

        $this->page = $page;
        $this->limit = $limit;
    }

    private function setupFormParameters($formParameters)
    {
        $formParameters['limit'] = $this->limit;
        $this->assign('searchParamsUrl', http_build_query($formParameters, '', '&amp;'));

        foreach ($formParameters as $key => $value) {
            $this->assign($key, $value);
        }
    }
}
