<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\Tasks\InternalPageBase;

class PageTeam extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $path = $this->getSiteConfiguration()->getFilePath() . '/team.json';
        $json = file_get_contents($path);

        $teamData = json_decode($json, true);

        $active = array();
        $inactive = array();

        foreach ($teamData as $name => $item) {
            if (count($item['Role']) == 0) {
                $inactive[$name] = $item;
            }
            else {
                $active[$name] = $item;
            }
        }

        $this->assign('developer', $this->assocArrayShuffle($active));
        $this->assign('inactiveDeveloper', $this->assocArrayShuffle($inactive));
        $this->setTemplate('team/team.tpl');
    }

    private function assocArrayShuffle($array)
    {
        $arrayKeys = array_keys($array);
        shuffle($arrayKeys);

        $sorted = [];
        foreach ($arrayKeys as $k) {
            $sorted[$k] = $array[$k];
        }

        return $sorted;
    }
}
