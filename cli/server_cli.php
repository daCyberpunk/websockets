<?php
if (!(defined('TYPO3_cliMode') &&
	  (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_CLI) &&
	  (basename(PATH_thisScript) === 'cli_dispatch.phpsh')

)
) {
	die('This script must be included by the "CLI module dispatcher"');
}

// Call the functionality
$serverObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Websockets\\ServerCommand');
$serverObj->cli_main($_SERVER['argv']);
