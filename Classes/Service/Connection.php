<?php
namespace TYPO3\CMS\Websockets\Service;

use \Ratchet\MessageComponentInterface;
use \Ratchet\ConnectionInterface;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use \TYPO3\CMS\Frontend\Utility\EidUtility;

/**
 * Class Connection provides processing of all WS-requests
 */
class Connection implements MessageComponentInterface {
	protected $clients;
	protected $fe_users;

	private $hookObjects = array();

	public function __construct() {
		$this->clients = new \SplObjectStorage();
		$this->fe_users = new \ArrayObject();

		// Collect hooks
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['websockets']['connection'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['websockets']['connection'] as $classRef) {
				$this->hookObjects[] = GeneralUtility::getUserObj($classRef);
			}
		}

		$GLOBALS['BE_USER']->writelog(4, 0, 0, 0, 'WebSockets Server has been started successfully', '');
	}

	public function onOpen(ConnectionInterface $connection) {
		// Save current client
		$this->clients->attach($connection);

		// Try to authenticate the client as FE-user
		try {
			$fe_user = $this->initFeUser($connection);
			$this->fe_users->offsetSet($connection->resourceId, $fe_user);
		} catch (\Exception $e) {
			// Client can't be authenticated
			$fe_user = FALSE;
		}

		$this->applyHooks(__FUNCTION__, $connection, $fe_user);
	}

	public function onMessage(ConnectionInterface $connection, $message) {
		foreach($this->clients as $client){
			$client->send('blub');
		}
		if ($this->clients->contains($connection)) {
			// Get FE-user for corresponding connection
			if ($this->fe_users->offsetExists($connection->resourceId)) {
				$fe_user = $this->fe_users->offsetGet($connection->resourceId);
			} else {
				$fe_user = FALSE;
			}
			$this->applyHooks(__FUNCTION__, $connection, $message, $fe_user);
		} else { // Close the connection
			$connection->close();
		}
	}

	public function onError(ConnectionInterface $connection, \Exception $e) {
		$GLOBALS['BE_USER']->writelog(4, 0, 2, 0, "An error has occurred: {$e->getMessage()}", '');
		$connection->close();
	}

	public function onClose(ConnectionInterface $connection) {
		if ($this->fe_users->offsetExists($connection->resourceId)) {
			$fe_user = $this->fe_users->offsetGet($connection->resourceId);
			$this->fe_users->offsetUnset($connection->resourceId);
		} else {
			$fe_user = FALSE;
		}
		$this->clients->detach($connection);
		$this->applyHooks(__FUNCTION__, $connection, $fe_user);
	}

	/**
	 * Provide initialization of connected client as of TSFE-user.
	 * Used two ways of GP-authentication altogether:
	 *        - by FE_SESSION_KEY
	 *        - by using cookies and verification code (vC) (flash mode) (Note: $TYPO3_CONF_VARS['SYS']['cookieHttpOnly'] must be set to '0')
	 * @param object $connection Connection interface object
	 * @return object of TSFE user
	 * @throws \Exception If user can't be initialized
	 */
	private function initFeUser($connection) {
		// Set 'FE_SESSION_KEY'
		$FE_SESSION_KEY = $connection->WebSocket->request->getQuery()->get('FE_SESSION_KEY');
		if ($FE_SESSION_KEY) {
			GeneralUtility::_GETset($FE_SESSION_KEY, 'FE_SESSION_KEY');
		}
		// Set 'vC'
		$vC = $connection->WebSocket->request->getQuery()->get('vC');
		if ($vC) {
			GeneralUtility::_GETset($vC, 'vC');
			$GLOBALS['CLIENT']['BROWSER'] = 'flash';
		}
		// Set cookie
		$cookieName = FrontendUserAuthentication::getCookieName();
		if ($FE_SESSION_KEY) {
			$cookieValue = explode('-', $FE_SESSION_KEY, 2);
			$cookieValue = $cookieValue[0];
		} else { // Try to get cookie from request cookies (if any)
			$cookieValue = $connection->WebSocket->request->getCookie($cookieName);
		}
		if (!isset($_COOKIE[$cookieName]) && $cookieValue) {
			$_COOKIE[$cookieName] = $cookieValue;
			if (isset($_SERVER['HTTP_COOKIE'])) {
				$_SERVER['HTTP_COOKIE'] .= ';' . $cookieName . '=' . $cookieValue;
			}
		}

		// TODO: retrieve client IP and setUp $_SERVER['REMOTE_ADDR']
		$GLOBALS['TYPO3_CONF_VARS']['FE']['lockIP'] = FALSE;
		$fe_user = EidUtility::initFeUser();

		if ($fe_user->user) { // Authenticated successfully
			return $fe_user;
		} else {
			throw new \Exception('FE-user can not be authenticated.');
		}
	}

	private function applyHooks($method_name) {
		// Get additional arguments
		$with_args = (func_num_args() > 1);
		if ($with_args) {
			$args = func_get_args();
			array_shift($args);
		} else {
			$args = array();
		}
		// Apply hooks
		foreach ($this->hookObjects as $hookObject) {
			if (method_exists($hookObject, $method_name)) {
				if ($with_args) {
					call_user_func_array(array($hookObject, $method_name), $args);
				} else {
					$hookObject->$method_name();
				}
			}
		}
	}
}