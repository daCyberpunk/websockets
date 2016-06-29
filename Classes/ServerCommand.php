<?php
namespace TYPO3\CMS\Websockets;

use \TYPO3\CMS\Core\Controller\CommandLineController;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Websockets\Utility\ThisProcess;
use \TYPO3\CMS\Websockets\Service\Connection;
use \Ratchet\Server\IoServer;
use \Ratchet\WebSocket\WsServer;

class ServerCommand extends CommandLineController {
	public function __construct() {
		parent::__construct();
		$this->cli_help = array_merge(
			$this->cli_help,
			array(
				'name'        => 'websockets_server',
				'synopsis'    => 'toolkey ###OPTIONS###',
				'description' => 'This script provides support for web-socket daemon to stay alive.',
				'examples'    => 'typo3/cli_dispatch.phpsh websockets start',
				'author'      => '(c) 2013 Taras Zakus <Taras.Zakus@pixabit-interactive.de>'
			)
		);
	}

	private function init() {
		$this->cli_validateArgs();
	}

	public function cli_main($argv) {
		$this->init();

		$shellExitCode = 0;
		try {
			$action = 'action_' . $this->getAction();
			if (!method_exists($this, $action)) {
				$action = 'cli_help';
			}
			$this->$action();
		} catch (\Exception $e) {
			$shellExitCode = 1;
		}

		return $shellExitCode;
	}

	// --------------------------------------------------------------------------------------
	// Actions
	// --------------------------------------------------------------------------------------

	private function action_start() {
		$extPath = ExtensionManagementUtility::extPath('websockets');
		// Check whether websockets-server is already running
		$thisProcess = new ThisProcess($extPath);
		if (!$thisProcess->already_running) { // Start websockets-server
			require $extPath . 'vendor/autoload.php';
			IoServer::factory(
				new WsServer(
					new Connection()
				),
				8080
			)->run();
		} else {
			$GLOBALS['BE_USER']->writelog(4, 0, 1, 0, 'Attempting to start WebSockets Server while it is already running', '');
		}
	}

	// --------------------------------------------------------------------------------------
	// Helpers
	// --------------------------------------------------------------------------------------

	private function getAction() {
		return (string)$this->cli_args['_DEFAULT'][1];
	}
}

?>