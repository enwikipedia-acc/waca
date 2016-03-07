<?php

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

	final public function finalisePage(){
		parent::finalisePage();
	}
}