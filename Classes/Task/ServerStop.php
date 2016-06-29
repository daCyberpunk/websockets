<?php
namespace TYPO3\CMS\Websockets\Task;

use \TYPO3\CMS\Scheduler\Task\AbstractTask;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This task kills PHP daemon which provides the WebSockets technology.
 */
class ServerStop extends AbstractTask {
	public function execute() {
		GeneralUtility::makeInstance('TYPO3\\CMS\\Websockets\\Server')->stop();
		return TRUE;
	}
}