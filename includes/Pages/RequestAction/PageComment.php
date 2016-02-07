<?php

namespace Waca\Pages\RequestAction;

use Comment;
use Notification;
use User;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PageComment extends RequestActionBase
{
	/**
	 * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
	 * the return value from this function.
	 *
	 * If this page even supports actions, you will need to check the route
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	protected function getSecurityConfiguration()
	{
		return SecurityConfiguration::internalPage();
	}

	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	protected function main()
	{
		$this->checkPosted();
		$database = gGetDb();
		$request = $this->getRequest($database);

		$commentText = WebRequest::postString('comment');
		if ($commentText === false || $commentText == '') {
			$this->redirect('viewRequest', array('id' => $request->getId()));

			return;
		}

		//Look for and detect IPv4/IPv6 addresses in comment text, and warn the commenter.
		$ipv4Regex = '/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/';
		$ipv6Regex = '/(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))/';

		$overridePolicy = WebRequest::postBoolean('privpol-check-override');

		if ((preg_match($ipv4Regex, $commentText) || preg_match($ipv6Regex, $commentText)) && !$overridePolicy) {
			$this->assign("request", $request);
			$this->assign("comment", $commentText);
			$this->setTemplate("privpol-warning.tpl");

			return;
		}

		$visibility = WebRequest::postBoolean('adminOnly') ? 'admin' : 'user';

		$comment = new Comment();
		$comment->setDatabase($database);

		$comment->setRequest($request->getId());
		$comment->setVisibility($visibility);
		$comment->setUser(User::getCurrent()->getId());
		$comment->setComment($commentText);

		$comment->save();

		Notification::commentCreated($comment);
		$this->redirect('viewRequest', null, array('id' => $request->getId()));
	}
}