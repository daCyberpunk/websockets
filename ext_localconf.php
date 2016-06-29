<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (TYPO3_MODE === 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['TYPO3\\CMS\\Websockets\\Task\\ServerStart'] = array(
		'extension'   => $_EXTKEY,
		'title'       => 'WS_Server START',
		'description' => 'This task starts PHP daemon which provides the WebSockets technology.'
	);

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['TYPO3\\CMS\\Websockets\\Task\\ServerStop'] = array(
		'extension'   => $_EXTKEY,
		'title'       => 'WS_Server STOP',
		'description' => 'This task kills PHP daemon which provides the WebSockets technology.'
	);

	if (defined('TYPO3_cliMode')) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array(
			'EXT:' . $_EXTKEY . '/cli/server_cli.php',
			'_CLI_scheduler'
		);
	}
}

// Allow to transfer cookies via WebSockets (required for TSFE-user initialization)
$GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieSecure'] = 0;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieHttpOnly'] = FALSE;

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['websockets']['connection'])) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['websockets']['connection'] = array();
}

### Testing-hooks class (Note: the following lines are for testing purposes only and should be removed in production)
$connectionTest_class = 'TYPO3\\CMS\\Websockets\\Service\\Connectiontest';

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['websockets']['connection'][] = $connectionTest_class;
if (TYPO3_MODE === 'FE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] = $connectionTest_class . '->addPageHeaderData';
/*	$rrr = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($connectionTest_class);

	$pid_fileName = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('websockets', 'testfile.txt');
	file_put_contents($pid_fileName, $rrr->addPageHeaderData());*/
}
###
?>