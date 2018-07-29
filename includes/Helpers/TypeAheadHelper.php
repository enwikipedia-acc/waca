<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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
var substringMatcher = function(strs) {
  return function findMatches(q, cb) {
    var matches, substringRegex;

    // an array that will be populated with substring matches
    matches = [];

    // regex used to determine if a string contains the substring `q`
    substrRegex = new RegExp(q, 'i');

    // iterate through the pool of strings and for any string that
    // contains the substring `q`, add it to the `matches` array
    $.each(strs, function(i, str) {
      if (substrRegex.test(str)) {
        matches.push(str);
      }
    });

    cb(matches);
  };
};
$('.{$class}').typeahead({
    hint: true,
    highlight: true,
    minLength: 1
  },
	  {
    name: "username",
    source: substringMatcher({$dataList})
});
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
