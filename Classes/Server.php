<?php
namespace TYPO3\CMS\Websockets;

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * WebSockets server controller
 */
class Server {

	/**
	 * WebSocket server process identifier
	 * @var NULL|int
	 */
	public $pid = NULL;

	private $pid_fileName;

	public function __construct() {
		$this->pid_fileName = ExtensionManagementUtility::extPath('websockets', 'cli_dispatch.phpsh.pid');
		$this->definePid();

		if (!$this->isRunning() && file_exists($this->pid_fileName)) {
			unlink($this->pid_fileName);
		}
	}

	private function definePid() {
		// Get from pid-file
		if (file_exists($this->pid_fileName)) {
			$pid = (int)trim(file_get_contents($this->pid_fileName));
			if (($pid !== 0) && self::process($pid, 'detect')) {
				$this->pid = $pid;
				return;
			}
		}
		if (!self::isWindowsOS()) { // Additionally check by unix-command
			$cli_dispatch = PATH_typo3 . 'cli_dispatch.phpsh';
			$pid = intval(
				shell_exec(
					"ps ax | grep '" .
					preg_replace(
						array(
							'/^\/.*?\/(typo3)/', // Remove portion of the path before 'typo3' directory
							'/(^.)/' // Wrap first character with brackets
						),
						array(
							'$1',
							'[$1]'
						),
						$cli_dispatch
					) .
					" websockets start' | awk '{print $1}' | head -1"
				)
			);
			if ($pid !== 0) {
				$this->pid = $pid;
				file_put_contents($this->pid_fileName, $this->pid);
				return;
			}
		}
	}

	public function isRunning() {
		return ($this->pid !== NULL);
	}

	public function getElapsedTime() {
		$eTime = 0;
		if (file_exists($this->pid_fileName)) { // Based on pid-file
			$eTime = (time() - filemtime($this->pid_fileName));
		}
		return $eTime;
	}

	public function start($waitForResult = FALSE) {
		if ($this->isRunning()) {
			return TRUE;
		}

		$cli_dispatch = PATH_typo3 . 'cli_dispatch.phpsh';
		if (!file_exists($cli_dispatch)) {
			return FALSE;
		}

		// Make a command
		$script = array(
			'cliKey' => 'websockets',
			'argv'   => array(
				'action' => 'start'
			)
		);
		if (self::isWindowsOS()) {
			$cli_dispatch = 'php.exe ' . str_replace('/', '\\', $cli_dispatch);
		}
		$command = "{$cli_dispatch} {$script['cliKey']} " . implode(' ', $script['argv']);

	//	DebuggerUtility::var_dump($command);

		self::execute($command);

		if ($waitForResult) {
			// Sleep for few seconds (cause we can't keep track of the moment when the server will be ready)
			$i = 3;
			do {
				sleep($i);
				$this->definePid();
			} while (!$this->isRunning() && (--$i > 0));

			// Now we can return the result
			return $this->isRunning();
		}

		return NULL;
	}

	public function stop() {
		if (!$this->isRunning()) {
			DebuggerUtility::var_dump('was not running');
			return;
		}
		DebuggerUtility::var_dump('was running');
		self::process($this->pid, 'kill');

		$this->pid = NULL;
		if (file_exists($this->pid_fileName)) {
			unlink($this->pid_fileName);
		}
	}

	// --------------------------------------------------------------------------------------
	// Helpers
	// --------------------------------------------------------------------------------------

	public static function isWindowsOS() {
		static $result = NULL;
		if (!is_null($result)) {
			return $result;
		}
//		$result = (stristr(PHP_OS, 'win') && !stristr(PHP_OS, 'darwin'));
		$result = (TYPO3_OS === 'WIN');
		return $result;
	}

	public static function process($pid, $action) {
		// Method to retrieve corresponding process by id
		$getWin32_Process = function ($pid) {
			$wmi = new \COM('winmgmts://');
			return $wmi->ExecQuery('SELECT * FROM Win32_Process WHERE ProcessId = \'' . $pid . '\'');
		};

		switch ($action) {
			case 'detect':
				return (self::isWindowsOS()
						?
						(count($getWin32_Process($pid)) > 0)
						: // *nix OS (expected)
						posix_kill($pid, 0));
				break;
			case 'kill':
				if (self::isWindowsOS()) {
					foreach ($getWin32_Process($pid) as $process) {
						$process->Terminate();
					}
				} else { // *nix OS (expected)
					posix_kill($pid, 9);
				}
				return TRUE;
			default:
				throw new \Exception('Action is undefined!');
		}
	}

	public static function execute($command) {
		if (self::isWindowsOS()) {
			$WshShell = new \COM('WScript.Shell');
			$WshShell->Run($command, 0, FALSE);
		} else { // *nix OS (expected)
			//DebuggerUtility::var_dump($command . 'tttest.txt');
			shell_exec("{$command} > /dev/null 2>/dev/null &");
		}
	}
}