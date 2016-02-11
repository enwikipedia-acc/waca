<?php

namespace Waca\Helpers\Interfaces;

interface ITypeAheadHelper
{
	/**
	 * @param string   $class     CSS class to apply this typeahead to.
	 * @param callable $generator Generator function taking no arguments to return an array of strings.
	 */
	public function defineTypeAheadSource($class, callable $generator);

	/**
	 * @return string HTML fragment containing a JS block for typeaheads.
	 */
	public function getTypeAheadScriptBlock();
}