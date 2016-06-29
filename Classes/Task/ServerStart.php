<?php
namespace TYPO3\CMS\Websockets\Task;

use \TYPO3\CMS\Scheduler\Task\AbstractTask;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This task starts PHP daemon which provides the WebSockets technology.
 */
class ServerStart extends AbstractTask {
	public function execute() {
		GeneralUtility::makeInstance('TYPO3\\CMS\\Websockets\\Server')->start();
		return TRUE;
	}
}