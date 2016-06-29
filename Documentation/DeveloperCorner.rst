==================
Developer Corner
==================

Target group: **Developers**


Note:
----
1. To START/STOP WS Server - use the corresponding tasks in TYPO3 Scheduler.
2. After installing (and testing) - you should remove the lines related to 'ConnectionTest' class from ext_localconf.php of the extension.
The class, mentioned above, you should use as an example for your own hooks.
3. And about your WebSocket hooks - keep in mind that WS_Server is the Typo3, running in CLI mode! (do not worry, $GLOBALS['TYPO3_DB'] is available;)
4. To fall back on flash that emulates WebSockets (for old browsers) - don't forget to include a corresponding static configuration file called 'Websockets' into your template.


Notice:
------
1. This extension uses "Ratchet WebSockets for PHP" - http://socketo.me/.


Known problems:
--------------

1. WS server does not start:
	- BE-user '_cli_lowlevel' does not exist.
2. After some idle time, FE-user can't be authenticated:
	- currently working on this issue...
