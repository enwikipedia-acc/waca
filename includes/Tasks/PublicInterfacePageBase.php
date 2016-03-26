<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tasks;

abstract class PublicInterfacePageBase extends PageBase
{
	/**
	 * PublicInterfaceInternalPageBase constructor.
	 */
	public function __construct()
	{
		$this->template = 'publicbase.tpl';
	}

	final public function execute()
	{
		parent::execute();
	}

	final public function finalisePage()
	{
		parent::finalisePage();
	}
}