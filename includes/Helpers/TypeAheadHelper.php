<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Helpers;

use Waca\Helpers\Interfaces\ITypeAheadHelper;

class TypeAheadHelper implements ITypeAheadHelper
{
    private $definedClasses = array();

    /**
     * @param string   $class     CSS class to apply this typeahead to.
     * @param callable $generator Generator function taking no arguments to return an array of strings.
     */
    public function defineTypeAheadSource($class, callable $generator)
    {
        $dataList = '';
        foreach ($generator() as $dataItem) {
            $dataList .= '"' . htmlentities($dataItem) . '", ';
        }
        $dataList = "[" . rtrim($dataList, ", ") . "]";

        $script = <<<JS

$('.{$class}').typeahead({
        hint: true,
        highlight: true,
        minLength: 1
    },
    {
        name: "username",
        source: substringMatcher( {$dataList})
})
;
JS;
        $this->definedClasses[$class] = $script;
    }

    /**
     * @return string HTML fragment containing a JS block for typeaheads.
     */
    public function getTypeAheadScriptBlock()
    {
        $jsBlocks = '';

        if (count($this->definedClasses) === 0) {
            return '';
        }

        foreach ($this->definedClasses as $class => $js) {
            $jsBlocks = $js . "\r\n\r\n";
        }

        $data = <<<HTML
<script type="text/javascript">
	{$jsBlocks}
</script>
HTML;

        $this->definedClasses = array();

        return $data;
    }
}
