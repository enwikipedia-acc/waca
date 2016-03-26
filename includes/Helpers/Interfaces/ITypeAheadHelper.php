<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers\Interfaces;

interface ITypeAheadHelper
{
	/**
	 * @param string   $class     CSS class to apply this typeahead to.
	 * @param callable $generator Generator function taking no arguments to return an array of strings.
	 * @return void
	 */
	public function defineTypeAheadSource($class, callable $generator);

	/**
	 * @return string HTML fragment containing a JS block for typeaheads.
	 */
	public function getTypeAheadScriptBlock();
}