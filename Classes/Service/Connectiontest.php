<?php
namespace TYPO3\CMS\Websockets\Service;

use \Ratchet\ConnectionInterface;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * ConnectionTest
 */
class Connectiontest {
	protected $fe_users;

	public function __construct() {

	}

	public function onOpen(ConnectionInterface $connection, $fe_user) {
		$hereDoc = function ($a) {
			return $a;
		};

		$message = <<<"EOT"
ServerSide: Connection is opened!
ID:			{$connection->resourceId};
FE-User:	{$hereDoc((is_object($fe_user) ? $fe_user->user['username'] : 'false'))}
EOT;
		$connection->send($message);
	}

	public function onMessage(ConnectionInterface $connection, $message, $fe_user) {
		$hereDoc = function ($a) {
			return $a;
		};

		$message = <<<"EOT"
ServerSide: Message received!
ID:				{$connection->resourceId};
MessageBody:	{$message};
FE-User:		{$hereDoc((is_object($fe_user) ? $fe_user->user['username'] : 'false'))}
EOT;
		$connection->send($message);
	}

	public function onClose($connection, $fe_user) {
		// "ABCD!!! Connection closed! ID: {$connection->resourceId};"
	}

	public static function addPageHeaderData(&$params, &$ref) {
		$ref->addJsInlineCode(
			'WS_ConnectionTest',
			// Authentication parameters for fe-users:
			GeneralUtility::minifyJavaScript(
				'var GP_auth = ' . json_encode(
					array(
						'FE_SESSION_KEY' => rawurlencode(
							$GLOBALS['TSFE']->fe_user->cookieId . '-' .
							md5(
								$GLOBALS['TSFE']->fe_user->cookieId . '/' .
								$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
							)
						),
						'vC'             => $GLOBALS['TSFE']->fe_user->veriCode()
					)
				) . ',' .
				'WS_isRunning = ' . (GeneralUtility::makeInstance('TYPO3\\CMS\\Websockets\\Server')->isRunning() ? 'true' : 'false') . ';'
			) . "\n"

			// Connection test:
			. <<<'EOT'
if (WS_isRunning) {
	// Set protocol
	var protocol = 'ws';
	if (window.location.protocol === 'https:') {
		protocol += 's';
	}
	// Establish connection
	var ws_conn = new WebSocket(protocol + '://' + window.location.hostname + ':8080/?FE_SESSION_KEY=' + GP_auth.FE_SESSION_KEY + '&vC=' + GP_auth.vC);
	ws_conn.onopen = function(e) {
		console.log("Connection established!\nNow you may use WebSocket 'ws_conn' object for testing...");
		ws_conn.send('Hello World!');
	};
	ws_conn.onmessage = function(e) {
		console.log(e.data);
	};

} else {
    console.log('WebSockets in testing-mode. WS_Server is not started. Please, start the server from AdminPanel->Scheduler section.');
}
EOT
			,
			FALSE
		);
	}
}