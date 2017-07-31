<?php
/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\RansomwareProtection\Notification;


use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

	/** @var IFactory */
	protected $l10nFactory;

	/** @var IUserManager */
	protected $userManager;

	/** @var IManager */
	protected $notificationManager;

	/** @var IURLGenerator */
	protected $urlGenerator;

	/**
	 * @param IFactory $l10nFactory
	 * @param IUserManager $userManager
	 * @param IManager $notificationManager
	 * @param IURLGenerator $urlGenerator
	 */
	public function __construct(IFactory $l10nFactory, IUserManager $userManager, IManager $notificationManager, IURLGenerator $urlGenerator) {
		$this->l10nFactory = $l10nFactory;
		$this->userManager = $userManager;
		$this->notificationManager = $notificationManager;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @param INotification $notification
	 * @param string $languageCode The code of the language that should be used to prepare the notification
	 * @return INotification
	 * @throws \InvalidArgumentException When the notification was not prepared by a notifier
	 */
	public function prepare(INotification $notification, $languageCode) {
		if ($notification->getApp() !== 'ransomware_protection') {
			// Not my app => throw
			throw new \InvalidArgumentException();
		}

		// Read the language from the notification
		$l = $this->l10nFactory->get('ransomware_protection', $languageCode);

		switch ($notification->getSubject()) {
			// Deal with known subjects
			case 'upload_blocked':
				$params = $notification->getSubjectParameters();

				$notification->setParsedSubject($l->t('File “%1$s” could not be uploaded because it matched “%2$s” which is identified as a ransomware file.', $params))
					->setParsedMessage(
						$l->t(
							'The file you tried to upload matches the naming pattern of a ransomware/virus. '
							. 'If you are sure that your device is not affected, you can temporarily disable the protection.'
							. 'Otherwise you can request help from your admin, so they reach out to you.'
						)
					);

				$pauseAction = $notification->createAction();
				$pauseAction->setLabel('pause')
					->setParsedLabel($l->t('Temporarily disable protection'))
					->setLink($this->urlGenerator->linkToRouteAbsolute('ransomware_protection.Api.pauseForAnHour'), 'DELETE');
				$notification->addAction($pauseAction);

				$helpAction = $notification->createAction();
				$helpAction->setLabel('help')
					->setParsedLabel($l->t('I need help!'))
					->setLink($this->urlGenerator->linkToRouteAbsolute('ransomware_protection.Api.requestHelp'), 'POST');
				$notification->addAction($helpAction);

				return $notification;

			case 'help_requested':
				$victim = $this->userManager->get($notification->getObjectId());

				if (!$victim instanceof IUser) {
					$this->notificationManager->markProcessed($notification);
					throw new \InvalidArgumentException('User is deleted');
				}

				$notification->setParsedSubject($l->t('User %s may be infected with ransomware and is asking for your help.', [$victim->getDisplayName()]))
					->setRichSubject(
						'User {user} may be infected with ransomware and is asking for your help.', [
							'user' => [
								'id' => $victim->getUID(),
								'name' => $victim->getDisplayName(),
							]
						]
					);

				$imHelpingAction = $notification->createAction();
				$imHelpingAction->setLabel('help')
					->setParsedLabel($l->t('I will help'))
					->setLink(
						$this->urlGenerator->linkToRouteAbsolute('ransomware_protection.Api.imHelping', ['victim' => $victim->getUID()]),
						'DELETE'
					);
				$notification->addAction($imHelpingAction);

				return $notification;

			default:
				// Unknown subject => Unknown notification => throw
				throw new \InvalidArgumentException();
		}
	}
}
