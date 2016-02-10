<?php

namespace Waca\Pages;

use Comment;
use Logger;
use Notification;
use Request;
use SessionAlert;
use User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PageEditComment extends PageBase
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
		switch ($this->getRouteName()) {
			case 'editOthers':
				return SecurityConfiguration::adminPage();
			default:
				return SecurityConfiguration::internalPage();
		}
	}

	/**
	 * Main function for this page, when no specific actions are called.
	 * @throws ApplicationLogicException
	 */
	protected function main()
	{
		$commentId = WebRequest::getInt('id');
		if ($commentId === null) {
			throw new ApplicationLogicException('Comment ID not specified');
		}

		$database = $this->getDatabase();

		/** @var Comment $comment */
		$comment = Comment::getById($commentId, $database);
		if ($comment === false) {
			throw new ApplicationLogicException('Comment not found');
		}

		$currentUser = User::getCurrent();
		if ($comment->getUser() !== $currentUser->getId() && !$this->barrierTest('editOthers')) {
			throw new AccessDeniedException();
		}

		$request = Request::getById($comment->getRequest(), $database);

		if (WebRequest::wasPosted()) {
			$newComment = WebRequest::postString('newcomment');
			$visibility = WebRequest::postString('visibility');

			if ($visibility !== 'user' && $visibility !== 'admin') {
				throw new ApplicationLogicException('Comment visibility is not valid');
			}

			$comment->setComment($newComment);
			$comment->setVisibility($visibility);

			$comment->save();

			Logger::editComment($database, $comment, $request);
			Notification::commentEdited($comment, $request);
			SessionAlert::success("Comment has been saved successfully");

			$this->redirect('viewRequest', null, array('id' => $comment->getRequest()));
		}
		else {
			$this->assign('comment', $comment);
			$this->assign('request', $request);
			$this->assign('user', User::getById($comment->getUser(), $database));
			$this->setTemplate('edit-comment.tpl');
		}
	}
}