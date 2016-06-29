<?php
namespace TYPO3\CMS\Websockets\Utility;

/**
 * Determines whether the script is already running or not.
 */
class ThisProcess {
	/**
	 * The filename of the process
	 * @var string $filename
	 */
	protected $filename = '';
	/**
	 * Whether script is already running
	 * @var boolean $already_running
	 */
	public $already_running = FALSE;
	/**
	 * Returns the PID of the process
	 * @var integer $pid
	 */
	public $pid = 0;

	/**
	 * The main function that does it all
	 * @param string $directory The directory where the process PID file goes to
	 */
	public function __construct($directory = '.') {
		if (!is_writable($directory)) {
			die("Directory {$directory} is not writable!");
		}

		$this->filename = ($directory . '/' . basename($_SERVER['PHP_SELF']) . '.pid');

		if (file_exists($this->filename)) {
			$this->pid = (int)trim(file_get_contents($this->filename));
			if (stristr(PHP_OS, 'win') && !stristr(PHP_OS, 'darwin')) {
				$wmi = new \COM('winmgmts://');
				$processes = $wmi->ExecQuery('SELECT * FROM Win32_Process WHERE ProcessId = \'' . $this->pid . '\'');
				$this->already_running = (count($processes) > 0);
			} else {
				$this->already_running = (posix_kill($this->pid, 0));
			}
		}

		if (!$this->already_running) {
			$this->pid = getmypid();
			file_put_contents($this->filename, $this->pid);
		}
	}

	public function __destruct() {
		if (is_writable($this->filename) &&
			!$this->already_running
		) {
			unlink($this->filename);
		}
		return TRUE;
	}
}
